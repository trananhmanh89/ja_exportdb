<?php

use Ifsnop\Mysqldump\Mysqldump;
use Joomla\Database\DatabaseFactory;
use Joomla\Database\DatabaseDriver;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Http\HttpFactory;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

define('JPATH_ROOT', __DIR__);

require_once JPATH_ROOT . '/vendor/autoload.php';

class App
{
    /**
     * @var Input
     */
    protected $input;

    /**
     * @var DatabaseDriver
     */
    protected $db;
    /**
     * @var Registry
     */
    protected $config;
    protected $base_path;
    protected $folder;
    protected $project;
    protected $uri_root;
    protected $uri_current;
    protected $error = array();
    const WITH_PREFIX = true;

    public function run()
    {
        $this->input = new Input();
        $this->config = $this->getConfig();
        $this->uri_root = $this->getUriRoot();
        $this->uri_current = $this->getUriCurrent();
        $this->base_path = $this->getBasePath();
        $this->profiles = $this->getProfiles();
        $this->profileConfig = $this->getProfileConfig();
        $this->folder = $this->input->get('folder', '', 'raw');
        if ($this->folder) {
            $this->project = $this->getProject();
        }

        $task = $this->input->get('task');

        if ($task === 'delete_extension') {
            $this->deleteExtension();
        }

        if ($task === 'get_list_svn_folder') {
            $this->getListSvnFolder();
        }

        if ($task === 'new_svn_folder') {
            $this->newSvnFolder();
        }

        if ($task === 'save_config') {
            $this->saveConfig();
        }

        if ($task === 'commit') {
            $this->commit();
        }

        if ($task === 'export') {
            $this->export();
        }

        if ($task === 'export_prefix') {
            $this->export(self::WITH_PREFIX);
        }

        if ($task === 'new_profile') {
            $this->newProfile();
        }

        if ($task === 'change_profile') {
            $this->changeProfile();
        }

        if ($task === 'delete_profile') {
            $this->deleteProfile();
        }

        if ($task === 'save_profile') {
            $this->saveProfile();
        }

        $view = $this->input->get('view');

        switch ($view) {
            case 'config':
                require JPATH_ROOT . '/tmpl/config.php';
                break;

            case 'guide':
                require JPATH_ROOT . '/tmpl/guide.php';
                break;

            default:
                require JPATH_ROOT . '/tmpl/default.php';
                break;
        }
    }

    protected function deleteExtension()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->delete($db->qn('#__extensions'))
            ->where($db->qn('extension_id') . '=' . $db->q($this->input->getInt('extension_id', 0)));

        $db->setQuery($query)->execute();

        header('Location: ' . $this->uri_current);
    }

    protected function getJVersion()
    {
        $file = $this->base_path . '/' . $this->folder  . '/libraries/src/Version.php';
        $content = file_get_contents($file);
        preg_match('/const.*?MAJOR_VERSION.*?(\d+)/', $content, $match);

        return (int) $match[1];
    }

    protected function newSvnFolder()
    {
        $result = new Registry();
        $connected = $this->checkSvnConnection();
        if (!$connected) {
            $result->set('error', 'Could not connect to svn server. Please check your config.');
            die($result->toString());
        }

        $name = $this->input->get('name');
        $config = $this->config;
        $user = $config->get('svn_user');
        $pass = $config->get('svn_pass');
        $host = $config->get('svn_host');
        $url = trim($host, '/') . "/$name";

        $res = $this->connectToSvnHttp($url, $user, $pass);
        if ($res->code === 404) {
            $user = escapeshellarg($user);
            $pass = escapeshellarg($pass);
            $url = escapeshellarg($url);
            $msg = escapeshellarg('New folder');
            $command = "svn mkdir -m $msg --username $user --password $pass $url 2>&1";

            exec($command, $data);
            if (count($data) === 2) {
                $result->set('success', true);
            } else {
                $result->set('error', 'Create error');
            }
        } else {
            $result->set('error', 'Folder already exists');
        }

        die($result->toString());
    }

    protected function getListSvnFolder()
    {
        $result = new Registry();
        $connected = $this->checkSvnConnection();
        if (!$connected) {
            $result->set('error', 'Could not connect to svn server. Please check your config.');
            die($result->toString());
        }

        $config = $this->config;
        $user = escapeshellarg($config->get('svn_user'));
        $pass = escapeshellarg($config->get('svn_pass'));
        $host = escapeshellarg($config->get('svn_host'));
        $command = "svn list --username $user --password $pass $host";
        exec($command, $data);

        $list = array_filter($data, function($str) {
            return preg_match('/^.*?\/$/', $str);
        });

        $list = array_map(function($str) {
            return substr($str, 0, -1);
        }, $list);

        $result->set('list', array_values($list));

        die($result->toString());
    }

    protected function checkSvnConnection()
    {
        $config = $this->config;
        $user = $config->get('svn_user');
        $pass = $config->get('svn_pass');
        $host = $config->get('svn_host');

        $res = $this->connectToSvnHttp($host, $user, $pass);
        if (isset($res->code) && $res->code === 200) {
            return true;
        }

        return false;
    }

    protected function connectToSvnHttp($host, $user, $pass)
    {
        $token = base64_encode("$user:$pass");
        $headers = array(
            'Authorization' => "Basic $token"
        );
        $http = HttpFactory::getHttp();
        
        try {
            return $http->get($host, $headers);
        } catch (Exception $e) {
            return false;
        }
    }

    protected function getConfig()
    {
        $fileConfig = JPATH_ROOT . '/config/config.json';
        if (!file_exists($fileConfig)) {
            return $this->saveConfig();
        }

        $config = new Registry();
        $config->loadFile(JPATH_ROOT . '/config/config.json');

        return $config;
    }

    protected function getHidden()
    {
        $db = $this->getDbo();
        $dbPrefix = $db->getPrefix();
        $query = "SELECT `type`, `element`, `folder` FROM `#__extensions`";
        // die('<pre>'.print_r(json_encode($db->setQuery($query)->loadObjectList()), 1).'</pre>');

        $query = "SHOW TABLES LIKE " . $db->q("$dbPrefix%");
        $str = json_encode($db->setQuery($query)->loadColumn());
        $str = str_replace($dbPrefix, '#__', $str);
        
        $j4 = json_decode('["#__action_log_config","#__action_logs","#__action_logs_extensions","#__action_logs_users","#__assets","#__associations","#__banner_clients","#__banner_tracks","#__banners","#__categories","#__contact_details","#__content","#__content_frontpage","#__content_rating","#__content_types","#__contentitem_tag_map","#__csp","#__extensions","#__fields","#__fields_categories","#__fields_groups","#__fields_values","#__finder_filters","#__finder_links","#__finder_links_terms","#__finder_logging","#__finder_taxonomy","#__finder_taxonomy_map","#__finder_terms","#__finder_terms_common","#__finder_tokens","#__finder_tokens_aggregate","#__finder_types","#__history","#__languages","#__mail_templates","#__menu","#__menu_types","#__messages","#__messages_cfg","#__modules","#__modules_menu","#__newsfeeds","#__overrider","#__postinstall_messages","#__privacy_consents","#__privacy_requests","#__redirect_links","#__schemas","#__session","#__tags","#__template_overrides","#__template_styles","#__ucm_base","#__ucm_content","#__update_sites","#__update_sites_extensions","#__updates","#__user_keys","#__user_notes","#__user_profiles","#__user_usergroup_map","#__usergroups","#__users","#__viewlevels","#__webauthn_credentials","#__workflow_associations","#__workflow_stages","#__workflow_transitions","#__workflows"]');
        $j3 = json_decode('["#__action_log_config","#__action_logs","#__action_logs_extensions","#__action_logs_users","#__assets","#__associations","#__banner_clients","#__banner_tracks","#__banners","#__categories","#__contact_details","#__content","#__content_frontpage","#__content_rating","#__content_types","#__contentitem_tag_map","#__core_log_searches","#__extensions","#__fields","#__fields_categories","#__fields_groups","#__fields_values","#__finder_filters","#__finder_links","#__finder_links_terms0","#__finder_links_terms1","#__finder_links_terms2","#__finder_links_terms3","#__finder_links_terms4","#__finder_links_terms5","#__finder_links_terms6","#__finder_links_terms7","#__finder_links_terms8","#__finder_links_terms9","#__finder_links_termsa","#__finder_links_termsb","#__finder_links_termsc","#__finder_links_termsd","#__finder_links_termse","#__finder_links_termsf","#__finder_taxonomy","#__finder_taxonomy_map","#__finder_terms","#__finder_terms_common","#__finder_tokens","#__finder_tokens_aggregate","#__finder_types","#__languages","#__menu","#__menu_types","#__messages","#__messages_cfg","#__modules","#__modules_menu","#__newsfeeds","#__overrider","#__postinstall_messages","#__privacy_consents","#__privacy_requests","#__redirect_links","#__schemas","#__session","#__tags","#__template_styles","#__ucm_base","#__ucm_content","#__ucm_history","#__update_sites","#__update_sites_extensions","#__updates","#__user_keys","#__user_notes","#__user_profiles","#__user_usergroup_map","#__usergroups","#__users","#__utf8_conversion","#__viewlevels"]');
        $tables = array_merge($j3, $j4);
        die('<pre>'.print_r(json_encode($tables), 1).'</pre>');
        // die('<pre>'.print_r($str, 1).'</pre>');
    }

    protected function getProfileConfig()
    {
        $profile = $this->input->get('profile');
        $file = JPATH_ROOT . "/profiles/$profile.json";
        $config = new Registry();

        if ($profile && file_exists($file)) {
            $config->loadFile($file);
        }

        return $config;
    }

    protected function saveProfile()
    {
        $profile = $this->input->get('profile');
        $demo = $this->input->get('demo', array(), 'array');
        $qs = $this->input->get('qs', array(), 'array');

        $data = new Registry();
        $data->set('demo', $demo);
        $data->set('qs', $qs);
        $json = $data->toString();
        
        File::write(JPATH_ROOT . "/profiles/$profile.json", $json);
        header('Location: ' . $this->uri_current);
    }

    protected function deleteProfile()
    {
        $profile = $this->input->get('profile');
        $folder = $this->input->get('folder', '', 'raw');

        File::delete(JPATH_ROOT . "/profiles/$profile.json");

        header('Location: ' . $this->uri_root . "?folder=$folder");
    }

    protected function changeProfile()
    {
        $profile = $this->input->get('profile');
        $folder = $this->input->get('folder', '', 'raw');

        header('Location: ' . $this->uri_root . "?folder=$folder&profile=$profile");
    }

    protected function getProfiles()
    {
        Folder::create(JPATH_ROOT . '/profiles');
        $files = Folder::files(JPATH_ROOT . '/profiles');
        $profiles = array_map(function ($file) {
            return File::stripExt($file);
        }, $files);

        return $profiles;
    }

    protected function newProfile()
    {
        $profile = $this->input->get('new_profile');
        $folder = $this->input->get('folder', '', 'raw');

        $path = JPATH_ROOT . "/profiles/$profile.json";
        if (!file_exists($path)) {
            $str = '{}';
            File::write($path, $str);
        }

        header('Location: ' . $this->uri_root . "?folder=$folder&profile=$profile");
    }

    protected function commit()
    {
        if (!$this->checkSvnConnection()) {
            die(json_encode(array('error' => 'SVN connect error')));
        }

        $file = $this->base_path . '/' . $this->folder . '/configuration.php';
        if (!file_exists($file)) {
            die(json_encode(array('error' => 'configuration.php not found')));
        }

        $jversion = $this->getJVersion();
        require_once $file;
        $jConfig = new JConfig;
        $svnFolder = $this->input->get('svn_folder');
        $config = $this->config;
        $user = escapeshellarg($config->get('svn_user'));
        $pass = escapeshellarg($config->get('svn_pass'));
        $url = escapeshellarg($config->get('svn_host') . '/' . $svnFolder);
        $msg = escapeshellarg($this->input->getString('msg', 'export'));
        $target = escapeshellarg(JPATH_ROOT . '/svn/' . $svnFolder);

        $this->initSampleDataFolder($svnFolder);

        $command = "svn co --username $user --password $pass $url $target 2>&1";
        exec($command);

        $files = Folder::files(JPATH_ROOT . '/svn/' . $svnFolder);
        foreach ($files as $file) {
            File::delete(JPATH_ROOT . '/svn/' . $svnFolder . '/' . $file);
        }

        $qsConfig = $this->input->get('qs', array(), 'array');
        $this->exportSampleData(array(
            'file_name' => $jversion === 4 ? 'custom' : 'sample_data',
            'data' => $qsConfig,
            'svn_folder' => $svnFolder,
            'jconfig' => $jConfig,
            'local' => false,
        ));

        $demoConfig = $this->input->get('demo', array(), 'array');
        $this->exportSampleData(array(
            'file_name' => 'demo',
            'data' => $demoConfig,
            'svn_folder' => $svnFolder,
            'jconfig' => $jConfig,
            'local' => false,
        ));

        exec('svn status ' . escapeshellarg(JPATH_ROOT .'/svn/' . $svnFolder) . ' 2>&1', $output);
        if ($output) {
            foreach ($output as $value) {
                if (strpos($value, '?') === 0) {
                    $newFile = trim(substr($value, 1));
                    exec('svn add ' . escapeshellarg($newFile));
                } else if (strpos($value, '!') === 0) {
                    $deletedFile = trim(substr($value, 1));
                    exec('svn rm ' . escapeshellarg($deletedFile));
                }
            }
        }
        
        exec("svn commit --username $user --password $pass -m $msg $target 2>&1", $result);
        $success = false;
        foreach ($result as $value) {
            if (strpos($value, 'Committed') !== false) {
                $success = true;
            }
        }

        if ($success) {
            die(json_encode(array('success' => true)));
        } else {
            die(json_encode(array('error' => 'Commit error')));
        }
    }

    protected function export($withPrefix = false)
    {
        $file = $this->base_path . '/' . $this->folder . '/configuration.php';
        if (!file_exists($file)) {
            die(json_encode(array('error' => 'configuration.php not found')));
        }

        $jversion = $this->getJVersion();
        require_once $file;
        $jConfig = new JConfig;
        $svnFolder = $this->input->get('svn_folder');
        $this->initSampleDataFolder($svnFolder, true);

        $qsConfig = $this->input->get('qs', array(), 'array');
        $this->exportSampleData(array(
            'file_name' => $jversion === 4 ? 'custom' : 'sample_data',
            'data' => $qsConfig,
            'svn_folder' => $svnFolder,
            'jconfig' => $jConfig,
            'local' => true,
            'withPrefix' => $withPrefix,
        ));

        $demoConfig = $this->input->get('demo', array(), 'array');
        $this->exportSampleData(array(
            'file_name' => 'demo',
            'data' => $demoConfig,
            'svn_folder' => $svnFolder,
            'jconfig' => $jConfig,
            'local' => true,
            'withPrefix' => $withPrefix,
        ));

        die(json_encode(array('success' => true)));
    }

    protected function exportSampleData($exportConfig = array())
    {
        $fileName = $exportConfig['file_name'];
        $data = $exportConfig['data'];
        $svnFolder = $exportConfig['svn_folder'];
        $jConfig = $exportConfig['jconfig'];
        $isLocal = $exportConfig['local'];
        $withPrefix = isset($exportConfig['withPrefix']) ? $exportConfig['withPrefix'] : false;

        $host = $jConfig->host;
        $dbName = $jConfig->db;
        $prefix = $jConfig->dbprefix;
        $user = $jConfig->user;
        $pass = $jConfig->password;

        $nodata = json_decode(file_get_contents(JPATH_ROOT . '/config/nodata.json'));
        $nodata = array_map(function($str) use ($prefix) {
            return str_replace('#__', $prefix, $str);
        }, $nodata);

        $includeTables = json_decode($data['project-tables']);
        $includeTables = array_map(function($tbl) use ($prefix) {
            return str_replace('#__', $prefix, $tbl);
        }, $includeTables);

        $settings = array(
            'include-tables' => $includeTables,
            'no-data' => $nodata,
            'add-drop-table' => true,
            'reset-auto-increment' => true,
            'skip-comments' => true,
            'skip-dump-date' => true,
            'default-character-set' => Mysqldump::UTF8MB4,
            'no-autocommit' => false,
        );

        $db = $this->getDbo();
        $tableWheres = array();
        if (!empty($data['extension'])) {
            $tableWheres[$prefix . 'extensions'] = 'extension_id NOT IN (' . implode(',', $data['extension']) . ')';
            $tableWheres[$prefix . 'update_sites_extensions'] = 'extension_id NOT IN (' . implode(',', $data['extension']) . ')';
        }

        $menuItems = array();
        if (!empty($data['menutype'])) {
            $types = (array) $db->q($data['menutype']);
            $tableWheres[$prefix . 'menu_types'] = 'menutype NOT IN (' . implode(',', $types) . ')';
            $menuItems[] = 'menutype NOT IN (' . implode(',', $types) . ')';
        }

        if (!empty($data['menuitem'])) {
            $items = (array) $db->q($data['menuitem']);
            $menuItems[] = 'id NOT IN (' . implode(',', $items) . ')';
        }

        if ($menuItems) {
            $tableWheres[$prefix . 'menu'] = implode(' AND ', $menuItems);
        }

        if (!empty($data['ts'])) {
            $styles = (array) $db->q($data['ts']);
            $tableWheres[$prefix . 'template_styles'] = 'id NOT IN (' . implode(',', $styles) . ')';
        }

        if (!empty($data['table'])) {
            $tbls = array_map(function($tbl) use ($prefix) {
                return str_replace('#__', $prefix, $tbl);
            }, $data['table']);

            $settings['exclude-tables'] = $tbls;
        }

        $tableWheres[$prefix . 'user_usergroup_map'] = '(user_id, group_id) != (42, 8)';

        $dumper = new Mysqldump("mysql:host=$host;dbname=$dbName", $user, $pass, $settings);
        $dumper->setTableWheres($tableWheres);

        $dumper->setTransformTableRowHook(function ($tableName, array $row) use ($prefix, $data) {
            if (!empty($data['home'])) {
                if ($tableName === $prefix . 'menu') {
                    foreach ($data['home'] as $key => $value) {
                        if ($key !== $row['language']) {
                            continue;
                        }

                        if ($row['id'] == $value) {
                            $row['home'] = 1;
                        } else {
                            $row['home'] = 0;
                        }
                    }
                }
            }

            if ($tableName === $prefix . 'users') {
                if ($row['id'] == 42) {
                    $row['password'] = password_hash('joom@admin@vnn', PASSWORD_BCRYPT);
                }
            }

            if ($tableName === $prefix . 'acym_configuration') {
                // fix mysqli syntax error when insert uploadfolder patch
                if ($row['name'] === 'uploadfolder') {
                    $row['value'] = str_replace('\\', '/', $row['value']);
                }
            }
            
            return $row;
        });

        $target = $isLocal
            ? JPATH_ROOT . '/local/' . $svnFolder . '/' . $fileName . '.sql'
            : JPATH_ROOT . '/svn/' . $svnFolder . '/' . $fileName . '.sql';

        try {
            $dumper->start($target);
            if (!$withPrefix) {
                $content = file_get_contents($target);
                $content = str_replace("$prefix", "#__", $content);
                File::write($target, $content);
            }
        } catch (Exception $e) {
            die(json_encode(array('error' => $e->getMessage())));
        }
    }

    protected function initSampleDataFolder($name, $local = false)
    {
        $path = $local ? JPATH_ROOT . '/local/' . $name : JPATH_ROOT . '/svn/' . $name;

        if (is_dir($path)) {
            Folder::delete($path);
        }

        Folder::create($path);
    }

    protected function getProject()
    {
        $db = $this->getDbo();
        if (!$db) {
            return false;
        }

        $project = new stdClass;
        $project->menus = $this->getMenus();
        $project->templateStyles = $this->getTemplateStyles();
        $project->extensions = $this->getExtensions();
        $project->tables = $this->getTables();

        return $project;
    }

    protected function getTables()
    {
        $db = $this->getDbo();
        $prefix = $db->getPrefix();
        $query = "SHOW TABLES LIKE " . $db->q("$prefix%");
        $tables = $db->setQuery($query)->loadColumn();
        $tables = array_map(function($table) use ($prefix) {
            $start = strlen($prefix) - 1;
            return '#_' . substr($table, $start);
        }, $tables);

        return $tables;
    }

    protected function getTemplateStyles()
    {
        $db = $this->getDbo();
        $query = "SELECT `id`, `template`, `client_id`, `title` FROM `#__template_styles`";
        $rows = $db->setQuery($query)->loadObjectList();

        $site = new stdClass;
        $site->name = 'site';
        $site->styles = array();

        $admin = new stdClass;
        $admin->name = 'admin';
        $admin->styles = array();

        foreach ($rows as $row) {
            if ($row->client_id) {
                $admin->styles[] = $row;
            } else {
                $site->styles[] = $row;
            }
        }

        $styles = array($site, $admin);

        return $styles;
    }

    protected function getMenus()
    {
        $menus = array();
        $db = $this->getDbo();
        $query = "SELECT `menutype`, `title` FROM `#__menu_types`";
        $types = $db->setQuery($query)->loadObjectList();
        $main = new stdClass;
        $main->menutype = 'main';
        $main->title = 'Backend Menu';
        $types[] = $main;
        $home = array();

        foreach ($types as $type) {
            $menu = new stdClass;
            $menu->type = $type;
            $query = "SELECT `id`,`title`, `level`, `language`,`home`
                FROM `#__menu`
                WHERE menutype = " . $db->q($type->menutype). " ORDER BY lft asc";

            $menu->items = $db->setQuery($query)->loadObjectList();
            foreach ($menu->items as $item) {
                if ($item->home) {
                    $home[$item->language] = $item->id;
                }
            }

            $menus[] = $menu;
        }

        $result = new stdClass;
        $result->list = $menus;
        $result->home = $home;

        return $result;
    }

    protected function getExtensions()
    {
        $jversion = $this->getJVersion();
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select($db->qn(array(
                'extension_id',
                'name',
                'type',
                'element',
                'folder',
                'client_id',
            )))
            ->from($db->qn('#__extensions'))
            ->order('extension_id DESC');

        $extensions = $db->setQuery($query)->loadObjectList();
        foreach ($extensions as $ext) {
            $basePath = $this->base_path . '/' . $this->folder;
            $client_path = $ext->client_id ? $basePath . '/administrator' : $basePath;
            $file = '';
            switch ($ext->type) {
                case 'component':
                    $name = substr($ext->element, 4);
                    $file = "$client_path/components/{$ext->element}/$name.xml";
                    break;

                case 'file':
                    $file = "$basePath/administrator/manifests/files/{$ext->element}.xml";
                    break;

                case 'language':
                    $name = $jversion == 4 ? 'langmetadata' : $ext->element;
                    $file = "$client_path/language/{$ext->element}/$name.xml";
                    break;

                case 'library':
                    $file = "$basePath/administrator/manifests/libraries/{$ext->element}.xml";
                    break;

                case 'module':
                    $file = "$client_path/modules/{$ext->element}/{$ext->element}.xml";
                    break;

                case 'package':
                    $file = "$basePath/administrator/manifests/packages/{$ext->element}.xml";
                    break;

                case 'plugin':
                    $file = "$client_path/plugins/{$ext->folder}/{$ext->element}/{$ext->element}.xml";
                    break;

                case 'template':
                    $file = "$client_path/templates/{$ext->element}/templateDetails.xml";
                    break;
            }

            if (!$file || !file_exists($file)) {
                $this->error[] = "Missing xml [{$ext->type}] [{$ext->element}] [{$ext->folder}] $file";
            }
        }

        return $extensions;
    }

    protected function getDbo()
    {
        if ($this->db) {
            return $this->db;
        }

        $file = $this->base_path . '/' . $this->folder . '/configuration.php';
        if (!file_exists($file)) {
            return false;
        }

        require_once $file;
        $config = new JConfig;
        $dbFactory = new DatabaseFactory;

        $db = $dbFactory->getDriver(
            $config->dbtype,
            array(
                'host' => $config->host,
                'user' => $config->user,
                'password' => $config->password,
                'database' => $config->db,
                'prefix' => $config->dbprefix
            )
        );

        $this->db = $db;

        return $this->db;
    }

    protected function saveConfig()
    {
        $file = JPATH_ROOT . '/config/config.json';
        if (!file_exists($file)) {
            $buffer = '{}';
            File::write($file, $buffer);
        }

        $content = file_get_contents($file);
        $config = new Registry($content);
        $config->set('base_path', rtrim($this->input->get('base_path', '', 'raw'), "\\/"));
        $config->set('svn_host', rtrim($this->input->get('svn_host', '', 'raw'), "\\/"));
        $config->set('svn_user', $this->input->get('svn_user', '', 'raw'));
        $config->set('svn_pass', $this->input->get('svn_pass', '', 'raw'));
        $str = $config->toString();
        File::write($file, $str);

        header('Location: ' . $this->uri_root . '?view=config');
    }

    protected function getBasePath()
    {
        $file = JPATH_ROOT . '/config/config.json';
        if (!file_exists($file)) {
            $buffer = new Registry;
            $buffer->set('base_path', JPATH_ROOT);
            $str = $buffer->toString();
            File::write($file, $str);
        }

        $content = file_get_contents($file);
        $config = new Registry($content);
        return rtrim($config->get('base_path'), "\\/");
    }

    protected function getUriRoot()
    {
        return str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
    }

    protected function getUriCurrent()
    {
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
}

$app = new App;
$app->run();

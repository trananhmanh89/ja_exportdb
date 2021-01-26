<?php
$config = $this->getConfig();
$user = escapeshellarg($config->get('svn_user'));
$pass = escapeshellarg($config->get('svn_pass'));
$host = escapeshellarg($config->get('svn_host'));

$svnConnected = $this->checkSvnConnection();

$command = "svn list --username $user --password $pass $host";
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DB Export Config</title>
    <link rel="stylesheet" href="<?php echo $this->uri_root . 'assets/bootstrap.min.css' ?>">
    <link rel="stylesheet" href="<?php echo $this->uri_root . 'assets/style.css?t=' . time() ?>">
</head>

<body>
    <div class="db-container">
        <?php require JPATH_ROOT . '/tmpl/menu.php' ?>
        <hr>
        <h2>Guide</h2>
    </div>
</body>
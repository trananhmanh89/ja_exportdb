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
        <?php if ($svnConnected) : ?>
            <div class="alert alert-success" role="alert">
                Successfully connect to SVN.
            </div>
        <?php else : ?>
            <div class="alert alert-danger" role="alert">
                Failed to connect to SVN!
            </div>
        <?php endif ?>
        <form class="form-base-path" action="<?php echo $this->uri_current ?>" method="post" autocomplete="off">
            <div class="form-group">
                <label for="base-path-input">Base Path</label>
                <input type="text" class="form-control" id="base-path-input" name="base_path" value="<?php echo $this->base_path ?>">
            </div>
            <div class="form-group">
                <label for="svn-host-input">SVN Host</label>
                <input type="text" class="form-control" id="svn-host-input" name="svn_host" value="<?php echo $this->config->get('svn_host') ?>">
            </div>
            <div class="form-group">
                <label for="svn-user-input">SVN User</label>
                <input type="text" class="form-control" id="svn-user-input" name="svn_user" value="<?php echo $this->config->get('svn_user') ?>">
            </div>
            <div class="form-group">
                <label for="svn-password-input">SVN Password</label>
                <input type="password" class="form-control" id="svn-password-input" name="svn_pass" value="<?php echo $this->config->get('svn_pass') ?>">
            </div>
            <input type="hidden" name="task" value="save_config">
            <input type="submit" value="Save Config" class="btn btn-primary" />
        </form>
    </div>
</body>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DB Export</title>
    <link rel="stylesheet" href="<?php echo $this->uri_root . 'assets/bootstrap.min.css' ?>">
    <link rel="stylesheet" href="<?php echo $this->uri_root . 'assets/select2.min.css' ?>">
    <link rel="stylesheet" href="<?php echo $this->uri_root . 'assets/style.css?t=' . time() ?>">
    <script src="<?php echo $this->uri_root . 'assets/jquery.min.js' ?>"></script>
    <script src="<?php echo $this->uri_root . 'assets/jquery.mark.min.js' ?>"></script>
    <script src="<?php echo $this->uri_root . 'assets/select2.min.js' ?>"></script>
    <script src="<?php echo $this->uri_root . 'assets/script.js?t=' . time() ?>"></script>
    <script>
        var uri_root = '<?php echo $this->uri_root ?>';
        var uri_current = '<?php echo $this->uri_current ?>';
    </script>
</head>

<body>
    <div class="db-container">
        <?php require JPATH_ROOT . '/tmpl/menu.php' ?>
        <hr>
        <form class="form-project" action="<?php echo $this->uri_current ?>" method="get">
            <label for="input-profile-folder">Project Folder</label>
            <div class="input-group mb-3">
                <input id="input-profile-folder" type="text" class="form-control" name="folder" value="<?php echo $this->folder ?>" autocomplete="off">
                <div class="input-group-append">
                    <input type="submit" value="Load" class="btn btn-primary" />
                </div>
            </div>
        </form>
        <form class="form-export" action="<?php echo $this->uri_current ?>" method="post">
            <label for="input-base-path">Profile</label>
            <div style="display: flex;">
                <div class="mr-2" style="width: 500px;">
                    <select class="profile-selection" style="display:none; width: 100%;" name="profile">
                        <option value="">Select Profile</option>
                        <?php foreach ($this->profiles as $profile) : ?>
                            <option value="<?php echo $profile ?>" 
                                <?php echo $this->input->get('profile') === $profile ? 'selected' : '' ?>
                            >
                                <?php echo $profile ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <input class="new-profile-input" type="hidden" name="new_profile" value="">
                <button type="button" class="btn btn-outline-success btn-save-profile mr-2">Save Profile</button>
                <button type="button" class="btn btn-outline-primary btn-new-profile mr-2">New Profile</button>
                <button type="button" class="btn btn-outline-danger btn-delete-profile">Delete Profile</button>
            </div>
            <hr>
            <label for="">SVN Folder</label>
            <div style="display: flex;">
                <div class="mr-2" style="width: 500px;">
                    <select class="svn-folder-selection" style="display:none; width: 100%;" name="svn_folder">
                        <option value="">Select SVN Folder</option>
                    </select>
                </div>
                <button type="button" class="btn btn-outline-success btn-new-svn-folder mr-2">New Folder</button>
                <button type="button" class="btn btn-outline-primary btn-export mr-2" task="export">Export</button>
                <button type="button" class="btn btn-outline-primary btn-export mr-2" task="export_prefix">Export Prefix</button>
                <button type="button" class="btn btn-outline-danger btn-export" task="commit">Commit</button>
            </div>
            <input type="hidden" name="task" value="">
            <input type="hidden" name="msg" value="">
            <input type="hidden" name="folder" value="<?php echo $this->folder ?>">
            <br>
            <div class="error-list">
                <?php if ($this->error): ?>
                    <div class="alert alert-danger" role="alert">
                        <div><strong>Error</strong></div>
                        <?php foreach ($this->error as $error): ?>
                            <div><?php echo $error ?></div>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>
            </div>
            <?php if ($this->project) : ?>
                <hr>
                <div class="hints" style="font-size: .8rem;">
                    <div>*** GUIDE ***</div>
                    <div>* <b>QS</b> - Quick Start</div>
                    <div>* Ticking checkbox means you don't want to export it</div>
                    <div>* Ticking checkbox on <b>Demo</b> section means data won't be present on next build </div>
                    <div>* <b>Menu > Home QS | Home Demo</b>: Set default menu when exporting</div>
                </div>
                <br>
                <div class="db-nav">
                    <ul class="nav nav-tabs">
                        <li class="nav-item" data-content="extension">
                            <a class="nav-link" href="javascript:;">Extension</a>
                        </li>
                        <li class="nav-item" data-content="menu">
                            <a class="nav-link" href="javascript:;">Menu</a>
                        </li>
                        <li class="nav-item" data-content="templatestyle">
                            <a class="nav-link" href="javascript:;">Template Style</a>
                        </li>
                        <li class="nav-item" data-content="table">
                            <a class="nav-link" href="javascript:;">Table</a>
                        </li>
                    </ul>
                </div>
                <div class="mt-3 db-config">
                    <div class="tab-content" data-content="extension">
                        <?php include JPATH_ROOT . '/tmpl/default_extension.php' ?>
                    </div>
                    <div class="tab-content" data-content="menu">
                        <?php include JPATH_ROOT . '/tmpl/default_menu.php' ?>
                    </div>
                    <div class="tab-content" data-content="templatestyle">
                        <?php include JPATH_ROOT . '/tmpl/default_templatestyle.php' ?>
                    </div>
                    <div class="tab-content" data-content="table">
                        <?php include JPATH_ROOT . '/tmpl/default_table.php' ?>
                    </div>
                </div>
            <?php else : ?>
                <div class="alert alert-danger">No project found</div>
            <?php endif ?>
        </form>
    </div>
    <div class="loader">
        <div class="spinner"></div>
    </div>
</body>

</html>
<?php
$app = \Liten\Liten::getInstance();
ob_start();
ob_implicit_flush(0);
$cookie = get_secure_cookie_data('SWITCH_USERBACK');
$user = get_userdata(get_current_user_id());
$app->hook->{'do_action'}('admin_init');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="<?= get_base_url(); ?>">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?= $title . ' &lsaquo; ' . $app->hook->{'get_option'}('sitename'); ?> &#8212; <?= _t('TriTan CMS', 'tritan-cms'); ?></title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=6, user-scalable=yes" name="viewport">
        <meta name="theme-color" content="#ffffff">
        <!-- Bootstrap 3.3.6 -->
        <link rel="stylesheet" href="static/assets/css/bootstrap/lumen-bootstrap.min.css">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <!-- Ionicons -->
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
        <!-- Favicon Package -->
        <link rel="apple-touch-icon" sizes="180x180" href="static/assets/img/apple-touch-icon.png">
        <link rel="icon" type="image/png" href="static/assets/img/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="static/assets/img/favicon-16x16.png" sizes="16x16">
        <link rel="manifest" href="static/assets/img/manifest.json">
        <link rel="mask-icon" href="static/assets/img/safari-pinned-tab.svg" color="#5bbad5">
        <!-- jQuery 2.2.4 -->
        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.js"></script>
        <!-- jQuery UI 1.12.1 -->
        <script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <link href="static/assets/css/datatables/dataTables.bootstrap.css" type="text/css" rel="stylesheet" />
        <link href="static/assets/css/select2/select2.min.css" type="text/css" rel="stylesheet" />
        <link href="static/assets/css/iCheck/all.css" type="text/css" rel="stylesheet" />
        <link href="//gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
        <link href="static/assets/css/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css" type="text/css" rel="stylesheet" />
        <link href="static/assets/css/bootstrap-switchery/switchery.min.css" type="text/css" rel="stylesheet" />
        <!-- Theme style -->
        <link rel="stylesheet" href="static/assets/css/AdminLTE.min.css">
        <!-- AdminLTE Skins. Choose a skin from the css/skins
             folder instead of downloading all of them to reduce the load. -->
        <link rel="stylesheet" href="static/assets/css/skins/_all-skins.min.css">
        <?php admin_head(); ?>
    </head>
    <body class="hold-transition <?=get_user_option('user_admin_skin', _escape($user['user_id']));?> <?=(get_user_option('user_admin_layout', _escape($user['user_id'])) == 1 ? 'fixed ' : '');?><?=(get_user_option('user_admin_sidebar', _escape($user['user_id'])) == 1 ? 'sidebar-collapse ' : '');?>sidebar-mini">
        <div class="wrapper">

            <header class="main-header">
                <!-- Logo -->
                <a href="<?= get_base_url(); ?>admin/" class="logo">
                    <!-- mini logo for sidebar mini 50x50 pixels -->
                    <span class="logo-mini"><?= get_logo_mini(); ?></span>
                    <!-- logo for regular state and mobile devices -->
                    <span class="logo-lg"><?= get_logo_large(); ?></span>
                </a>
                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top">
                    <!-- Sidebar toggle button-->
                    <!--<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                      <span class="sr-only"><?= _t('Toggle navigation', 'tritan-cms'); ?></span>
                    </a>-->

                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <!-- User Account: style can be found in dropdown.less -->
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <?= get_user_avatar(_escape($user['user_email']), 160, 'user-image'); ?>
                                    <span class="hidden-xs"><?= get_name(get_current_user_id()); ?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header">
                                        <?= get_user_avatar(_escape($user['user_email']), 160, 'image-circle'); ?>

                                        <p>
                                            <?= get_name(get_current_user_id()); ?>
                                            <small><?= _t('Member since', 'tritan-cms'); ?> <?= Jenssegers\Date\Date::parse(_escape($user['user_registered']))->format('M Y'); ?></small>
                                        </p>
                                    </li>
                                    <!-- Menu Footer-->
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="<?= get_base_url(); ?>admin/user/profile/" class="btn btn-default btn-flat"><?= _t('Profile', 'tritan-cms'); ?></a>
                                        </div>
                                            <?php if (isset($app->req->cookie['SWITCH_USERBACK'])) : ?>
                                            <div class="pull-left">
                                                <a href="<?= get_base_url(); ?>admin/user/<?= $cookie->user_id; ?>/switch-back/" class="btn btn-default btn-flat"><?= _t('Switch back', 'tritan-cms'); ?></a>
                                            </div>
                                            <?php endif; ?>
                                        <div class="pull-right">
                                            <a href="<?= get_base_url(); ?>logout/" class="btn btn-default btn-flat"><?= _t('Logout', 'tritan-cms'); ?></a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <!-- Control Sidebar Toggle Button -->
                            <li><p>&nbsp;</p></li>
                        </ul>
                    </div>
                </nav>
            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="main-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- Sidebar user panel -->
                    <div class="user-panel">
                        <div class="pull-left image">
                            <?= get_user_avatar(_escape($user['user_email']), 160, 'img-circle'); ?>
                        </div>
                        <div class="pull-left info">
                            <p><?= get_name(get_current_user_id()); ?></p>
                            <a><i class="fa fa-circle text-success"></i> <?= _t('Online', 'tritan-cms'); ?></a>
                        </div>
                    </div>
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu">
                        <li class="header"><?= _t('MAIN NAVIGATION', 'tritan-cms'); ?></li>
                        <li class="treeview<?= (SCREEN_PARENT === 'dashboard' ? ' active' : ''); ?>">
                            <a href="#">
                                <i class="fa fa-clock-o"></i>
                                <span><?= _t('Dashboard', 'tritan-cms'); ?></span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li<?= (SCREEN === 'home' ? ' class="active"' : ''); ?>><a href="<?= get_base_url(); ?>admin/"><i class="fa fa-circle-o"></i> <?= _t('Home', 'tritan-cms'); ?></a></li>
                                <li<?=ae('manage_ftp');?><?= (SCREEN === 'ftp' ? ' class="active"' : ''); ?>><a href="<?= get_base_url(); ?>admin/ftp/"><i class="fa fa-circle-o"></i> <?= _t('FTP', 'tritan-cms'); ?></a></li>
                                <li<?= (SCREEN === 'snapshot' ? ' class="active"' : ''); ?><?= ae('manage_settings'); ?>><a href="<?= get_base_url(); ?>admin/system-snapshot/"><i class="fa fa-circle-o"></i> <?= _t('Snapshot Report', 'tritan-cms'); ?></a></li>
                                <li<?= (SCREEN === 'error' ? ' class="active"' : ''); ?><?= ae('manage_settings'); ?>><a href="<?= get_base_url(); ?>admin/error/"><i class="fa fa-circle-o"></i> <?= _t('Error Logs', 'tritan-cms'); ?></a></li>
                                <li<?= (SCREEN === 'audit' ? ' class="active"' : ''); ?><?= ae('manage_settings'); ?>><a href="<?= get_base_url(); ?>admin/audit-trail/"><i class="fa fa-circle-o"></i> <?= _t('Audit Trail', 'tritan-cms'); ?></a></li>
                                <?php $app->hook->{'do_action'}('dashboard_submenu'); ?>
                            </ul>
                        </li>
                        <li<?= ae('manage_settings'); ?> class="treeview">
                            <a href="<?= get_base_url(); ?>admin/flush-cache/">
                                <i class="fa fa-file-text"></i> <span><?= _t('Flush Cache', 'tritan-cms'); ?></span>
                            </a>
                        </li>
                        <li<?= ae('manage_sites'); ?> class="treeview<?= (SCREEN_PARENT === 'sites' ? ' active' : ''); ?>">
                            <a href="#">
                                <i class="fa fa-globe"></i>
                                <span><?= _t('Sites', 'tritan-cms'); ?></span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li<?= (SCREEN === "sites" ? ' class="active"' : ''); ?>><a href="<?= get_base_url(); ?>admin/site/"><i class="fa fa-circle-o"></i> <?= _t('Manage', 'tritan-cms'); ?></a></li>
                                <li<?= (SCREEN === "sites-user" ? ' class="active"' : ''); ?>><a href="<?= get_base_url(); ?>admin/site/users/"><i class="fa fa-circle-o"></i> <?= _t('Users', 'tritan-cms'); ?></a></li>
                                <?php $app->hook->{'do_action'}('sites_submenu'); ?>
                            </ul>
                        </li>
                        <li<?= ae('manage_posts'); ?> class="treeview<?= (SCREEN_PARENT === 'post_types' ? ' active' : ''); ?>">
                            <a href="<?= get_base_url(); ?>admin/post-type/">
                                <i class="fa fa-thumb-tack"></i> <span><?= _t('Post Types', 'tritan-cms'); ?></span>
                            </a>
                        </li>
                        <?php foreach (get_all_post_types() as $post_type) : ?>
                            <li<?= ae('manage_posts'); ?> class="treeview<?= (SCREEN_PARENT === _escape($post_type['posttype_slug']) ? ' active' : ''); ?>">
                                <a href="#">
                                    <i class="fa fa-text-width"></i>
                                    <span><?= _escape($post_type['posttype_title']); ?></span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    <li<?= (SCREEN === _escape($post_type['posttype_slug']) ? ' class="active"' : ''); ?>><a href="<?= get_base_url(); ?>admin/<?= _escape($post_type['posttype_slug']); ?>/"><i class="fa fa-circle-o"></i> <?= _t('All', 'tritan-cms'); ?> <?= _escape($post_type['posttype_title']); ?></a></li>
                                    <li<?= (SCREEN === _escape($post_type['posttype_slug']) . "-create" ? ' class="active"' : ''); ?>><a href="<?= get_base_url(); ?>admin/<?= _escape($post_type['posttype_slug']); ?>/create/"><i class="fa fa-circle-o"></i> <?= _t('Add New', 'tritan-cms'); ?></a></li>
                                    <?php $app->hook->{'do_action'}('posttype_submenu', _escape($post_type['posttype_slug'])); ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                        <?php $app->hook->{'do_action'}('posttype_menu'); ?>
                        <li<?= ae('manage_media'); ?> class="treeview<?= (SCREEN_PARENT === 'media' ? ' active' : ''); ?>">
                            <a href="<?= get_base_url(); ?>admin/media/">
                                <i class="fa fa-camera"></i> <span><?= _t('Media Library', 'tritan-cms'); ?></span>
                            </a>
                        </li>
                        <li<?= ae('manage_plugins'); ?> class="treeview<?= (SCREEN_PARENT === 'plugins' ? ' active' : ''); ?>">
                            <a href="#">
                                <i class="fa fa-plug"></i>
                                <span><?= _t('Plugins', 'tritan-cms'); ?></span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li<?= (SCREEN === 'installed-plugins' ? ' class="active"' : ''); ?>><a href="<?= get_base_url(); ?>admin/plugin/"><i class="fa fa-circle-o"></i> <?= _t('Installed Plugins', 'tritan-cms'); ?></a></li>
                                <li<?= (SCREEN === 'plugin-new' ? ' class="active"' : ''); ?><?=ae('install_plugins');?>><a href="<?= get_base_url(); ?>admin/plugin/install/"><i class="fa fa-circle-o"></i> <?= _t('Add New', 'tritan-cms'); ?></a></li>
                                <?php $app->hook->{'do_action'}('plugins_submenu'); ?>
                            </ul>
                        </li>
                        <li class="treeview<?= (SCREEN_PARENT === 'users' ? ' active' : ''); ?>">
                            <a href="#">
                                <i class="fa fa-user"></i>
                                <span><?= _t('Users', 'tritan-cms'); ?></span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <?php $app->hook->{'do_action'}('admin_submenu_users'); ?>
                                <li<?= (SCREEN === 'all-users' ? ' class="active"' : ''); ?><?= ae('manage_users'); ?>><a href="<?= get_base_url(); ?>admin/user/"><i class="fa fa-circle-o"></i> <?= _t('All Users', 'tritan-cms'); ?></a></li>
                                <li<?= (SCREEN === 'create-user' ? ' class="active"' : ''); ?><?= ae('create_users'); ?>><a href="<?= get_base_url(); ?>admin/user/create/"><i class="fa fa-circle-o"></i> <?= _t('Add New', 'tritan-cms'); ?></a></li>
                                <li<?= (SCREEN === 'profile' ? ' class="active"' : ''); ?>><a href="<?= get_base_url(); ?>admin/user/profile/"><i class="fa fa-circle-o"></i> <?= _t('Your Profile', 'tritan-cms'); ?></a></li>
                                <?php $app->hook->{'do_action'}('users_submenu'); ?>
                            </ul>
                        </li>
                        <li<?= ae('manage_roles'); ?> class="treeview<?= (SCREEN_PARENT === 'roles') ? ' active' : ''; ?>">
                            <a href="#">
                                <i class="fa fa-key"></i>
                                <span><?= _t('User Roles', 'tritan-cms'); ?></span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li<?= (SCREEN === 'role' ? ' class="active"' : ''); ?><?= ae('manage_roles'); ?>><a href="<?= get_base_url(); ?>admin/role/"><i class="fa fa-circle-o"></i> <?= _t('Roles', 'tritan-cms'); ?></a></li>
                                <li<?= (SCREEN === 'crole' ? ' class="active"' : ''); ?><?= ae('manage_roles'); ?>><a href="<?= get_base_url(); ?>admin/role/create/"><i class="fa fa-circle-o"></i> <?= _t('Create Role', 'tritan-cms'); ?></a></li>
                                <li<?= (SCREEN === 'perm' ? ' class="active"' : ''); ?><?= ae('manage_roles'); ?>><a href="<?= get_base_url(); ?>admin/permission/"><i class="fa fa-circle-o"></i> <?= _t('Permissions', 'tritan-cms'); ?></a></li>
                                <li<?= (SCREEN === 'aperm' ? ' class="active"' : ''); ?><?= ae('manage_roles'); ?>><a href="<?= get_base_url(); ?>admin/permission/create/"><i class="fa fa-circle-o"></i> <?= _t('Create Permission', 'tritan-cms'); ?></a></li>
                                <?php $app->hook->{'do_action'}('role_submenu'); ?>
                            </ul>
                        </li>
                        <li<?= ae('manage_options'); ?> class="treeview<?= (SCREEN_PARENT === 'options') ? ' active' : ''; ?>">
                            <a href="#">
                                <i class="fa fa-cogs"></i>
                                <span><?= _t('Options', 'tritan-cms'); ?></span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li<?= (SCREEN === 'options-general' ? ' class="active"' : ''); ?>><a href="<?= get_base_url(); ?>admin/options-general/"><i class="fa fa-circle-o"></i> <?= _t('General', 'tritan-cms'); ?></a></li>
                                <li<?= (SCREEN === 'options-reading' ? ' class="active"' : ''); ?>><a href="<?= get_base_url(); ?>admin/options-reading/"><i class="fa fa-circle-o"></i> <?= _t('Reading', 'tritan-cms'); ?></a></li>
                                <?php $app->hook->{'do_action'}('options_submenu'); ?>
                            </ul>
                        </li>
                        <li>
                            <a href="//github.com/parkerj/TriTan-CMS/issues">
                                <i class="fa fa-ticket"></i> <span><?= _t('Submit Issue', 'tritan-cms'); ?></span>
                            </a>
                        </li>
                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>
            
            <?= $app->view->show('admin'); ?>

            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    <b><?= _t('Release', 'tritan-cms'); ?></b> <?= ttcms_release(); ?>
                </div>
                <?=ttcms_admin_copyright_footer();?>
            </footer>
        </div>
        <!-- ./wrapper -->

        <script>
            var basePath = '<?= get_base_url(); ?>';
        </script>
        <!-- Bootstrap 3.3.6 -->
        <script src="static/assets/js/bootstrap/bootstrap.min.js"></script>
        <!-- Bootstrap Validator 0.11.7 -->
        <script src="static/assets/plugins/bootstrap-validator/validator.js"></script>
        <!-- AdminLTE App -->
        <script src="static/assets/js/app.min.js"></script>
        <!-- Switchery -->
        <script src="static/assets/js/bootstrap-switchery/switchery.min.js"></script>
        <script src="static/assets/js/select2/select2.full.min.js" type="text/javascript"></script>
        <script src="static/assets/js/pages/select2.js" type="text/javascript"></script>
        <script src="static/assets/js/daterangepicker/moment.min.js" type="text/javascript"></script>
        <script src="static/assets/js/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
        <script src="static/assets/js/pages/datetime.js" type="text/javascript"></script>
        <script src="static/assets/js/iCheck/icheck.min.js" type="text/javascript"></script>
        <script src="static/assets/js/pages/iCheck.js" type="text/javascript"></script>
        <script src="//gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
        <script src="static/assets/js/app.min.js"></script>
        <script src="static/assets/js/demo.js"></script>
        <script src="static/assets/js/datatables/jquery.dataTables.min.js" type="text/javascript"></script>
        <script src="static/assets/js/datatables/dataTables.bootstrap.min.js" type="text/javascript"></script>
        <script src="static/assets/js/pages/datatable.js" type="text/javascript"></script>
        <script>
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            elems.forEach(function (html) {
                var switchery = new Switchery(html, {size: 'small', secondaryColor: '#ee0000'});
            });
        </script>

        <?php admin_footer(); ?>
    </body>
</html>
<?php print_gzipped_page(); ?>
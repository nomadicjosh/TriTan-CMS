<?php
$app = \Liten\Liten::getInstance();
use TriTan\Container as c;
use TriTan\Common\Hooks\ActionFilterHook as hook;

ob_start();
ob_implicit_flush(0);
$cookie = get_secure_cookie_data('SWITCH_USERBACK');
$user = get_userdata(get_current_user_id());
hook::getInstance()->{'doAction'}('admin_init');
$option = (
    new \TriTan\Common\Options\Options(
        new TriTan\Common\Options\OptionsMapper(
            new \TriTan\Database(),
            new TriTan\Common\Context\HelperContext()
        )
    )
);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="<?= site_url(); ?>">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?= $this->title . ' &lsaquo; ' . $option->{'read'}('sitename'); ?> &#8212; <?= esc_html__('TriTan CMS'); ?></title>
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
    <body class="hold-transition <?= get_user_option('admin_skin', $user->getId()); ?> <?= (get_user_option('admin_layout', $user->getId()) == 1 ? 'fixed ' : ''); ?><?= (get_user_option('admin_sidebar', $user->getId()) == 1 ? 'sidebar-collapse ' : ''); ?>sidebar-mini">
        <div class="wrapper">

            <header class="main-header">
                <!-- Logo -->
                <a href="<?= admin_url(); ?>" class="logo">
                    <!-- mini logo for sidebar mini 50x50 pixels -->
                    <span class="logo-mini"><?= get_logo_mini(); ?></span>
                    <!-- logo for regular state and mobile devices -->
                    <span class="logo-lg"><?= get_logo_large(); ?></span>
                </a>
                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top">
                    <!-- Sidebar toggle button-->
                    <!--<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                      <span class="sr-only"><?= esc_html__('Toggle navigation'); ?></span>
                    </a>-->

                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <!-- User Account: style can be found in dropdown.less -->
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <?= get_user_avatar($user->getEmail(), 160, 'user-image'); ?>
                                    <span class="hidden-xs"><?= get_name(get_current_user_id()); ?></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header">
                                    <?= get_user_avatar($user->getEmail(), 160, 'image-circle'); ?>

                                        <p>
                                            <?= get_name(get_current_user_id()); ?>
                                            <small><?= esc_html__('Member since'); ?> <?= (new \TriTan\Common\Date())->{'laci2date'}('M Y', $user->getRegistered()); ?></small>
                                        </p>
                                    </li>
                                    <!-- Menu Footer-->
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="<?= admin_url( 'user/profile/' ); ?>" class="btn btn-default btn-flat"><?= esc_html__('Profile'); ?></a>
                                        </div>
                                        <?php if (isset($app->req->cookie['SWITCH_USERBACK'])) : ?>
                                            <div class="pull-left">
                                                <a href="<?= admin_url( 'user/' . $cookie->user_id . '/switch-back/' ); ?>" class="btn btn-default btn-flat"><?= esc_html__('Switch back'); ?></a>
                                            </div>
                                        <?php endif; ?>
                                        <div class="pull-right">
                                            <a href="<?= site_url( 'logout/' ); ?>" class="btn btn-default btn-flat"><?= esc_html__('Logout'); ?></a>
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
                            <?= get_user_avatar($user->getEmail(), 160, 'img-circle'); ?>
                        </div>
                        <div class="pull-left info">
                            <p><?= get_name(get_current_user_id()); ?></p>
                            <a><i class="fa fa-circle text-success"></i> <?= esc_html__('Online'); ?></a>
                        </div>
                    </div>
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu">
                        <li class="header"><?= esc_html__('MAIN NAVIGATION'); ?></li>
                        <li class="treeview<?= (c::getInstance()->get('screen_parent') === 'dashboard' ? ' active' : ''); ?>">
                            <a href="#">
                                <i class="fa fa-clock-o"></i>
                                <span><?= esc_html__('Dashboard'); ?></span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <?php add_dashboard_submenu(esc_html__('Home'), '/', 'home'); ?>
                                <?php add_dashboard_submenu(esc_html__('FTP'), '/ftp/', 'ftp', 'manage_ftp'); ?>
                                <?php add_dashboard_submenu(esc_html__('Snapshot Report'), '/system-snapshot/', 'snapshot', 'manage_settings'); ?>
                                <?php add_dashboard_submenu(esc_html__('Error Logs'), '/error/', 'error', 'manage_settings'); ?>
                                <?php add_dashboard_submenu(esc_html__('Audit Trail'), '/audit-trail/', 'audit', 'manage_settings'); ?>
                                <?php hook::getInstance()->{'doAction'}('dashboard_submenu'); ?>
                            </ul>
                        </li>
                        <li<?= ae('manage_settings'); ?> class="treeview">
                            <a href="<?= admin_url( 'flush-cache/' ); ?>">
                                <i class="fa fa-file-text"></i> <span><?= esc_html__('Flush Cache'); ?></span>
                            </a>
                        </li>
                        <li<?= ae('manage_sites'); ?> class="treeview<?= (c::getInstance()->get('screen_parent') === 'sites' ? ' active' : ''); ?>">
                            <a href="#">
                                <i class="fa fa-globe"></i>
                                <span><?= esc_html__('Sites'); ?></span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <?php add_dashboard_submenu(esc_html__('Manage'), '/site/', 'sites'); ?>
                                <?php add_dashboard_submenu(esc_html__('Users'), '/site/users/', 'sites-user'); ?>
                                <?php hook::getInstance()->{'doAction'}('sites_submenu'); ?>
                            </ul>
                        </li>
                        <li<?= ae('manage_posts'); ?> class="treeview<?= (c::getInstance()->get('screen_parent') === 'post_types' ? ' active' : ''); ?>">
                            <a href="<?= admin_url( 'post-type/' ); ?>">
                                <i class="fa fa-thumb-tack"></i> <span><?= esc_html__('Post Types'); ?></span>
                            </a>
                        </li>
                        <?php foreach (get_all_post_types() as $post_type) : ?>
                            <li<?= ae('manage_posts'); ?> class="treeview<?= (c::getInstance()->get('screen_parent') === esc_attr($post_type->getSlug()) ? ' active' : ''); ?>">
                                <a href="#">
                                    <i class="fa fa-text-width"></i>
                                    <span><?= esc_html($post_type->getTitle()); ?></span>
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    <?php add_dashboard_submenu(esc_html__('All') . ' ' . esc_html($post_type->getTitle()), '/' . esc_html($post_type->getSlug()) . '/', esc_html($post_type->getSlug())); ?>
                                    <?php add_dashboard_submenu(esc_html__('Add New'), '/' . esc_html($post_type->getSlug()) . '/create/', esc_html($post_type->getSlug()) . '-create'); ?>
                                    <?php hook::getInstance()->{'doAction'}('posttype_submenu', esc_html($post_type->getSlug())); ?>
                                </ul>
                            </li>
                        <?php endforeach; ?>
                        <?php hook::getInstance()->{'doAction'}('posttype_menu'); ?>
                        <li<?= ae('manage_media'); ?> class="treeview<?= (c::getInstance()->get('screen_parent') === 'media' ? ' active' : ''); ?>">
                            <a href="<?= admin_url( 'media/' ); ?>">
                                <i class="fa fa-camera"></i> <span><?= esc_html__('Media Library'); ?></span>
                            </a>
                        </li>
                        <li<?= ae('manage_plugins'); ?> class="treeview<?= (c::getInstance()->get('screen_parent') === 'plugins' ? ' active' : ''); ?>">
                            <a href="#">
                                <i class="fa fa-plug"></i>
                                <span><?= esc_html__('Plugins'); ?></span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <?php add_dashboard_submenu(esc_html__('Installed Plugins'), '/plugin/', 'installed-plugins'); ?>
                                <?php add_dashboard_submenu(esc_html__('Add New'), '/plugin/install/', 'plugin-new', 'install_plugins'); ?>
                                <?php hook::getInstance()->{'doAction'}('plugins_submenu'); ?>
                            </ul>
                        </li>
                        <?php if (current_user_can('manage_users')) : ?>
                        <li class="treeview<?= (c::getInstance()->get('screen_parent') === 'users' ? ' active' : ''); ?>">
                            <a href="#">
                                <i class="fa fa-user"></i>
                                <span><?= esc_html__('Users'); ?></span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <?php hook::getInstance()->{'doAction'}('admin_submenu_users'); ?>
                                <?php add_dashboard_submenu(esc_html__('All Users'), '/user/', 'all-users', 'manage_users'); ?>
                                <?php add_dashboard_submenu(esc_html__('Add New'), '/user/create/', 'create-user', 'create_users'); ?>
                                <?php add_dashboard_submenu(esc_html__('Your Profile'), '/user/profile/', 'profile'); ?>
                                <?php hook::getInstance()->{'doAction'}('users_submenu'); ?>
                            </ul>
                        </li>
                        <?php else : ?>
                        <li>
                            <a href="<?= admin_url( 'user/profile/' ); ?>">
                                <i class="fa fa-id-card"></i> <span><?= esc_html__('Your Profile'); ?></span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <li<?= ae('manage_roles'); ?> class="treeview<?= (c::getInstance()->get('screen_parent') === 'roles') ? ' active' : ''; ?>">
                            <a href="#">
                                <i class="fa fa-key"></i>
                                <span><?= esc_html__('User Roles'); ?></span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <?php add_dashboard_submenu(esc_html__('Roles'), '/role/', 'role', 'manage_roles'); ?>
                                <?php add_dashboard_submenu(esc_html__('Create Role'), '/role/create/', 'crole', 'manage_roles'); ?>
                                <?php add_dashboard_submenu(esc_html__('Permissions'), '/permission/', 'perm', 'manage_roles'); ?>
                                <?php add_dashboard_submenu(esc_html__('Create Permission'), '/permission/create/', 'cperm', 'manage_roles'); ?>
                                <?php hook::getInstance()->{'doAction'}('role_submenu'); ?>
                            </ul>
                        </li>
                        <li<?= ae('manage_options'); ?> class="treeview<?= (c::getInstance()->get('screen_parent') === 'options') ? ' active' : ''; ?>">
                            <a href="#">
                                <i class="fa fa-cogs"></i>
                                <span><?= esc_html__('Options'); ?></span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <?php add_dashboard_submenu(esc_html__('General'), '/options-general/', 'options-general'); ?>
                                <?php add_dashboard_submenu(esc_html__('Reading'), '/options-reading/', 'options-reading'); ?>
                                <?php hook::getInstance()->{'doAction'}('options_submenu'); ?>
                            </ul>
                        </li>
                        <li>
                            <a href="//gitspace.us/projects/tritan-cms/issues">
                                <i class="fa fa-ticket"></i> <span><?= esc_html__('Submit Issue'); ?></span>
                            </a>
                        </li>
                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>

            <?php $this->section('backend'); ?>
            <?php $this->stop(); ?>

            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    <?php ttcms_release(); ?>
                </div>
                <?= ttcms_admin_copyright_footer(); ?>
            </footer>
        </div>
        <!-- ./wrapper -->

        <script>
            var basePath = '<?= site_url(); ?>';
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

<?php

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Exception\Exception;
use Cascade\Cascade;

$user = get_userdata(get_current_user_id());

/**
 * Before router checks to make sure the logged in user
 * us allowed to access admin.
 */
$app->before('GET|POST', '/admin(.*)', function() {
    if (!is_user_logged_in()) {
        _ttcms_flash()->{'error'}(_t('401 - Error: Unauthorized.', 'tritan-cms'), get_base_url() . 'login' . '/');
        exit();
    }
    if (!hasPermission('access_admin')) {
        _ttcms_flash()->{'error'}(_t('403 - Error: Forbidden.', 'tritan-cms'), get_base_url());
        exit();
    }
});

$app->group('/admin', function() use ($app, $user) {

    $app->get('/', function () use($app) {

        $app->view->display('admin/index', [
            'title' => _t('Admin Dashboard', 'tritan-cms')
                ]
        );
    });

    $app->before('GET', '/media/', function() {
        if (!hasPermission('manage_media')) {
            _ttcms_flash()->{'error'}(_t('You do not have permission to manage the media library.', 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/media/', function () use($app) {

        $app->view->display('admin/media', [
            'title' => _t('Media Library', 'tritan-cms')
                ]
        );
    });

    $app->before('GET', '/ftp/', function() {
        if (!hasPermission('manage_ftp')) {
            _ttcms_flash()->{'error'}(_t('You do not have permission to manage FTP.', 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/ftp/', function () use($app) {

        $app->view->display('admin/ftp', [
            'title' => _t('FTP', 'tritan-cms')
                ]
        );
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to manage options/settings.
     */
    $app->before('GET|POST', '/options-general/', function() {
        if (!hasPermission('manage_options')) {
            _ttcms_flash()->{'error'}(_t('You do not have permission to manage options.', 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/options-general/', function() use($app) {
        if ($app->req->isPost()) {
            $options = [
                'sitename', 'site_description', 'admin_email', 'ttcms_core_locale',
                'cookieexpire', 'cookiepath', 'enable_cron_jobs', 'site_cache',
                'system_timezone', 'api_key'
            ];
            foreach ($options as $option_name) {
                if (!isset($app->req->post[$option_name]))
                    continue;
                $value = $app->req->post[$option_name];
                $app->hook->{'update_option'}($option_name, $value);
            }

            $site = $app->db->table('site');
            $site->begin();
            try {
                $site->where('site_id', (int) Config::get('site_id'))
                        ->update([
                            'site_name' => $app->req->post['sitename'],
                            'site_modified' => (string) Jenssegers\Date\Date::now()
                ]);
                $site->commit();
            } catch (Exception $ex) {
                $site->rollback();
                Cascade::getLogger('error')->{'error'}(sprintf('SQLSTATE[%s]: %s', $ex->getCode(), $ex->getMessage()));
            }
            _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
        }

        $app->view->display('admin/options-general', [
            'title' => _t('General Options', 'tritan-cms'),
                ]
        );
    });

    /**
     * Before route checks to make sure the logged in user
     * us allowed to manage options/settings.
     */
    $app->before('GET|POST', '/options-reading/', function() {
        if (!hasPermission('manage_options')) {
            _ttcms_flash()->{'error'}(_t('You do not have permission to manage options.', 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/options-reading/', function() use($app) {
        if ($app->req->isPost()) {
            $options = [
                'current_site_theme', 'posts_per_page', 'date_format', 'time_format'
            ];
            foreach ($options as $option_name) {
                if (!isset($app->req->post[$option_name]))
                    continue;
                $value = $app->req->post[$option_name];
                $app->hook->{'update_option'}($option_name, $value);
            }
            _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
        }

        $app->view->display('admin/options-reading', [
            'title' => _t('Reading Options', 'tritan-cms'),
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/plugin/', function() {
        if (!hasPermission('manage_plugins')) {
            _ttcms_flash()->{'error'}(_t("You do not have permission to manage plugins.", 'tritan-cms'), get_base_url() . 'admin' . '/');
        }
    });

    $app->get('/plugin/', function() use($app) {
        $app->view->display('admin/plugin/index', ['title' => _t('Plugins')]);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/plugin/install/', function() {
        if (!hasPermission('install_plugins')) {
            _ttcms_flash()->{'error'}(_t("You do not have permission to install plugins.", 'tritan-cms'), get_base_url() . 'admin' . '/');
        }
    });

    $app->match('GET|POST', '/plugin/install/', function() use($app) {
        if ($app->req->isPost()) {
            $name = explode(".", $_FILES["plugin_zip"]["name"]);
            $accepted_types = [
                'application/zip',
                'application/x-zip-compressed',
                'multipart/x-zip',
                'application/x-compressed'
            ];

            foreach ($accepted_types as $mime_type) {
                if ($mime_type == $type) {
                    $okay = true;
                    break;
                }
            }

            $continue = strtolower($name[1]) == 'zip' ? true : false;

            if (!$continue) {
                _tc_flash()->{'error'}(_t('The file you are trying to upload is not the accepted file type (.zip). Please try again.'));
            }
            $target_path = BASE_PATH . 'plugins' . DS . $_FILES["plugin_zip"]["name"];
            if (move_uploaded_file($_FILES["plugin_zip"]["tmp_name"], $target_path)) {
                $zip = new \ZipArchive();
                $x = $zip->open($target_path);
                if ($x === true) {
                    $zip->extractTo(BASE_PATH . 'plugins' . DS);
                    $zip->close();
                    unlink($target_path);
                }
                _ttcms_flash()->{'success'}(_t('Your plugin was uploaded and installed properly.'), $app->req->server['HTTP_REFERER']);
            } else {
                _ttcms_flash()->{'error'}(_t('There was a problem uploading your plugin. Please try again or check the plugin package.'), $app->req->server['HTTP_REFERER']);
            }
        }

        $app->view->display('admin/plugin/install', ['title' => _t('Install Plugins')]);
    });

    $app->before('GET|POST', '/plugin/activate/', function () {
        if (!hasPermission('manage_plugins')) {
            _ttcms_flash()->{'error'}(_t('Permission denied to activate a plugin.'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/plugin/activate/', function () use($app) {
        ob_start();

        $plugin_name = $app->req->get['id'];

        /**
         * This function will validate a plugin and make sure
         * there are no errors before activating it.
         *
         * @since 1.0.0
         */
        ttcms_validate_plugin($plugin_name);

        if (ob_get_length() > 0) {
            $output = ob_get_clean();
            $error = new TriTan\Error('unexpected_output', _t('The plugin generated unexpected output.'), $output);
            Cascade::getLogger('error')->{'error'}(sprintf('PLUGIN[%s]: %s', $error->get_error_code(), $error->get_error_message()));
            _ttcms_flash()->{'error'}($error->get_error_message());
        }
        ob_end_clean();

        ttcms_redirect($app->req->server['HTTP_REFERER']);
    });

    $app->before('GET|POST', '/deactivate/', function () {
        if (!hasPermission('manage_plugins')) {
            _ttcms_flash()->{'error'}(_t('Permission denied to deactivate a plugin.'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/plugin/deactivate/', function () use($app) {
        $pluginName = $app->req->get['id'];
        /**
         * Fires before a specific plugin is deactivated.
         *
         * $pluginName refers to the plugin's
         * name (i.e. smtp.plugin.php).
         *
         * @since 1.0.0
         * @param string $pluginName
         *            The plugin's base name.
         */
        $app->hook->{'do_action'}('deactivate_plugin', $pluginName);

        /**
         * Fires as a specifig plugin is being deactivated.
         *
         * $pluginName refers to the plugin's
         * name (i.e. smtp.plugin.php).
         *
         * @since 1.0.0
         * @param string $pluginName
         *            The plugin's base name.
         */
        $app->hook->{'do_action'}('deactivate_' . $pluginName);

        deactivate_plugin($pluginName);

        /**
         * Fires after a specific plugin has been deactivated.
         *
         * $pluginName refers to the plugin's
         * name (i.e. smtp.plugin.php).
         *
         * @since 1.0.0
         * @param string $pluginName
         *            The plugin's base name.
         */
        $app->hook->{'do_action'}('deactivated_plugin', $pluginName);

        ttcms_redirect($app->req->server['HTTP_REFERER']);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST|PATCH|PUT|OPTIONS|DELETE', '/connector/', function() {
        if (!is_user_logged_in()) {
            _ttcms_flash()->{'error'}(_t("You do not have permission to access requested screen", 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST|PATCH|PUT|OPTIONS|DELETE', '/connector/', function () use($app) {
        error_reporting(0);
        try {
            _mkdir(BASE_PATH . 'private' . DS . 'sites' . DS . (int) Config::get('site_id') . DS . 'uploads' . DS . '__optimized__' . DS);
        } catch (\TriTan\Exception\IOException $e) {
            Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Unable to create directory: %s', $e->getCode(), $e->getMessage()));
        }
        $opts = [
            // 'debug' => true,
            'locale' => 'en_US.UTF-8',
            'roots' => [
                [
                    'driver' => 'LocalFileSystem',
                    'startPath' => Config::get('site_path') . 'uploads' . DS,
                    'path' => Config::get('site_path') . 'uploads' . DS,
                    'alias' => 'Media Library',
                    'mimeDetect' => 'auto',
                    'accessControl' => 'access',
                    'tmbURL' => get_base_url() . 'private/sites/' . (int) Config::get('site_id') . '/uploads/' . '.tmb',
                    'tmpPath' => Config::get('site_path') . 'uploads' . DS . '.tmb',
                    'URL' => get_base_url() . 'private/sites/' . (int) Config::get('site_id') . '/uploads/',
                    'attributes' => [
                        [
                            'read' => true,
                            'write' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\__optimized__/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\.gitkeep/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\.gitignore/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\.htaccess/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\index.html/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.tmb/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.quarantine/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.DS_Store/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.json$/',
                            'read' => true,
                            'write' => true,
                            'hidden' => false,
                            'locked' => false
                        ]
                    ],
                    'uploadMaxSize' => '500M',
                    'uploadAllow' => [
                        'text/plain', 'image/png', 'image/jpeg', 'image/gif', 'application/zip',
                        'text/csv', 'application/pdf', 'application/msword', 'application/vnd.ms-excel',
                        'application/vnd.ms-powerpoint', 'application/msword', 'application/vnd.ms-excel',
                        'application/vnd.ms-powerpoint', 'video/mp4'
                    ],
                    'uploadOrder' => ['allow', 'deny']
                ]
            ]
        ];
        // run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST|PATCH|PUT|OPTIONS|DELETE', '/ftp-connector/', function() {
        if (!is_user_logged_in()) {
            _ttcms_flash()->{'error'}(_t("You do not have permission to access requested screen", 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST|PATCH|PUT|OPTIONS|DELETE', '/ftp-connector/', function () use($app) {
        error_reporting(0);
        try {
            _mkdir(BASE_PATH . 'private' . DS . 'sites' . DS . (int) Config::get('site_id') . DS . 'uploads' . DS . '__optimized__' . DS);
        } catch (\TriTan\Exception\IOException $e) {
            Cascade::getLogger('error')->error(sprintf('IOSTATE[%s]: Unable to create directory: %s', $e->getCode(), $e->getMessage()));
        }
        $opts = [
            // 'debug' => true,
            'locale' => 'en_US.UTF-8',
            'roots' => [
                [
                    'driver' => 'LocalFileSystem',
                    'path' => BASE_PATH . 'private' . DS,
                    'tmbURL' => get_base_url() . 'private/.tmb',
                    'tmpPath' => BASE_PATH . 'private' . DS . '.tmb',
                    'alias' => 'Files',
                    'mimeDetect' => 'auto',
                    'accessControl' => 'access',
                    'attributes' => [
                        [
                            'read' => true,
                            'write' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.gitkeep/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\.gitignore/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\.htaccess/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => true
                        ],
                        [
                            'pattern' => '/\index.html/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.tmb/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.quarantine/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.DS_Store/',
                            'read' => false,
                            'write' => false,
                            'hidden' => true,
                            'locked' => false
                        ],
                        [
                            'pattern' => '/\.json$/',
                            'read' => true,
                            'write' => true,
                            'hidden' => false,
                            'locked' => false
                        ]
                    ],
                    'uploadMaxSize' => '500M',
                    'uploadAllow' => [
                        'text/plain', 'text/html', 'application/json', 'application/xml',
                        'application/javascript'
                    ],
                    'uploadOrder' => ['allow', 'deny']
                ]
            ]
        ];
        // run elFinder
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/elfinder/', function() {
        if (!is_user_logged_in()) {
            _ttcms_flash()->{'error'}(_t("You don't have permission to view the requested screen", 'tritan-cms'), get_base_url() . 'login' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/elfinder/', function () use($app) {

        $app->view->display('admin/elfinder', [
            'title' => 'elfinder 2.1'
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/permission.*', function() {
        if (!hasPermission('manage_roles')) {
            _ttcms_flash()->{'error'}(_t("You don't have permission to manage roles/permissions.", 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/permission/', function () use($app) {


        $app->view->display('admin/permission/index', [
            'title' => _t('Manage Permissions', 'tritan-cms')
                ]
        );
    });

    $app->match('GET|POST', '/permission/(\d+)/', function ($id) use($app, $user) {
        if ($app->req->isPost()) {
            $perm = $app->db->table('permission');
            $perm->begin();
            try {
                $perm->where('permission_id', (int) $id)
                        ->update([
                            'permission_key' => if_null($app->req->post['permission_key']),
                            'permission_name' => if_null($app->req->post['permission_name']),
                ]);
                $perm->commit();
                ttcms_logger_activity_log_write('Update Record', 'Permission', $app->req->post['permission_name'], _escape($user['user_login']));
                _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), $app->req->server['HTTP_REFERER']);
            } catch (Exception $ex) {
                $perm->rollback();
                Cascade::getLogger('error')->{'error'}($ex->getMessage());
                _ttcms_flash()->{'error'}($ex->getMessage());
            }
        }

        $perm = $app->db->table('permission')->where('permission_id', (int) $id)->first();


        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($perm == false) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($perm) == true) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If data is zero, 404 not found.
         */ elseif ((int) _escape($perm['permission_id']) <= 0) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            $app->view->display('admin/permission/update', [
                'title' => _t('Update Permission', 'tritan-cms'),
                'perm' => $perm
                    ]
            );
        }
    });

    $app->match('GET|POST', '/permission/create/', function () use($app, $user) {

        if ($app->req->isPost()) {
            $perm = $app->db->table('permission');
            $perm->begin();
            try {
                $permission_id = auto_increment('permission', 'permission_id');
                $perm->insert([
                    'permission_id' => (int) $permission_id,
                    'permission_key' => if_null($app->req->post['permission_key']),
                    'permission_name' => if_null($app->req->post['permission_name']),
                ]);
                $perm->commit();
                ttcms_logger_activity_log_write('Create Record', 'Permission', $app->req->post['permission_name'], _escape($user['user_login']));
                _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), get_base_url() . 'admin/permission' . '/');
            } catch (Exception $ex) {
                $perm->rollback();
                Cascade::getLogger('error')->{'error'}($ex->getMessage());
                _ttcms_flash()->{'error'}($ex->getMessage());
            }
        }

        $app->view->display('admin/permission/create', [
            'title' => _t('Create New Permission', 'tritan-cms')
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/role(.*)', function() {
        if (!hasPermission('manage_roles')) {
            _ttcms_flash()->{'error'}(_t("You don't have permission to manage roles/permissions."), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->match('GET|POST', '/role/', function () use($app) {

        $app->view->display('admin/role/index', [
            'title' => _t('Manage Roles', 'tritan-cms')
                ]
        );
    });

    $app->match('GET|POST', '/role/(\d+)/', function ($id) use($app) {

        $role = $app->db->table('role')
                ->where('role_id', (int) $id)
                ->first();

        /**
         * If the database table doesn't exist, then it
         * is false and a 404 should be sent.
         */
        if ($role == false) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If the query is legit, but there
         * is no data in the table, then 404
         * will be shown.
         */ elseif (empty($role) == true) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If data is zero, 404 not found.
         */ elseif ((int) _escape($role['role_id']) <= 0) {

            $app->res->_format('json', 404);
            exit();
        }
        /**
         * If we get to this point, the all is well
         * and it is ok to process the query and print
         * the results in a html format.
         */ else {

            $app->view->display('admin/role/update', [
                'title' => _t('Update Role', 'tritan-cms'),
                'role' => $role
                    ]
            );
        }
    });

    $app->match('GET|POST', '/role/create/', function () use($app) {

        if ($app->req->isPost()) {
            $role = $app->db->table('role');
            $role->begin();
            try {
                $role_id = auto_increment('role', 'role_id');
                $role->insert([
                    'role_id' => (int) $role_id,
                    'role_name' => (string) $app->req->post['role_name'],
                    'role_permission' => $app->hook->{'maybe_serialize'}($app->req->post['role_permission'])
                ]);
                $role->commit();
                _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), get_base_url() . 'admin/role' . '/' . (int) $role_id . '/');
            } catch (Exception $e) {
                $role->rollback();
                _ttcms_flash()->{'error'}($e->getMessage());
            }
        }

        $app->view->display('admin/role/create', [
            'title' => _t('Create Role', 'tritan-cms')
                ]
        );
    });

    $app->post('/role/edit-role/', function () use($app) {
        $role = $app->db->table('role');
        $role->begin();
        try {
            $role->where('role_id', (int) $app->req->post['role_id'])
                    ->update([
                        'role_name' => (string) $app->req->post['role_name'],
                        'role_permission' => $app->hook->{'maybe_serialize'}($app->req->post['role_permission'])
            ]);
            $role->commit();
            _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200));
        } catch (Exception $e) {
            $role->rollback();
            _ttcms_flash()->{'error'}($e->getMessage());
        }

        ttcms_redirect($app->req->server['HTTP_REFERER']);
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/system-snapshot/', function () {
        if (!hasPermission('manage_settings')) {
            _ttcms_flash()->{'error'}(_t("You don't have permission to view the System Snapshot Report screen.", 'tritan-cms'), get_base_url() . 'admin' . '/');
        }
    });

    $app->get('/system-snapshot/', function () use($app) {
        $user = $app->db->table('user')->where('user_status', 'A')->get();
        $error = $app->db->table(Config::get('tbl_prefix') . 'error')->all();
        $app->view->display('admin/system-snapshot', [
            'title' => _t('System Snapshot Report', 'tritan-cms'),
            'user' => count($user),
            'error' => count($error)
        ]);
    });

    $app->before('GET|POST', '/error/(.*)', function () {
        if (!hasPermission('manage_settings')) {
            _ttcms_flash()->{'error'}(_t("You don't have permission to view the Error Log screen.", 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/error/', function () use($app) {
        $errors = $app->db->table(Config::get('tbl_prefix') . 'error')
                ->all();

        $app->view->display('error/index', [
            'title' => _t('Error Logs', 'tritan-cms'),
            'errors' => $errors
                ]
        );
    });

    $app->get('/error/(\d+)/delete/', function ($id) use($app) {
        $errors = $app->db->table(Config::get('tbl_prefix') . 'error');
        $errors->begin();
        try {
            $errors->where('error_id', (int) $id)
                    ->delete();
            $errors->commit();
            _ttcms_flash()->{'success'}(_ttcms_flash()->notice(200), get_base_url() . 'admin/error/' . '/');
        } catch (Exception $ex) {
            $errors->rollback();
            Cascade::getLogger('error')->{'error'}($ex->getMessage());
            _ttcms_flash()->{'error'}($ex->getMessage());
        }

        $app->view->display('error/index', [
            'title' => _t('Error Logs', 'tritan-cms'),
            'errors' => $errors
                ]
        );
    });

    $app->before('GET|POST', '/audit-trail/', function () {
        if (!hasPermission('manage_settings')) {
            _ttcms_flash()->{'error'}(_t("You don't have permission to view the Audit Trail screen.", 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/audit-trail/', function () use($app) {

        $audit = $app->db->table(Config::get('tbl_prefix') . 'activity')
                ->sortBy('created_at', 'DESC')
                ->get();

        $app->view->display('error/audit', [
            'title' => _t('Audit Trail', 'tritan-cms'),
            'audit' => $audit
                ]
        );
    });

    /**
     * Before route check.
     */
    $app->before('GET|POST', '/flush-cache/', function () {
        if (!hasPermission('manage_settings')) {
            _ttcms_flash()->{'error'}(_t("You are not allowed to flush the site cache.", 'tritan-cms'), get_base_url() . 'admin' . '/');
            exit();
        }
    });

    $app->get('/flush-cache/', function () use($app) {
        if ($app->hook->{'get_option'}('current_site_theme') !== 'null' && $app->hook->{'get_option'}('current_site_theme') !== '' && $app->hook->{'get_option'}('current_site_theme') !== false) {
            $app->fenom->clearAllCompiles();
        }
        ttcms_cache_flush();
        ttcms_redirect($app->req->server['HTTP_REFERER']);
    });

    /**
     * If the requested page does not exist,
     * return a 404.
     */
    $app->setError(function() use($app) {
        $app->res->_format('json', 404);
    });
});

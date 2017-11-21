<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
/**
 * Manage Roles View
 *  
 * @license GPLv3
 * 
 * @since       1.0.0
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/admin');
$app->view->block('admin');
$roles = new \TriTan\ACL();
Config::set('screen_parent', 'roles');
Config::set('screen_child', 'role');

?>            

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-text-width"></i>
            <h3 class="box-title"><?= _t('Roles', 'tritan-cms'); ?></h3>
            
            <div class="pull-right">
                <button type="button" class="btn btn-success" onclick="window.location = '<?= get_base_url(); ?>admin/role/create/'"><i class="fa fa-plus-circle"></i> <?= _t('Create a Role'); ?></button>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

        <?= _ttcms_flash()->showMessage(); ?>

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= _t('Name'); ?></th>
                            <th class="text-center"><?= _t('Edit'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $listRoles = $roles->getAllRoles('full');
                        if ($listRoles != '') {
                            foreach ($listRoles as $k => $v) {
                                echo '<tr class="gradeX">' . "\n";
                                echo '<td class="text-center">' . _escape($v['Name']) . '</td>' . "\n";
                                echo '<td class="text-center"><a href="' . get_base_url() . 'admin/role/' . _escape((int) $v['ID']) . '/" data-toggle="tooltip" data-placement="top" title="View/Edit" class="btn bg-yellow"><i class="fa fa-edit"></i></a></td>';
                                echo '</tr>';
                            }
                        }

                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('Name'); ?></th>
                            <th class="text-center"><?= _t('Edit'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>
<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
/**
 * Update Role View
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
$eRole = new \TriTan\ACL();
Config::set('screen_parent', 'roles');
Config::set('screen_child', 'role');

?>

<!-- form start -->
<form method="post" action="<?= get_base_url(); ?>admin/role/edit-role/" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-user"></i>
                <h3 class="box-title"><?= _t('Update Role', 'tritan-cms'); ?></h3>

                <div class="pull-right">
                    <input type="hidden" name="role_id" value="<?= _escape((int) $role['role_id']); ?>" />
                    <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= _t('Update', 'tritan-cms'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>admin/role/'"><i class="fa fa-ban"></i> <?= _t('Cancel', 'tritan-cms'); ?></button>
                </div>
            </div>
        </div> 

        <!-- Main content -->
        <section class="content">

            <?= _ttcms_flash()->showMessage(); ?> 

            <!-- SELECT2 EXAMPLE -->
            <div class="box box-default">
                <div class="box-body">

                    <!-- Group -->
                    <div class="form-group">
                        <label class="col-md-3 control-label"><font color="red">*</font> <?= _t('Role Name'); ?></label>
                        <div class="col-md-12"><input class="form-control" name="role_name" type="text" value="<?= _escape($role['role_name']); ?>" required/></div>
                    </div>
                    <!-- // Group END -->

                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th><?= _t('Permission'); ?></th>
                                <th class="text-center"><?= _t('Allow'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php role_perm(_escape((int) $role['role_id'])); ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th><?= _t('Permission'); ?></th>
                                <th class="text-center"><?= _t('Allow'); ?></th>
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
</form>
<?php $app->view->stop(); ?>
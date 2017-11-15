<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * View Permission View
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
$ePerm = new \TriTan\ACL();
define('SCREEN_PARENT', 'roles');
define('SCREEN', 'perm');

?>

<!-- form start -->
<form method="post" action="<?= get_base_url(); ?>admin/permission/<?= _escape((int) $perm['permission_id']); ?>/" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-text-width"></i>
                <h3 class="box-title"><?= _t('Update Permission', 'tritan-cms'); ?></h3>

                <div class="pull-right">
                    <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= _t('Update', 'tritan-cms'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>admin/permission/'"><i class="fa fa-ban"></i> <?= _t('Cancel', 'tritan-cms'); ?></button>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">

            <?= _ttcms_flash()->showMessage(); ?>

            <!-- SELECT2 EXAMPLE -->
            <div class="box box-default">

                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Name'); ?></label>
                                <input type="text" class="form-control" name="permission_name" value="<?= $ePerm->getPermNameFromID(_escape((int) $perm['permission_id'])); ?>" required>
                            </div>

                        </div>
                        <!-- /.col -->

                        <div class="col-md-6">

                            <div class="form-group">
                                <label><font color="red">*</font> <?= _t('Key'); ?></label>
                                <input type="text" class="form-control" name="permission_key" value="<?= $ePerm->getPermKeyFromID(_escape((int) $perm['permission_id'])); ?>" required>
                            </div>

                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
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
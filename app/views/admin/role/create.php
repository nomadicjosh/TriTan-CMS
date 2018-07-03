<?php
use TriTan\Config;
use TriTan\Functions\Dependency;
use TriTan\Functions\User;
use TriTan\Functions\Core;

/**
 * Create Role View
 *
 * @license GPLv3
 *
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
Config::set('screen_parent', 'roles');
Config::set('screen_child', 'crole');
?>

<!-- form start -->
<form method="post" action="<?= Core\get_base_url(); ?>admin/role/create/" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-text-width"></i>
                <h3 class="box-title"><?= Core\_t('Create Role', 'tritan-cms'); ?></h3>

                <div class="pull-right">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?= Core\_t('Save', 'tritan-cms'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= Core\get_base_url(); ?>admin/role/'"><i class="fa fa-ban"></i> <?= Core\_t('Cancel', 'tritan-cms'); ?></button>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">

            <?= Dependency\_ttcms_flash()->showMessage(); ?>

            <!-- SELECT2 EXAMPLE -->
            <div class="box box-default">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= Core\_t('Role Name', 'tritan-cms'); ?></label>
                                <input class="form-control" name="role_name" type="text" required/>
                            </div>
                        </div>
                        <!-- /.col -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= Core\_t('Role Key', 'tritan-cms'); ?></label>
                                <input type="text" class="form-control" name="role_key" required>
                            </div>
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->

            <!-- SELECT2 EXAMPLE -->
            <div class="box box-default">
                <div class="box-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th><?= Core\_t('Permission', 'tritan-cms'); ?></th>
                                <th class="text-center"><?= Core\_t('Allow', 'tritan-cms'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php User\role_perm(); ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th><?= Core\_t('Permission', 'tritan-cms'); ?></th>
                                <th class="text-center"><?= Core\_t('Allow', 'tritan-cms'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
    </div>
    <!-- /.box -->

</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->
</form>
<?php $this->stop(); ?>

<?php
use TriTan\Container;

/**
 * Update Role View
 *
 * @license GPLv3
 *
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
Container::getInstance()->{'set'}('screen_parent', 'roles');
Container::getInstance()->{'set'}('screen_child', 'role');
?>

<!-- form start -->
<form method="post" action="<?= admin_url('role/edit-role/'); ?>" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-user"></i>
                <h3 class="box-title"><?= esc_html__('Update Role'); ?></h3>

                <div class="pull-right">
                    <input type="hidden" name="role_id" value="<?= (int) $this->role->getId(); ?>" />
                    <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= esc_html__('Update'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= admin_url('role/'); ?>'"><i class="fa fa-ban"></i> <?= esc_html__('Cancel'); ?></button>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">

            <?= (new \TriTan\Common\FlashMessages())->showMessage(); ?>

            <!-- SELECT2 EXAMPLE -->
            <div class="box box-default">

                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= esc_html__('Role Name'); ?></label>
                                <input class="form-control" name="role_name" type="text" value="<?= $this->role->getName(); ?>" required/>
                            </div>
                        </div>
                        <!-- /.col -->

                        <div class="col-md-6">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= esc_html__('Role Key'); ?></label>
                                <input class="form-control" name="role_key" type="text" value="<?= $this->role->getKey(); ?>" required/>
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
                                <th><?= esc_html__('Permission'); ?></th>
                                <th class="text-center"><?= esc_html__('Allow'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php role_perm((int) $this->role->getId()); ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th><?= esc_html__('Permission'); ?></th>
                                <th class="text-center"><?= esc_html__('Allow'); ?></th>
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
<?php $this->stop(); ?>

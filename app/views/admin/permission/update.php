<?php
use TriTan\Container;

/**
 * View Permission View
 *
 * @license GPLv3
 *
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
$ePerm = new \TriTan\Common\Acl\PermissionRepository(
    new \TriTan\Common\Acl\PermissionMapper(
        new TriTan\Database(),
        new \TriTan\Common\Context\HelperContext()
    )
);
Container::getInstance()->{'set'}('screen_parent', 'roles');
Container::getInstance()->{'set'}('screen_child', 'perm');

?>

<!-- form start -->
<form method="post" action="<?= admin_url('permission/' . (int) $this->perm->getId() . '/'); ?>" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-text-width"></i>
                <h3 class="box-title"><?= esc_html__('Update Permission'); ?></h3>

                <div class="pull-right">
                    <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= esc_html__('Update'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= admin_url('permission/'); ?>'"><i class="fa fa-ban"></i> <?= esc_html__('Cancel'); ?></button>
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
                                <label><font color="red">*</font> <?= esc_html__('Name'); ?></label>
                                <input type="text" class="form-control" name="permission_name" value="<?= $ePerm->findNameById((int) $this->perm->getId()); ?>" required>
                            </div>

                        </div>
                        <!-- /.col -->

                        <div class="col-md-6">

                            <div class="form-group">
                                <label><font color="red">*</font> <?= esc_html__('Key'); ?></label>
                                <input type="text" class="form-control" name="permission_key" value="<?= $ePerm->findKeyById((int) $this->perm->getId()); ?>" required>
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
<?php $this->stop(); ?>

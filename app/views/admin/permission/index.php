<?php
use TriTan\Container;

/**
 * Manage Permissions View
 *
 * @license GPLv3
 *
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
$perms = new \TriTan\Common\Acl\PermissionRepository(
    new \TriTan\Common\Acl\PermissionMapper(
        new TriTan\Database(),
        new \TriTan\Common\Context\HelperContext()
    )
);
Container::getInstance()->{'set'}('screen_parent', 'roles');
Container::getInstance()->{'set'}('screen_child', 'perm');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-text-width"></i>
            <h3 class="box-title"><?= esc_html__('Permissions'); ?></h3>

            <div class="pull-right">
                <button type="button" class="btn btn-icon btn-success" onclick="window.location = '<?= admin_url('permission/create/'); ?>'"><i class="fa fa-plus-circle"></i> <?= esc_html__('Create Permission'); ?></button>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

        <?= (new \TriTan\Common\FlashMessages())->showMessage(); ?>

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= esc_html__('Key'); ?></th>
                            <th class="text-center"><?= esc_html__('Name'); ?></th>
                            <th class="text-center"><?= esc_html__('Edit'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $listPerms = $perms->findAll('full'); foreach ($listPerms as $v) : ?>
                            <tr class="gradeX">
                            <td class="text-center"><?=$v['Key'];?></td>
                            <td class="text-center"><?=$v['Name'];?></td>
                            <td class="text-center"><a href="<?=admin_url('permission/' . (int) $v['ID'] . '/');?>" data-toggle="tooltip" data-placement="top" title="<?=esc_attr__('Update');?>" class="btn bg-yellow"><i class="fa fa-edit"></i></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= esc_html__('Key'); ?></th>
                            <th class="text-center"><?= esc_html__('Name'); ?></th>
                            <th class="text-center"><?= esc_html__('Edit'); ?></th>
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
<?php $this->stop(); ?>

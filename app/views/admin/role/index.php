<?php
use TriTan\Container;

/**
 * Manage Roles View
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

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-text-width"></i>
            <h3 class="box-title"><?= esc_html__('Roles'); ?></h3>

            <div class="pull-right">
                <button type="button" class="btn btn-success" onclick="window.location = '<?= admin_url('role/create/'); ?>'"><i class="fa fa-plus-circle"></i> <?= esc_html__('Create a Role'); ?></button>
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
                            <th class="text-center"><?= esc_html__('Name'); ?></th>
                            <th class="text-center"><?= esc_html__('Key'); ?></th>
                            <th class="text-center"><?= esc_html__('Edit'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ($this->roles as $role) : ?>
                                <tr class="gradeX">
                                    <td class="text-center"><?=$role['Name'];?></td>
                                    <td class="text-center"><?=$role['Key'];?></td>
                                    <td class="text-center"><a href="<?= admin_url('role/' . (int) $role['ID'] . '/'); ?>" data-toggle="tooltip" data-placement="top" title="View/Edit" class="btn bg-yellow"><i class="fa fa-edit"></i></a></td>
                                </tr>
                        <?php
                            endforeach;
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
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

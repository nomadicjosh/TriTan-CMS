<?php
use TriTan\Container;

/**
 * User Permission View
 *
 * @license GPLv3
 *
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/admin');
$app->view->block('admin');
Container::getInstance()->{'set'}('screen_parent', 'users');
Container::getInstance()->{'set'}('screen_child', 'user');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= esc_html__('Manage Permissions for '); ?> <?= get_name(esc_html((int) $user['user_id']));?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= admin_url(); ?>"><i class="fa fa-dashboard"></i> <?= esc_html__('Dashboard'); ?></a></li>
            <li><a href="<?= admin_url('user/'); ?>"><i class="fa fa-group"></i> <?= esc_html__('Users'); ?></a></li>
            <li class="active"><?= esc_html__('Manage User Permissions'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <?= (new \TriTan\Common\FlashMessages())->showMessage(); ?>

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= admin_url('user/' . esc_html((int)$user['user_id']) . '/perm/'); ?>" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th><?=esc_html__('Permission');?></th>
                                <th class="text-center"><?=esc_html__('Allow');?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php user_permission(esc_html((int)$user['user_id'])); ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th><?=esc_html__('Permission');?></th>
                                <th class="text-center"><?=esc_html__('Allow');?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <button<?= ae('user_inquiry_only');?> type="submit" class="btn btn-primary"><?=esc_html__('Save');?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location='<?=admin_url('user/');?>'"><?=esc_html__('Cancel');?></button>
                </div>
            </form>
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>

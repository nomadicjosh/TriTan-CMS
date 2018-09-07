<?php
use TriTan\Container as c;

/**
 * User's List View
 *
 * @license GPLv3
 *
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
c::getInstance()->{'set'}('screen_parent', 'users');
c::getInstance()->{'set'}('screen_child', 'all-users');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-user"></i>
            <h3 class="box-title"><?= esc_html__('Users'); ?></h3>

            <div class="pull-right">
                <button type="button"<?=ae('create_users');?> class="btn btn-warning" onclick="window.location='<?=admin_url('user/create/');?>'"><i class="fa fa-plus"></i> <?= esc_html__('New User'); ?></button>
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
                            <th class="text-center"><?= esc_html__('Username'); ?></th>
                            <th class="text-center"><?= esc_html__('First Name'); ?></th>
                            <th class="text-center"><?= esc_html__('Last Name'); ?></th>
                            <th class="text-center"><?= esc_html__('Status'); ?></th>
                            <th class="text-center"><?= esc_html__('Role'); ?></th>
                            <th class="text-center"><?= esc_html__('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->users as $user) : $data = get_userdata($user['user_id']); ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= $user['user_login']; ?></td>
                                <td class="text-center"><?= $user['user_fname']; ?></td>
                                <td class="text-center"><?= $user['user_lname']; ?></td>
                                <td class="text-center">
                                    <span class="label <?= ttcms_user_status_label(get_user_option('status', $user['user_id'])); ?>" style="font-size:1em;font-weight: bold;">
                                        <?= (get_user_option('status', $user['user_id']) == 'A' ? esc_html__('Active') : esc_html__('Inactive')); ?>
                                    </span>
                                </td>
                                <td class="text-center"><?= get_role_by_id(get_user_option('role', $user['user_id']))['role']['role_name'];?></td>
                                <td class="text-center">
                                    <a href="<?= admin_url('user/' . (int) $user['user_id'] . '/'); ?>" data-toggle="tooltip" data-placement="top" title="<?= esc_attr__('Update'); ?>"><button type="button" class="btn bg-yellow"><i class="fa fa-edit"></i></button></a>
                                    <!--<a href="<?= admin_url('user/' . (int) $user['user_id'] . '/perm/'); ?>" data-toggle="tooltip" data-placement="top" title="<?= esc_attr__('Edit Permissions') ;?>"><button type="button" class="btn bg-purple"><i class="fa fa-key"></i></button></a>-->
                                    <a href="<?= admin_url('user/' . (int) $user['user_id'] . '/reset-password/'); ?>" data-toggle="tooltip" data-placement="top" title="<?= esc_attr__('Reset Password'); ?>"><button type="button" class="btn bg-purple"><i class="fa fa-refresh"></i></button></a>
                                    <?php if (!isset($this->app->req->cookie['SWITCH_USERBACK']) && (int) $user['user_id'] !== (int) $this->current_user_id) : ?>
                                        <a<?= ae('switch_user'); ?> href="<?= admin_url('user/' . (int) esc_html($user['user_id']) . '/switch-to/'); ?>" data-toggle="tooltip" data-placement="top" title="<?= esc_attr__('Switch to'); ?>"><button type="button" class="btn bg-blue"><i class="fa fa-exchange"></i></button></a>
                                    <?php endif; ?>
                                    <?php if ((int) $user['user_id'] !== (int) 1 && (int) $user['user_id'] !== (int) $this->current_user_id) : ?>
                                        <a<?= ae('delete_users'); ?> href="#" data-toggle="modal" data-target="#delete-<?= (int) $user['user_id']; ?>"><button type="button" class="btn bg-red"><i class="fa fa-trash-o"></i></button></a>
                                    <?php endif; ?>

                                    <div class="modal" id="delete-<?= (int) $user['user_id']; ?>">
                                        <form method="post" action="<?=admin_url('user/' . (int) $user['user_id'] . '/d/');?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= get_name((int) $user['user_id']); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?= esc_html__('Are you sure you want to remove the user from this site?'); ?></p>
                                                    <div class="alert alert-info"><?=esc_html__("Would you like to assign this user's content to a different user? Choose below.");?></div>
                                                    <select class="form-control select2" name="assign_id" style="width: 100%;">
                                                        <option>&nbsp;</option>
                                                        <?php get_users_reassign((int) $user['user_id']); ?>
                                                    </select>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= esc_html__('Close'); ?></button>
                                                    <button type="submit" class="btn btn-primary"><?= esc_html__('Confirm'); ?></button>
                                                </div>
                                            </div>
                                            <!-- /.modal-content -->
                                        </div>
                                        <!-- /.modal-dialog -->
                                    </form>
                                    </div>
                                    <!-- /.modal -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= esc_html__('Username'); ?></th>
                            <th class="text-center"><?= esc_html__('First Name'); ?></th>
                            <th class="text-center"><?= esc_html__('Last Name'); ?></th>
                            <th class="text-center"><?= esc_html__('Status'); ?></th>
                            <th class="text-center"><?= esc_html__('Role'); ?></th>
                            <th class="text-center"><?= esc_html__('Action'); ?></th>
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

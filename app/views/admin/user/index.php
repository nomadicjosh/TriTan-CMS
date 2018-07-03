<?php
use TriTan\Config;
use TriTan\Functions\Auth;
use TriTan\Functions\User;
use TriTan\Functions\Core;
use TriTan\Functions\Dependency;

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
Config::set('screen_parent', 'users');
Config::set('screen_child', 'all-users');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-user"></i>
            <h3 class="box-title"><?= Core\_t('Users', 'tritan-cms'); ?></h3>

            <div class="pull-right">
                <button type="button"<?=Auth\ae('create_users');?> class="btn btn-warning" onclick="window.location='<?=Core\get_base_url();?>admin/user/create/'"><i class="fa fa-plus"></i> <?= Core\_t('New User', 'tritan-cms'); ?></button>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

        <?= Dependency\_ttcms_flash()->showMessage(); ?>

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= Core\_t('Username', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('First Name', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Last Name', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Status', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Role', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Action', 'tritan-cms'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->users as $user) : $role = Auth\get_role_by_id($user['user_role']); ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= $user['user_login']; ?></td>
                                <td class="text-center"><?= $user['user_fname']; ?></td>
                                <td class="text-center"><?= $user['user_lname']; ?></td>
                                <td class="text-center">
                                    <span class="label <?= User\ttcms_user_status_label(User\get_user_option('status', $user['user_id'])); ?>" style="font-size:1em;font-weight: bold;">
                                        <?= (User\get_user_option('status', $user['user_id']) == 'A' ? Core\_t('Active', 'tritan-cms') : Core\_t('Inactive', 'tritan-cms')); ?>
                                    </span>
                                </td>
                                <td class="text-center"><?= Auth\get_role_by_id(User\get_user_option('role', $user['user_id']))['role']['role_name'];?></td>
                                <td class="text-center">
                                    <a href="<?= Core\get_base_url(); ?>admin/user/<?= (int) $user['user_id']; ?>/" data-toggle="tooltip" data-placement="top" title="Update"><button type="button" class="btn bg-yellow"><i class="fa fa-edit"></i></button></a>
                                    <!--<a href="<?= Core\get_base_url(); ?>admin/user/<?= (int) $user['user_id']; ?>/perm/" data-toggle="tooltip" data-placement="top" title="Edit Permissions"><button type="button" class="btn bg-purple"><i class="fa fa-key"></i></button></a>-->
                                    <a href="<?= Core\get_base_url(); ?>admin/user/<?= (int) $user['user_id']; ?>/reset-password/" data-toggle="tooltip" data-placement="top" title="Reset Password"><button type="button" class="btn bg-purple"><i class="fa fa-refresh"></i></button></a>
                                    <?php if (!isset($this->app->req->cookie['SWITCH_USERBACK']) && (int) $user['user_id'] !== (int) $this->current_user_id) : ?>
                                        <a<?= Auth\ae('switch_user'); ?> href="<?= Core\get_base_url(); ?>admin/user/<?= (int) Core\_escape($user['user_id']); ?>/switch-to/" data-toggle="tooltip" data-placement="top" title="Switch to"><button type="button" class="btn bg-blue"><i class="fa fa-exchange"></i></button></a>
                                    <?php endif; ?>
                                    <?php if ((int) $user['user_id'] !== (int) 1 && (int) $user['user_id'] !== (int) $this->current_user_id) : ?>
                                        <a<?= Auth\ae('delete_users'); ?> href="#" data-toggle="modal" data-target="#delete-<?= (int) $user['user_id']; ?>"><button type="button" class="btn bg-red"><i class="fa fa-trash-o"></i></button></a>
                                    <?php endif; ?>

                                    <div class="modal" id="delete-<?= (int) $user['user_id']; ?>">
                                        <form method="post" action="<?=Core\get_base_url();?>admin/user/<?= (int) $user['user_id']; ?>/d/">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= User\get_name((int) $user['user_id']); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?= Core\_t('Are you sure you want to remove the user from this site?', 'tritan-cms'); ?></p>
                                                    <div class="alert alert-info"><?=Core\_t("Would you like to assign this user's content to a different user? Choose below.");?></div>
                                                    <select class="form-control select2" name="assign_id" style="width: 100%;">
                                                        <option>&nbsp;</option>
                                                        <?php User\get_users_reassign((int) $user['user_id']); ?>
                                                    </select>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Core\_t('Close', 'tritan-cms'); ?></button>
                                                    <button type="submit" class="btn btn-primary"><?= Core\_t('Confirm', 'tritan-cms'); ?></button>
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
                            <th class="text-center"><?= Core\_t('Username', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('First Name', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Last Name', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Status', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Role', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Action', 'tritan-cms'); ?></th>
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

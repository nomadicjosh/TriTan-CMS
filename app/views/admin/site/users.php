<?php
use TriTan\Config;
use TriTan\Functions\Db;
use TriTan\Functions\Auth;
use TriTan\Functions\User;
use TriTan\Functions\Core;
use TriTan\Functions\Dependency;

/**
 * Site Users View
 *
 * @license GPLv3
 *
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
Config::set('screen_parent', 'sites');
Config::set('screen_child', 'sites-user');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-user"></i>
            <h3 class="box-title"><?= Core\_t('Multisite Users', 'tritan-cms'); ?></h3>
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
                            <th class="text-center"><?= Core\_t('Action', 'tritan-cms'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->users as $user) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= $user['user_login']; ?></td>
                                <td class="text-center"><?= $user['user_fname']; ?></td>
                                <td class="text-center"><?= $user['user_lname']; ?></td>
                                <td class="text-center">
                                    <?php if (!isset($this->app->req->cookie['SWITCH_USERBACK']) && (int) $user['user_id'] !== $this->current_user_id) : ?>
                                        <a<?= Auth\ae('switch_user'); ?> href="<?= Core\get_base_url(); ?>admin/user/<?= (int) $user['user_id']; ?>/switch-to/" data-toggle="tooltip" data-placement="top" title="Switch to"><button type="button" class="btn bg-blue"><i class="fa fa-exchange"></i></button></a>
                                    <?php endif; ?>
                                    <?php if ((int) $user['user_id'] !== 1) : ?>
                                        <a<?= Auth\ae('delete_users'); ?> href="#" data-toggle="modal" data-target="#delete-<?= (int) $user['user_id']; ?>"><button type="button" class="btn bg-red"><i class="fa fa-trash-o"></i></button></a>
                                    <?php endif; ?>
                                    <?php if (Db\does_user_have_sites($user['user_id'])) : ?>
                                    <div class="modal" id="delete-<?= $user['user_id']; ?>">
                                        <form method="post" action="<?=Core\get_base_url();?>admin/site/users/<?= (int) $user['user_id']; ?>/d/">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= User\get_name($user['user_id']); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?= Core\_t("Are you sure you want to delete this site and all it's content", 'tritan-cms'); ?></p>
                                                    <div class="alert alert-info"><?=Core\_t("Would you like to assign this user's site(s) to a different user? Choose below.");?></div>
                                                    <select class="form-control select2" name="assign_id" style="width: 100%;">
                                                        <option>&nbsp;</option>
                                                        <?php User\get_users_reassign((int) $user['user_id']); ?>
                                                    </select>
                                                </div>
                                                <div class="modal-footer">
                                                    <input type="hidden" name="role" value="admin" />
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
                                    <?php else: ?>
                                    <div class="modal" id="delete-<?= (int) $user['user_id']; ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= User\get_name((int) $user['user_id']); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?= Core\_t('Are you sure you want to permanently delete this user?', 'tritan-cms'); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Core\_t('Close', 'tritan-cms'); ?></button>
                                                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= Core\get_base_url(); ?>admin/site/users/<?= (int) $user['user_id']; ?>/d/'"><?= Core\_t('Confirm', 'tritan-cms'); ?></button>
                                                </div>
                                            </div>
                                            <!-- /.modal-content -->
                                        </div>
                                        <!-- /.modal-dialog -->
                                    </div>
                                    <!-- /.modal -->
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= Core\_t('Username', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('First Name', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Last Name', 'tritan-cms'); ?></th>
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

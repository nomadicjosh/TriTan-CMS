<?php
use TriTan\Container;

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
Container::getInstance()->{'set'}('screen_parent', 'sites');
Container::getInstance()->{'set'}('screen_child', 'sites-user');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-user"></i>
            <h3 class="box-title"><?= esc_html__('Multisite Users'); ?></h3>
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
                            <th class="text-center"><?= esc_html__('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->users as $user) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= $user->getLogin(); ?></td>
                                <td class="text-center"><?= $user->getFname(); ?></td>
                                <td class="text-center"><?= $user->getLname(); ?></td>
                                <td class="text-center">
                                    <?php if (!isset($this->app->req->cookie['SWITCH_USERBACK']) && (int) $user->getId() !== $this->current_user_id) : ?>
                                        <a<?= ae('switch_user'); ?> href="<?= admin_url('user/' . (int) $user->getId() . '/switch-to/'); ?>" data-toggle="tooltip" data-placement="top" title="Switch to"><button type="button" class="btn bg-blue"><i class="fa fa-exchange"></i></button></a>
                                    <?php endif; ?>
                                    <?php if ((int) $user->getId() !== 1) : ?>
                                        <a<?= ae('delete_users'); ?> href="#" data-toggle="modal" data-target="#delete-<?= (int) $user->getId(); ?>"><button type="button" class="btn bg-red"><i class="fa fa-trash-o"></i></button></a>
                                    <?php endif; ?>
                                    <?php if (does_user_have_sites($user->getId())) : ?>
                                    <div class="modal" id="delete-<?= $user->getId(); ?>">
                                        <form method="post" action="<?= admin_url('site/users/' . (int) $user->getId() . '/d/'); ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= get_name($user->getId()); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?= esc_html__("Are you sure you want to delete this site and all it's content"); ?></p>
                                                    <div class="alert alert-info"><?=esc_html__("Would you like to assign this user's site(s) to a different user? Choose below.");?></div>
                                                    <select class="form-control select2" name="assign_id" style="width: 100%;">
                                                        <option>&nbsp;</option>
                                                        <?php get_users_reassign((int) $user->getId()); ?>
                                                    </select>
                                                </div>
                                                <div class="modal-footer">
                                                    <input type="hidden" name="role" value="admin" />
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
                                    <?php else: ?>
                                    <div class="modal" id="delete-<?= (int) $user->getId(); ?>">
                                        <form method="post" action="<?= admin_url('site/users/' . (int) $user->getId() . '/d/'); ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= get_name((int) $user->getId()); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?= esc_html__('Are you sure you want to permanently delete this user?'); ?></p>
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
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= esc_html__('Username'); ?></th>
                            <th class="text-center"><?= esc_html__('First Name'); ?></th>
                            <th class="text-center"><?= esc_html__('Last Name'); ?></th>
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

<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Functions as func;
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
            <h3 class="box-title"><?= func\_t('Users', 'tritan-cms'); ?></h3>
            
            <div class="pull-right">
                <button type="button"<?=func\ae('create_users');?> class="btn btn-warning" onclick="window.location='<?=func\get_base_url();?>admin/user/create/'"><i class="fa fa-plus"></i> <?= func\_t('New User', 'tritan-cms'); ?></button>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

        <?= func\_ttcms_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= func\_t('Username'); ?></th>
                            <th class="text-center"><?= func\_t('First Name'); ?></th>
                            <th class="text-center"><?= func\_t('Last Name'); ?></th>
                            <th class="text-center"><?= func\_t('Status'); ?></th>
                            <th class="text-center"><?= func\_t('Role'); ?></th>
                            <th class="text-center"><?= func\_t('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->users as $user) : $role = func\get_role_by_id($user['user_role']); ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= $user['user_login']; ?></td>
                                <td class="text-center"><?= $user['user_fname']; ?></td>
                                <td class="text-center"><?= $user['user_lname']; ?></td>
                                <td class="text-center">
                                    <span class="label <?= func\ttcms_user_status_label($user['user_status']); ?>" style="font-size:1em;font-weight: bold;">
                                        <?= ($user['user_status'] == 'A' ? func\_t('Active') : func\_t('Inactive')); ?>
                                    </span>
                                </td>
                                <td class="text-center"><?=func\get_role_by_id($user['user_role'])['role']['role_name'];?></td>
                                <td class="text-center">
                                    <a href="<?= func\get_base_url(); ?>admin/user/<?= (int) $user['user_id']; ?>/" data-toggle="tooltip" data-placement="top" title="Update"><button type="button" class="btn bg-yellow"><i class="fa fa-edit"></i></button></a>
                                    <!--<a href="<?= func\get_base_url(); ?>admin/user/<?= (int) $user['user_id']; ?>/perm/" data-toggle="tooltip" data-placement="top" title="Edit Permissions"><button type="button" class="btn bg-purple"><i class="fa fa-key"></i></button></a>-->
                                    <a href="<?= func\get_base_url(); ?>admin/user/<?= (int) $user['user_id']; ?>/reset-password/" data-toggle="tooltip" data-placement="top" title="Reset Password"><button type="button" class="btn bg-purple"><i class="fa fa-refresh"></i></button></a>
                                    <?php if (!isset($this->app->req->cookie['SWITCH_USERBACK']) && (int) $user['user_id'] !== (int) $this->current_user_id) : ?>
                                        <a<?= func\ae('switch_user'); ?> href="<?= func\get_base_url(); ?>admin/user/<?= (int) func\_escape($user['user_id']); ?>/switch-to/" data-toggle="tooltip" data-placement="top" title="Switch to"><button type="button" class="btn bg-blue"><i class="fa fa-exchange"></i></button></a>
                                    <?php endif; ?>
                                    <?php if ((int) $user['user_id'] !== (int) 1 && (int) $user['user_id'] !== (int) $this->current_user_id) : ?>
                                        <a<?= func\ae('delete_users'); ?> href="#" data-toggle="modal" data-target="#delete-<?= (int) $user['user_id']; ?>"><button type="button" class="btn bg-red"><i class="fa fa-trash-o"></i></button></a>
                                    <?php endif; ?>

                                    <div class="modal" id="delete-<?= (int) $user['user_id']; ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= func\get_name((int) $user['user_id']); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?= func\_t('Are you sure you want to remove the user from this site?'); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= func\_t('Close'); ?></button>
                                                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= func\get_base_url(); ?>admin/user/<?= (int) $user['user_id']; ?>/d/'"><?= func\_t('Confirm'); ?></button>
                                                </div>
                                            </div>
                                            <!-- /.modal-content -->
                                        </div>
                                        <!-- /.modal-dialog -->
                                    </div>
                                    <!-- /.modal -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= func\_t('Username'); ?></th>
                            <th class="text-center"><?= func\_t('First Name'); ?></th>
                            <th class="text-center"><?= func\_t('Last Name'); ?></th>
                            <th class="text-center"><?= func\_t('Status'); ?></th>
                            <th class="text-center"><?= func\_t('Role'); ?></th>
                            <th class="text-center"><?= func\_t('Action'); ?></th>
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
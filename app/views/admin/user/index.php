<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * User's List View
 *  
 * @license GPLv3
 * 
 * @since       1.0.0
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/admin');
$app->view->block('admin');
define('SCREEN_PARENT', 'users');
define('SCREEN', 'all-users');

?>        

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-user"></i>
            <h3 class="box-title"><?= _t('Users', 'tritan-cms'); ?></h3>
            
            <div class="pull-right">
                <button type="button"<?=ae('create_users');?> class="btn btn-warning" onclick="window.location='<?=get_base_url();?>admin/user/create/'"><i class="fa fa-plus"></i> <?= _t('New User', 'tritan-cms'); ?></button>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

        <?= _ttcms_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= _t('Username'); ?></th>
                            <th class="text-center"><?= _t('First Name'); ?></th>
                            <th class="text-center"><?= _t('Last Name'); ?></th>
                            <th class="text-center"><?= _t('Status'); ?></th>
                            <th class="text-center"><?= _t('Role'); ?></th>
                            <th class="text-center"><?= _t('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) : $role = get_role_by_id(_escape($user['user_role'])); ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= _escape($user['user_login']); ?></td>
                                <td class="text-center"><?= _escape($user['user_fname']); ?></td>
                                <td class="text-center"><?= _escape($user['user_lname']); ?></td>
                                <td class="text-center">
                                    <span class="label <?= ttcms_user_status_label(_escape($user['user_status'])); ?>" style="font-size:1em;font-weight: bold;">
                                        <?= (_escape($user['user_status']) == 'A' ? _t('Active') : _t('Inactive')); ?>
                                    </span>
                                </td>
                                <td class="text-center"><?=get_role_by_id(_escape($user['user_role']))['role']['role_name'];?></td>
                                <td class="text-center">
                                    <a href="<?= get_base_url(); ?>admin/user/<?= (int) _escape($user['user_id']); ?>/" data-toggle="tooltip" data-placement="top" title="Update"><button type="button" class="btn bg-yellow"><i class="fa fa-edit"></i></button></a>
                                    <!--<a href="<?= get_base_url(); ?>admin/user/<?= (int) _escape($user['user_id']); ?>/perm/" data-toggle="tooltip" data-placement="top" title="Edit Permissions"><button type="button" class="btn bg-purple"><i class="fa fa-key"></i></button></a>-->
                                    <?php if (!isset($app->req->cookie['SWITCH_USERBACK']) && (int) _escape($user['user_id']) !== get_current_user_id()) : ?>
                                        <a<?= ae('switch_user'); ?> href="<?= get_base_url(); ?>admin/user/<?= (int) _escape($user['user_id']); ?>/switch-to/" data-toggle="tooltip" data-placement="top" title="Switch to"><button type="button" class="btn bg-blue"><i class="fa fa-exchange"></i></button></a>
                                    <?php endif; ?>
                                    <?php if ((int) _escape($user['user_id']) !== (int) 1 && (int) _escape($user['user_id']) !== get_current_user_id()) : ?>
                                        <a<?= ae('delete_users'); ?> href="#" data-toggle="modal" data-target="#delete-<?= (int) _escape($user['user_id']); ?>"><button type="button" class="btn bg-red"><i class="fa fa-trash-o"></i></button></a>
                                    <?php endif; ?>

                                    <div class="modal" id="delete-<?= (int) _escape($user['user_id']); ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= get_name((int) _escape($user['user_id'])); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?= _t('Are you sure you want to remove the user from this site?'); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                                                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>admin/user/<?= (int) _escape($user['user_id']); ?>/d/'"><?= _t('Confirm'); ?></button>
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
                            <th class="text-center"><?= _t('Username'); ?></th>
                            <th class="text-center"><?= _t('First Name'); ?></th>
                            <th class="text-center"><?= _t('Last Name'); ?></th>
                            <th class="text-center"><?= _t('Status'); ?></th>
                            <th class="text-center"><?= _t('Role'); ?></th>
                            <th class="text-center"><?= _t('Action'); ?></th>
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
<?php $app->view->stop(); ?>
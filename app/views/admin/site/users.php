<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
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
            <h3 class="box-title"><?= _t('Multisite Users', 'tritan-cms'); ?></h3>
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
                            <th class="text-center"><?= _t('Action'); ?></th>
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
                                        <a<?= ae('switch_user'); ?> href="<?= get_base_url(); ?>admin/user/<?= (int) $user['user_id']; ?>/switch-to/" data-toggle="tooltip" data-placement="top" title="Switch to"><button type="button" class="btn bg-blue"><i class="fa fa-exchange"></i></button></a>
                                    <?php endif; ?>
                                    <?php if ((int) $user['user_id'] !== 1) : ?>
                                        <a<?= ae('delete_users'); ?> href="#" data-toggle="modal" data-target="#delete-<?= (int) $user['user_id']; ?>"><button type="button" class="btn bg-red"><i class="fa fa-trash-o"></i></button></a>
                                    <?php endif; ?>

                                    <div class="modal" id="delete-<?= (int) $user['user_id']; ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= get_name((int) $user['user_id']); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?= _t('Are you sure you want to permanently delete this user?'); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                                                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>admin/site/users/<?= (int) $user['user_id']; ?>/d/'"><?= _t('Confirm'); ?></button>
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
<?php $this->stop(); ?>
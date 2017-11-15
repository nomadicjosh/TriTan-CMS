<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * User Permission View
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
define('SCREEN', 'user');

?>        

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= _t('Manage Permissions for '); ?> <?=get_name(_escape((int)$user['user_id']));?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= get_base_url(); ?>admin/"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></a></li>
            <li><a href="<?= get_base_url(); ?>user/"><i class="fa fa-group"></i> <?= _t('Users'); ?></a></li>
            <li class="active"><?= _t('Manage User Permissions'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <?= _ttcms_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= get_base_url(); ?>admin/user/<?=_escape((int)$user['user_id']);?>/perm/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th><?=_t( 'Permission' );?></th>
                                <th class="text-center"><?=_t( 'Allow' );?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php user_permission(_escape((int)$user['user_id'])); ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th><?=_t( 'Permission' );?></th>
                                <th class="text-center"><?=_t( 'Allow' );?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <button<?=ie('user_inquiry_only');?> type="submit" class="btn btn-primary"><?=_t('Save');?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>user/'"><?=_t( 'Cancel' );?></button>
                </div>
            </form>
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>
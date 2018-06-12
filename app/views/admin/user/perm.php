<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Functions as func;
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
Config::set('screen_parent', 'users');
Config::set('screen_child', 'user');

?>        

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?= func\_t('Manage Permissions for '); ?> <?=func\get_name(func\_escape((int)$user['user_id']));?></h1>
        <ol class="breadcrumb">
            <li><a href="<?= func\get_base_url(); ?>admin/"><i class="fa fa-dashboard"></i> <?= func\_t('Dashboard'); ?></a></li>
            <li><a href="<?= func\get_base_url(); ?>user/"><i class="fa fa-group"></i> <?= func\_t('Users'); ?></a></li>
            <li class="active"><?= func\_t('Manage User Permissions'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <?= func\_ttcms_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <!-- form start -->
            <form method="post" action="<?= func\get_base_url(); ?>admin/user/<?=func\_escape((int)$user['user_id']);?>/perm/" data-toggle="validator" autocomplete="off">
                <div class="box-body">
                    <table id="example2" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th><?=func\_t( 'Permission' );?></th>
                                <th class="text-center"><?=func\_t( 'Allow' );?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php func\user_permission(_escape((int)$user['user_id'])); ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th><?=func\_t( 'Permission' );?></th>
                                <th class="text-center"><?=func\_t( 'Allow' );?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <button<?=func\ie('user_inquiry_only');?> type="submit" class="btn btn-primary"><?=func\_t('Save');?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location='<?=func\get_base_url();?>user/'"><?=func\_t( 'Cancel' );?></button>
                </div>
            </form>
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>
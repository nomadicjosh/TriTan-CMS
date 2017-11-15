<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use Jenssegers\Date\Date;

/**
 * Audit Trail View
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
define('SCREEN_PARENT', 'dashboard');
define('SCREEN', 'audit');

?>        

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-road"></i>
            <h3 class="box-title"><?= _t('Audit Trail', 'tritan-cms'); ?></h3>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

        <?= _ttcms_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example2" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= _t('Action', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Process', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Record', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Username', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Action Date', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Expire Date', 'tritan-cms'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($audit as $aud) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= _escape($aud['action']); ?></td>
                                <td class="text-center"><?= _escape($aud['process']); ?></td>
                                <td class="text-center"><?= _escape($aud['record']); ?></td>
                                <td class="text-center"><?= _escape($aud['uname']); ?></td>
                                <td class="text-center"><?= Date::parse(_escape($aud['created_at']))->format('D, M d, o'); ?></td>
                                <td class="text-center"><?= Date::parse(_escape($aud['expires_at']))->format('D, M d, o'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('Action', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Process', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Record', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Username', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Action Date', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Expire Date', 'tritan-cms'); ?></th>
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
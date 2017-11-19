<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
/**
 * Error Log View
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
$logger = new TriTan\Logger();
Config::set('screen_parent', 'dashboard');
Config::set('screen_child', 'error');

?>        

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-exclamation-triangle"></i>
            <h3 class="box-title"><?= _t('Error Logs', 'tritan-cms'); ?></h3>
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
                            <th class="text-center"><?= _t('Error Type', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('String', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('File', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Line Number', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Action', 'tritan-cms'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($errors as $error) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= $logger->error_constant_to_name(_escape($error['type'])); ?></td>
                                <td class="text-center"><?= _escape($error['string']); ?></td>
                                <td class="text-center"><?=_escape($error['file']); ?></td>
                                <td class="text-center"><?=_escape($error['line']); ?></td>
                                <td class="text-center">
                                    <a href="<?= get_base_url(); ?>admin/error/<?= _escape($error['error_id']); ?>/delete/" class="btn btn-danger"><i class="fa fa-trash-o"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('Error Type', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('String', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('File', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Line Number', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Action', 'tritan-cms'); ?></th>
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
<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
/**
 * Error Log View
 *  
 * @license GPLv3
 * 
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
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
                        <?php foreach ($this->errors as $error) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= $logger->error_constant_to_name($error['type']); ?></td>
                                <td class="text-center"><?= $error['string']; ?></td>
                                <td class="text-center"><?= $error['file']; ?></td>
                                <td class="text-center"><?= $error['line']; ?></td>
                                <td class="text-center">
                                    <a href="<?= get_base_url(); ?>admin/error/<?= $error['error_id']; ?>/delete/" class="btn btn-danger"><i class="fa fa-trash-o"></i></a>
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
<?php $this->stop(); ?>
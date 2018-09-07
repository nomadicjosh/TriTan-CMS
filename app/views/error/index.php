<?php
use TriTan\Container;
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
Container::getInstance()->{'set'}('screen_parent', 'dashboard');
Container::getInstance()->{'set'}('screen_child', 'error');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-exclamation-triangle"></i>
            <h3 class="box-title"><?= esc_html__('Error Logs'); ?></h3>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

        <?= (new \TriTan\Common\FlashMessages())->showMessage(); ?>

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example2" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= esc_html__('Error Type'); ?></th>
                            <th class="text-center"><?= esc_html__('String'); ?></th>
                            <th class="text-center"><?= esc_html__('File'); ?></th>
                            <th class="text-center"><?= esc_html__('Line Number'); ?></th>
                            <th class="text-center"><?= esc_html__('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->errors as $error) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= $logger->errorConstantToName(esc_html($error['type'])); ?></td>
                                <td class="text-center"><?= esc_html($error['string']); ?></td>
                                <td class="text-center"><?= esc_html($error['file']); ?></td>
                                <td class="text-center"><?= esc_html($error['line']); ?></td>
                                <td class="text-center">
                                    <a href="<?= admin_url('error/' . $error['error_id'] . '/delete/'); ?>" class="btn btn-danger"><i class="fa fa-trash-o"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= esc_html__('Error Type'); ?></th>
                            <th class="text-center"><?= esc_html__('String'); ?></th>
                            <th class="text-center"><?= esc_html__('File'); ?></th>
                            <th class="text-center"><?= esc_html__('Line Number'); ?></th>
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

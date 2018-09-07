<?php
use TriTan\Common\Date;
use TriTan\Container;

/**
 * Audit Trail View
 *
 * @license GPLv3
 *
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
Container::getInstance()->{'set'}('screen_parent', 'dashboard');
Container::getInstance()->{'set'}('screen_child', 'audit');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-road"></i>
            <h3 class="box-title"><?= esc_html__('Audit Trail'); ?></h3>
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
                            <th class="text-center"><?= esc_html__('Action'); ?></th>
                            <th class="text-center"><?= esc_html__('Process'); ?></th>
                            <th class="text-center"><?= esc_html__('Record'); ?></th>
                            <th class="text-center"><?= esc_html__('Username'); ?></th>
                            <th class="text-center"><?= esc_html__('Action Date'); ?></th>
                            <th class="text-center"><?= esc_html__('Expire Date'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->audit as $aud) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><?= esc_html($aud['action']); ?></td>
                                <td class="text-center"><?= esc_html($aud['process']); ?></td>
                                <td class="text-center"><?= esc_html($aud['record']); ?></td>
                                <td class="text-center"><?= esc_html($aud['uname']); ?></td>
                                <td class="text-center"><?= (new Date())->{'laci2date'}('D, M d, o', esc_html($aud['created_at'])); ?></td>
                                <td class="text-center"><?= (new Date())->{'laci2date'}('D, M d, o', esc_html($aud['expires_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= esc_html__('Action'); ?></th>
                            <th class="text-center"><?= esc_html__('Process'); ?></th>
                            <th class="text-center"><?= esc_html__('Record'); ?></th>
                            <th class="text-center"><?= esc_html__('Username'); ?></th>
                            <th class="text-center"><?= esc_html__('Action Date'); ?></th>
                            <th class="text-center"><?= esc_html__('Expire Date'); ?></th>
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

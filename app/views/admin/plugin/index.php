<?php

use TriTan\Functions\Dependency;
use TriTan\Functions\Core;

$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Config::set('screen_parent', 'plugins');
TriTan\Config::set('screen_child', 'installed-plugins');
$plugins_header = $this->app->hook->{'getPluginsHeader'}(BASE_PATH . 'plugins' . DS);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-thumb-tack"></i>
            <h3 class="box-title"><?= Core\_t('Plugins', 'tritan-cms'); ?></h3>

            <div class="pull-right">
                <button type="button" class="btn btn-success" onclick="window.location = '<?= Core\get_base_url(); ?>admin/plugin/install/'"><i class="fa fa-upload"></i> <?= Core\_t('Install', 'tritan-cms'); ?></button>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

    <?= Dependency\_ttcms_flash()->showMessage(); ?>

      <div class="row">

        <!-- left column -->
        <div class="col-md-12">
          <!-- general form elements -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title"><?=Core\_t('Plugins', 'tritan-cms');?></h3>
            </div>
            <!-- /.box-header -->
              <div class="box-body">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= Core\_t('Name', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Description', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Action', 'tritan-cms'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plugins_header as $plugin) : ?>
                            <?php if ($this->app->hook->{'isPluginActivated'}($plugin['filename']) == true) : ?>
                            <tr class="gradeX" style="background-color:#B0E0E6 !important;">
                            <?php else : ?>
                            <tr class="gradeX">
                            <?php endif; ?>
                                <td class="text-center"><?= $plugin['Name']; ?></td>
                                <td class="text-center"><?= $plugin['Description']; ?></td>
                                <?php if ($this->app->hook->{'isPluginActivated'}($plugin['filename']) == true) : ?>
                                <td class="text-center"><a class="btn btn-danger" href="<?= Core\sanitize_url(Core\get_base_url() . 'admin/plugin/deactivate/?id=' . $plugin['filename'], true);?>"><i class="fa fa-minus-circle"></i></a></td>
                                <?php else : ?>
                                <td class="text-center"><a class="btn btn-success" href="<?= Core\sanitize_url(Core\get_base_url() . 'admin/plugin/activate/?id=' . $plugin['filename'], true);?>"><i class="fa fa-plus-circle"></i></a></td>
                                <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= Core\_t('Name', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Description', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Action', 'tritan-cms'); ?></th>
                        </tr>
                    </tfoot>
                </table>
              </div>
              <!-- /.box-body -->
          </div>
          <!-- /.box-primary -->
        </div>
        <!-- /.left column -->

        </div>
        <!--/.row -->
    </section>
    <!-- /.Main content -->

</div>
<!-- /.Content Wrapper. Contains page content -->
<?php $this->stop(); ?>

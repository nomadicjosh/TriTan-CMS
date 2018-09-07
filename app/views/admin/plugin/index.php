<?php
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Container::getInstance()->{'set'}('screen_parent', 'plugins');
TriTan\Container::getInstance()->{'set'}('screen_child', 'installed-plugins');
$plugins_header = (new TriTan\Common\Plugin\PluginHeader())->{'read'}(TTCMS_PLUGIN_DIR);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-thumb-tack"></i>
            <h3 class="box-title"><?= esc_html__('Plugins'); ?></h3>

            <div class="pull-right">
                <button type="button" class="btn btn-success" onclick="window.location = '<?= admin_url('plugin/install/'); ?>'"><i class="fa fa-upload"></i> <?= esc_html__('Install'); ?></button>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

    <?= (new \TriTan\Common\FlashMessages())->showMessage(); ?>

      <div class="row">

        <!-- left column -->
        <div class="col-md-12">
          <!-- general form elements -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title"><?=esc_html__('Plugins');?></h3>
            </div>
            <!-- /.box-header -->
              <div class="box-body">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= esc_html__('Name'); ?></th>
                            <th class="text-center"><?= esc_html__('Version'); ?></th>
                            <th class="text-center"><?= esc_html__('Description'); ?></th>
                            <th class="text-center"><?= esc_html__('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plugins_header as $plugin) : ?>
                            <?php if (is_plugin_activated($plugin['filename']) == true) : ?>
                            <tr class="gradeX" style="background-color:#B0E0E6 !important;">
                            <?php else : ?>
                            <tr class="gradeX">
                            <?php endif; ?>
                                <td class="text-center"><?= $plugin['Name']; ?></td>
                                <td class="text-center"><?= $plugin['Version']; ?></td>
                                <td class="text-center">
                                    <?= $plugin['Description']; ?> <br />
                                    <strong><?=esc_html__('Developer:');?></strong> 
                                    <a href="<?= esc_url($plugin['AuthorURI']); ?>"><?= $plugin['Author']; ?></a> | 
                                    <a href="<?= esc_url($plugin['PluginURI']); ?>"><?= esc_html__('View Details'); ?></a>
                                </td>
                                <?php if (is_plugin_activated($plugin['filename']) == true) : ?>
                                <td class="text-center"><a class="btn btn-danger" href="<?=admin_url('plugin/deactivate/?id=' . $plugin['filename']);?>"><i class="fa fa-minus-circle"></i></a></td>
                                <?php else : ?>
                                <td class="text-center"><a class="btn btn-success" href="<?=admin_url('plugin/activate/?id=' . $plugin['filename']);?>"><i class="fa fa-plus-circle"></i></a></td>
                                <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= esc_html__('Name'); ?></th>
                            <th class="text-center"><?= esc_html__('Version'); ?></th>
                            <th class="text-center"><?= esc_html__('Description'); ?></th>
                            <th class="text-center"><?= esc_html__('Action'); ?></th>
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

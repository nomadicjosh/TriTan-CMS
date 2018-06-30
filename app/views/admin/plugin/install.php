<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Functions\Dependency;
use TriTan\Functions\Core;
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Config::set('screen_parent', 'plugins');
TriTan\Config::set('screen_child', 'plugin-new');

?>

<!-- form start -->
<form method="post" action="<?= Core\get_base_url(); ?>admin/plugin/install/" data-toggle="validator" autocomplete="off" enctype="multipart/form-data">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-thumb-tack"></i>
                <h3 class="box-title"><?= Core\_t('Install Plugin', 'tritan-cms'); ?></h3>

                <div class="pull-right">
                    <button type="submit" class="btn btn-success"><i class="fa fa-upload"></i> <?= Core\_t('Install', 'tritan-cms'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= Core\get_base_url(); ?>admin/plugin/'"><i class="fa fa-ban"></i> <?= Core\_t('Cancel', 'tritan-cms'); ?></button>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">

            <?= Dependency\_ttcms_flash()->showMessage(); ?>

            <div class="row">
                <!-- left column -->
                <div class="col-md-9">
                    <div class="box box-default">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="exampleInputFile"><?= Core\_t('File input', 'tritan-cms'); ?></label>
                                <input type="file" name="plugin_zip" />
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box-header -->
                </div>
                <!-- /.left column -->
            </div>
            <!--/.row -->

        </section>
        <!-- /.content -->

    </div>
    <!-- /.content-wrapper -->
</form>

<?php $this->stop(); ?>
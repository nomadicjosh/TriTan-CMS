<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Config::set('screen_parent', 'dashboard');
TriTan\Config::set('screen_child', 'home');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <?= _t('Dashboard'); ?>
            <small><?= _t('Control panel'); ?></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> <?= _t('Home'); ?></a></li>
            <li class="active"><?= _t('Dashboard'); ?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

            <?= _ttcms_flash()->showMessage(); ?>

        <div class="row">
            <?php //top widgets can go here.  ?>
        </div>
        <!-- /.row -->

        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <section class="col-lg-6 connectedSortable">

                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-text-width"></i> <?= _t('Recently Posted'); ?></h3>
                        <div class="box-tools pull-right">
                            <!-- Collapse Button -->
                            <!--<button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>-->
                            </button>
                        </div>
                        <!-- /.box-tools -->
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <?php recently_published_widget(); ?>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->

            </section>
            <!-- /.Left col -->

            <!-- Left col -->
            <section class="col-lg-6 connectedSortable">
                
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-rss"></i> <?= _t('TriTan CMS Feed'); ?></h3>
                        <div class="box-tools pull-right">
                            <!-- Collapse Button -->
                            <!--<button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>-->
                        </div>
                        <!-- /.box-tools -->
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <?php tritan_cms_feed_widget(); ?>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->

            </section>
            <!-- /.Left col -->
        </div>
        <!-- /.row (main row) -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $this->stop() ?>
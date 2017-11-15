<?php if (!defined('BASE_PATH')) exit('No direct script access allowed');
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/admin');
$app->view->block('admin');
define('SCREEN_PARENT', 'dashboard');
define('SCREEN', 'home');
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <?=_t('Dashboard');?>
            <small><?=_t('Control panel');?></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> <?=_t('Home');?></a></li>
            <li class="active"><?=_t('Dashboard');?></li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        
        <?= _ttcms_flash()->showMessage(); ?>
        
        <div class="row">
            <?php //top widgets can go here. ?>
        </div>
        <!-- /.row -->
        
        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <section class="col-lg-12 connectedSortable">

                <div class="nav-tabs-custom">
                    <!-- Tabs within a box -->
                    <ul class="nav nav-tabs pull-right">
                        <li class="pull-left header"><i class="fa fa-dashboard"></i> <?= _t('Dashboard'); ?></li>
                    </ul>
                    <div class="tab-content no-padding">
                        <!-- Highchart subscribers/list -->
                        <div class="chart tab-pane active" id="subList" style="padding:10px 10px;height:300px;text-align:center;font-size:2em;"><p><?=sprintf(_t('Welcome to the TriTan CMS dashboard. If you have any ideas on what should be on the dashboard, please head over to <a href="%s">Github</a> and let you know your ideas.'),'https://github.com/parkerj/TriTan-CMS/issues');?></p></div>
                    </div>
                </div>

            </section>
            <!-- /.Left col -->
        </div>
        <!-- /.row (main row) -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php $app->view->stop(); ?>
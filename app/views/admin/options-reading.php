<?php if (!defined('BASE_PATH')) exit('No direct script access allowed');
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/admin');
$app->view->block('admin');
define('SCREEN_PARENT', 'options');
define('SCREEN', 'options-reading');
?>
<!-- form start -->
<form name="form" method="post" data-toggle="validator" action="<?= get_base_url(); ?>admin/options-reading/" id="form" autocomplete="off">
    <!-- Content Wrapper. Contains post content -->
    <div class="content-wrapper">
        <!-- Content Header (Post header) -->
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fa fa-cogs"></i>
                    <h3 class="box-title"><?= _t('Reading Options', 'tritan-cms'); ?></h3>

                    <div class="pull-right">
                        <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= _t('Update', 'tritan-cms'); ?></button>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                
                <?= _ttcms_flash()->showMessage(); ?>
                
                <div class="row">
                    <!-- left column -->
                    <div class="col-md-9">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?= _t('Reading Options', 'tritan-cms'); ?></h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="form-group">
                                    <label><strong><?= _t('Site Theme', 'tritan-cms'); ?></strong></label>
                                    <select class="form-control select2" name="current_site_theme" style="width: 100%;">
                                        <option value=""> ------------------------- </option>
                                        <?php get_site_themes($app->hook->{'get_option'}( 'current_site_theme' )); ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><strong><?= _t('Posts per Page', 'tritan-cms'); ?></strong></label>
                                    <input type="text" class="form-control" name="posts_per_page" value="<?= $app->hook->{'get_option'}('posts_per_page'); ?>" />
                                </div>
                                <div class="form-group">
                                    <label><strong><?= _t('Date Format', 'tritan-cms'); ?></strong></label>
                                    <input type="text" class="form-control" name="date_format" value="<?= $app->hook->{'get_option'}('date_format'); ?>" />
                                </div>
                                <div class="form-group">
                                    <label><strong><?= _t('Time Format', 'tritan-cms'); ?></strong></label>
                                    <input type="text" class="form-control" name="time_format" value="<?= $app->hook->{'get_option'}('time_format'); ?>" />
                                </div>
                                <?php $app->hook->{'do_action'}('options_reading_form'); ?>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.left column -->

                </div>
                <!--/.row -->
            </section>
            <!-- /.Main content -->
    </div>
</form>
<!-- /.Content Wrapper. Contains post content -->
<?php $app->view->stop(); ?>
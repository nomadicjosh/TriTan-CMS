<?php if (!defined('BASE_PATH')) exit('No direct script access allowed');
use TriTan\Functions as func;
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Config::set('screen_parent', 'options');
TriTan\Config::set('screen_child', 'options-reading');
?>
<!-- form start -->
<form name="form" method="post" data-toggle="validator" action="<?= func\get_base_url(); ?>admin/options-reading/" id="form" autocomplete="off">
    <!-- Content Wrapper. Contains post content -->
    <div class="content-wrapper">
        <!-- Content Header (Post header) -->
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fa fa-cogs"></i>
                    <h3 class="box-title"><?= func\_t('Reading Options', 'tritan-cms'); ?></h3>

                    <div class="pull-right">
                        <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= func\_t('Update', 'tritan-cms'); ?></button>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                
                <?= func\_ttcms_flash()->showMessage(); ?>
                
                <div class="row">
                    <!-- left column -->
                    <div class="col-md-9">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?= func\_t('Reading Options', 'tritan-cms'); ?></h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="form-group">
                                    <label><strong><?= func\_t('Site Theme', 'tritan-cms'); ?></strong></label>
                                    <select class="form-control select2" name="current_site_theme" style="width: 100%;">
                                        <option value=""> ------------------------- </option>
                                        <?php func\get_site_themes($this->app->hook->{'get_option'}( 'current_site_theme' )); ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><strong><?= func\_t('Posts per Page', 'tritan-cms'); ?></strong></label>
                                    <input type="text" class="form-control" name="posts_per_page" value="<?= $this->app->hook->{'get_option'}('posts_per_page'); ?>" />
                                </div>
                                <div class="form-group">
                                    <label><strong><?= func\_t('Date Format', 'tritan-cms'); ?></strong></label>
                                    <input type="text" class="form-control" name="date_format" value="<?= $this->app->hook->{'get_option'}('date_format'); ?>" />
                                </div>
                                <div class="form-group">
                                    <label><strong><?= func\_t('Time Format', 'tritan-cms'); ?></strong></label>
                                    <input type="text" class="form-control" name="time_format" value="<?= $this->app->hook->{'get_option'}('time_format'); ?>" />
                                </div>
                                <?php $this->app->hook->{'do_action'}('options_reading_form'); ?>
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
<?php $this->stop(); ?>
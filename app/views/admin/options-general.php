<?php
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Container::getInstance()->{'set'}('screen_parent', 'options');
TriTan\Container::getInstance()->{'set'}('screen_child', 'options-general');
use TriTan\Common\Hooks\ActionFilterHook as hook;
hook::getInstance()->{'doAction'}('options_init');

$option = (
    new \TriTan\Common\Options\Options(
        new TriTan\Common\Options\OptionsMapper(
            new \TriTan\Database(),
            new TriTan\Common\Context\HelperContext()
        )
    )
);
?>
<!-- form start -->
<form name="form" method="post" data-toggle="validator" action="<?= admin_url('options-general/'); ?>" id="form" autocomplete="off">
    <!-- Content Wrapper. Contains post content -->
    <div class="content-wrapper">
        <!-- Content Header (Post header) -->
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fa fa-cogs"></i>
                    <h3 class="box-title"><?= esc_html__('General Options'); ?></h3>

                    <div class="pull-right">
                        <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= esc_html__('Update'); ?></button>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">

                <?= (new \TriTan\Common\FlashMessages())->showMessage(); ?>

                <div class="row">
                    <!-- left column -->
                    <div class="col-md-9">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?= esc_html__('General Options'); ?></h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= esc_html__('Site Name'); ?></strong></label>
                                    <input type="text" class="form-control" name="sitename" value="<?= $option->{'read'}('sitename'); ?>" required/>
                                </div>
                                <div class="form-group">
                                    <label><strong><?= esc_html__('Site Description'); ?></strong></label>
                                    <input type="text" class="form-control" name="site_description" value="<?= $option->{'read'}('site_description'); ?>" />
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= esc_html__('Admin Email'); ?></strong></label>
                                    <input type="text" class="form-control" name="admin_email" value="<?= $option->{'read'}('admin_email'); ?>" required/>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= esc_html__('Locale'); ?></strong></label>
                                    <select class="form-control select2" name="ttcms_core_locale" style="width: 100%;" required>
                                        <option>&nbsp;</option>
                                        <?php ttcms_dropdown_languages($option->{'read'}('ttcms_core_locale')); ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= esc_html__('Cookie Expire'); ?></strong></label>
                                    <input type="text" class="form-control" name="cookieexpire" value="<?= $option->{'read'}('cookieexpire'); ?>" required/>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= esc_html__('Cookie Path'); ?></strong></label>
                                    <input type="text" class="form-control" name="cookiepath" value="<?= $option->{'read'}('cookiepath'); ?>" required/>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= esc_html__('Cronjobs'); ?></strong></label>
                                    <div class="ios-switch switch-md">
                                        <input type="hidden" class="js-switch" name="cron_jobs" value="0" />
                                        <input type="checkbox" class="js-switch" name="cron_jobs"<?= checked(1, (int) $option->{'read'}('cron_jobs'), false); ?>  value="1" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= esc_html__('Site Cache'); ?></strong></label>
                                    <div class="ios-switch switch-md">
                                        <input type="hidden" class="js-switch" name="site_cache" value="0" />
                                        <input type="checkbox" class="js-switch" name="site_cache"<?= checked(1, (int) $option->{'read'}('site_cache'), false); ?>  value="1" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= esc_html__('Maintenance Mode'); ?></strong></label>
                                    <div class="ios-switch switch-md">
                                        <input type="hidden" class="js-switch" name="maintenance_mode" value="0" />
                                        <input type="checkbox" class="js-switch" name="maintenance_mode"<?= checked(1, (int) $option->{'read'}('maintenance_mode'), false); ?>  value="1" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= esc_html__('System Timezone'); ?></strong></label>
                                    <select class="form-control select2" name="system_timezone" style="width: 100%;" required>
                                        <option value=""> ------------------------- </option>
                                        <?php foreach (generate_timezone_list() as $k => $v) : ?>
                                            <option value="<?=$k;?>"<?=selected($option->{'read'}('system_timezone'), $k, false); ?>><?=$v;?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= esc_html__('API Key'); ?></strong></label>
                                    <input type="text" class="form-control" name="api_key" value="<?= $option->{'read'}('api_key'); ?>" required/>
                                </div>
                                <?php hook::getInstance()->{'doAction'}('options_general_form'); ?>
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

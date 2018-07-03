<?php
use TriTan\Functions\User;
use TriTan\Functions\Core;
use TriTan\Functions\Dependency;

$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Config::set('screen_parent', 'sites');
TriTan\Config::set('screen_child', 'sites');

?>

<script src="static/assets/js/url_slug.js" type="text/javascript"></script>
<script>
$(function(){
    $('#site_name').keyup(function() {
        $('#site_slug').val(url_slug($(this).val()));
    });
});
</script>

<!-- form start -->
<form method="post" action="<?= Core\get_base_url(); ?>admin/site/<?= $this->site['site_id']; ?>/" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-thumb-tack"></i>
                <h3 class="box-title"><?= Core\_t('Update Site', 'tritan-cms'); ?></h3>

                <div class="pull-right">
                    <input type="hidden" id="site_slug" name="site_slug" value="<?= $this->site['site_slug']; ?>" />
                    <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= Core\_t('Update', 'tritan-cms'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= Core\get_base_url(); ?>admin/site/'"><i class="fa fa-ban"></i> <?= Core\_t('Cancel', 'tritan-cms'); ?></button>
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
                                <label><font color="red">*</font> <?= Core\_t('Site Domain', 'tritan-cms'); ?></label>
                                <input type="text" class="form-control" name="site_domain" value="<?= $this->site['site_domain']; ?>" required/>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= Core\_t('Site Name', 'tritan-cms'); ?></label>
                                <input type="text" id="site_name" class="form-control" name="site_name" value="<?= $this->site['site_name']; ?>" required/>
                            </div>

                            <div class="form-group">
                                <label><?= Core\_t('Path', 'tritan-cms'); ?></label>
                                <input type="text" class="form-control" name="site_path" value="<?= $this->site['site_path']; ?>" />
                            </div>

                            <div class="form-group">
                                <label><?= Core\_t('Administrator', 'tritan-cms'); ?></label>
                                <select class="form-control select2" name="site_owner" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <?php User\get_users_list((int) $this->site['site_owner']); ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><?= Core\_t('Status', 'tritan-cms'); ?></label>
                                <select class="form-control select2" name="site_status" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <option value="public"<?= selected('public', $this->site['site_status'], false); ?>><?= Core\_t('Public', 'tritan-cms'); ?></option>
                                    <option value="archive"<?= selected('archive', $this->site['site_status'], false); ?>><?= Core\_t('Archive', 'tritan-cms'); ?></option>
                                </select>
                            </div>
                            <?php $this->app->hook->{'do_action'}('update_site_form_fields', (int) $this->site['site_id']); ?>
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

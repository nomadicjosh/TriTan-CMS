<?php
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Container::getInstance()->{'set'}('screen_parent', 'sites');
TriTan\Container::getInstance()->{'set'}('screen_child', 'sites');
use TriTan\Common\Hooks\ActionFilterHook as hook;

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
<form method="post" action="<?= admin_url('site/' . $this->site->getId() . '/'); ?>" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-thumb-tack"></i>
                <h3 class="box-title"><?= esc_html__('Update Site'); ?></h3>

                <div class="pull-right">
                    <input type="hidden" id="site_slug" name="site_slug" value="<?= $this->site->getSlug(); ?>" />
                    <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= esc_html__('Update'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= admin_url('site/'); ?>'"><i class="fa fa-ban"></i> <?= esc_html__('Cancel'); ?></button>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">

            <?= (new \TriTan\Common\FlashMessages())->showMessage(); ?>

            <div class="row">
                <!-- left column -->
                <div class="col-md-9">
                    <div class="box box-default">
                        <div class="box-body">
                            <div class="form-group">
                                <label><font color="red">*</font> <?= esc_html__('Site Domain'); ?></label>
                                <input type="text" class="form-control" name="site_domain" value="<?= $this->site->getDomain(); ?>" required/>
                            </div>

                            <div class="form-group">
                                <label><font color="red">*</font> <?= esc_html__('Site Name'); ?></label>
                                <input type="text" id="site_name" class="form-control" name="site_name" value="<?= $this->site->getName(); ?>" required/>
                            </div>

                            <div class="form-group">
                                <label><?= esc_html__('Path'); ?></label>
                                <input type="text" class="form-control" name="site_path" value="<?= $this->site->getPath(); ?>" />
                            </div>

                            <div class="form-group">
                                <label><?= esc_html__('Administrator'); ?></label>
                                <select class="form-control select2" name="site_owner" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <?php get_users_list((int) $this->site->getOwner()); ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><?= esc_html__('Status'); ?></label>
                                <select class="form-control select2" name="site_status" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <option value="public"<?= selected('public', $this->site->getStatus(), false); ?>><?= esc_html__('Public'); ?></option>
                                    <option value="archive"<?= selected('archive', $this->site->getStatus(), false); ?>><?= esc_html__('Archive'); ?></option>
                                </select>
                            </div>
                            <?php hook::getInstance()->{'doAction'}('update_site_form_fields', (int) $this->site->getId()); ?>
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

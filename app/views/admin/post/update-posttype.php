<?php if (!defined('BASE_PATH')) exit('No direct script access allowed');
use TriTan\Functions\Dependency;
use TriTan\Functions\Core;
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Config::set('screen_parent', 'post_types');
?>

<script src="static/assets/js/url_slug.js" type="text/javascript"></script>
<script>
$(function(){
    $('#posttype_title').keyup(function() {
        $('#posttype_slug').val(url_slug($(this).val()));
    });
});
</script>

<!-- form start -->
<form method="post" action="<?=Core\get_base_url(); ?>admin/post-type/<?=$this->posttype['posttype_id'];?>/" data-toggle="validator" autocomplete="off">
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-thumb-tack"></i>
            <h3 class="box-title"><?= Core\_t('Update Post Type', 'tritan-cms'); ?></h3>

            <div class="pull-right">
                <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= Core\_t('Update', 'tritan-cms'); ?></button>
                <button type="button" class="btn btn-primary" onclick="window.location='<?=Core\get_base_url();?>admin/post-type/'"><i class="fa fa-ban"></i> <?= Core\_t('Cancel', 'tritan-cms'); ?></button>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

    <?= Dependency\_ttcms_flash()->showMessage(); ?>

        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><font color="red">*</font> <?= Core\_t('Post Type Name', 'tritan-cms'); ?></label>
                            <input type="text" class="form-control input-lg" name="posttype_title" id="posttype_title" value="<?=$this->posttype['posttype_title'];?>" required/>
                        </div>
                        <div class="form-group">
                            <label><?= Core\_t('Post Type Slug', 'tritan-cms'); ?></label>
                            <input type="text" class="form-control" name="posttype_slug" id="posttype_slug" value="<?=$this->posttype['posttype_slug'];?>" />
                        </div>

                        <div class="form-group">
                            <label><?= Core\_t('Post Type Description', 'tritan-cms'); ?></label>
                            <textarea class="form-control" rows="3" name="posttype_description"><?=$this->posttype['posttype_description'];?></textarea>
                        </div>
                        <?php $this->app->hook->{'do_action'}('update_posttype_form_fields', $this->posttype['posttype_id']); ?>
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
    
</div>
<!-- /.content-wrapper -->
</form>

<?php $this->stop(); ?>
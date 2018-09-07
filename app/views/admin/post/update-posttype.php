<?php
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Container::getInstance()->{'set'}('screen_parent', 'post_types');
use TriTan\Common\Hooks\ActionFilterHook as hook;
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
<form method="post" action="<?=admin_url('post-type/' . $this->posttype->getId() . '/'); ?>" data-toggle="validator" autocomplete="off">
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-thumb-tack"></i>
            <h3 class="box-title"><?= esc_html__('Update Post Type'); ?></h3>

            <div class="pull-right">
                <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= esc_html__('Update'); ?></button>
                <button type="button" class="btn btn-primary" onclick="window.location='<?=admin_url('post-type/');?>'"><i class="fa fa-ban"></i> <?= esc_html__('Cancel'); ?></button>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

    <?= (new \TriTan\Common\FlashMessages())->showMessage(); ?>

        <div class="box box-default">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label><font color="red">*</font> <?= esc_html__('Post Type Name'); ?></label>
                            <input type="text" class="form-control input-lg" name="posttype_title" id="posttype_title" value="<?=$this->posttype->getTitle();?>" required/>
                        </div>
                        <div class="form-group">
                            <label><?= esc_html__('Post Type Slug'); ?></label>
                            <input type="text" class="form-control" name="posttype_slug" id="posttype_slug" value="<?=$this->posttype->getSlug();?>" />
                        </div>

                        <div class="form-group">
                            <label><?= esc_html__('Post Type Description'); ?></label>
                            <textarea class="form-control" rows="3" name="posttype_description"><?=$this->posttype->getDescription();?></textarea>
                        </div>
                        <?php hook::getInstance()->{'doAction'}('update_posttype_form_fields', $this->posttype->getId()); ?>
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

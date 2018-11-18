<?php
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Container::getInstance()->{'set'}('screen_parent', $this->posttype);
TriTan\Container::getInstance()->{'set'}('screen_child', $this->posttype . '-create');
use TriTan\Common\Hooks\ActionFilterHook as hook;

?>

<?= ttcms_upload_image();?>
<script src="static/assets/js/url_slug.js" type="text/javascript"></script>
<script>
$(function(){
    $('#post_title').keyup(function() {
        $('#post_slug').val(url_slug($(this).val()));
    });
});
</script>

<!-- form start -->
<form name="form" method="post" data-toggle="validator" action="<?= admin_url($this->posttype . '/create/' ); ?>" id="form" autocomplete="off">
    <!-- Content Wrapper. Contains post content -->
    <div class="content-wrapper">
        <!-- Content Header (Post header) -->
            <div class="box box-solid">
                <div class="box-header with-border">
                    <i class="fa fa-text-width"></i>
                    <h3 class="box-title"><?= esc_html__('Create'); ?> <?=$this->posttype_title;?></h3>

                    <div class="pull-right">
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?= esc_html__('Save'); ?></button>
                        <button type="button" class="btn btn-primary" onclick="window.location='<?= admin_url( $this->posttype . '/');?>'"><i class="fa fa-minus-circle"></i> <?= esc_html__('Cancel'); ?></button>
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
                                <h3 class="box-title"><?= esc_html__('Content'); ?></h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= esc_html__('Title'); ?></strong></label>
                                    <input type="text" class="form-control input-lg" name="post_title" id="post_title" value="<?= __return_post('post_title'); ?>" required/>
                                </div>
                                <div class="form-group">
                                    <label><strong><?= esc_html__('Slug'); ?></strong> <a href="#slug" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                                    <input type="text" class="form-control" name="post_slug" id="post_slug" value="<?= __return_post('post_slug'); ?>" />
                                </div>
                                <?php hook::getInstance()->{'doAction'}('create_post_content_field', $this->posttype) ;?>
                                <div class="form-group">
                                    <label><strong><?= esc_html__('Content'); ?></strong></label>
                                    <textarea id="post_content" class="form-control" name="post_content"><?= __return_post('post_content'); ?></textarea>
                                </div>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.left column -->

                    <?php hook::getInstance()->{'doAction'}('create_post_metabox', $this->posttype, 'normal', 'middle'); ?>

                    <div class="col-md-3">
                        <?php hook::getInstance()->{'doAction'}('create_post_metabox', $this->posttype, 'side', 'top'); ?>
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><font color="red">*</font> <?= esc_html__('Post Type'); ?></h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="form-group">
                                    <select class="form-control select2" name="post_posttype" style="width: 100%;" required>
                                        <option>&nbsp;</option>
                                        <?php foreach (get_all_post_types() as $post_type) : ?>
                                        <option value="<?= $post_type->getSlug(); ?>"<?= selected($post_type->getSlug(), $this->posttype, false); ?>><?= $post_type->getTitle(); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <!-- /.box-body -->
                            <?php hook::getInstance()->{'doAction'}('create_post_metabox_posttype', $this->posttype) ;?>
                        </div>
                        <!-- /.box-primary -->

                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?= esc_html__('Publish'); ?></h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= esc_html__('Publication Date'); ?></strong></label>
                                    <div class='input-group date' id='datetimepicker1'>
                                        <input type="text" class="form-control" name="post_published" value="<?= __return_post('post_published'); ?>" required/>
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= esc_html__('Status'); ?></strong></label>
                                    <select class="form-control select2" name="post_status" style="width: 100%;" required>
                                        <option>&nbsp;</option>
                                        <?php if (current_user_can('publish_posts')) : ?>
                                        <option value="published"<?= selected('published', __return_post('post_status'), false); ?>><?= esc_html__('Publish'); ?></option>
                                        <?php endif; ?>
                                        <option value="draft"<?= selected('draft', __return_post('post_status'), false); ?>><?= esc_html__('Draft'); ?></option>
                                        <option value="pending"<?= selected('pending', __return_post('post_status'), false); ?>><?= esc_html__('Pending'); ?></option>
                                        <option value="archived"<?= selected('archived', __return_post('post_status'), false); ?>><?= esc_html__('Archive'); ?></option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><strong><font color="red">*</font> <?= esc_html__('Author'); ?></strong></label>
                                    <select class="form-control select2" name="post_author" style="width: 100%;" required>
                                        <option>&nbsp;</option>
                                        <?php get_users_list((int) get_current_user_id()); ?>
                                    </select>
                                </div>
                                <?php hook::getInstance()->{'doAction'}('create_post_metabox_publish', $this->posttype) ;?>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box-primary -->

                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?= esc_html__('Post Attributes'); ?></h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div class="form-group">
                                    <label><strong><?= esc_html__('Parent'); ?></strong></label>
                                    <select class="form-control select2" name="post_parent" style="width: 100%;">
                                        <option value="">&nbsp;</option>
                                        <?php if ($this->post_count > 0) : ?>
                                        <?php get_post_dropdown_list(); ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label><strong><?= esc_html__('Sidebar'); ?></strong></label>
                                    <div class="ios-switch switch-md pull-right">
                                        <input type="checkbox" class="js-switch" name="post_sidebar"<?=checked(1, __return_post('post_sidebar'), false);?> value="1" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><strong><?= esc_html__('Show in Menu'); ?></strong></label>
                                    <div class="ios-switch switch-md pull-right">
                                        <input type="checkbox" class="js-switch" name="post_show_in_menu"<?=checked(1, __return_post('post_show_in_menu'), false);?> value="1" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label><strong><?= esc_html__('Show in Search'); ?></strong></label>
                                    <div class="ios-switch switch-md pull-right">
                                        <input type="checkbox" class="js-switch" name="post_show_in_search"<?=checked(1, __return_post('post_show_in_search'), false);?> value="1" />
                                    </div>
                                </div>
                                <?php hook::getInstance()->{'doAction'}('create_post_metabox_attributes', $this->posttype) ;?>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box-primary -->

                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?= esc_html__('Featured Image'); ?></h3>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                                <div id="elfinder"></div>
                                <div id="elfinder_image"></div>
                                <button type="button" id="set_image" class="btn btn-primary" style="display:none;"><?= esc_html__('Set featured image'); ?></button>
                                <button type="button" id="remove_image" class="btn btn-primary" style="display:none;"><?= esc_html__('Remove featured image'); ?></button>
                                <input type="hidden" class="form-control" name="post_featured_image" id="upload_image" />
                                <?php hook::getInstance()->{'doAction'}('create_post_metabox_featured_image', $this->posttype) ;?>
                            </div>
                            <!-- /.box-body -->
                        </div>
                        <!-- /.box-primary -->
                        <?php hook::getInstance()->{'doAction'}('create_post_metabox', $this->posttype, 'side', 'bottom'); ?>
                    </div>

                </div>
                <!--/.row -->
            </section>
            <!-- /.Main content -->
    </div>
</form>
<!-- /.Content Wrapper. Contains post content -->
<?php
/**
 * Fires before the create post screen is fully loaded.
 *
 * @since 0.9
 */
hook::getInstance()->{'doAction'}('enqueue_ttcms_editor');
?>
<!-- modal -->
<div class="modal" id="slug">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?=$this->posttype_title;?> <?= esc_html__('Slug'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?= sprintf(esc_html__("If left blank, the system will auto generate the %s slug."), $this->posttype_title); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= esc_html__('Close'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<?php $this->stop(); ?>

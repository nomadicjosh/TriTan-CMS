<?php
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Container::getInstance()->{'set'}('screen_parent', $this->posttype);
TriTan\Container::getInstance()->{'set'}('screen_child', $this->posttype);
TriTan\Container::getInstance()->{'set'}('post_id', $this->post->getId());
use TriTan\Common\Hooks\ActionFilterHook as hook;
?>

<?= ttcms_upload_image(); ?>

<script src="static/assets/js/url_slug.js" type="text/javascript"></script>
<script>
    $(function () {
        $('#post_title').keyup(function () {
            $('#post_slug').val(url_slug($(this).val()));
        });
    });
</script>

<!-- form start -->
<form name="form" method="post" data-toggle="validator" action="<?= admin_url(sprintf('%s/%s/', $this->posttype, (int) $this->post->getId()));?>" id="form" autocomplete="off">
    <!-- Content Wrapper. Contains post content -->
    <div class="content-wrapper">
        <!-- Content Header (Post header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-text-width"></i>
                <h3 class="box-title"><?= esc_html__('Update'); ?> <?= $this->posttype_title; ?></h3>

                <div class="pull-right">
                    <button type="button"<?=ae('create_posts');?> class="btn btn-warning" onclick="window.location = '<?= admin_url(sprintf('%s/create/', $this->posttype));?>'"><i class="fa fa-plus"></i> <?= esc_html__('New'); ?> <?=$this->posttype;?></button>
                    <button type="submit"<?=ae('update_posts');?> class="btn btn-success"><i class="fa fa-pencil"></i> <?= esc_html__('Update'); ?></button>
                    <button type="button"<?=ae('delete_posts');?> class="btn btn-danger" data-toggle="modal" data-target="#delete-<?= (int) $this->post->getId(); ?>"><i class="fa fa-trash"></i> <?= esc_html__('Delete'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= admin_url(sprintf('%s/', $this->posttype));?>'"><i class="fa fa-ban"></i> <?= esc_html__('Cancel'); ?></button>
                    <input type="hidden" name="post_id" value="<?= $this->post->getId(); ?>" />
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
                            <h3 class="box-title"><?= esc_html__('Update'); ?> <?= $this->posttype_title; ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= esc_html__('Title'); ?></strong></label>
                                <input type="text" class="form-control input-lg" name="post_title" id="post_title" value="<?= $this->post->getTitle(); ?>" required/>
                            </div>
                            <div class="form-group">
                                <label><strong><?= esc_html__('Slug'); ?></strong> <a href="#slug" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                                <input type="text" class="form-control" name="post_slug" id="post_slug" value="<?= $this->post->getSlug(); ?>" />
                            </div>
                            <?php hook::getInstance()->{'doAction'}('update_post_content_field', $this->posttype, $this->post) ;?>
                            <div class="form-group">
                                <label><strong><?= esc_html__('Content'); ?></strong></label>
                                <textarea id="post_content" class="form-control" name="post_content"><?= $this->post->getContent(); ?></textarea>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.left column -->

                <?php hook::getInstance()->{'doAction'}('update_post_metabox', $this->posttype, $this->post, 'normal', 'middle'); ?>

                <div class="col-md-3">
                    <?php hook::getInstance()->{'doAction'}('update_post_metabox', $this->posttype, $this->post, 'side', 'top'); ?>
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
                                    <option value="<?= esc_html($post_type->getSlug()); ?>"<?= selected(esc_html($post_type->getSlug()), $this->post->getPosttype(), false); ?>><?= esc_html($post_type->getTitle()); ?></option>
                                        <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <?php hook::getInstance()->{'doAction'}('update_post_metabox_posttype', $this->posttype, $this->post) ;?>
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
                                    <input type="text" class="form-control" name="post_published" value="<?= $this->post->getPublished(); ?>" required/>
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
                                    <option value="published"<?= selected('published', $this->post->getStatus(), false); ?>><?= esc_html__('Publish'); ?></option>
                                    <?php endif; ?>
                                    <option value="draft"<?= selected('draft', $this->post->getStatus(), false); ?>><?= esc_html__('Draft'); ?></option>
                                    <option value="archived"<?= selected('archived', $this->post->getStatus(), false); ?>><?= esc_html__('Archive'); ?></option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= esc_html__('Author'); ?></strong></label>
                                <select class="form-control select2" name="post_author" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <?php get_users_list((int) $this->post->getAuthor()); ?>
                                </select>
                            </div>
                            <?php hook::getInstance()->{'doAction'}('update_post_metabox_publish', $this->posttype, $this->post) ;?>
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
                                    <?php get_post_dropdown_list($this->post->getParent(), (int) $this->post->getId()); ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><strong><?= esc_html__('Sidebar'); ?></strong></label>
                                <div class="ios-switch switch-md pull-right">
                                    <input type="hidden" class="js-switch" name="post_sidebar" value="0" />
                                    <input type="checkbox" class="js-switch" name="post_sidebar"<?= checked(1, (int) $this->post->getSidebar(), false); ?> value="1" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label><strong><?= esc_html__('Show in Menu'); ?></strong></label>
                                <div class="ios-switch switch-md pull-right">
                                    <input type="hidden" class="js-switch" name="post_show_in_menu" value="0" />
                                    <input type="checkbox" class="js-switch" name="post_show_in_menu"<?= checked(1, (int) $this->post->getShowInMenu(), false); ?> value="1" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label><strong><?= esc_html__('Show in Search'); ?></strong></label>
                                <div class="ios-switch switch-md pull-right">
                                    <input type="hidden" class="js-switch" name="post_show_in_search" value="0" />
                                    <input type="checkbox" class="js-switch" name="post_show_in_search"<?= checked(1, (int) $this->post->getShowInSearch(), false); ?> value="1" />
                                </div>
                            </div>
                            <?php hook::getInstance()->{'doAction'}('update_post_metabox_attributes', $this->posttype, $this->post) ;?>
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
                            <div id="elfinder_image"><img src="<?= $this->post->getFeaturedImage(); ?>" style="width:280px;height:auto;background-size:contain;margin-bottom:.9em;background-repeat:no-repeat" /></div>
                            <?php if ($this->post->getFeaturedImage() != '') : ?>
                            <button type="button" class="btn btn-primary" onclick="window.location = '<?= admin_url(sprintf('%s/%s/remove-featured-image/', $this->posttype, (int) $this->post->getId())); ?>'"><?= esc_html__('Remove featured image'); ?></button>
                            <?php else : ?>
                            <button type="button" id="set_image" class="btn btn-primary" style="display:none;"><?= esc_html__('Set featured image'); ?></button>
                            <button type="button" id="remove_image" class="btn btn-primary" style="display:none;"><?= esc_html__('Remove featured image'); ?></button>
                            <?php endif; ?>
                            <input type="hidden" class="form-control" name="post_featured_image" id="upload_image" value="<?= $this->post->getFeaturedImage(); ?>" />
                            <?php hook::getInstance()->{'doAction'}('update_post_metabox_featured_image', $this->posttype, $this->post) ;?>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box-primary -->
                    <?php hook::getInstance()->{'doAction'}('update_post_metabox', $this->posttype, $this->post, 'side', 'bottom'); ?>
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
 * Fires before the update post screen is fully loaded.
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
                <h4 class="modal-title"><?= $this->posttype_title; ?> <?= esc_html__('Slug'); ?></h4>
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

<!-- modal -->
<div class="modal" id="delete-<?= $this->post->getId(); ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= $this->post->getTitle(); ?></h4>
            </div>
            <div class="modal-body">
                <p><?=esc_html__('Are you sure you want to delete this post?');?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= esc_html__('Close'); ?></button>
                <button type="button" class="btn btn-primary" onclick="window.location='<?= admin_url(sprintf('%s/%s/d/', $this->posttype, (int) $this->post->getId())); ?>'"><?= esc_html__('Confirm'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<?php $this->stop(); ?>

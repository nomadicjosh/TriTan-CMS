<?php
if (!defined('BASE_PATH')) exit('No direct script access allowed');
use TriTan\Functions as func;
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Config::set('screen_parent', $this->posttype);
TriTan\Config::set('screen_child', $this->posttype);
TriTan\Config::set('post_id', $this->post['post_id']);
?>

<?= func\ttcms_upload_image(); ?>

<script src="static/assets/js/url_slug.js" type="text/javascript"></script>
<script>
    $(function () {
        $('#post_title').keyup(function () {
            $('#post_slug').val(url_slug($(this).val()));
        });
    });
</script>

<!-- form start -->
<form name="form" method="post" data-toggle="validator" action="<?= func\get_base_url() ?>admin/<?=$this->posttype;?>/<?= (int) $this->post['post_id']; ?>/" id="form" autocomplete="off">
    <!-- Content Wrapper. Contains post content -->
    <div class="content-wrapper">
        <!-- Content Header (Post header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-text-width"></i>
                <h3 class="box-title"><?= func\_t('Update', 'tritan-cms'); ?> <?= $this->posttype_title; ?></h3>

                <div class="pull-right">
                    <button type="button"<?=func\ae('create_posts');?> class="btn btn-warning" onclick="window.location = '<?= func\get_base_url(); ?>admin/<?=$this->posttype;?>/create/'"><i class="fa fa-plus"></i> <?= func\_t('New', 'tritan-cms'); ?> <?=$this->posttype;?></button>
                    <button type="submit"<?=func\ae('update_posts');?> class="btn btn-success"><i class="fa fa-pencil"></i> <?= func\_t('Update', 'tritan-cms'); ?></button>
                    <button type="button"<?=func\ae('delete_posts');?> class="btn btn-danger" data-toggle="modal" data-target="#delete-<?= (int) $this->post['post_id']; ?>"><i class="fa fa-trash"></i> <?= func\_t('Delete', 'tritan-cms'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= func\get_base_url(); ?>admin/<?= $this->posttype; ?>/'"><i class="fa fa-ban"></i> <?= func\_t('Cancel', 'tritan-cms'); ?></button>
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
                            <h3 class="box-title"><?= func\_t('Update', 'tritan-cms'); ?> <?= $this->posttype_title; ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= func\_t('Title', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control input-lg" name="post_title" id="post_title" value="<?= $this->post['post_title']; ?>" required/>
                            </div>
                            <div class="form-group">
                                <label><strong><?= func\_t('Slug', 'tritan-cms'); ?></strong> <a href="#slug" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                                <input type="text" class="form-control" name="post_slug" id="post_slug" value="<?= $this->post['post_slug']; ?>" />
                            </div>
                            <?php $this->app->hook->{'do_action'}('update_post_content_field', $this->posttype, $this->post) ;?>
                            <div class="form-group">
                                <label><strong><?= func\_t('Content', 'tritan-cms'); ?></strong></label>
                                <textarea id="tinymce_editor" class="form-control" name="post_content"><?= $this->post['post_content']; ?></textarea>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.left column -->
                
                <?php $this->app->hook->{'do_action'}('update_post_metabox', $this->posttype, $this->post, 'normal', 'middle'); ?>

                <div class="col-md-3">
                    <?php $this->app->hook->{'do_action'}('update_post_metabox', $this->posttype, $this->post, 'side', 'top'); ?>
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><font color="red">*</font> <?= func\_t('Post Type', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <select class="form-control select2" name="post_posttype" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                        <?php foreach (func\get_all_post_types() as $post_type) : ?>
                                    <option value="<?= func\_escape($post_type['posttype_slug']); ?>"<?= selected(func\_escape($post_type['posttype_slug']), $this->post['post_type']['post_posttype'], false); ?>><?= func\_escape($post_type['posttype_title']); ?></option>
                                        <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <?php $this->app->hook->{'do_action'}('update_post_metabox_posttype', $this->posttype, $this->post) ;?>
                    </div>
                    <!-- /.box-primary -->

                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= func\_t('Publish', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= func\_t('Publication Date', 'tritan-cms'); ?></strong></label>
                                <div class='input-group date' id='datetimepicker1'>
                                    <input type="text" class="form-control" name="post_created" value="<?= $this->post['post_created']; ?>" required/>
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= func\_t('Status', 'tritan-cms'); ?></strong></label>
                                <select class="form-control select2" name="post_status" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <?php if(func\current_user_can('publish_posts')) : ?>
                                    <option value="published"<?= selected('published', $this->post['post_status'], false); ?>><?= func\_t('Publish', 'tritan-cms'); ?></option>
                                    <?php endif; ?>
                                    <option value="draft"<?= selected('draft', $this->post['post_status'], false); ?>><?= func\_t('Draft', 'tritan-cms'); ?></option>
                                    <option value="archived"<?= selected('archived', $this->post['post_status'], false); ?>><?= func\_t('Archive', 'tritan-cms'); ?></option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= func\_t('Author', 'tritan-cms'); ?></strong></label>
                                <select class="form-control select2" name="post_author" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <?php func\get_users_list((int) $this->post['post_author']); ?>
                                </select>
                            </div>
                            <?php $this->app->hook->{'do_action'}('update_post_metabox_publish', $this->posttype, $this->post) ;?>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box-primary -->

                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= func\_t('Post Attributes', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><?= func\_t('Parent', 'tritan-cms'); ?></strong></label>
                                <select class="form-control select2" name="post_parent" style="width: 100%;">
                                    <option value="">&nbsp;</option>
                                    <?php func\get_post_dropdown_list($this->post['post_attributes']['parent']['post_parent'], (int) $this->post['post_id']); ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><strong><?= func\_t('Sidebar', 'tritan-cms'); ?></strong></label>
                                <div class="ios-switch switch-md pull-right">
                                    <input type="hidden" class="js-switch" name="post_sidebar" value="0" />
                                    <input type="checkbox" class="js-switch" name="post_sidebar"<?= checked(1, (int) $this->post['post_attributes']['post_sidebar'], false); ?> value="1" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label><strong><?= func\_t('Show in Menu', 'tritan-cms'); ?></strong></label>
                                <div class="ios-switch switch-md pull-right">
                                    <input type="hidden" class="js-switch" name="post_show_in_menu" value="0" />
                                    <input type="checkbox" class="js-switch" name="post_show_in_menu"<?= checked(1, (int) $this->post['post_attributes']['post_show_in_menu'], false); ?> value="1" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label><strong><?= func\_t('Show in Search', 'tritan-cms'); ?></strong></label>
                                <div class="ios-switch switch-md pull-right">
                                    <input type="hidden" class="js-switch" name="post_show_in_search" value="0" />
                                    <input type="checkbox" class="js-switch" name="post_show_in_search"<?= checked(1, (int) $this->post['post_attributes']['post_show_in_search'], false); ?> value="1" />
                                </div>
                            </div>
                            <?php $this->app->hook->{'do_action'}('update_post_metabox_attributes', $this->posttype, $this->post) ;?>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box-primary -->

                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= func\_t('Featured Image', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div id="elfinder"></div>
                            <div id="elfinder_image"><img src="<?= $this->post['post_featured_image']; ?>" style="width:280px;height:auto;background-size:contain;margin-bottom:.9em;background-repeat:no-repeat" /></div>
                            <?php if($this->post['post_featured_image'] != '') : ?>
                            <button type="button" class="btn btn-primary" onclick="window.location = '<?= func\get_base_url(); ?>admin/<?= $this->posttype; ?>/<?= (int) $this->post['post_id']; ?>/remove-featured-image/'"><?= func\_t('Remove featured image', 'tritan-cms'); ?></button>
                            <?php else : ?>
                            <button type="button" id="set_image" class="btn btn-primary" style="display:none;"><?= func\_t('Set featured image', 'tritan-cms'); ?></button>
                            <button type="button" id="remove_image" class="btn btn-primary" style="display:none;"><?= func\_t('Remove featured image', 'tritan-cms'); ?></button>
                            <?php endif; ?>
                            <input type="hidden" class="form-control" name="post_featured_image" id="upload_image" value="<?= $this->post['post_featured_image']; ?>" />
                            <?php $this->app->hook->{'do_action'}('update_post_metabox_featured_image', $this->posttype, $this->post) ;?>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box-primary -->
                    <?php $this->app->hook->{'do_action'}('update_post_metabox', $this->posttype, $this->post, 'side', 'bottom'); ?>
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
$this->app->hook->{'do_action'}('enqueue_ttcms_editor');
?>
<!-- modal -->
<div class="modal" id="slug">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= $this->posttype_title; ?> <?= func\_t('Slug', 'tritan-cms'); ?></h4>
            </div>
            <div class="modal-body">
                <p><?= func\_t(sprintf("If left blank, the system will auto generate the %s slug.", $this->posttype_title), 'tritan-cms'); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= func\_t('Close', 'tritan-cms'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- modal -->
<div class="modal" id="delete-<?= $this->post['post_id']; ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?= $this->post['post_title']; ?></h4>
            </div>
            <div class="modal-body">
                <p><?=func\_t('Are you sure you want to delete this post?');?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= func\_t('Close'); ?></button>
                <button type="button" class="btn btn-primary" onclick="window.location='<?=func\get_base_url();?>admin/<?=$this->posttype;?>/<?= $this->post['post_id']; ?>/d/'"><?= func\_t('Confirm'); ?></button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<?php $this->stop(); ?>
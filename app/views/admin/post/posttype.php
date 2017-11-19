<?php if (!defined('BASE_PATH')) exit('No direct script access allowed');
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/admin');
$app->view->block('admin');
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
<form name="form" method="post" data-toggle="validator" action="<?=get_base_url();?>admin/post-type/" autocomplete="off">
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-thumb-tack"></i>
            <h3 class="box-title"><?= _t('Post Types', 'tritan-cms'); ?></h3>

            <div class="pull-right">
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?= _t('Save', 'tritan-cms'); ?></button>
            </div>
        </div>
    </div>
        
    <!-- Main content -->
    <section class="content">
    
    <?= _ttcms_flash()->showMessage(); ?>
        
      <div class="row">
        <!-- left column -->
        <div class="col-md-4">
          <!-- general form elements -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title"><?=_t('Add New Post Type', 'tritan-cms');?></h3>
            </div>
            <!-- /.box-header -->
              <div class="box-body">
                <div class="form-group">
                  <label><?=_t('Post Type Name', 'tritan-cms');?></label>
                  <input type="text" class="form-control input-lg" name="posttype_title" id="posttype_title" value="<?= __return_post('posttype_title'); ?>" required/>
                </div>
                <div class="form-group">
                  <label><?=_t('Post Type Slug', 'tritan-cms');?> <a href="#slug" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                  <input type="text" class="form-control" name="posttype_slug" id="posttype_slug" value="<?= __return_post('posttype_slug'); ?>" />
                </div>
                <div class="form-group">
                  <label><?=_t('Post Type Description', 'tritan-cms');?></label>
                  <textarea class="form-control" name="posttype_description"><?= __return_post('posttype_description'); ?></textarea>
                </div>
                <?php $app->hook->{'do_action'}('create_posttype_form_fields'); ?>
              </div>
              <!-- /.box-body -->
          </div>
          <!-- /.box-body -->
        </div>
        <!-- /.left column -->

        <!-- right column -->
        <div class="col-md-8">
          <!-- general form elements -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title"><?=_t('Post Types', 'tritan-cms');?></h3>
            </div>
            <!-- /.box-header -->
              <div class="box-body">
                <table id="example1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= _t('Post Type', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Description', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Slug', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Count', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Action', 'tritan-cms'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posttypes as $posttype) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><a href="<?=get_base_url();?>admin/post-type/<?=_escape($posttype['posttype_id']);?>/"><?= _escape($posttype['posttype_title']); ?></a></td>
                                <td class="text-center"><?= _escape($posttype['posttype_description']); ?></td>
                                <td class="text-center"><?= _escape($posttype['posttype_slug']); ?></td>
                                <td class="text-center"><a href="<?=get_base_url();?>admin/<?= _escape($posttype['posttype_slug']); ?>/"><?=number_posts_per_type(_escape($posttype['posttype_slug'])); ?></a></td>
                                <td class="text-center">
                                    <a<?=ae('delete_posts');?> href="#" data-toggle="modal" data-target="#delete-<?= _escape($posttype['posttype_id']); ?>"><button type="button" class="btn bg-red"><i class="fa fa-trash-o"></i></button></a>
                                    <div class="modal" id="delete-<?= _escape($posttype['posttype_id']); ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= _escape($posttype['posttype_title']); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?=_t('Are you sure you want to delete this post type? By deleting this post type, you also delete all posts connected to this post type as well.');?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                                                    <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>admin/post-type/<?= _escape($posttype['posttype_id']); ?>/d/'"><?= _t('Confirm'); ?></button>
                                                </div>
                                            </div>
                                            <!-- /.modal-content -->
                                        </div>
                                        <!-- /.modal-dialog -->
                                    </div>
                                    <!-- /.modal -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= _t('Post Type', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Description', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Slug', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Count', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= _t('Action', 'tritan-cms'); ?></th>
                        </tr>
                    </tfoot>
                </table>
              </div>
              <!-- /.box-body -->
          </div>
          <!-- /.box-primary -->
        </div>
        <!-- /.right column -->

        </div>
        <!--/.row -->
    </section>
    <!-- /.Main content -->
    
    <!-- modal -->
    <div class="modal" id="slug">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?=_t( 'Post Type Slug', 'tritan-cms' );?></h4>
                </div>
                <div class="modal-body">
                    <p><?=_t( "If left blank, the system will auto generate the post type slug.", 'tritan-cms' );?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close', 'tritan-cms'); ?></button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
    
</div>
</form>
<!-- /.Content Wrapper. Contains page content -->
<?php $app->view->stop(); ?>
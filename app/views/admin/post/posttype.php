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
<form name="form" method="post" data-toggle="validator" action="<?= admin_url( 'post-type/' ); ?>" autocomplete="off">
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-thumb-tack"></i>
            <h3 class="box-title"><?= esc_html__('Post Types'); ?></h3>

            <div class="pull-right">
                <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?= esc_html__('Save'); ?></button>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

    <?= (new \TriTan\Common\FlashMessages())->showMessage(); ?>

      <div class="row">
        <!-- left column -->
        <div class="col-md-4">
          <!-- general form elements -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title"><?=esc_html__('Add New Post Type');?></h3>
            </div>
            <!-- /.box-header -->
              <div class="box-body">
                <div class="form-group">
                  <label><?=esc_html__('Post Type Name');?></label>
                  <input type="text" class="form-control input-lg" name="posttype_title" id="posttype_title" value="<?= __return_post('posttype_title'); ?>" required/>
                </div>
                <div class="form-group">
                  <label><?=esc_html__('Post Type Slug');?> <a href="#slug" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                  <input type="text" class="form-control" name="posttype_slug" id="posttype_slug" value="<?= __return_post('posttype_slug'); ?>" />
                </div>
                <div class="form-group">
                  <label><?=esc_html__('Post Type Description');?></label>
                  <textarea class="form-control" name="posttype_description"><?= __return_post('posttype_description'); ?></textarea>
                </div>
                <?php hook::getInstance()->{'doAction'}('create_posttype_form_fields'); ?>
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
              <h3 class="box-title"><?=esc_html__('Post Types');?></h3>
            </div>
            <!-- /.box-header -->
              <div class="box-body">
                <table id="example1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= esc_html__('Post Type'); ?></th>
                            <th class="text-center"><?= esc_html__('Description'); ?></th>
                            <th class="text-center"><?= esc_html__('Slug'); ?></th>
                            <th class="text-center"><?= esc_html__('Count'); ?></th>
                            <th class="text-center"><?= esc_html__('Action'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->posttypes as $posttype) : ?>
                            <tr class="gradeX">
                                <td class="text-center"><a href="<?= admin_url( 'post-type/' . $posttype->getId() . '/'); ?>/"><?= $posttype->getTitle(); ?></a></td>
                                <td class="text-center"><?= $posttype->getDescription(); ?></td>
                                <td class="text-center"><?= $posttype->getSlug(); ?></td>
                                <td class="text-center"><a href="<?= admin_url($posttype->getSlug() . '/'); ?>/"><?= number_posts_per_type($posttype->getSlug()); ?></a></td>
                                <td class="text-center">
                                    <a<?=ae('delete_posts');?> href="#" data-toggle="modal" data-target="#delete-<?= $posttype->getId(); ?>"><button type="button" class="btn bg-red"><i class="fa fa-trash-o"></i></button></a>
                                    <div class="modal" id="delete-<?= $posttype->getId(); ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= $posttype->getTitle(); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?=esc_html__('Are you sure you want to delete this post type? By deleting this post type, you also delete all posts connected to this post type as well.');?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= esc_html__('Close'); ?></button>
                                                    <button type="button" class="btn btn-primary" onclick="window.location='<?= admin_url( 'post-type/' . $posttype->getId() . '/d/'); ?>'"><?= esc_html__('Confirm'); ?></button>
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
                            <th class="text-center"><?= esc_html__('Post Type'); ?></th>
                            <th class="text-center"><?= esc_html__('Description'); ?></th>
                            <th class="text-center"><?= esc_html__('Slug'); ?></th>
                            <th class="text-center"><?= esc_html__('Count'); ?></th>
                            <th class="text-center"><?= esc_html__('Action'); ?></th>
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
                    <h4 class="modal-title"><?=esc_html__('Post Type Slug');?></h4>
                </div>
                <div class="modal-body">
                    <p><?=esc_html__("If left blank, the system will auto generate the post type slug.");?></p>
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

</div>
</form>
<!-- /.Content Wrapper. Contains page content -->
<?php $this->stop(); ?>

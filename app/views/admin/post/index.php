<?php
use TriTan\Functions\Dependency;
use TriTan\Functions\Auth;
use TriTan\Functions\Core;
use TriTan\Functions\Post;
use TriTan\Functions\User;

$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Config::set('screen_parent', $this->posttype);
TriTan\Config::set('screen_child', $this->posttype);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-text-width"></i>
            <h3 class="box-title"><?=$this->title;?></h3>

            <div class="pull-right">
                <button type="button"<?= Auth\ae('create_posts');?> class="btn btn-warning" onclick="window.location = '<?= Core\get_base_url(); ?>admin/<?=$this->posttype;?>/create/'"><i class="fa fa-plus"></i> <?= Core\_t('New', 'tritan-cms'); ?> <?=$this->posttype;?></button>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

        <?= Dependency\_ttcms_flash()->showMessage(); ?>

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th><?= Core\_t('Title', 'tritan-cms'); ?></th>
                            <th><?= Core\_t('Author', 'tritan-cms'); ?></th>
                            <th><?= Core\_t('Date', 'tritan-cms'); ?></th>
                            <?php $this->app->hook->{'do_action'}('manage_post_header_column', 'default', $this->posttype);?>
                            <th><?= Core\_t('Last Modified', 'tritan-cms'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->posts as $post) : ?>
                            <tr class="gradeX">
                                <td>
                                    <div class="post_title">
                                        <strong><a href="<?= Core\get_base_url(); ?>admin/<?=$this->posttype;?>/<?= $post['post_id']; ?>/"><?= $post['post_title']; ?></a></strong> --
                                        <span class="label <?= Post\ttcms_post_status_label($post['post_status']);?>" style="font-size:1em;font-weight: bold;">
                                            <?= ucfirst($post['post_status']); ?>
                                        </span>
                                    </div>
                                    <div class="row-actions">
                                        <span class="edit"><a href="<?= Core\get_base_url(); ?>admin/<?=$this->posttype;?>/<?= $post['post_id']; ?>/"><?=Core\_t('Edit', 'tritan-cms');?></a></span> |
                                        <span class="delete"><a<?= Auth\ae('delete_posts');?> href="#" data-toggle="modal" data-target="#delete-<?= $post['post_id']; ?>"><?=Core\_t('Delete', 'tritan-cms');?></a></span>
                                    </div>
                                    <div class="modal" id="delete-<?= $post['post_id']; ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= $post['post_title']; ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?=Core\_t('Are you sure you want to delete this post?', 'tritan-cms');?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= Core\_t('Close', 'tritan-cms'); ?></button>
                                                    <button type="button" class="btn btn-primary" onclick="window.location='<?=Core\get_base_url();?>admin/<?=$this->posttype;?>/<?= $post['post_id']; ?>/d/'"><?= Core\_t('Confirm', 'tritan-cms'); ?></button>
                                                </div>
                                            </div>
                                            <!-- /.modal-content -->
                                        </div>
                                        <!-- /.modal-dialog -->
                                    </div>
                                    <!-- /.modal -->
                                </td>
                                <td><a href="<?=Core\get_base_url();?>admin/user/<?=$post['post_author'];?>/"><?= User\get_name($post['post_author'], true); ?></a></td>
                                <td><?= format_date($post['post_published'], 'Y-m-d @ h:i A');?></td>
                                <?php $this->app->hook->{'do_action'}('manage_post_content_column', 'default', (int) $post['post_id']);?>
                                <td><?= format_date($post['post_modified'], 'Y-m-d @ h:i A');?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th><?= Core\_t('Title', 'tritan-cms'); ?></th>
                            <th><?= Core\_t('Author', 'tritan-cms'); ?></th>
                            <th><?= Core\_t('Date', 'tritan-cms'); ?></th>
                            <?php $this->app->hook->{'do_action'}('manage_post_header_column', 'default', $this->posttype);?>
                            <th><?= Core\_t('Last Modified', 'tritan-cms'); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $this->stop(); ?>

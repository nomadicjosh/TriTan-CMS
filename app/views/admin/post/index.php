<?php if (!defined('BASE_PATH')) exit('No direct script access allowed');
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/admin');
$app->view->block('admin');
TriTan\Config::set('screen_parent', $posttype);
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-text-width"></i>
            <h3 class="box-title"><?=_escape($title);?></h3>
            
            <div class="pull-right">
                <button type="button"<?=ae('create_posts');?> class="btn btn-warning" onclick="window.location = '<?= get_base_url(); ?>admin/<?=$posttype;?>/create/'"><i class="fa fa-plus"></i> <?= _t('New', 'tritan-cms'); ?> <?=$posttype;?></button>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

        <?= _ttcms_flash()->showMessage(); ?> 

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th><?= _t('Title'); ?></th>
                            <th><?= _t('Author'); ?></th>
                            <th><?= _t('Date'); ?></th>
                            <?php $app->hook->{'do_action'}('manage_post_header_column', 'default', $posttype);?>
                            <th><?= _t('Last Modified'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post) : ?>
                            <tr class="gradeX">
                                <td>
                                    <div class="post_title">
                                        <strong><a href="<?= get_base_url(); ?>admin/<?=$posttype;?>/<?= _escape($post['post_id']); ?>/"><?= _escape($post['post_title']); ?></a></strong> -- 
                                        <span class="label <?=ttcms_post_status_label(_escape($post['post_status']));?>" style="font-size:1em;font-weight: bold;">
                                            <?= ucfirst(_escape($post['post_status'])); ?>
                                        </span>
                                    </div>
                                    <div class="row-actions">
                                        <span class="edit"><a href="<?= get_base_url(); ?>admin/<?=$posttype;?>/<?= _escape($post['post_id']); ?>/"><?=_t('Edit');?></a></span> | 
                                        <span class="delete"><a<?=ae('delete_posts');?> href="#" data-toggle="modal" data-target="#delete-<?= _escape($post['post_id']); ?>"><?=_t('Delete');?></a></span>
                                    </div>
                                    <div class="modal" id="delete-<?= _escape($post['post_id']); ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= _escape($post['post_title']); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?=_t('Are you sure you want to delete this post?');?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                                                    <button type="button" class="btn btn-primary" onclick="window.location='<?=get_base_url();?>admin/<?=$posttype;?>/<?= _escape($post['post_id']); ?>/d/'"><?= _t('Confirm'); ?></button>
                                                </div>
                                            </div>
                                            <!-- /.modal-content -->
                                        </div>
                                        <!-- /.modal-dialog -->
                                    </div>
                                    <!-- /.modal -->
                                </td>
                                <td><a href="<?=get_base_url();?>admin/user/<?=_escape($post['post_author']);?>/"><?= get_name(_escape($post['post_author']), true); ?></a></td>
                                <td><?=Jenssegers\Date\Date::parse(_escape($post['post_created']))->format('Y-m-d @ h:i A');?></td>
                                <?php $app->hook->{'do_action'}('manage_post_content_column', 'default', (int) _escape($post['post_id']));?>
                                <td><?=Jenssegers\Date\Date::parse(_escape($post['post_modified']))->format('Y-m-d @ h:i A');?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th><?= _t('Title'); ?></th>
                            <th><?= _t('Author'); ?></th>
                            <th><?= _t('Date'); ?></th>
                            <?php $app->hook->{'do_action'}('manage_post_header_column', 'default', $posttype);?>
                            <th><?= _t('Last Modified'); ?></th>
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
<?php $app->view->stop(); ?>
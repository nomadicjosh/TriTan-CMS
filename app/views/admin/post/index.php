<?php
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Container::getInstance()->{'set'}('screen_parent', $this->posttype);
TriTan\Container::getInstance()->{'set'}('screen_child', $this->posttype);
use TriTan\Common\Hooks\ActionFilterHook as hook;
use TriTan\Common\Date;
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-text-width"></i>
            <h3 class="box-title"><?=$this->title;?></h3>

            <div class="pull-right">
                <button type="button"<?= ae('create_posts');?> class="btn btn-warning" onclick="window.location = '<?= admin_url($this->posttype . '/create/'); ?>'"><i class="fa fa-plus"></i> <?= esc_html__('New'); ?> <?=$this->posttype;?></button>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

        <?= (new \TriTan\Common\FlashMessages())->showMessage(); ?>

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th><?= esc_html__('Title'); ?></th>
                            <th><?= esc_html__('Author'); ?></th>
                            <th><?= esc_html__('Date'); ?></th>
                            <?php hook::getInstance()->{'doAction'}('manage_post_header_column', 'default', $this->posttype);?>
                            <th><?= esc_html__('Last Modified'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->posts as $post) : ?>
                            <tr class="gradeX">
                                <td>
                                    <div class="post_title">
                                        <strong><a href="<?= admin_url($this->posttype . '/'. $post->getId() . '/'); ?>"><?= $post->getTitle(); ?></a></strong> --
                                        <span class="label <?= ttcms_post_status_label($post->getStatus());?>" style="font-size:1em;font-weight: bold;">
                                            <?= ucfirst($post->getStatus()); ?>
                                        </span>
                                    </div>
                                    <div class="row-actions">
                                        <span class="edit"><a href="<?= admin_url($this->posttype . '/'. $post->getId() . '/'); ?>"><?=esc_html__('Edit');?></a></span> |
                                        <span class="delete"><a<?= ae('delete_posts');?> href="#" data-toggle="modal" data-target="#delete-<?= $post->getId(); ?>"><?=esc_html__('Delete');?></a></span>
                                    </div>
                                    <div class="modal" id="delete-<?= $post->getId(); ?>">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span></button>
                                                    <h4 class="modal-title"><?= $post->getTitle(); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?=esc_html__('Are you sure you want to delete this post?');?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= esc_html__('Close'); ?></button>
                                                    <button type="button" class="btn btn-primary" onclick="window.location='<?= admin_url($this->posttype . '/'. $post->getId() . '/d/'); ?>'"><?= esc_html__('Confirm'); ?></button>
                                                </div>
                                            </div>
                                            <!-- /.modal-content -->
                                        </div>
                                        <!-- /.modal-dialog -->
                                    </div>
                                    <!-- /.modal -->
                                </td>
                                <td><a href="<?= admin_url('user/' . $post->getAuthor() . '/'); ?>"><?= get_name($post->getAuthor(), true); ?></a></td>
                                <td><?= (new Date())->{'laci2date'}('Y-m-d @ h:i A', $post->getPublished());?></td>
                                <?php hook::getInstance()->{'doAction'}('manage_post_content_column', 'default', (int) $post->getId());?>
                                <td><?= (new Date())->{'laci2date'}('Y-m-d @ h:i A', $post->getModified());?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th><?= esc_html__('Title'); ?></th>
                            <th><?= esc_html__('Author'); ?></th>
                            <th><?= esc_html__('Date'); ?></th>
                            <?php hook::getInstance()->{'doAction'}('manage_post_header_column', 'default', $this->posttype);?>
                            <th><?= esc_html__('Last Modified'); ?></th>
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

<?php
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
TriTan\Container::getInstance()->{'set'}('screen_parent', 'sites');
TriTan\Container::getInstance()->{'set'}('screen_child', 'sites');
?>

<script src="static/assets/js/url_slug.js" type="text/javascript"></script>
<script>
$(function(){
    $('#site_name').keyup(function() {
        $('#site_slug').val(url_slug($(this).val()));
    });
});
</script>

<!-- form start -->
<form name="form" method="post" data-toggle="validator" action="<?= admin_url('site/'); ?>" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-thumb-tack"></i>
                <h3 class="box-title"><?= esc_html__('Sites'); ?></h3>

                <div class="pull-right">
                    <input type="hidden" id="site_slug" name="site_slug" value="<?= __return_post('site_slug'); ?>" />
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
                            <h3 class="box-title"><?= esc_html__('Add New Site'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><?= esc_html__('Subdomain'); ?></label>
                                <input type="text" class="form-control input-lg" name="subdomain" value="<?= __return_post('subdomain'); ?>" required/>.<?= $this->app->req->server['HTTP_HOST']; ?>
                            </div>
                            <div class="form-group">
                                <label><?= esc_html__('Site Name'); ?></label>
                                <input type="text" id="site_name" class="form-control" name="site_name" value="<?= __return_post('site_name'); ?>" required/>
                            </div>
                            <div class="form-group">
                                <label><?= esc_html__('Path'); ?> <a href="#path" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                                <input type="text" class="form-control" name="site_path" value="<?=str_replace('index.php', '', esc_html($this->app->req->server['PHP_SELF']));?>" required/>
                            </div>
                            <div class="form-group">
                                <label><?= esc_html__('Administrator'); ?></label>
                                <select class="form-control select2" name="site_owner" style="width: 100%;" required>
                                    <option>&nbsp;</option>
                                    <?php get_users_list(__return_post('site_owner')); ?>
                                </select>
                            </div>
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
                            <h3 class="box-title"><?= esc_html__('Sites'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <table id="example1" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center"><?= esc_html__('URL'); ?></th>
                                        <th class="text-center"><?= esc_html__('Name'); ?></th>
                                        <th class="text-center"><?= esc_html__('Admin'); ?></th>
                                        <th class="text-center"><?= esc_html__('Status'); ?></th>
                                        <th class="text-center"><?= esc_html__('Action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($this->sites as $site) : ?>
                                        <tr class="gradeX">
                                            <td class="text-center"><a href="//<?= $site->getDomain(); ?><?= $site->getPath(); ?>" target="new"><?= $site->getDomain(); ?></a></td>
                                            <td class="text-center"><?= $site->getName(); ?></td>
                                            <td class="text-center"><?= get_name((int) $site->getOwner()); ?></td>
                                            <td class="text-center">
                                                <span class="label <?= ttcms_site_status_label($site->getStatus()); ?>" style="font-size:1em;font-weight: bold;">
                                                    <?= ucfirst($site->getStatus()); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button"<?= ae('update_sites');?> class="btn btn-success" onclick="window.location = '<?= admin_url('site/' . (int) $site->getId() . '/'); ?>'"><i class="fa fa-pencil"></i></button>
                                                <?php if ((int) $site->getId() <> 1) : ?>
                                                <button type="button"<?= ae('delete_sites'); ?> class="btn bg-red" data-toggle="modal" data-target="#delete-<?= $site->getId(); ?>"><i class="fa fa-trash-o"></i></button>
                                                <?php endif; ?>
                                                <div class="modal" id="delete-<?= $site->getId(); ?>">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span></button>
                                                                <h4 class="modal-title"><?= $site->getDomain(); ?></h4>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><?= esc_html__("Are you sure you want to delete this site and all it's content"); ?></p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= esc_html__('Close'); ?></button>
                                                                <button type="button" class="btn btn-primary" onclick="window.location = '<?= admin_url('site/' . (int) $site->getId() . '/d/'); ?>'"><?= esc_html__('Confirm'); ?></button>
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
                                        <th class="text-center"><?= esc_html__('URL'); ?></th>
                                        <th class="text-center"><?= esc_html__('Name'); ?></th>
                                        <th class="text-center"><?= esc_html__('Admin'); ?></th>
                                        <th class="text-center"><?= esc_html__('Status'); ?></th>
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
        <div class="modal" id="path">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?= esc_html__('Site Path'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <p><?= esc_html__("Based on your setup and where you installed TriTan, the system will figure out the correct path."); ?></p>
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

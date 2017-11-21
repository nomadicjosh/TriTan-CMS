<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/admin');
$app->view->block('admin');
TriTan\Config::set('screen_parent', 'sites');
TriTan\Config::set('screen_child', 'sites');
?>

<!-- form start -->
<form name="form" method="post" data-toggle="validator" action="<?= get_base_url(); ?>admin/site/" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-thumb-tack"></i>
                <h3 class="box-title"><?= _t('Sites', 'tritan-cms'); ?></h3>

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
                            <h3 class="box-title"><?= _t('Add New Site', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><?= _t('Subdomain', 'tritan-cms'); ?></label>
                                <input type="text" class="form-control input-lg" name="subdomain" value="<?= __return_post('subdomain'); ?>" required/>.<?= $app->req->server['HTTP_HOST']; ?>
                            </div>
                            <div class="form-group">
                                <label><?= _t('Site Name', 'tritan-cms'); ?></label>
                                <input type="text" class="form-control" name="site_name" value="<?= __return_post('site_name'); ?>" required/>
                            </div>
                            <div class="form-group">
                                <label><?= _t('Path', 'tritan-cms'); ?> <a href="#path" data-toggle="modal"><span class="badge"><i class="fa fa-question"></i></span></a></label>
                                <input type="text" class="form-control" name="site_path" value="<?=str_replace('index.php', '', $app->req->server['PHP_SELF']);?>" required/>
                            </div>
                            <div class="form-group">
                                <label><?= _t('Administrator', 'tritan-cms'); ?></label>
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
                            <h3 class="box-title"><?= _t('Sites', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <table id="example1" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center"><?= _t('URL', 'tritan-cms'); ?></th>
                                        <th class="text-center"><?= _t('Name', 'tritan-cms'); ?></th>
                                        <th class="text-center"><?= _t('Admin', 'tritan-cms'); ?></th>
                                        <th class="text-center"><?= _t('Status', 'tritan-cms'); ?></th>
                                        <th class="text-center"><?= _t('Action', 'tritan-cms'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sites as $site) : ?>
                                        <tr class="gradeX">
                                            <td class="text-center"><a href="//<?= _escape($site['site_domain']); ?><?= _escape($site['site_path']); ?>" target="new"><?= _escape($site['site_domain']); ?></a></td>
                                            <td class="text-center"><?= _escape($site['site_name']); ?></td>
                                            <td class="text-center"><?= get_name(_escape($site['site_owner'])); ?></td>
                                            <td class="text-center">
                                                <span class="label <?= ttcms_site_status_label(_escape($site['site_status'])); ?>" style="font-size:1em;font-weight: bold;">
                                                    <?= ucfirst(_escape($site['site_status'])); ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button"<?=ae('update_sites');?> class="btn btn-success" onclick="window.location = '<?= get_base_url(); ?>admin/site/<?= (int) _escape($site['site_id']); ?>/'"><i class="fa fa-pencil"></i></button>
                                                <?php if((int)_escape($site['site_id']) <> 1) : ?>
                                                <button type="button"<?= ae('delete_sites'); ?> class="btn bg-red" data-toggle="modal" data-target="#delete-<?= _escape($site['site_id']); ?>"><i class="fa fa-trash-o"></i></button>
                                                <?php endif; ?>
                                                <div class="modal" id="delete-<?= _escape($site['site_id']); ?>">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span></button>
                                                                <h4 class="modal-title"><?= _escape($site['site_domain']); ?></h4>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><?= _t("Are you sure you want to delete this site and all it's content"); ?></p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?= _t('Close'); ?></button>
                                                                <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>admin/site/<?= _escape($site['site_id']); ?>/d/'"><?= _t('Confirm'); ?></button>
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
                                        <th class="text-center"><?= _t('URL', 'tritan-cms'); ?></th>
                                        <th class="text-center"><?= _t('Name', 'tritan-cms'); ?></th>
                                        <th class="text-center"><?= _t('Admin', 'tritan-cms'); ?></th>
                                        <th class="text-center"><?= _t('Status', 'tritan-cms'); ?></th>
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
        <div class="modal" id="path">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title"><?= _t('Site Path', 'tritan-cms'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <p><?= _t("Based on your setup and where you installed TriTan, the system will figure out the correct path.", 'tritan-cms'); ?></p>
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
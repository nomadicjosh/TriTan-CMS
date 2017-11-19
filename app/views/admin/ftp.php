<?php if (!defined('BASE_PATH')) exit('No direct script access allowed');
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/admin');
$app->view->block('admin');
TriTan\Config::set('screen_parent', 'dashboard');
TriTan\Config::set('screen_child', 'ftp');
?>

<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<link href="vendor/studio-42/elfinder/css/elfinder.full.css" type="text/css" rel="stylesheet" />
<link href="vendor/studio-42/elfinder/css/theme.css" type="text/css" rel="stylesheet" />
<script src="vendor/studio-42/elfinder/js/elfinder.full.js" type="text/javascript"></script>
<script type="text/javascript">
	$().ready(function() {
		var elf = $('#elfinder').elfinder({
			url : '<?=get_base_url();?>admin/ftp-connector/',
			modal: true,
			resizable:false
		}).elfinder('instance');
	});
</script>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-exchange"></i>
            <h3 class="box-title"><?= _t('FTP'); ?></h3>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        
        <?= _ttcms_flash()->showMessage(); ?>
        
        <div class="box box-default">
            <div class="box-body">
                <div id="elfinder"></div>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<?php $app->view->stop(); ?>
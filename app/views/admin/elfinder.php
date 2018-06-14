<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Functions as func;
/**
 * File Manager Window
 * 
 * This file will be called when the image button is invoked on
 * tinyMCE
 *  
 * @license GPLv3
 * 
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$this->layout('main::_layouts/blank-layout');
$this->section('blank');
error_reporting(0);
?>

<!DOCTYPE html>
<html>
    <head>
        <base href="<?= func\get_base_url(); ?>">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?=$this->title . ' &lsaquo; ' . $this->app->hook->{'get_option'}('sitename'); ?> &#8212; <?=func\_t('TriTan CMS', 'tritan-cms');?></title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
        <link href="vendor/studio-42/elfinder/css/elfinder.full.css" type="text/css" rel="stylesheet" />
        <link href="vendor/studio-42/elfinder/css/theme.css" type="text/css" rel="stylesheet" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <script>
            var rootPath = '<?= func\get_base_url(); ?>';
        </script>
        <!-- jQuery 2.2.3 -->
        <script src="static/assets/js/jQuery/jquery-2.2.3.min.js"></script>
        <!-- jQuery UI 1.11.4 -->
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
        <!-- Bootstrap 3.3.6 -->
        <script src="vendor/studio-42/elfinder/js/elfinder.full.js" type="text/javascript"></script>
        <script src="static/assets/js/tinymce/tinymce.plugin.js" type="text/javascript"></script>
    </head>
    <body class="">

        <div class="widget-body">
            <div class="row">

                <div class="col-md-12">
                    <div class="panel-body">
                        <div id="elfinder"></div>
                    </div>
                </div>
           	</div>
        </div>

    </body>
</html>
<?php $this->stop(); ?>
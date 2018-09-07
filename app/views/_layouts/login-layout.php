<?php
$app = \Liten\Liten::getInstance();
use TriTan\Common\Hooks\ActionFilterHook as hook;

ob_start();
ob_implicit_flush(0);
hook::getInstance()->{'doAction'}('login_init');
$option = (
    new \TriTan\Common\Options\Options(
        new TriTan\Common\Options\OptionsMapper(
            new \TriTan\Database(),
            new TriTan\Common\Context\HelperContext()
        )
    )
);
?>
<!DOCTYPE html>
<html>
    <head>
        <base href="<?= site_url(); ?>">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?= t__('Login', 'tritan-cms') . ' &lsaquo; ' . $option->{'read'}('sitename'); ?> &#8212; <?= t__('TriTan CMS', 'tritan-cms'); ?></title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <meta name="theme-color" content="#ffffff">
        <!-- Bootstrap 3.3.6 -->
        <link rel="stylesheet" href="static/assets/css/bootstrap/lumen-bootstrap.min.css">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <!-- Main -->
        <link rel="stylesheet" href="static/assets/css/login.css">
    </head>
    <body>

<?php $this->section('login'); ?>
<?php $this->stop(); ?>

        <!-- jQuery 2.2.4 -->
        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.js"></script>
        <!-- jQuery UI 1.12.1 -->
        <script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <!-- Bootstrap 3.3.6 -->
        <script src="static/assets/js/bootstrap/bootstrap.min.js"></script>
        <script src="static/assets/js/login.js" type="text/javascript"></script>
    </body>
</html>
<?php print_gzipped_page(); ?>

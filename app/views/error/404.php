<?php if (!defined('BASE_PATH')) exit('No direct script access allowed'); ?>

<?php $app = \Liten\Liten::getInstance(); ?>
<?php $app->view->extend('_layouts/default'); ?>
<?php $app->view->block('default'); ?>

<div class="center message">
    <h1><?= _t("This page doesn't exist!"); ?></h1>
    <p><?= sprintf(_t('Would you like to try our <a href="%s">homepage</a> instead?'), get_base_url()); ?></p>
</div>

<?php $app->view->stop(); ?>
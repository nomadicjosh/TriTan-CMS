<?php if (!defined('BASE_PATH')) exit('No direct script access allowed'); ?>

<?php $app = \Liten\Liten::getInstance(); ?>
<?php $app->view->extend('_layouts/default'); ?>
<?php $app->view->block('default'); ?>

<div class="center message">
    <h1><?= esc_html__("This page doesn't exist!"); ?></h1>
    <p><?= sprintf(esc_html__('Would you like to try our <a href="%s">homepage</a> instead?'), home_url()); ?></p>
</div>

<?php $app->view->stop(); ?>
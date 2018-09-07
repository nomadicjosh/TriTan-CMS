<?php
use TriTan\Common\Hooks\ActionFilterHook as hook;
$this->layout('main::_layouts/login-layout');
$this->section('login');
?>

<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
        <?php hook::getInstance()->{'doAction'}('login_form_top'); ?>
            <div class="panel panel-login">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <form id="login-form" action="<?= login_url(); ?>" method="post" role="form" style="display: block;" autocomplete="off">
                                <h2><?= esc_html__('Login'); ?></h2>
                                <div class="form-group">
                                    <input type="text" name="user_login" id="username" tabindex="1" class="form-control" placeholder="<?= esc_attr__('Username'); ?>" required/>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="user_pass" id="password" tabindex="2" class="form-control" placeholder="<?= esc_attr__('Password'); ?>" required/>
                                </div>
                                <div class="col-xs-6 form-group pull-left checkbox">
                                    <input id="checkbox1" type="checkbox" name="rememberme" value="yes" />
                                    <label for="checkbox1"><?= esc_html__('Remember Me'); ?></label>
                                </div>
                                <div class="col-xs-6 form-group pull-right">
                                    <input type="submit" name="login-submit" id="login-submit" tabindex="4" class="form-control btn btn-login" value="<?= esc_html__('Log In'); ?>">
                                </div>
                            </form>
                            <form id="register-form" action="<?= site_url('reset-password/'); ?>" method="post" role="form" style="display: none;">
                                <h2><?= esc_html__('Lost Password'); ?></h2>
                                <div class="form-group">
                                    <input type="text" name="username" id="username" tabindex="1" class="form-control" placeholder="<?= esc_attr__('Username'); ?>" value="">
                                </div>
                                <div class="form-group">
                                    <input type="email" name="email" id="email" tabindex="1" class="form-control" placeholder="<?= esc_attr__('Email Address'); ?>" value="">
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-6 col-sm-offset-3">
                                            <input type="submit" name="reset_password" tabindex="4" class="form-control btn btn-register" value="<?= esc_attr__('Reset Password'); ?>">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-6 tabs">
                            <a href="#" class="active" id="login-form-link"><div class="login"><?= esc_html__('Login'); ?></div></a>
                        </div>
                        <div class="col-xs-6 tabs">
                            <a href="#" id="register-form-link"><div class="register"><?= esc_html__('Lost Password'); ?></div></a>
                        </div>
                    </div>
                </div>
            </div>
        <?php hook::getInstance()->{'doAction'}('login_form_bottom'); ?>
        </div>
    </div>
</div>
<footer>
    <div class="container">
        <div class="col-md-10 col-md-offset-1 text-center">
            <h6 style="font-size:14px;font-weight:100;color: #fff;"><?= esc_html__('Powered by'); ?> <a href="//www.tritancms.com" style="color: #fff;"><?= esc_html__('TriTan CMS'); ?></a> r<?= CURRENT_RELEASE; ?></h6>
        </div>
    </div>
</footer>
<?php $this->stop(); ?>

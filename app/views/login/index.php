<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Functions as func;
$this->layout('main::_layouts/login-layout');
$this->section('login');
?>

<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
        <?php $this->app->hook->{'do_action'}('login_form_top'); ?>
            <div class="panel panel-login">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <form id="login-form" action="<?= get_base_url(); ?>login/" method="post" role="form" style="display: block;" autocomplete="off">
                                <h2><?= func\_t('Login', 'tritan-cms'); ?></h2>
                                <div class="form-group">
                                    <input type="text" name="user_login" id="username" tabindex="1" class="form-control" placeholder="Username" required/>
                                </div>
                                <div class="form-group">
                                    <input type="password" name="user_pass" id="password" tabindex="2" class="form-control" placeholder="Password" required/>
                                </div>
                                <div class="col-xs-6 form-group pull-left checkbox">
                                    <input id="checkbox1" type="checkbox" name="rememberme" value="yes" />
                                    <label for="checkbox1"><?= func\_t('Remember Me', 'tritan-cms'); ?></label>   
                                </div>
                                <div class="col-xs-6 form-group pull-right">     
                                    <input type="submit" name="login-submit" id="login-submit" tabindex="4" class="form-control btn btn-login" value="<?= func\_t('Log In', 'tritan-cms'); ?>">
                                </div>
                            </form>
                            <form id="register-form" action="<?= get_base_url(); ?>reset-password/" method="post" role="form" style="display: none;">
                                <h2><?= func\_t('Lost Password', 'tritan-cms'); ?></h2>
                                <div class="form-group">
                                    <input type="text" name="username" id="username" tabindex="1" class="form-control" placeholder="Username" value="">
                                </div>
                                <div class="form-group">
                                    <input type="email" name="email" id="email" tabindex="1" class="form-control" placeholder="Email Address" value="">
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-sm-6 col-sm-offset-3">
                                            <input type="submit" name="reset_password" tabindex="4" class="form-control btn btn-register" value="<?= func\_t('Reset Password', 'tritan-cms'); ?>">
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
                            <a href="#" class="active" id="login-form-link"><div class="login"><?= func\_t('Login', 'tritan-cms'); ?></div></a>
                        </div>
                        <div class="col-xs-6 tabs">
                            <a href="#" id="register-form-link"><div class="register"><?= func\_t('Lost Password', 'tritan-cms'); ?></div></a>
                        </div>
                    </div>
                </div>
            </div>
        <?php $this->app->hook->{'do_action'}('login_form_bottom'); ?>
        </div>
    </div>
</div>
<footer>
    <div class="container">
        <div class="col-md-10 col-md-offset-1 text-center">
            <h6 style="font-size:14px;font-weight:100;color: #fff;"><?= func\_t('Powered by', 'tritan-cms'); ?> <a href="//www.tritancms.com" style="color: #fff;"><?= func\_t('TriTan CMS', 'tritan-cms'); ?></a> r<?= CURRENT_RELEASE; ?></h6>
        </div>   
    </div>
</footer>
<?php $this->stop(); ?>

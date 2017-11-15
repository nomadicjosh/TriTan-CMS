<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * Installation View
 *  
 * @license GPLv3
 * 
 * @since       1.0.0
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/blank');
$app->view->block('blank');

?>        

<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="<?= get_base_url(); ?>">
        <meta charset="utf-8">
        <title><?= _t('Install', 'tritan-cms'); ?></title>

        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="TriTan CMS Installer">

        <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
        <!--[if lt IE 9]>
            <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->

        <!-- Le styles -->
        <link href="static/assets/css/bootstrap/bootstrap-install.css" rel="stylesheet" media="screen">
        <link href="static/assets/css/bootstrap/todc-bootstrap.css" rel="stylesheet" media="screen">
        <link href="static/assets/css/bootstrap/addon.css" rel="stylesheet" media="screen">
        <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->

    </head>

    <body>

        <!-- Navigation
        ================================================== -->

        <!-- Part 1: Wrap all page content here -->
        <div id="wrap">

            <!-- Fixed navbar -->

            <div class="navbar navbar-googlebar navbar-fixed-top">
                <div class="navbar-inner">
                    <div class="container">
                        <ul class="nav">
                            <a class="brand" href="install/"><?= _t('TriTan CMS Install', 'tritan-cms'); ?></a>
                        </ul>
                    </div>


                </div>

            </div>
            <!--/.nav-collapse -->


            <!-- Begin page content -->
            <div class="container">
                <div class="l_content">

                    <!-- Main content
                    ================================================== -->
                    <div id="wrap">
                        <div class="container">
                            <div class="page-header">

                                <div class="alert alert-info" data-dismiss="alert"><strong><?=_t('Notice!', 'tritan-cms');?></strong> <?=_t('After filling out the form and clicking the Install button, the system will do a quick install and the redirect you to the admin backend to login with the username and password you entered.');?></div>
                                
                                <div class="row">
                                    <div class="span6">
                                        <form class="form-horizontal" method="post" action="<?=get_base_url();?>install/">

                                            <fieldset>
                                                <legend><small><?=_t('Site Details', 'tritan-cms');?></small></legend>
                                                <div class="control-group">
                                                    <label class="control-label"><font color="red">*</font> <?=_t('Site Name', 'tritan-cms');?></label>
                                                    <div class="controls">
                                                        <input type="text" class="input-large" name="sitename" value="<?=__return_post('sitename')?>" required/>
                                                    </div>
                                                </div>
                                                <div class="control-group">
                                                    <label class="control-label"><?=_t('Site Description', 'tritan-cms');?></label>
                                                    <div class="controls">
                                                        <input type="text" class="input-large" name="site_description" value="<?=__return_post('site_description')?>" />
                                                    </div>
                                                </div>
                                            </fieldset>
                                    </div>
                                    <div class="span5 offset1">
                                        <fieldset>
                                            <legend><small><?=_t('Superadmin Account', 'tritan-cms');?></small></legend>
                                            <div class="control-group">
                                                <label class="control-label"><font color="red">*</font> <?=_t('First Name', 'tritan-cms');?></label>
                                                <div class="controls">
                                                    <input type="text" class="input-large" name="user_fname" value="<?=__return_post('user_fname')?>" required/>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label"><font color="red">*</font> <?=_t('Last Name', 'tritan-cms');?></label>
                                                <div class="controls">
                                                    <input type="text" class="input-large" name="user_lname" value="<?=__return_post('user_lname')?>" required/>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label"><font color="red">*</font> <?=_t('Email', 'tritan-cms');?></label>
                                                <div class="controls">
                                                    <input type="email" class="input-large" name="user_email" value="<?=__return_post('user_email')?>" required/>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label"><font color="red">*</font> <?=_t('Username', 'tritan-cms');?></label>
                                                <div class="controls">
                                                    <input type="text" class="input-large" name="user_login" value="<?=__return_post('user_login')?>" required/>
                                                </div>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label"><font color="red">*</font> <?=_t('Password', 'tritan-cms');?></label>
                                                <div class="controls">
                                                    <input type="text" class="input-large" name="user_pass" value="<?=__return_post('user_pass')?>" required/>
                                                </div>
                                            </div>
                                        </fieldset>

                                    </div>
                                    <div class="form-actions">
                                        <input type="hidden" name="user_status" value="A" />
                                        <input type="hidden" name="user_role" value="1" />
                                        <button type="submit" class="pull-right btn btn-primary"><?=_t('Install', 'tritan-cms');?></button>
                                    </div>

                                    </form>

                                </div>
                            </div>

                            <!-- Footer
                ================================================== -->
                        </div>
                    </div>
                </div>
            </div>

            <div id="push"></div>
        </div>

        <div id="footer">
            <div class="container">
                <br />
                <p class="pull-left muted"><a href="//www.tritancms.com/">&copy; TriTan CMS 2017</a></p>
            </div>
        </div>


        <!-- Le javascript -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
        <script src="static/assets/js/bootstrap/bootstrap.min.js"></script>

    </body>
</html>

<?php $app->view->stop(); ?>
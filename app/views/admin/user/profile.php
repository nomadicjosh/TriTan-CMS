<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Functions as func;
/**
 * Update Profile View
 *  
 * @license GPLv3
 * 
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
Config::set('screen_parent', 'users');
Config::set('screen_child', 'profile');
$user = func\get_userdata($this->current_user_id);
?>

<!-- form start -->
<form method="post" action="<?= func\get_base_url(); ?>admin/user/profile/" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-user"></i>
                <h3 class="box-title"><?= func\_t('Profile', 'tritan-cms'); ?></h3>

                <div class="pull-right">
                    <input type="hidden" name="user_id" value="<?=$this->current_user_id;?>" />
                    <input type="hidden" name="user_role" value="<?= func\get_role_by_id(func\get_user_option('role', (int) $this->current_user_id))['role']['role_key']; ?>" />
                    <input type="hidden" name="user_status" value="<?= func\get_user_option('status', (int) $this->current_user_id); ?>" />
                    <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= func\_t('Update', 'tritan-cms'); ?></button>
                    <button type="button"<?=func\ae('manage_users');?> class="btn btn-primary" onclick="window.location = '<?= func\get_base_url(); ?>admin/user/'"><i class="fa fa-ban"></i> <?= func\_t('Cancel', 'tritan-cms'); ?></button>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">

            <?= func\_ttcms_flash()->showMessage(); ?> 

            <div class="row">
                <!-- left column -->
                <div class="col-md-9">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= func\_t('Layout Options', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><?= func\_t('Fixed Layout', 'tritan-cms'); ?></strong></label>
                                <div class="ios-switch switch-md pull-right">
                                    <input type="hidden" class="js-switch" name="user_admin_layout" value="0" />
                                    <input type="checkbox" class="js-switch" name="user_admin_layout"<?= checked('1', func\get_user_option('admin_layout', (int) func\_escape($user->user_id))); ?> value="1" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label><strong><?= func\_t('Toggle Sidebar', 'tritan-cms'); ?></strong></label>
                                <div class="ios-switch switch-md pull-right">
                                    <input type="hidden" class="js-switch" name="user_admin_sidebar" value="0" />
                                    <input type="checkbox" class="js-switch" name="user_admin_sidebar"<?= checked('1', func\get_user_option('admin_sidebar', (int) func\_escape($user->user_id))); ?> value="1" />
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><strong><?= func\_t('Skin', 'tritan-cms'); ?></strong></label>
                                <ul style="list-style: none;margin:0px 0px 0px -30px;">
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-blue', func\get_user_option('admin_skin', (int) func\_escape($user->user_id))); ?> value="skin-blue" />
                                        <a href="javascript:void(0)" data-skin="skin-blue" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px; background: #367fa9"></span><span class="bg-light-blue" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222d32"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-black', func\get_user_option('admin_skin', (int) func\_escape($user->user_id))); ?> value="skin-black" />
                                        <a href="javascript:void(0)" data-skin="skin-black" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1)" class="clearfix"><span style="display:block; width: 20%; float: left; height: 7px; background: #fefefe"></span><span style="display:block; width: 80%; float: left; height: 7px; background: #fefefe"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-purple', func\get_user_option('admin_skin', (int) func\_escape($user->user_id))); ?> value="skin-purple" />
                                        <a href="javascript:void(0)" data-skin="skin-purple" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-purple-active"></span><span class="bg-purple" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222d32"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-green', func\get_user_option('admin_skin', (int) func\_escape($user->user_id))); ?> value="skin-green" />
                                        <a href="javascript:void(0)" data-skin="skin-green" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-green-active"></span><span class="bg-green" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222d32"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-red', func\get_user_option('admin_skin', (int) func\_escape($user->user_id))); ?> value="skin-red" />
                                        <a href="javascript:void(0)" data-skin="skin-red" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-red-active"></span><span class="bg-red" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222d32"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-yellow', func\get_user_option('admin_skin', (int) func\_escape($user->user_id))); ?> value="skin-yellow" />
                                        <a href="javascript:void(0)" data-skin="skin-yellow" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-yellow-active"></span><span class="bg-yellow" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222d32"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-blue-light', func\get_user_option('admin_skin', (int) func\_escape($user->user_id))); ?> value="skin-blue-light" />
                                        <a href="javascript:void(0)" data-skin="skin-blue-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px; background: #367fa9"></span><span class="bg-light-blue" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-black-light', func\get_user_option('admin_skin', (int) func\_escape($user->user_id))); ?> value="skin-black-light" />
                                        <a href="javascript:void(0)" data-skin="skin-black-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1)" class="clearfix"><span style="display:block; width: 20%; float: left; height: 7px; background: #fefefe"></span><span style="display:block; width: 80%; float: left; height: 7px; background: #fefefe"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-purple-light', func\get_user_option('admin_skin', (int) func\_escape($user->user_id))); ?> value="skin-purple-light" />
                                        <a href="javascript:void(0)" data-skin="skin-purple-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-purple-active"></span><span class="bg-purple" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-green-light', func\get_user_option('admin_skin', (int) func\_escape($user->user_id))); ?> value="skin-green-light" />
                                        <a href="javascript:void(0)" data-skin="skin-green-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-green-active"></span><span class="bg-green" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-red-light', func\get_user_option('admin_skin', (int) func\_escape($user->user_id))); ?> value="skin-red-light" />
                                        <a href="javascript:void(0)" data-skin="skin-red-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-red-active"></span><span class="bg-red" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-yellow-light', func\get_user_option('admin_skin', (int) func\_escape($user->user_id))); ?> value="skin-yellow-light" />
                                        <a href="javascript:void(0)" data-skin="skin-yellow-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-yellow-active"></span><span class="bg-yellow" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Layout Options' section on the 'Profile' screen.
                             * 
                             * @since 0.9
                             * @param array $user User object.
                             */
                            $this->app->hook->{'do_action'}('user_profile_layout', $user);
                            ?>
                        </div>
                    </div>
                    
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= func\_t('Name', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><?= func\_t('Username', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_login" value="<?= func\get_user_option('username', (int) func\_escape($user->user_id)); ?>" readonly required>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= func\_t('First Name', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_fname" value="<?= func\get_user_option('fname', (int) func\_escape($user->user_id)); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= func\_t('Last Name', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_lname" value="<?= func\get_user_option('lname', (int) func\_escape($user->user_id)); ?>" required>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Name' section on the 'Profile' screen.
                             * 
                             * @since 0.9
                             * @param array $user User object.
                             */
                            $this->app->hook->{'do_action'}('user_profile_name', $user);
                            ?>
                        </div>
                    </div>
                    
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= func\_t('Contact info', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= func\_t('Email', 'tritan-cms'); ?></strong></label>
                                <input type="email" class="form-control" name="user_email" value="<?= func\get_user_option('email', (int) func\_escape($user->user_id)); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><strong><?= func\_t('URL', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_url" value="<?= func\get_userdata((int) func\_escape($user->user_id))->user_url; ?>" />
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Contact info' section on the 'Profile' screen.
                             * 
                             * @since 0.9
                             * @param array $user User object.
                             */
                            $this->app->hook->{'do_action'}('user_profile_contact', $user);
                            ?>
                            
                        </div>
                    </div>
                    
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= func\_t('Password', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            
                            <div class="form-group">
                                <label><strong><?= func\_t('New Password', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_pass" />
                                <p class="help-block"><?= func\_t('Leave blank if not updating password.', 'tritan-cms'); ?></p>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'New Password' section on the 'Profile' screen.
                             * 
                             * @since 0.9
                             * @param array $user User object.
                             */
                            $this->app->hook->{'do_action'}('user_profile_password', $user);
                            ?>
                            
                        </div>
                    </div>
                    
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= func\_t('About yourself', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><?= func\_t('Biography', 'tritan-cms'); ?></strong></label>
                                <textarea class="form-control" name="user_bio" rows="5"><?= func\get_user_option('bio', (int) func\_escape($user->user_id)); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label><strong><?= func\_t('Profile Picture', 'tritan-cms'); ?></strong></label>
                                <div><?=func\get_user_avatar(func\get_user_option('email', (int) func\_escape($user->user_id)), 100);?></div>
                                <p class="help-block"><?= sprintf(func\_t('You can change your profile picture on <a href="%s">Gravatar</a>.', 'tritan-cms'), '//en.gravatar.com/'); ?></p>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'About yourself' section on the 'Profile' screen.
                             * 
                             * @since 0.9
                             * @param array $user User object.
                             */
                            $this->app->hook->{'do_action'}('user_profile_about', $user);
                            ?>
                            
                        </div>
                    </div>
                    
                    <?php
                    /**
                     * Fires after the 'About yourself' section on the 'Profile' screen.
                     * 
                     * @since 0.9
                     * @param array $user User object.
                     */
                    $this->app->hook->{'do_action'}('user_profile', $user);
                    ?>
                    
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->

        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
</form>
<!-- form end -->
<?php $this->stop(); ?>

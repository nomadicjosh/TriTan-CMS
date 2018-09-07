<?php
use TriTan\Container;
use TriTan\Common\Hooks\ActionFilterHook as hook;

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
Container::getInstance()->{'set'}('screen_parent', 'users');
Container::getInstance()->{'set'}('screen_child', 'profile');
$user = get_userdata($this->current_user_id);
?>

<!-- form start -->
<form method="post" action="<?= admin_url('user/profile/'); ?>" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-user"></i>
                <h3 class="box-title"><?= esc_html__('Profile'); ?></h3>

                <div class="pull-right">
                    <input type="hidden" name="user_id" value="<?=$this->current_user_id;?>" />
                    <input type="hidden" name="user_role" value="<?= get_role_by_id(get_user_option('role', (int) $this->current_user_id))['role']['role_key']; ?>" />
                    <input type="hidden" name="user_status" value="<?= get_user_option('status', (int) $this->current_user_id); ?>" />
                    <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= esc_html__('Update'); ?></button>
                    <button type="button"<?=ae('manage_users');?> class="btn btn-primary" onclick="window.location = '<?= admin_url('user/'); ?>'"><i class="fa fa-ban"></i> <?= esc_html__('Cancel'); ?></button>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">

            <?= (new \TriTan\Common\FlashMessages())->showMessage(); ?>

            <div class="row">
                <!-- left column -->
                <div class="col-md-9">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= esc_html__('Layout Options'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><?= esc_html__('Fixed Layout'); ?></strong></label>
                                <div class="ios-switch switch-md pull-right">
                                    <input type="hidden" class="js-switch" name="user_admin_layout" value="0" />
                                    <input type="checkbox" class="js-switch" name="user_admin_layout"<?= checked('1', get_user_option('admin_layout', (int) esc_html($this->current_user_id))); ?> value="1" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label><strong><?= esc_html__('Toggle Sidebar'); ?></strong></label>
                                <div class="ios-switch switch-md pull-right">
                                    <input type="hidden" class="js-switch" name="user_admin_sidebar" value="0" />
                                    <input type="checkbox" class="js-switch" name="user_admin_sidebar"<?= checked('1', get_user_option('admin_sidebar', (int) esc_html($this->current_user_id))); ?> value="1" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label><strong><?= esc_html__('Skin'); ?></strong></label>
                                <ul style="list-style: none;margin:0px 0px 0px -30px;">
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-blue', get_user_option('admin_skin', (int) esc_html($this->current_user_id))); ?> value="skin-blue" />
                                        <a href="javascript:void(0)" data-skin="skin-blue" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px; background: #367fa9"></span><span class="bg-light-blue" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222d32"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-black', get_user_option('admin_skin', (int) esc_html($this->current_user_id))); ?> value="skin-black" />
                                        <a href="javascript:void(0)" data-skin="skin-black" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1)" class="clearfix"><span style="display:block; width: 20%; float: left; height: 7px; background: #fefefe"></span><span style="display:block; width: 80%; float: left; height: 7px; background: #fefefe"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-purple', get_user_option('admin_skin', (int) esc_html($this->current_user_id))); ?> value="skin-purple" />
                                        <a href="javascript:void(0)" data-skin="skin-purple" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-purple-active"></span><span class="bg-purple" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222d32"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-green', get_user_option('admin_skin', (int) esc_html($this->current_user_id))); ?> value="skin-green" />
                                        <a href="javascript:void(0)" data-skin="skin-green" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-green-active"></span><span class="bg-green" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222d32"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-red', get_user_option('admin_skin', (int) esc_html($this->current_user_id))); ?> value="skin-red" />
                                        <a href="javascript:void(0)" data-skin="skin-red" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-red-active"></span><span class="bg-red" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222d32"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-yellow', get_user_option('admin_skin', (int) esc_html($this->current_user_id))); ?> value="skin-yellow" />
                                        <a href="javascript:void(0)" data-skin="skin-yellow" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-yellow-active"></span><span class="bg-yellow" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #222d32"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-blue-light', get_user_option('admin_skin', (int) esc_html($this->current_user_id))); ?> value="skin-blue-light" />
                                        <a href="javascript:void(0)" data-skin="skin-blue-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px; background: #367fa9"></span><span class="bg-light-blue" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-black-light', get_user_option('admin_skin', (int) esc_html($this->current_user_id))); ?> value="skin-black-light" />
                                        <a href="javascript:void(0)" data-skin="skin-black-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div style="box-shadow: 0 0 2px rgba(0,0,0,0.1)" class="clearfix"><span style="display:block; width: 20%; float: left; height: 7px; background: #fefefe"></span><span style="display:block; width: 80%; float: left; height: 7px; background: #fefefe"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-purple-light', get_user_option('admin_skin', (int) esc_html($this->current_user_id))); ?> value="skin-purple-light" />
                                        <a href="javascript:void(0)" data-skin="skin-purple-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-purple-active"></span><span class="bg-purple" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-green-light', get_user_option('admin_skin', (int) esc_html($this->current_user_id))); ?> value="skin-green-light" />
                                        <a href="javascript:void(0)" data-skin="skin-green-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-green-active"></span><span class="bg-green" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-red-light', get_user_option('admin_skin', (int) esc_html($this->current_user_id))); ?> value="skin-red-light" />
                                        <a href="javascript:void(0)" data-skin="skin-red-light" style="display: block; box-shadow: 0 0 3px rgba(0,0,0,0.4)" class="clearfix full-opacity-hover">
                                        <div><span style="display:block; width: 20%; float: left; height: 7px;" class="bg-red-active"></span><span class="bg-red" style="display:block; width: 80%; float: left; height: 7px;"></span></div>
                                        <div><span style="display:block; width: 20%; float: left; height: 20px; background: #f9fafc"></span><span style="display:block; width: 80%; float: left; height: 20px; background: #f4f5f7"></span></div>
                                        </a>
                                    </li>
                                    <li style="float:left; width: 25%; padding: 5px;">
                                        <input type="radio" class="flat-red" name="user_admin_skin"<?= checked('skin-yellow-light', get_user_option('admin_skin', (int) esc_html($this->current_user_id))); ?> value="skin-yellow-light" />
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
                            hook::getInstance()->{'doAction'}('user_profile_layout', $user);
                            ?>
                        </div>
                    </div>

                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= esc_html__('Name'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><?= esc_html__('Username'); ?></strong></label>
                                <input type="text" class="form-control" name="user_login" value="<?= get_user_option('username', (int) esc_html($this->current_user_id)); ?>" readonly required>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= esc_html__('First Name'); ?></strong></label>
                                <input type="text" class="form-control" name="user_fname" value="<?= get_user_option('fname', (int) esc_html($this->current_user_id)); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= esc_html__('Last Name'); ?></strong></label>
                                <input type="text" class="form-control" name="user_lname" value="<?= get_user_option('lname', (int) esc_html($this->current_user_id)); ?>" required>
                            </div>

                            <?php
                            /**
                             * Fires at the end of the 'Name' section on the 'Profile' screen.
                             *
                             * @since 0.9
                             * @param array $user User object.
                             */
                            hook::getInstance()->{'doAction'}('user_profile_name', $user);
                            ?>
                        </div>
                    </div>

                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= esc_html__('Contact info'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= esc_html__('Email'); ?></strong></label>
                                <input type="email" class="form-control" name="user_email" value="<?= get_user_option('email', (int) esc_html($this->current_user_id)); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><strong><?= esc_html__('URL'); ?></strong></label>
                                <input type="text" class="form-control" name="user_url" value="<?= $user->getUrl(); ?>" />
                            </div>

                            <?php
                            /**
                             * Fires at the end of the 'Contact info' section on the 'Profile' screen.
                             *
                             * @since 0.9
                             * @param array $user User object.
                             */
                            hook::getInstance()->{'doAction'}('user_profile_contact', $user);
                            ?>

                        </div>
                    </div>

                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= esc_html__('Password'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><?= esc_html__('New Password'); ?></strong></label>
                                <input type="text" class="form-control" name="user_pass" />
                                <p class="help-block"><?= esc_html__('Leave blank if not updating password.'); ?></p>
                            </div>

                            <?php
                            /**
                             * Fires at the end of the 'New Password' section on the 'Profile' screen.
                             *
                             * @since 0.9
                             * @param array $user User object.
                             */
                            hook::getInstance()->{'doAction'}('user_profile_password', $user);
                            ?>

                        </div>
                    </div>

                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= esc_html__('About yourself'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><?= esc_html__('Biography'); ?></strong></label>
                                <textarea class="form-control" name="user_bio" rows="5"><?= get_user_option('bio', (int) esc_html($this->current_user_id)); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label><strong><?= esc_html__('Profile Picture'); ?></strong></label>
                                <div><?= get_user_avatar(get_user_option('email', (int) esc_html($this->current_user_id)), 100);?></div>
                                <p class="help-block"><?= sprintf(t__('You can change your profile picture on <a href="%s">Gravatar</a>.'), '//en.gravatar.com/'); ?></p>
                            </div>

                            <?php
                            /**
                             * Fires at the end of the 'About yourself' section on the 'Profile' screen.
                             *
                             * @since 0.9
                             * @param array $user User object.
                             */
                            hook::getInstance()->{'doAction'}('user_profile_about', $user);
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
                    hook::getInstance()->{'doAction'}('user_profile', $user);
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

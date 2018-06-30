<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Functions\Dependency;
use TriTan\Functions\Auth;
use TriTan\Functions\User;
use TriTan\Functions\Core;
use TriTan\Functions\Hook;
/**
 * User Update View
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
Config::set('screen_child', 'all-users');

?>

<!-- form start -->
<form method="post" action="<?= Core\get_base_url(); ?>admin/user/<?= (int) $this->user->user_id; ?>/" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-user"></i>
                <h3 class="box-title"><?= Core\_t('Update User', 'tritan-cms'); ?></h3>

                <div class="pull-right">
                    <button type="button"<?=Auth\ae('create_users');?> class="btn btn-warning" onclick="window.location='<?=Core\get_base_url();?>admin/user/create/'"><i class="fa fa-plus"></i> <?= Core\_t('New User', 'tritan-cms'); ?></button>
                    <button type="submit"<?=Auth\ae('update_users');?> class="btn btn-success"><i class="fa fa-save"></i> <?= Core\_t('Update', 'tritan-cms'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= Core\get_base_url(); ?>admin/user/'"><i class="fa fa-ban"></i> <?= Core\_t('Cancel', 'tritan-cms'); ?></button>
                    <?php if((int) $this->user->user_id === (int) $this->current_user_id) : ?>
                    <input type="hidden" name="user_role" value="<?= Auth\get_role_by_id(User\get_user_option('role', (int) $this->user->user_id))['role']['role_key']; ?>" />
                    <input type="hidden" name="user_status" value="<?= User\get_user_option('status', (int) $this->user->user_id); ?>" />
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">

            <?= Dependency\_ttcms_flash()->showMessage(); ?> 

            <div class="row">
                <!-- left column -->
                <div class="col-md-9">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= Core\_t('Name', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><?= Core\_t('Username', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" id="user_login" name="user_login" value="<?= User\get_user_option('username', (int) $this->user->user_id); ?>" readonly="readonly" required/>
                                <?php if(Auth\current_user_can('update_users')) : ?>
                                <button type="button" class="btn btn-primary" id="enable_button" style="display:none"><?= Core\_t('Change username', 'tritan-cms'); ?></button>
                                <button type="button" class="btn btn-danger" id="disable_button" style="display:none"><?= Core\_t('Cancel', 'tritan-cms'); ?></button>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= Core\_t('First Name', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_fname" value="<?= User\get_user_option('fname', (int) $this->user->user_id); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= Core\_t('Last Name', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_lname" value="<?= User\get_user_option('lname', (int) $this->user->user_id); ?>" required>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Name' section on the 'Update User' screen.
                             * 
                             * @since 0.9
                             * @param array $this->user User object.
                             */
                            $this->app->hook->{'do_action'}('update_user_profile_name', $this->user);
                            ?>
                        </div>
                    </div>
                    
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= Core\_t('Contact info', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= Core\_t('Email', 'tritan-cms'); ?></strong></label>
                                <input type="email" class="form-control" name="user_email" value="<?= User\get_user_option('email', (int) $this->user->user_id); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><strong><?= Core\_t('URL', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_url" value="<?= Auth\get_userdata((int) $this->user->user_id)->user_url; ?>" />
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Contact info' section on the 'Update User' screen.
                             * 
                             * @since 0.9
                             * @param array $this->user User object.
                             */
                            $this->app->hook->{'do_action'}('update_user_profile_contact', $this->user);
                            ?>
                            
                        </div>
                    </div>
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= Core\_t('About the user', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><?= Core\_t('Biography', 'tritan-cms'); ?></strong></label>
                                <textarea class="form-control" name="user_bio" rows="5"><?= User\get_user_option('bio', (int) $this->user->user_id); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label><strong><?= Core\_t('Profile Picture', 'tritan-cms'); ?></strong></label>
                                <div><?= Hook\get_user_avatar(User\get_user_option('email', (int) $this->user->user_id), 100);?></div>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'About the user' section on the 'Update User' screen.
                             * 
                             * @since 0.9
                             * @param array $this->user User object.
                             */
                            $this->app->hook->{'do_action'}('update_user_profile_about', $this->user);
                            ?>
                            
                        </div>
                    </div>
                    <?php if((int) $this->user->user_id !== (int) $this->current_user_id) : ?>
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= Core\_t('User Status', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><?= Core\_t('Role', 'tritan-cms'); ?></strong></label>
                                <select class="form-control select2" name="user_role" style="width: 100%;">
                                    <option value=""> ---------------------- </option>
                                    <?php User\get_user_roles(User\get_user_option('role', (int) $this->user->user_id)); ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= Core\_t('Status', 'tritan-cms'); ?></strong></label>
                                <select class="form-control select2" name="user_status" style="width: 100%;" required>
                                    <option value=""> ---------------------- </option>
                                    <option value="A"<?= selected('A', User\get_user_option('status', (int) $this->user->user_id), false); ?>><?= Core\_t('Active', 'tritan-cms'); ?></option>
                                    <option value="I"<?= selected('I', User\get_user_option('status', (int) $this->user->user_id), false); ?>><?= Core\_t('Inactive', 'tritan-cms'); ?></option>
                                    <option value="S"<?= selected('S', User\get_user_option('status', (int) $this->user->user_id), false); ?>><?= Core\_t('Spammer', 'tritan-cms'); ?></option>
                                    <option value="B"<?= selected('B', User\get_user_option('status', (int) $this->user->user_id), false); ?>><?= Core\_t('Blocked', 'tritan-cms'); ?></option>
                                </select>
                                <p class="help-block"><?= Core\_t('If the account is Inactive, the user will be incapable of logging into the system.', 'tritan-cms'); ?></p>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Status' section on the 'Update User' screen.
                             * 
                             * @since 0.9
                             * @param array $this->user User object.
                             */
                            $this->app->hook->{'do_action'}('update_user_profile_status', $this->user);
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php
                    /**
                     * Fires after the 'About the User' section on the 'Update User' screen.
                     * 
                     * @since 0.9
                     * @param array $this->user User object.
                     */
                    $this->app->hook->{'do_action'}('update_user_profile', $this->user);
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
<script>
$("#disable_button").hide();
$("#enable_button").show();

$('#disable_button').click(function() {
    $("#disable_button").hide();
    $("#enable_button").show();
        $('#user_login').prop('readonly', true);
});
$('#enable_button').click(function() {
    $("#disable_button").show();
    $("#enable_button").hide();
        $('#user_login').prop('readonly', false);
});
</script>
<?php $this->stop(); ?>

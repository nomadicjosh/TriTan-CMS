<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
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
Config::set('screen_child', 'user');

?>

<!-- form start -->
<form method="post" action="<?= get_base_url(); ?>admin/user/<?= (int) $this->user->user_id; ?>/" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-user"></i>
                <h3 class="box-title"><?= _t('Update User', 'tritan-cms'); ?></h3>

                <div class="pull-right">
                    <button type="button"<?=ae('create_users');?> class="btn btn-warning" onclick="window.location='<?=get_base_url();?>admin/user/create/'"><i class="fa fa-plus"></i> <?= _t('New User', 'tritan-cms'); ?></button>
                    <button type="submit"<?=ae('update_users');?> class="btn btn-success"><i class="fa fa-save"></i> <?= _t('Update', 'tritan-cms'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>admin/user/'"><i class="fa fa-ban"></i> <?= _t('Cancel'); ?></button>
                    <?php if((int) $this->user->user_id === (int) $this->current_user_id) : ?>
                    <input type="hidden" name="user_role" value="<?= get_user_option('role', (int) $this->user->user_id); ?>" />
                    <input type="hidden" name="user_status" value="<?= get_user_option('status', (int) $this->user->user_id); ?>" />
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">

            <?= _ttcms_flash()->showMessage(); ?> 

            <div class="row">
                <!-- left column -->
                <div class="col-md-9">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= _t('Name', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><?= _t('Username', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" id="user_login" name="user_login" value="<?= get_user_option('username', (int) $this->user->user_id); ?>" readonly="readonly" required/>
                                <?php if(hasPermission('update_users')) : ?>
                                <button type="button" class="btn btn-primary" id="enable_button" style="display:none"><?= _t('Change username', 'tritan-cms'); ?></button>
                                <button type="button" class="btn btn-danger" id="disable_button" style="display:none"><?= _t('Cancel', 'tritan-cms'); ?></button>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('First Name', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_fname" value="<?= get_user_option('fname', (int) $this->user->user_id); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('Last Name', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_lname" value="<?= get_user_option('lname', (int) $this->user->user_id); ?>" required>
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
                            <h3 class="box-title"><?= _t('Contact info', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('Email', 'tritan-cms'); ?></strong></label>
                                <input type="email" class="form-control" name="user_email" value="<?= get_user_option('email', (int) $this->user->user_id); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><strong><?= _t('URL', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_url" value="<?= get_userdata((int) $this->user->user_id)->user_url; ?>" />
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
                            <h3 class="box-title"><?= _t('About the user', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><?= _t('Biography', 'tritan-cms'); ?></strong></label>
                                <textarea class="form-control" name="user_bio" rows="5"><?= get_user_option('bio', (int) $this->user->user_id); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label><strong><?= _t('Profile Picture', 'tritan-cms'); ?></strong></label>
                                <div><?=get_user_avatar(get_user_option('email', (int) $this->user->user_id), 100);?></div>
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
                            <h3 class="box-title"><?= _t('User Status', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><?= _t('Role', 'tritan-cms'); ?></strong></label>
                                <select class="form-control select2" name="user_role" style="width: 100%;">
                                    <option value=""> ---------------------- </option>
                                    <?php get_user_roles(get_user_option('role', (int) $this->user->user_id)); ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('Status', 'tritan-cms'); ?></strong></label>
                                <select class="form-control select2" name="user_status" style="width: 100%;" required>
                                    <option value=""> ---------------------- </option>
                                    <option value="A"<?= selected('A', get_user_option('status', (int) $this->user->user_id), false); ?>><?= _t('Active'); ?></option>
                                    <option value="I"<?= selected('I', get_user_option('status', (int) $this->user->user_id), false); ?>><?= _t('Inactive'); ?></option>
                                    <option value="S"<?= selected('S', get_user_option('status', (int) $this->user->user_id), false); ?>><?= _t('Spammer'); ?></option>
                                    <option value="B"<?= selected('B', get_user_option('status', (int) $this->user->user_id), false); ?>><?= _t('Blocked'); ?></option>
                                </select>
                                <p class="help-block"><?= _t('If the account is Inactive, the user will be incapable of logging into the system.', 'tritan-cms'); ?></p>
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

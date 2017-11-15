<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
/**
 * User Create View
 *  
 * @license GPLv3
 * 
 * @since       1.0.0
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$app = \Liten\Liten::getInstance();
$app->view->extend('_layouts/admin');
$app->view->block('admin');
define('SCREEN_PARENT', 'users');
define('SCREEN', 'auser');
?>

<style>
    #other{display:none;}
</style>

<script type="text/javascript">
$(document).ready(function(){
    $('#user').on('change', function(e) {
        $.ajax({
            type    : 'POST',
            url     : '<?=get_base_url();?>admin/user/lookup/',
            dataType: 'json',
            data    : $('#user_form').serialize(),
            cache: false,
            success: function( data ) {
                   for(var id in data) {        
                          $(id).val( data[id] );
                   }
            }
        });
    });
});
</script>

<!-- form start -->
<form id="user_form" method="post" action="<?= get_base_url(); ?>admin/user/create/" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-user"></i>
                <h3 class="box-title"><?= _t('Create User', 'tritan-cms'); ?></h3>

                <div class="pull-right">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?= _t('Save', 'tritan-cms'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= get_base_url(); ?>admin/user/'"><i class="fa fa-ban"></i> <?= _t('Cancel'); ?></button>
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
                                <label><strong><font color="red">*</font> <?= _t('Username', 'tritan-cms'); ?></strong></label>
                                <div id="other">
                                    <p style="margin-top:.6em;"><input type="text" class="form-control" name="user_login" placeholder="Username" /></p>
                                </div>
                                
                                <select id="user" class="form-control select2" name="user_login" style="width: 100%;" required>
                                    <option value=""> ---------------------- </option>
                                    <?php get_users_dropdown(__return_post('user_login')); ?>
                                    <option value="other"><?= _t('New User', 'tritan-cms'); ?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('First Name', 'tritan-cms'); ?></strong></label>
                                <input id="fname" type="text" class="form-control" name="user_fname" value="<?= __return_post('user_fname'); ?>" required/>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('Last Name', 'tritan-cms'); ?></strong></label>
                                <input id="lname" type="text" class="form-control" name="user_lname" value="<?= __return_post('user_lname'); ?>" required/>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Name' section on the 'Create User' screen.
                             * 
                             * @since 1.0.0
                             */
                            $app->hook->{'do_action'}('create_user_profile_name');
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
                                <input id="email" type="email" class="form-control" name="user_email" value="<?= __return_post('user_email'); ?>" required/>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Contact info' section on the 'Create User' screen.
                             * 
                             * @since 1.0.0
                             */
                            $app->hook->{'do_action'}('create_user_profile_contact');
                            ?>
                            
                        </div>
                    </div>
                    
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= _t('User Status', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('Role', 'tritan-cms'); ?></strong></label>
                                <select class="form-control select2" name="user_role" style="width: 100%;" required>
                                    <option value=""> ---------------------- </option>
                                    <?php get_user_roles(__return_post('user_role')); ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('Status', 'tritan-cms'); ?></strong></label>
                                <select class="form-control select2" name="user_status" style="width: 100%;" required>
                                    <option value=""> ---------------------- </option>
                                    <option value="A"<?= selected('A', __return_post('user_status'), false); ?>><?= _t('Active'); ?></option>
                                    <option value="I"<?= selected('I', __return_post('user_status'), false); ?>><?= _t('Inactive'); ?></option>
                                    <option value="S"<?= selected('S', __return_post('user_status'), false); ?>><?= _t('Spammer'); ?></option>
                                    <option value="B"<?= selected('B', __return_post('user_status'), false); ?>><?= _t('Blocked'); ?></option>
                                </select>
                                <p class="help-block"><?= _t('If the account is Inactive, the user will be incapable of logging in.'); ?></p>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Status' section on the 'Create User' screen.
                             * 
                             * @since 1.0.0
                             */
                            $app->hook->{'do_action'}('create_user_profile_status');
                            ?>
                        </div>
                    </div>
                    
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= _t('Password', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= _t('Set Password', 'tritan-cms'); ?></strong></label>
                                <input type="text" class="form-control" name="user_pass" value="<?= __return_post('user_pass'); ?>" required/>
                            </div>
                            
                            <div class="form-group">
                                <label><strong><?= _t('Send Email', 'tritan-cms'); ?></strong></label>
                                <div class="ios-switch switch-md">
                                    <input type="checkbox" class="js-switch" name="sendemail" value="1" />
                                <div>
                                <p class="help-block"><?=_t( 'Send username & password to the user.' );?></p>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Password' section on the 'Create User' screen.
                             * 
                             * @since 1.0.0
                             */
                            $app->hook->{'do_action'}('create_user_profile_password');
                            ?>
                        </div>
                    </div>
                    
                    <?php
                    /**
                     * Fires after the 'About the user' section on the 'Create User' screen.
                     * 
                     * @since 1.0.0
                     */
                    $app->hook->{'do_action'}('create_user_profile');
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
$('select[name=user_login]').change(function(e){
  if ($('select[name=user_login]').val() == 'other'){
      $('#other').show();
  } else {
    $('#other').hide();
  }
});
</script>
<?php $app->view->stop(); ?>

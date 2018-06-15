<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Functions as func;
/**
 * User Create View
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
Config::set('screen_child', 'auser');
?>

<script type="text/javascript">
$(document).ready(function(){
    $('#user_select').on('change', function(e) {
        $.ajax({
            type    : 'POST',
            url     : '<?=func\get_base_url();?>admin/user/lookup/',
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

 $(document).ready(function(){
    $('[name^="check"]').on('change', function() {   
        if( $('#user_select').prop('disabled')) {
            $('#user_select').prop('disabled',false);
            $('#user_input').prop('disabled',true);
            $('#send_email').hide();
     } else {
            $('#user_input').prop('disabled',false);
            $('#user_select').prop('disabled',true);
            $('#send_email').show();
        }
    });    

});
</script>

<!-- form start -->
<form id="user_form" method="post" action="<?= func\get_base_url(); ?>admin/user/create/" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-user"></i>
                <h3 class="box-title"><?= func\_t('Create User', 'tritan-cms'); ?></h3>

                <div class="pull-right">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?= func\_t('Save', 'tritan-cms'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= func\get_base_url(); ?>admin/user/'"><i class="fa fa-ban"></i> <?= func\_t('Cancel', 'tritan-cms'); ?></button>
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
                            <h3 class="box-title"><?= func\_t('Name', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= func\_t('Username', 'tritan-cms'); ?></strong></label>
                                <div class="clearfix">
                                    <input type="checkbox" name="check" checked="checked"/> <?=func\_t('New User?', 'tritan-cms');?>
                                </div>
                                
                                <div class="clearfix">&nbsp;</div>
                                
                                <div class="clearfix">
                                    <p style="margin-top:.6em;"><input id="user_input" type="text" class="form-control" name="user_login" placeholder="Username" value="<?= __return_post('user_login'); ?>" /></p>
                                </div>
                                
                                <select id="user_select" class="form-control select2" name="user_id" style="width: 100%;" disabled="disabled">
                                    <option value=""> ----------Existing User?------------ </option>
                                    <?php func\get_users_dropdown(__return_post('user_id')); ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= func\_t('First Name', 'tritan-cms'); ?></strong></label>
                                <input id="fname" type="text" class="form-control" name="user_fname" value="<?= __return_post('user_fname'); ?>" required/>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= func\_t('Last Name', 'tritan-cms'); ?></strong></label>
                                <input id="lname" type="text" class="form-control" name="user_lname" value="<?= __return_post('user_lname'); ?>" required/>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Name' section on the 'Create User' screen.
                             * 
                             * @since 0.9
                             */
                            $this->app->hook->{'do_action'}('create_user_profile_name');
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
                                <input id="email" type="email" class="form-control" name="user_email" value="<?= __return_post('user_email'); ?>" required/>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Contact info' section on the 'Create User' screen.
                             * 
                             * @since 0.9
                             */
                            $this->app->hook->{'do_action'}('create_user_profile_contact');
                            ?>
                            
                        </div>
                    </div>
                    
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= func\_t('User Status', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= func\_t('Role', 'tritan-cms'); ?></strong></label>
                                <select class="form-control select2" name="user_role" style="width: 100%;" required>
                                    <option value=""> ---------------------- </option>
                                    <?php func\get_user_roles(__return_post('user_role')); ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= func\_t('Status', 'tritan-cms'); ?></strong></label>
                                <select class="form-control select2" name="user_status" style="width: 100%;" required>
                                    <option value=""> ---------------------- </option>
                                    <option value="A"<?= selected('A', __return_post('user_status'), false); ?>><?= func\_t('Active', 'tritan-cms'); ?></option>
                                    <option value="I"<?= selected('I', __return_post('user_status'), false); ?>><?= func\_t('Inactive', 'tritan-cms'); ?></option>
                                    <option value="S"<?= selected('S', __return_post('user_status'), false); ?>><?= func\_t('Spammer', 'tritan-cms'); ?></option>
                                    <option value="B"<?= selected('B', __return_post('user_status'), false); ?>><?= func\_t('Blocked', 'tritan-cms'); ?></option>
                                </select>
                                <p class="help-block"><?= func\_t('If the account is Inactive, the user will be incapable of logging in.', 'tritan-cms'); ?></p>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Status' section on the 'Create User' screen.
                             * 
                             * @since 0.9
                             */
                            $this->app->hook->{'do_action'}('create_user_profile_status');
                            ?>
                        </div>
                    </div>
                    
                    <div id="send_email" class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= func\_t('Email', 'tritan-cms'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            
                            <div class="form-group">
                                <label><strong><?= func\_t('Send Email', 'tritan-cms'); ?></strong></label>
                                <div class="ios-switch switch-md">
                                    <input type="checkbox" class="js-switch" name="sendemail" value="1" />
                                <div>
                                <p class="help-block"><?=func\_t( 'Send username & password to the user.', 'tritan-cms');?></p>
                            </div>
                            
                            <?php
                            /**
                             * Fires at the end of the 'Password' section on the 'Create User' screen.
                             * 
                             * @since 0.9
                             */
                            $this->app->hook->{'do_action'}('create_user_profile_password');
                            ?>
                        </div>
                    </div>
                    
                    <?php
                    /**
                     * Fires after the 'About the user' section on the 'Create User' screen.
                     * 
                     * @since 0.9
                     */
                    $this->app->hook->{'do_action'}('create_user_profile');
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

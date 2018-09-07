<?php
use TriTan\Container;
use TriTan\Common\Hooks\ActionFilterHook as hook;

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
Container::getInstance()->{'set'}('screen_parent', 'users');
Container::getInstance()->{'set'}('screen_child', 'create-user');
?>

<script type="text/javascript">
$(document).ready(function(){
    $('#user_select').on('change', function(e) {
        $.ajax({
            type    : 'POST',
            url     : "<?= admin_url('user/lookup/');?>",
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

$(document).ready(function(){
    $("#password").hide();
    $("#hide_password").hide();
    $("#show_password").click(function(){
        $("#password").show(1000);
        $("#hide_password").show(1000);
        $("#show_password").hide(1000);
    });
    $("#hide_password").click(function(){
        $("#password").hide(1000);
        $("#hide_password").hide(1000);
        $("#show_password").show(1000);
    });
});
</script>

<!-- form start -->
<form id="user_form" method="post" action="<?= admin_url('user/create/'); ?>" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-user"></i>
                <h3 class="box-title"><?= esc_html__('Create User'); ?></h3>

                <div class="pull-right">
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> <?= esc_html__('Save'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= admin_url('user/'); ?>'"><i class="fa fa-ban"></i> <?= esc_html__('Cancel'); ?></button>
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
                            <h3 class="box-title"><?= esc_html__('Name'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= esc_html__('Username'); ?></strong></label>
                                <div class="clearfix">
                                    <input type="checkbox" name="check" checked="checked"/> <?=esc_html__('New User?');?>
                                </div>

                                <div class="clearfix">&nbsp;</div>

                                <div class="clearfix">
                                    <p style="margin-top:.6em;"><input id="user_input" type="text" class="form-control" name="user_login" placeholder="Username" value="<?= __return_post('user_login'); ?>" /></p>
                                </div>

                                <select id="user_select" class="form-control select2" name="user_id" style="width: 100%;" disabled="disabled">
                                    <option value=""> ------------Existing User?------------ </option>
                                    <?php get_users_dropdown(__return_post('user_id')); ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= esc_html__('First Name'); ?></strong></label>
                                <input id="fname" type="text" class="form-control" name="user_fname" value="<?= __return_post('user_fname'); ?>" required/>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= esc_html__('Last Name'); ?></strong></label>
                                <input id="lname" type="text" class="form-control" name="user_lname" value="<?= __return_post('user_lname'); ?>" required/>
                            </div>

                            <?php
                            /**
                             * Fires at the end of the 'Name' section on the 'Create User' screen.
                             *
                             * @since 0.9
                             */
                            hook::getInstance()->{'doAction'}('create_user_profile_name');
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
                                <input id="email" type="email" class="form-control" name="user_email" value="<?= __return_post('user_email'); ?>" required/>
                            </div>

                            <?php
                            /**
                             * Fires at the end of the 'Contact info' section on the 'Create User' screen.
                             *
                             * @since 0.9
                             */
                            hook::getInstance()->{'doAction'}('create_user_profile_contact');
                            ?>

                        </div>
                    </div>

                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= esc_html__('User Status'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= esc_html__('Role'); ?></strong></label>
                                <select class="form-control select2" name="user_role" style="width: 100%;" required>
                                    <option value=""> ---------------------- </option>
                                    <?php get_user_roles(__return_post('user_role')); ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= esc_html__('Status'); ?></strong></label>
                                <select class="form-control select2" name="user_status" style="width: 100%;" required>
                                    <option value=""> ---------------------- </option>
                                    <option value="A"<?= selected('A', __return_post('user_status'), false); ?>><?= esc_html__('Active'); ?></option>
                                    <option value="I"<?= selected('I', __return_post('user_status'), false); ?>><?= esc_html__('Inactive'); ?></option>
                                    <option value="S"<?= selected('S', __return_post('user_status'), false); ?>><?= esc_html__('Spammer'); ?></option>
                                    <option value="B"<?= selected('B', __return_post('user_status'), false); ?>><?= esc_html__('Blocked'); ?></option>
                                </select>
                                <p class="help-block"><?= esc_html__('If the account is Inactive, the user will be incapable of logging in.'); ?></p>
                            </div>

                            <?php
                            /**
                             * Fires at the end of the 'Status' section on the 'Create User' screen.
                             *
                             * @since 0.9
                             */
                            hook::getInstance()->{'doAction'}('create_user_profile_status');
                            ?>
                        </div>
                    </div>

                    <div id="send_email" class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= esc_html__('Email / Password'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><?= esc_html__('Send Email'); ?></strong></label>
                                <div class="ios-switch switch-md">
                                    <input type="checkbox" class="js-switch" name="sendemail" value="1" />
                                <div>
                                <p class="help-block"><?=esc_html__('Send username & password to the user.');?></p>
                            </div>
                                    
                            <div class="form-group password">
                                <label><strong><font color="red">*</font> <?= esc_html__('Password'); ?></strong></label>
                                <input id="password" type="text" class="form-control" name="user_pass" value="<?= ttcms_generate_password(); ?>" required/><br />
                                <button type="button" class="btn btn-default" id="show_password"><?= esc_html__('Show Password'); ?></button>
                                <button type="button" class="btn btn-primary" id="hide_password"><?= esc_html__('Hide Password'); ?></button>
                            </div>

                            <?php
                            /**
                             * Fires at the end of the 'Email / Password' section on the 'Create User' screen.
                             *
                             * @since 0.9
                             */
                            hook::getInstance()->{'doAction'}('create_user_profile_credentials');
                            ?>
                        </div>
                    </div>
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

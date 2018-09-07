<?php
use TriTan\Container;
use TriTan\Common\Hooks\ActionFilterHook as hook;

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
Container::getInstance()->{'set'}('screen_parent', 'users');
Container::getInstance()->{'set'}('screen_child', 'all-users');
hook::getInstance()->{'doAction'}('update_user_init');
$username = get_user_option('username', (int) $this->user->getId()) == '' ? get_userdata((int) $this->user->getId())->getLogin() : get_user_option('username', (int) $this->user->getId());

?>

<!-- form start -->
<form method="post" action="<?= admin_url('user/' . (int) $this->user->getId() . '/'); ?>" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-user"></i>
                <h3 class="box-title"><?= esc_html__('Update User'); ?></h3>

                <div class="pull-right">
                    <button type="button"<?=ae('create_users');?> class="btn btn-warning" onclick="window.location='<?= admin_url('user/create/'); ?>'"><i class="fa fa-plus"></i> <?= esc_html__('New User'); ?></button>
                    <button type="submit"<?=ae('update_users');?> class="btn btn-success"><i class="fa fa-save"></i> <?= esc_html__('Update'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= admin_url('user/'); ?>'"><i class="fa fa-ban"></i> <?= esc_html__('Cancel'); ?></button>
                    <?php if ((int) $this->user->getId() === (int) $this->current_user_id) : ?>
                    <input type="hidden" name="user_role" value="<?= get_role_by_id(get_user_option('role', (int) $this->user->getId()))['role']['role_key']; ?>" />
                    <input type="hidden" name="user_status" value="<?= get_user_option('status', (int) $this->user->getId()); ?>" />
                    <?php endif; ?>
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
                                <label><strong><?= esc_html__('Username'); ?></strong></label>
                                <input type="text" class="form-control" id="user_login" name="user_login" value="<?= $username; ?>" readonly="readonly" required/>
                                <?php if (current_user_can('update_users')) : ?>
                                <button type="button" class="btn btn-primary" id="enable_button" style="display:none"><?= esc_html__('Change username'); ?></button>
                                <button type="button" class="btn btn-danger" id="disable_button" style="display:none"><?= esc_html__('Cancel'); ?></button>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= esc_html__('First Name'); ?></strong></label>
                                <input type="text" class="form-control" name="user_fname" value="<?= get_user_option('fname', (int) $this->user->getId()); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= esc_html__('Last Name'); ?></strong></label>
                                <input type="text" class="form-control" name="user_lname" value="<?= get_user_option('lname', (int) $this->user->getId()); ?>" required>
                            </div>

                            <?php
                            /**
                             * Fires at the end of the 'Name' section on the 'Update User' screen.
                             *
                             * @since 0.9
                             * @param array $user User object.
                             */
                            hook::getInstance()->{'doAction'}('update_user_profile_name', $this->user);
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
                                <input type="email" class="form-control" name="user_email" value="<?= get_user_option('email', (int) $this->user->getId()); ?>" required>
                            </div>

                            <div class="form-group">
                                <label><strong><?= esc_html__('URL'); ?></strong></label>
                                <input type="text" class="form-control" name="user_url" value="<?= $this->user->getUrl(); ?>" />
                            </div>

                            <?php
                            /**
                             * Fires at the end of the 'Contact info' section on the 'Update User' screen.
                             *
                             * @since 0.9
                             * @param array $user User object.
                             */
                            hook::getInstance()->{'doAction'}('update_user_profile_contact', $this->user);
                            ?>

                        </div>
                    </div>
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= esc_html__('About the user'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><?= esc_html__('Biography'); ?></strong></label>
                                <textarea class="form-control" name="user_bio" rows="5"><?= get_user_option('bio', (int) $this->user->getId()); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label><strong><?= esc_html__('Profile Picture'); ?></strong></label>
                                <div><?= get_user_avatar(get_user_option('email', (int) $this->user->getId()), 100);?></div>
                            </div>

                            <?php
                            /**
                             * Fires at the end of the 'About the user' section on the 'Update User' screen.
                             *
                             * @since 0.9
                             * @param array $user User object.
                             */
                            hook::getInstance()->{'doAction'}('update_user_profile_about', $this->user);
                            ?>

                        </div>
                    </div>
                    <?php if ((int) $this->user->getId() !== (int) $this->current_user_id) : ?>
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= esc_html__('User Status'); ?></h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">

                            <div class="form-group">
                                <label><strong><?= esc_html__('Role'); ?></strong></label>
                                <select class="form-control select2" name="user_role" style="width: 100%;">
                                    <option value=""> ---------------------- </option>
                                    <?php get_user_roles(get_user_option('role', (int) $this->user->getId())); ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label><strong><font color="red">*</font> <?= esc_html__('Status'); ?></strong></label>
                                <select class="form-control select2" name="user_status" style="width: 100%;" required>
                                    <option value=""> ---------------------- </option>
                                    <option value="A"<?= selected('A', get_user_option('status', (int) $this->user->getId()), false); ?>><?= esc_html__('Active'); ?></option>
                                    <option value="I"<?= selected('I', get_user_option('status', (int) $this->user->getId()), false); ?>><?= esc_html__('Inactive'); ?></option>
                                    <option value="S"<?= selected('S', get_user_option('status', (int) $this->user->getId()), false); ?>><?= esc_html__('Spammer'); ?></option>
                                    <option value="B"<?= selected('B', get_user_option('status', (int) $this->user->getId()), false); ?>><?= esc_html__('Blocked'); ?></option>
                                </select>
                                <p class="help-block"><?= esc_html__('If the account is Inactive, the user will be incapable of logging into the system.'); ?></p>
                            </div>

                            <?php
                            /**
                             * Fires at the end of the 'Status' section on the 'Update User' screen.
                             *
                             * @since 0.9
                             * @param array $user User object.
                             */
                            hook::getInstance()->{'doAction'}('update_user_profile_status', $this->user);
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php
                    /**
                     * Fires after the 'About the User' section on the 'Update User' screen.
                     *
                     * @since 0.9
                     * @param array $user User object.
                     */
                    hook::getInstance()->{'doAction'}('update_user_profile', $this->user);
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

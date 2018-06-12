<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Functions as func;
/**
 * Update Role View
 *  
 * @license GPLv3
 * 
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
$eRole = new \TriTan\ACL();
Config::set('screen_parent', 'roles');
Config::set('screen_child', 'role');

?>

<!-- form start -->
<form method="post" action="<?= func\get_base_url(); ?>admin/role/edit-role/" data-toggle="validator" autocomplete="off">
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <i class="fa fa-user"></i>
                <h3 class="box-title"><?= func\_t('Update Role', 'tritan-cms'); ?></h3>

                <div class="pull-right">
                    <input type="hidden" name="role_id" value="<?= (int) $this->role['role_id']; ?>" />
                    <button type="submit" class="btn btn-success"><i class="fa fa-pencil"></i> <?= func\_t('Update', 'tritan-cms'); ?></button>
                    <button type="button" class="btn btn-primary" onclick="window.location = '<?= func\get_base_url(); ?>admin/role/'"><i class="fa fa-ban"></i> <?= func\_t('Cancel', 'tritan-cms'); ?></button>
                </div>
            </div>
        </div> 

        <!-- Main content -->
        <section class="content">

            <?= func\_ttcms_flash()->showMessage(); ?> 

            <!-- SELECT2 EXAMPLE -->
            <div class="box box-default">
                <div class="box-body">

                    <!-- Group -->
                    <div class="form-group">
                        <label class="col-md-3 control-label"><font color="red">*</font> <?= func\_t('Role Name'); ?></label>
                        <div class="col-md-12"><input class="form-control" name="role_name" type="text" value="<?= $this->role['role_name']; ?>" required/></div>
                    </div>
                    <!-- // Group END -->

                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th><?= func\_t('Permission'); ?></th>
                                <th class="text-center"><?= func\_t('Allow'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php func\role_perm((int) $this->role['role_id']); ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th><?= func\_t('Permission'); ?></th>
                                <th class="text-center"><?= func\_t('Allow'); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->

        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
</form>
<?php $this->stop(); ?>
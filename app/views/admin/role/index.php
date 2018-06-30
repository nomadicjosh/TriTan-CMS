<?php
if (!defined('BASE_PATH'))
    exit('No direct script access allowed');
use TriTan\Config;
use TriTan\Functions\Dependency;
use TriTan\Functions\Core;
/**
 * Manage Roles View
 *  
 * @license GPLv3
 * 
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
$this->layout('main::_layouts/admin-layout');
$this->section('backend');
$roles = new \TriTan\ACL();
Config::set('screen_parent', 'roles');
Config::set('screen_child', 'role');

?>            

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="box box-solid">
        <div class="box-header with-border">
            <i class="fa fa-text-width"></i>
            <h3 class="box-title"><?= Core\_t('Roles', 'tritan-cms'); ?></h3>
            
            <div class="pull-right">
                <button type="button" class="btn btn-success" onclick="window.location = '<?= Core\get_base_url(); ?>admin/role/create/'"><i class="fa fa-plus-circle"></i> <?= Core\_t('Create a Role', 'tritan-cms'); ?></button>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

        <?= Dependency\_ttcms_flash()->showMessage(); ?>

        <!-- SELECT2 EXAMPLE -->
        <div class="box box-default">
            <div class="box-body">
                <table id="example1" class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th class="text-center"><?= Core\_t('Name', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Key', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Edit', 'tritan-cms'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $listRoles = $roles->getAllRoles('full');
                        if ($listRoles != '') {
                            foreach ($listRoles as $k => $v) {
                                echo '<tr class="gradeX">' . "\n";
                                echo '<td class="text-center">' . Core\_escape($v['Name']) . '</td>' . "\n";
                                echo '<td class="text-center">' . Core\_escape($v['Key']) . '</td>' . "\n";
                                echo '<td class="text-center"><a href="' . Core\get_base_url() . 'admin/role/' . Core\_escape((int) $v['ID']) . '/" data-toggle="tooltip" data-placement="top" title="View/Edit" class="btn bg-yellow"><i class="fa fa-edit"></i></a></td>';
                                echo '</tr>';
                            }
                        }

                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-center"><?= Core\_t('Name', 'tritan-cms'); ?></th>
                            <th class="text-center"><?= Core\_t('Edit', 'tritan-cms'); ?></th>
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
<?php $this->stop(); ?>
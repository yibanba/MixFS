<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


mixfs_top('财务软件入口');

unset($_SESSION['acc_tbl']);
unset($_SESSION['acc_name']);
unset($_SESSION['login_log']);


/**
 * 1. update: md5()
 * 2. create: $_SESSION['mixfs_login_id'] + $_SESSION['mixfs_access list'];
 */
global $wpdb, $current_user;        // $current_user->ID;

$current_week = date('W', time());
$old_week = get_option('mixfs_week_number');

if ($current_week != $old_week) {
    update_option('mixfs_week_number', $current_week);

    $tables_prefix = $wpdb->get_results("SELECT ma_tbl_prefix FROM {$wpdb->prefix}mixfs_accounts", ARRAY_A);

    foreach ($tables_prefix as $prefix) {
        $new_md5 = md5($prefix['ma_tbl_prefix'] . $current_week);
        $wpdb->update(
                "{$wpdb->prefix}mixfs_accounts", array('ma_create_md5' => $new_md5), array('ma_tbl_prefix' => $prefix['ma_tbl_prefix'])
        );
    }
}
?>

<div class="manage-menus"> 请点击下方的【账套名称】后再进行操作 ... </div>

<br class="clear" />

<table class="wp-list-table widefat fixed users" id="entrance" cellspacing="1">
    <thead>
        <tr>
            <th class='manage-column' style="width: 80px;">序号</th>
            <th class='manage-column'  style="">账套名称</th>
            <th class='manage-column' style="">账套缩写</th>
            <th class='manage-column'  style="">账套说明</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th class='manage-column' style="width: 80px;">序号</th>
            <th class='manage-column'  style="">账套名称</th>
            <th class='manage-column' style="">账套缩写</th>
            <th class='manage-column'  style="">账套说明</th>
        </tr>
    </tfoot>
    <tbody>
<?php
$_SESSION["mas"] = array(); // http://url/?mas=xxx

$mixfs_acc = $wpdb->get_results("SELECT ma_id, ma_tbl_prefix, ma_tbl_name, ma_create_md5, ma_ID_permission, ma_tbl_detail "
        . " FROM {$wpdb->prefix}mixfs_accounts", ARRAY_A);

if (empty($mixfs_acc)) {
    echo "<tr class='alternate'><td class='name' colspan='4' style='text-align: center; padding: 50px;'> 没有数据 </td></tr>";
} else {
    $counter = 0; // 账套序号

    foreach ($mixfs_acc as $acc_name) {
        //判断用户是否拥有访问某账套权限
        $permission = in_array($current_user->ID, explode(",", $acc_name['ma_ID_permission']));
        ++$counter;
        if ($permission) {
            ?>
        <tr class='alternate'>
            <td class='name'><?php echo $counter; ?></td>
            <td class='name'>
                <input type='button' class='button' value='<?php echo $acc_name['ma_tbl_name']; ?>' style="width: 200px;" 
                       onclick="location.href=location.href.substring(0, location.href.indexOf('?page')) + '?page=mixfs-instructions&mas=<?php echo $acc_name['ma_create_md5']; ?>'" />
            </td>
            <td class='name'><?php echo $acc_name['ma_tbl_prefix']; ?></td>
            <td class='name'><?php echo $acc_name['ma_tbl_detail']; ?></td>
        </tr>
<?php
            $_SESSION["mas"][$acc_name['ma_create_md5']] = $acc_name['ma_tbl_prefix'] . '|' . $acc_name['ma_tbl_name'];
        }
    }
    if ($counter == 0) {
        echo "<tr class='alternate'><td class='name' colspan='4' style='text-align: center; padding: 50px;'> 没有访问账目的权限 </td></tr>";
    }
}
?>
    </tbody>
</table>


<?php mixfs_bottom(); ?>
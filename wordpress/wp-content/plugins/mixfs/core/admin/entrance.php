<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


mixfs_top('财务软件入口');


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

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $(function () {
            Hover("entrance")
        })
        var Hover = function (id) {
            $("#" + id).find("tr").hover(function () {
                $(this).css({"background-color": "#0CF"}),
                        $(this).attr({'title': "点击指定行进入..."});
            }, function () {
                $(this).css({"background-color": ""});
            })
        }
        $("tr").click(function () {
            $(this).find("input[type='radio']").attr("checked", "checked");
            location.href = "http://mixfs/wordpress/wp-admin/admin.php?page=mixfs-instructions&mas=" + $('input:radio:checked').val();
        })
    });
</script>
<div class="manage-menus"> 请点击下方的账套名称登录 ... </div>
<br class="clear" />

<table class="wp-list-table widefat fixed users" id="entrance" cellspacing="1">
    <thead>
        <tr>
            <th class='manage-column column-cb check-column'  style="width: 50px;"></th>
            <th class='manage-column' style="width: 50px;">序号</th>
            <th class='manage-column' style="">账套缩写</th>
            <th class='manage-column'  style="">账套名称</th>
            <th class='manage-column'  style="">账套说明</th>
        </tr>
    </thead>

    <tfoot>
        <tr>
            <th class='manage-column column-cb check-column'  style="width: 50px;"></th>
            <th class='manage-column' style="width: 50px;">序号</th>
            <th class='manage-column' style="">账套缩写</th>
            <th class='manage-column'  style="">账套名称</th>
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
            echo "<tr class='alternate'>
                <th scope='row' class='check-column'>
                            <input type='radio' name='ma_id[]' class='administrator' value='{$acc_name['ma_create_md5']}' />
                        </th>
                        <td class='name'>{$counter}</td>"
            . "<td class='name'>{$acc_name['ma_tbl_prefix']}</td>"
            . "<td class='name'>{$acc_name['ma_tbl_name']}</td>"
            . "<td class='name'>{$acc_name['ma_tbl_detail']}</td>"
            . "</tr>";

            $_SESSION["mas"] = array_merge(array($acc_name['ma_create_md5'] => $acc_name['ma_tbl_prefix']), $_SESSION["mas"]);
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
<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$url_entrance = admin_url('admin.php?page=mixfs-entrance'); // 所有页面共用的返回入口链接URL == mixfs-entrance

$html = '<div class="wrap">'
        . '<div id="icon-themes" class="icon32"><br></div>'
        . '<h2 class="nav-tab-wrapper">';
$html .= '<a href="' . $url_entrance . '" class="nav-tab">财务软件入口</a>';
$html .= '<a href="' . $url_entrance . '" class="nav-tab nav-tab-active">权限分配</a>';

echo $html . '<a href="' . wp_logout_url() . '" class="nav-tab">退出软件</a></h2><br />';



global $wpdb;
if (isset($_POST['btn_add_op'])) {
    if ($_POST['op_id_list'] == '' || count($_POST['ma_id']) == 0) {
        echo '<div id="message" class="updated"><p>请选择用户名称和账目</p></div>';
    } else {
        $new_user_login = $_POST['op_id_list'];
        foreach ($_POST['ma_id'] as $acc) {
            $old_user = $wpdb->get_var("SELECT ma_ID_permission FROM {$wpdb->prefix}mixfs_accounts WHERE ma_id = '{$acc}'");
            $arr_old_user = explode(',', $old_user);
            if (in_array($new_user_login, $arr_old_user)) {
                echo '<div id="message" class="updated"><p>' . $_POST["ma_tbl_prefix{$acc}"] . ' 该用户已经存在</p></div>';
            } else {
                $old_user = empty($old_user) ? '' : ($old_user . ',');
                $wpdb->update(
                        $wpdb->prefix . 'mixfs_accounts', array('ma_ID_permission' => $old_user . $new_user_login), array('ma_id' => $acc)
                );
                echo '<div id="message" class="updated"><p>' . $_POST["ma_tbl_prefix{$acc}"] . ' 账套添加用户成功</p></div>';
            }
        }
    }
} elseif (isset($_POST['btn_del_op'])) {
    if ($_POST['op_id_list'] == '' || count($_POST['ma_id']) == 0) {
        echo '<div id="message" class="updated"><p>请选择操作员和账目名称</p></div>';
    } else {
        $del_user_login = $_POST['op_id_list'];
        foreach ($_POST['ma_id'] as $acc) {
            $old_user = $wpdb->get_var("SELECT ma_ID_permission FROM {$wpdb->prefix}mixfs_accounts WHERE ma_id = '{$acc}'");
            $arr_old_user = explode(',', $old_user);
            if (in_array($del_user_login, $arr_old_user)) {
                unset($arr_old_user[array_search($del_user_login, $arr_old_user)]);
                $str_new_user = implode(',', $arr_old_user);
                $wpdb->update(
                        $wpdb->prefix . 'mixfs_accounts', array('ma_ID_permission' => $str_new_user), array('ma_id' => $acc)
                );
                echo '<div id="message" class="updated"><p>' . $_POST["ma_tbl_prefix{$acc}"] . ' 指定用户已删除</p></div>';
            } else {
                echo '<div id="message" class="updated"><p>' . $_POST["ma_tbl_prefix{$acc}"] . ' 账套指定用户不存在</p></div>';
            }
        }
    }
}
?>

<form action="" method="post">

    <div class="tablenav top">
        <div class="alignleft actions">
            <select name="op_id_list" id="op_id_list">
                <option value="">请选择操作人员并指定账套...</option>
                <?php
                $op_sql = "SELECT u.ID, u.user_login "
                        . "FROM {$wpdb->prefix}users as u LEFT JOIN {$wpdb->prefix}usermeta as um ON (u.ID = um.user_id) "
                        . "WHERE um.meta_key = '{$wpdb->prefix}capabilities' "
                        . "AND ( um.meta_value LIKE '%mixfs_%' )";
                $op_results = $wpdb->get_results($op_sql, ARRAY_A);
                $i = 1;
                foreach ($op_results as $id_name) {
                    echo sprintf("<option value='%d'>%02d : &nbsp; %s</option>", $id_name['ID'], $i++, $id_name['user_login']);
                }
                ?>
            </select>
            <input type="submit" name="btn_add_op" id="btn_add_op" class="button button-primary" value=" 添加用户 "  />
            <input type="submit" name="btn_del_op" id="btn_del_op" class="button button-primary" value=" 删除用户 "  />
        </div>
        <br class="clear" />
    </div>

    <table class="wp-list-table widefat fixed users" cellspacing="1">
        <thead>
            <tr>
                <th class='manage-column column-cb check-column'  style="width: 50px;">
                    <input id="cb-select-all-1" type="checkbox" />
                </th>
                <th class='manage-column' style="width: 300px;">账套数据库表名</th>
                <th class='manage-column' style="width: 200px;">账套名称</th>
                <th class='manage-column'  style="">允许使用账套用户列表</th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <th class='manage-column column-cb check-column'  style="width: 50px;">
                    <input id="cb-select-all-1" type="checkbox" />
                </th>
                <th class='manage-column' style="width: 300px;">账套数据库表名</th>
                <th class='manage-column' style="width: 200px;">账套名称</th>
                <th class='manage-column'  style="">允许使用账套用户列表</th>
            </tr>
        </tfoot>

        <tbody>
            <?php
            $results_accounts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mixfs_accounts", ARRAY_A);
            foreach ($results_accounts as $tbl) :
                $user_str = ID2user_login($tbl['ma_ID_permission']);

                echo "<tr class='alternate'>
                        <th scope='row' class='check-column'>
                            <input type='checkbox' name='ma_id[]' class='administrator' value='{$tbl['ma_id']}' />
                        </th>
                        <td class='name'>
                            <code>mixfs_</code>
                            <input name='ma_tbl_prefix{$tbl['ma_id']}' type='text' value='{$tbl['ma_tbl_prefix']}' size='10' readonly='readonly' />
                            <code>_xxx</code>
                        </td>
                        <td class='name'>
                            <input name='ma_tbl_name{$tbl['ma_id']}' type='text' value='{$tbl['ma_tbl_name']}' size='20' readonly='readonly' />
                        </td>
                        <td class='name'>
                            <input name='ma_tbl_user' type='text' value='{$user_str}' size='60' readonly='readonly' />
                        </td>
                    </tr>";
            endforeach;
            ?>
        </tbody>
    </table>
</form>


<?php mixfs_bottom(); ?>
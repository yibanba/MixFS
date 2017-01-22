<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

include_once ( MixFS_PATH . 'core/accounting_schema/factory_schema.php');


$url_entrance = admin_url('admin.php?page=mixfs-entrance'); // 所有页面共用的返回入口链接URL == mixfs-entrance
$html = '<div class="wrap">'
        . '<div id="icon-themes" class="icon32"><br></div>'
        . '<h2 class="nav-tab-wrapper">'
        . '<a href="' . $url_entrance . '" class="nav-tab">财务软件入口</a>'
        . '<a href="' . $url_entrance . '" class="nav-tab nav-tab-active">账套列表</a>';
echo $html . '<a href="' . wp_logout_url() . '" class="nav-tab">退出软件</a></h2><br />';


global $wpdb;
if (isset($_POST['btn_tbl_add'])) {
    $tbl_prefix = preg_replace("/\s|　/", "", $_POST['tbl_prefix']); //删除所有空格和全角空格
    $tbl_prefix = ctype_alnum($tbl_prefix) ? $tbl_prefix : 0;
    $tbl_name = trim($_POST['tbl_name']);
    $tbl_detail = trim($_POST['tbl_detail']);
    if (empty($tbl_prefix) || empty($tbl_name)) {
        echo '<div id="message" class="updated"><p>表名不能为空，且必须是字母或数字</p></div>';
    } else {
        if (table_name_exists($tbl_prefix)) {
            echo '<div id="message" class="updated"><p>账套已存在，请重新命名后再次提交</p></div>';
        } else {
            $wpdb->insert(
                    $wpdb->prefix . 'mixfs_accounts', array(
                'ma_tbl_prefix' => $tbl_prefix,
                'ma_tbl_name' => $tbl_name,
                'ma_tbl_detail' => $tbl_detail,
                'ma_create_md5' => md5($tbl_prefix . get_option('mixfs_md5_week'))
                    )
            ); // md5(lc + 33)
            create_factory_tables($wpdb->prefix . 'mixfs_' . $tbl_prefix);
            echo '<div id="message" class="updated"><p>添加账套成功</p></div>';
        }
    }
} elseif (isset($_POST['btn_tbl_update']) && count($_POST['ma_id'])) {
    $counter = 0;
    foreach ($_POST['ma_id'] as $id) {
        $tbl_prefix = preg_replace("/\s|　/", "", $_POST['ma_tbl_prefix' . $id]); //删除所有空格和全角空格
        $tbl_prefix = ctype_alnum($tbl_prefix) ? $tbl_prefix : 0;
        $tbl_name = trim($_POST['ma_tbl_name' . $id]);
        $tbl_detail = trim($_POST['ma_tbl_detail' . $id]);
        if (empty($tbl_prefix) || empty($tbl_name)) {
            echo '<div id="message" class="updated"><p>表名不能为空，且必须是字母或数字</p></div>';
        } else {
            if (table_name_exists($tbl_prefix)) {
                echo '<div id="message" class="updated"><p>账套已存在，请重新命名后再次提交</p></div>';
            } else {
                $old = $wpdb->get_row("SELECT ma_tbl_prefix FROM {$wpdb->prefix}mixfs_accounts WHERE ma_id={$id}", ARRAY_A);
                $old = $wpdb->prefix . 'mixfs_' . $old['ma_tbl_prefix'] . '_';
                $new = $wpdb->prefix . 'mixfs_' . $tbl_prefix . '_';
                $counter += update_factory_tables($old, $new);

                $wpdb->update($wpdb->prefix . 'mixfs_accounts', array(
                    'ma_tbl_prefix' => $tbl_prefix,
                    'ma_tbl_name' => $tbl_name,
                    'ma_tbl_detail' => $tbl_detail,
                    'ma_create_md5' => md5($tbl_prefix . get_option('mixfs_md5_week')),
                    'ma_update_date' => current_time('mysql')
                        ), array('ma_id' => $id)
                );
            }
        }
    }
    if ($counter) {
        echo '<div id="message" class="updated"><p>信息更新成功，共 ' . $counter . ' 个表被更新！</p></div>';
    }
} elseif (isset($_POST['btn_tbl_update']) && empty($_POST['ma_id'])) {
    echo '<div id="message" class="updated"><p>必须选择一项才能更新，内容必须填充完整，表名必须是字母或数字</p></div>';
}
?>


<form action="" method="post">
    <div class="manage-menus">

        <div class="alignleft actions">
            <input type="text" id="tbl_prefix" name="tbl_prefix" value="输入指定账套字母表名..." maxlength="20" size="25" style="color: #ccc;" 
                   onblur="if (this.value == '') {
                               this.value = '输入指定账套字母表名...';
                               this.style.color = '#ccc';
                           }" 
                   onfocus="if (this.value == '输入指定账套字母表名...') {
                               this.value = '';
                               this.style.color = '#333';
                           }" />
            <input type="text" id="tbl_name" name="tbl_name" value="输入账套中文名称..." maxlength="20" size="25" style="color: #ccc;" 
                   onblur="if (this.value == '') {
                               this.value = '输入账套中文名称...';
                               this.style.color = '#ccc';
                           }" 
                   onfocus="if (this.value == '输入账套中文名称...') {
                               this.value = '';
                               this.style.color = '#333';
                           }" />
            <input type="text" id="tbl_detail" name="tbl_detail" value="输入备注..." maxlength="20" size="25" style="color: #ccc;" 
                   onblur="if (this.value == '') {
                               this.value = '输入备注...';
                               this.style.color = '#ccc';
                           }" 
                   onfocus="if (this.value == '输入备注...') {
                               this.value = '';
                               this.style.color = '#333';
                           }" />
            <input type="submit" name="btn_tbl_add" id="btn_tbl_add" class="button" value="添加新账套"  />
        </div>
        <br class="clear" />
    </div>
    <br />
    <table class="wp-list-table widefat fixed users" cellspacing="1">
        <thead>
            <tr>
                <th class='manage-column column-cb check-column'  style="width: 50px;">
                    <input id="cb-select-all-1" type="checkbox" />
                </th>
                <th class='manage-column' style="width: 300px;">账目数据库表名</th>
                <th class='manage-column' style="">账目名称</th>
                <th class='manage-column'  style="">信息摘要</th>
                <th class='manage-column'  style="">创建账套时间</th>
                <th class='manage-column'  style="">账套更新时间</th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <th class='manage-column column-cb check-column'  style="width: 50px;">
                    <input id="cb-select-all-1" type="checkbox" />
                </th>
                <th class='manage-column' style="width: 300px;">账目数据库表名</th>
                <th class='manage-column' style="">账目名称</th>
                <th class='manage-column'  style="">信息摘要</th>
                <th class='manage-column'  style="">创建账套时间</th>
                <th class='manage-column'  style="">账套更新时间</th>
            </tr>
        </tfoot>

        <tbody>
            <?php
            $results_accounts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mixfs_accounts", ARRAY_A);
            foreach ($results_accounts as $tbl) :
                echo "<tr class='alternate'>
                        <th scope='row' class='check-column'>
                            <input type='checkbox' name='ma_id[]' class='administrator' value='{$tbl['ma_id']}' />
                        </th>
                        <td class='name'>
                            <code>{$wpdb->prefix}mixfs_</code>
                            <input name='ma_tbl_prefix{$tbl['ma_id']}' type='text' value='{$tbl['ma_tbl_prefix']}' size='10' />
                            <code>_xxx</code>
                        </td>
                        <td class='name'>
                            <input name='ma_tbl_name{$tbl['ma_id']}' type='text' value='{$tbl['ma_tbl_name']}' size='20' />
                        </td>
                        <td class='name'>
                            <input name='ma_tbl_detail{$tbl['ma_id']}' type='text' value='{$tbl['ma_tbl_detail']}' size='20' />
                        </td>
                        <td class='name'>
                            {$tbl['ma_create_date']}
                        </td>
                        <td class='name'>
                            {$tbl['ma_update_date']}
                        </td>
                    </tr>";
            endforeach;
            ?>
        </tbody>
    </table>

    <div class="tablenav bottom">

        <div class="alignleft actions">
            <input type="submit" name="btn_tbl_update" id="btn_tbl_update" class="button button-primary" value="提交更新"  />
        </div>
        <br class="clear" />
    </div>
</form>


<?php mixfs_bottom(); ?>
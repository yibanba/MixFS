<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

mixfs_top('添加仓库店面', $_SESSION['acc_name']);

global $wpdb;

// 账套表名完整前缀 = fs_mixfs_xxx_ + goods_series
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

if (isset($_POST['btn_place_add'])) {              // 添加系列 
    $place_name = preg_replace("/\s|　/", "", $_POST['place_name']); //删除所有空格和全角空格
    $place_name = wp_strip_all_tags($place_name);
    $place_detail = wp_strip_all_tags($_POST['place_detail']);
    if (empty($place_name)) {
        echo '<div id="message" class="updated"><p>仓库店铺名称不能为空</p></div>';
    } else {
        $place_exists = $wpdb->get_row("SELECT gp_name FROM {$acc_prefix}goods_place WHERE gp_name='{$place_name}'");
        if ($place_exists) {
            echo '<div id="message" class="updated"><p>仓库店铺已存在，请重新命名后再次提交</p></div>';
        } else {
            $wpdb->insert(
                    $acc_prefix . 'goods_place', array('gp_name' => $place_name, 'gp_summary' => $place_detail)
            );
            echo "<div id='message' class='updated'><p>添加【{$place_name}】仓库店铺成功</p></div>";
        }
    }
}

    echo <<<Form_HTML
    <form action="" method="post">
        <div class="manage-menus">

            <div class="alignleft actions">
                <input type="text" id="place_name" name="place_name" value="输入仓库店铺名称..." maxlength="20" size="25" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入仓库店铺名称...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入仓库店铺名称...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="text" id="place_detail" name="place_detail" value="输入备注..." maxlength="20" size="25" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入备注...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入备注...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="submit" name="btn_place_add" id="btn_place_add" class="button" value="添加仓库店铺名称"  />
                <input type="reset" name="btn_place_reset" id="btn_place_reset" class="button" value="重新填写仓库店铺"  />
            </div>
            <br class="clear" />
        </div>
        <br />
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="width: 100px;">仓库店铺代码</th>
                    <th class='manage-column' style="">仓库店铺名称</th>
                    <th class='manage-column'  style="">仓库店铺说明</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th class='manage-column' style="width: 100px;">仓库店铺代码</th>
                    <th class='manage-column' style="">仓库店铺名称</th>
                    <th class='manage-column'  style="">仓库店铺说明</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

    $results_place = $wpdb->get_results("SELECT * FROM {$acc_prefix}goods_place", ARRAY_A);

    foreach ($results_place as $p_name) {
        echo "<tr class='alternate'>
                <td class='name'>{$p_name['gp_id']}</td>
                <td class='name'>{$p_name['gp_name']}</td>
                <td class='name'>{$p_name['gp_summary']}</td>
            </tr>";
    }
    echo '</tbody></table></form>';


mixfs_bottom(); // 框架页面底部

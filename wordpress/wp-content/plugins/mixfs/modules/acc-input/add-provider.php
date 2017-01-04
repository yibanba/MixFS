<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

mixfs_top('添加供应商', $_SESSION['acc_name']);

global $wpdb;

// 账套表名完整前缀 = fs_mixfs_xxx_ + goods_series
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

if (isset($_POST['btn_provider_add'])) {              // 添加系列 
    $provider_name = preg_replace("/\s|　/", "", $_POST['provider_name']); //删除所有空格和全角空格
    $provider_name = wp_strip_all_tags($provider_name);
    $provider_detail = wp_strip_all_tags($_POST['provider_detail']);
    if (empty($provider_name)) {
        echo '<div id="message" class="updated"><p>供应商名称不能为空</p></div>';
    } else {
        $provider_exists = $wpdb->get_row("SELECT p_name FROM {$acc_prefix}provider WHERE p_name='{$provider_name}'");
        if ($provider_exists) {
            echo '<div id="message" class="updated"><p>供应商已存在，请重新命名后再次提交</p></div>';
        } else {
            $wpdb->insert(
                    $acc_prefix . 'provider', array('p_name' => $provider_name, 'p_summary' => $provider_detail)
            );
            echo "<div id='message' class='updated'><p>添加【{$provider_name}】供应商成功</p></div>";
        }
    }
}

    echo <<<Form_HTML
    <form action="" method="post">
        <div class="manage-menus">

            <div class="alignleft actions">
                <input type="text" id="provider_name" name="provider_name" value="输入供应商名称..." maxlength="20" size="25" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入供应商名称...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入供应商名称...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="text" id="provider_detail" name="provider_detail" value="输入备注..." maxlength="20" size="25" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入备注...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入备注...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="submit" name="btn_provider_add" id="btn_provider_add" class="button" value="添加供应商名称"  />
                <input type="reset" name="btn_reset" id="btn_reset" class="button" value="重新填写供应商"  />
            </div>
            <br class="clear" />
        </div>
        <br />
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="width: 100px;">供应商代码</th>
                    <th class='manage-column' style="">供应商名称</th>
                    <th class='manage-column'  style="">供应商说明</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th class='manage-column' style="width: 100px;">供应商代码</th>
                    <th class='manage-column' style="">供应商名称</th>
                    <th class='manage-column'  style="">供应商说明</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

    $results_provider = $wpdb->get_results("SELECT * FROM {$acc_prefix}provider", ARRAY_A);

    foreach ($results_provider as $p_name) {
        echo "<tr class='alternate'>
                <td class='name'>{$p_name['p_id']}</td>
                <td class='name'>{$p_name['p_name']}</td>
                <td class='name'>{$p_name['p_summary']}</td>
            </tr>";
    }
    echo '</tbody></table></form>';


mixfs_bottom(); // 框架页面底部

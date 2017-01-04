<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

mixfs_top('添加货柜号', $_SESSION['acc_name']);

global $wpdb;

// 账套表名完整前缀 = fs_mixfs_xxx_ + goods_series
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

if (isset($_POST['btn_container_add'])) {              // 添加系列 
    $container_no = preg_replace("/\s|　/", "", $_POST['container_no']); //删除所有空格和全角空格
    $container_no = wp_strip_all_tags($container_no);
    $container_detail = wp_strip_all_tags($_POST['container_detail']);
    if (empty($container_no)) {
        echo '<div id="message" class="updated"><p>货柜号码不能为空</p></div>';
    } else {
        $container_exists = $wpdb->get_row("SELECT c_no FROM {$acc_prefix}container WHERE c_no='{$container_no}'");
        if ($container_exists) {
            echo '<div id="message" class="updated"><p>货柜号已存在，请重新命名后再次提交</p></div>';
        } else {
            $wpdb->insert(
                    $acc_prefix . 'container', array('c_no' => $container_no, 'c_summary' => $container_detail)
            );
            echo "<div id='message' class='updated'><p>添加【{$container_no}】仓库店铺成功</p></div>";
        }
    }
}

    echo <<<Form_HTML
    <form action="" method="post">
        <div class="manage-menus">

            <div class="alignleft actions">
                <input type="text" id="container_no" name="container_no" value="输入货柜号..." maxlength="20" size="25" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入货柜号...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入货柜号...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="text" id="container_detail" name="container_detail" value="输入备注..." maxlength="20" size="25" style="color: #ccc;" 
                       onblur="if (this.value == '') {
                                   this.value = '输入备注...';
                                   this.style.color = '#ccc';
                               }" 
                       onfocus="if (this.value == '输入备注...') {
                                   this.value = '';
                                   this.style.color = '#333';
                               }" />
                <input type="submit" name="btn_container_add" id="btn_container_add" class="button" value="添加货柜号"  />
                <input type="reset" name="btn_container_reset" id="btn_container_reset" class="button" value="重填货柜号"  />
            </div>
            <br class="clear" />
        </div>
        <br />
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="width: 100px;">货柜代码</th>
                    <th class='manage-column' style="">货柜号码</th>
                    <th class='manage-column'  style="">货柜说明</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th class='manage-column' style="width: 100px;">货柜代码</th>
                    <th class='manage-column' style="">货柜号码</th>
                    <th class='manage-column'  style="">货柜说明</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

    $results_container = $wpdb->get_results("SELECT * FROM {$acc_prefix}container", ARRAY_A);

    foreach ($results_container as $c_name) {
        echo "<tr class='alternate'>
                <td class='name'>{$c_name['c_id']}</td>
                <td class='name'>{$c_name['c_no']}</td>
                <td class='name'>{$c_name['c_summary']}</td>
            </tr>";
    }
    echo '</tbody></table></form>';


mixfs_bottom(); // 框架页面底部

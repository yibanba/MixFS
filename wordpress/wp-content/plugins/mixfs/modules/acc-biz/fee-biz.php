<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

mixfs_top('资金往来业务', $_SESSION['acc_name']);

global $wpdb;
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

$list_total = 15;


if( ! isset($_SESSION['rate'])) { // 设置当地货币转美元的汇率
    $_SESSION['rate'] = 1.000;
}

if (isset($_POST['feebiz_submit'])) {

    $_SESSION['feebiz']['date'] = '';

    $date_arr = explode('-', $_POST['feebiz_date']);
    if (count($date_arr) == 3 && checkdate($date_arr[1], $date_arr[2], $date_arr[0])) {
        $_SESSION['feebiz']['date'] = $_POST['feebiz_date'];
    }

    $fi_fields = $wpdb->get_row("SELECT fi_id, fi_in_out FROM {$acc_prefix}fee_item WHERE fi_name = '{$_POST['feebiz_item']}'", ARRAY_A);
    $fee_item_id = $fi_fields['fi_id'];    
    $in_out = ($fi_fields['fi_in_out'] == '1') ? 'fb_in' : 'fb_out';
    
    $_SESSION['rate'] = trim($_POST['feebiz_rate']);
    $money = trim($_POST['feebiz_money']);
    if ($fee_item_id && is_numeric(trim($_POST['feebiz_money'])) && $_SESSION['feebiz']['date']) {
        $wpdb->insert($acc_prefix . 'fee_biz', array('fb_date' => $_SESSION['feebiz']['date'],
            $in_out => ($money / $_SESSION['rate']),
            'fb_c_id' => $_POST['feebiz_container'],
            'fb_summary' => trim($_POST['feebiz_sum']),
            'fb_fi_id' => $fee_item_id
                )
        );
        echo "<div id='message' class='updated'><p>提交【{$_POST['feebiz_item']}】资金往来项目成功</p></div>";
    } else {
        echo "<div id='message' class='updated'><p>请正确完成(必填)选项后再提交</p></div>";
    }
} // if (isset($_POST['feebiz_submit']))


date_from_to("feebiz_date");
?>

<form action="" method="post" name="createuser" id="createuser" class="validate">

    <table class="form-table">
        <tbody>
            <tr class="form-field form-required">
                <th scope="row"><label for="feebiz_date">选择业务日期 <span class="description">(必填)</span></label></th>
                <td><input name="feebiz_date" type="text" id="feebiz_date" value="<?php echo $_SESSION['feebiz']['date']; ?>" aria-required="true"></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="feebiz_item">费用项目名称 (必填)</label></th>
                <td><input type="text" name="feebiz_item" id="feebiz_item" value="双击选择或输入关键字"></td>
            </tr>
            <?php
            // 自动完成文本框，选择费用名称
            $fee_cols = $wpdb->get_results("SELECT fs_name, fi_name, fi_id FROM {$acc_prefix}fee_item, {$acc_prefix}fee_series "
                    . " WHERE fi_fs_id=fs_id ORDER BY fi_fs_id, fi_name", ARRAY_A);

            $cols_str = '';
            foreach ($fee_cols as $value) {
                $cols_str .= '{ label: "' . $value['fi_name'] . '", category: "' . $value['fs_name'] . ' 总分类"},';
            }
            $cols_format = rtrim($cols_str, ',');

            autocompletejs($cols_format, 'feebiz_item');
            ?>
            <tr class="form-field">
                <th scope="row"><label for="feebiz_money">金额 (必填数字)</label></th>
                <td>
                    <input name="feebiz_money" type="text" id="feebiz_money" value="">
                    <input name="feebiz_rate" type="text" id="feebiz_rate" value="<?php echo $_SESSION['rate']; ?>" maxlength="6" tabindex="9" style="width: 4em;">
                            <label for="feebiz_rate">美元汇率
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="feebiz_container">货柜号</label></th>
                <td>
                    <select name="feebiz_container" id="feebiz_container" style="width: 25em;">
                        <option selected="selected" value="0">请选择货柜号</option>
                        <?php
                        $containers = $wpdb->get_results("SELECT c_id, c_no FROM {$acc_prefix}container ORDER BY c_no", ARRAY_A);
                        foreach ($containers as $c) {
                            printf('<option value="%d">%s</option>', $c['c_id'], $c['c_no']);
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="feebiz_sum">业务摘要</label></th>
                <td><input name="feebiz_sum" type="text" id="feebiz_sum" value=""></td>
            </tr>
        </tbody>
    </table>

    <p class="submit">
        <input type="submit" name="feebiz_submit" id="feebiz_submit" class="button button-primary" value="提交业务" />
        <input type="reset" name="feebiz_r" id="feebiz_r" class="button button-primary" value="清空内容" />
    </p>
</form>

<?php

/**
 * 资金往来业务列表
 */
echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="">流水号</th>
                    <th class='manage-column' style="">日期</th>
                    <th class='manage-column'  style="">总分类</th>
                    <th class='manage-column'  style="">明细项目</th>
                    <th class='manage-column'  style="">现金增加</th>
                    <th class='manage-column'  style="">现金减少</th>
                    <th class='manage-column'  style="">货柜号</th>
                    <th class='manage-column'  style="">业务摘要</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th class='manage-column' style="">流水号</th>
                    <th class='manage-column' style="">日期</th>
                    <th class='manage-column'  style="">总分类</th>
                    <th class='manage-column'  style="">明细项目</th>
                    <th class='manage-column'  style="">现金增加</th>
                    <th class='manage-column'  style="">现金减少</th>
                    <th class='manage-column'  style="">货柜号</th>
                    <th class='manage-column'  style="">业务摘要</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

$results_feebiz = $wpdb->get_results("SELECT fb_id, fb_date, fs_name, fi_name, fb_in, fb_out, fb_c_id, fb_summary "
        . " FROM {$acc_prefix}fee_biz, {$acc_prefix}fee_item, {$acc_prefix}fee_series "
        . " WHERE fi_fs_id = fs_id AND fb_fi_id = fi_id ORDER BY fb_id DESC LIMIT {$list_total} ", ARRAY_A);

foreach ($results_feebiz as $fb) {
    $container = id2name("c_no", "{$acc_prefix}container", $fb['fb_c_id'], "c_id");
    $in = ($fb['fb_in'] == 0) ? '' : number_format($fb['fb_in'], 2);
    $out = ($fb['fb_out'] == 0) ? '' : number_format($fb['fb_out'], 2);
    echo "<tr class='alternate'>
                <td class='name'>{$fb['fb_id']}</td>
                <td class='name'>{$fb['fb_date']}</td>
                <td class='name'>{$fb['fs_name']}</td>
                <td class='name'>{$fb['fi_name']}</td>
                <td class='name'>{$in}</td>
                <td class='name'>{$out}</td>
                <td class='name'>{$container}</td>
                <td class='name'>{$fb['fb_summary']}</td>
            </tr>";
}
echo '</tbody></table>';

mixfs_bottom(); // 框架页面底部

<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

mixfs_top('外币兑换业务', $_SESSION['acc_name']);

global $wpdb;
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

$list_total = 15;

date_from_to("currencybiz_date");


if (isset($_POST['currencybiz_submit'])) {

    $_SESSION['currencybiz']['date'] = '';

    $date_arr = explode('-', $_POST['currencybiz_date']);
    if (count($date_arr) == 3 && checkdate($date_arr[1], $date_arr[2], $date_arr[0])) {
        $_SESSION['currencybiz']['date'] = $_POST['currencybiz_date'];
    }

    $fi_fields = $wpdb->get_row("SELECT fi_id, fi_in_out FROM {$acc_prefix}fee_item WHERE fi_name = '{$_POST['currencybiz_item']}'", ARRAY_A);
    $fee_item_id = $fi_fields['fi_id'];
    $in_out = ($fi_fields['fi_in_out'] == '1') ? 'fb_in' : 'fb_out';
    
    $fee_money = trim($_POST['feebiz_money']);
    $currency_money = trim($_POST['currencybiz_money']);
    
    if ( $fi_fields && $_SESSION['currencybiz']['date'] && $fee_item_id && ($fee_money * $currency_money) ) {
        if( ($fee_money * $currency_money) > 0 ) {
            $wpdb->insert($acc_prefix . 'fee_biz', 
                array(
                    'fb_date' => $_SESSION['currencybiz']['date'],
                    $in_out => $fee_money,
                    'fb_summary' => $currency_money . ', (汇率:' . number_format($fee_money/$currency_money, 4) . ') ' . trim($_POST['currencybiz_sum']),
                    'fb_fi_id' => $fee_item_id
                )
            );
            $wpdb->insert($acc_prefix . 'currency_biz', 
                array(
                    'cb_date' => $_SESSION['currencybiz']['date'],
                    'cb_money' => $currency_money,
                    'cb_rate' => number_format($fee_money/$currency_money, 4),
                    'cb_summary' => $fee_money . trim($_POST['currencybiz_sum']),
                    'cb_fi_id' => $fee_item_id
                )
            );
            echo "<div id='message' class='updated'><p>提交【{$_POST['currencybiz_item']}】货币兑换项目成功</p></div>";
        } else {
            echo "<div id='message' class='updated'><p>兑换货币“正负号”必须一致</div>";
        }
        
    } else {
        echo "<div id='message' class='updated'><p>请正确完成(必填)选项后再提交</p></div>";
    }
} // if (isset($_POST['currencybiz_submit']))


?>

<form action="" method="post" name="createuser" id="createuser" class="validate">

    <table class="form-table">
        <tbody>
            <tr class="form-field form-required">
                <th scope="row"><label for="currencybiz_date">选择业务日期 <span class="description">(必填)</span></label></th>
                <td><input name="currencybiz_date" type="text" id="currencybiz_date" value="<?php echo $_SESSION['currencybiz']['date']; ?>" aria-required="true"></td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="currencybiz_item">兑换外币名称 (必填)</label></th>
                <td><input type="text" name="currencybiz_item" id="currencybiz_item" value="双击选择或输入关键字" /></td>
            </tr>
            <?php
            // 自动完成文本框，选择费用名称
            $currency_cols = $wpdb->get_results("SELECT fs_name, fi_name, fi_id FROM {$acc_prefix}fee_item, {$acc_prefix}fee_series "
                    . " WHERE fi_fs_id=fs_id AND fs_name='外币账户' ORDER BY fi_fs_id, fi_name", ARRAY_A);

            $cols_str = '';
            foreach ($currency_cols as $value) {
                $cols_str .= '{ label: "' . $value['fi_name'] . '", category: "' . $value['fs_name'] . ' 总分类"},';
            }
            $cols_format = rtrim($cols_str, ',');

            autocompletejs($cols_format, 'currencybiz_item');
            ?>
            <tr class="form-field">
                <th scope="row"><label for="feebiz_money">记账货币金额 (必填数字)</label></th>
                <td>
                    <input name="feebiz_money" type="text" id="currencybiz_money" value="" />
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="currencybiz_money">兑换外币金额 (必填数字)</label></th>
                <td>
                    <input name="currencybiz_money" type="text" id="currencybiz_money" value="" />
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="currencybiz_sum">业务摘要</label></th>
                <td><input name="currencybiz_sum" type="text" id="currencybiz_sum" value="" /></td>
            </tr>
        </tbody>
    </table>

    <p class="submit">
        <input type="submit" name="currencybiz_submit" id="currencybiz_submit" class="button button-primary" value="提交业务" />
        <input type="reset" name="currencybiz_r" id="currencybiz_r" class="button button-primary" value="清空内容" />
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
                    <th class='manage-column'  style="">业务摘要</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

$results_currencybiz = $wpdb->get_results("SELECT fb_id, fb_date, fs_name, fi_name, fb_in, fb_out, fb_summary "
        . " FROM {$acc_prefix}fee_biz, {$acc_prefix}fee_item, {$acc_prefix}fee_series "
        . " WHERE fi_fs_id = fs_id AND fb_fi_id = fi_id AND fs_name='外币账户' "
        . " ORDER BY fb_id DESC LIMIT {$list_total} ", ARRAY_A);

foreach ($results_currencybiz as $fb) {
    $in = ($fb['fb_in'] == 0) ? '' : number_format($fb['fb_in'], 2);
    $out = ($fb['fb_out'] == 0) ? '' : number_format($fb['fb_out'], 2);
    echo "<tr class='alternate'>
                <td class='name'>{$fb['fb_id']}</td>
                <td class='name'>{$fb['fb_date']}</td>
                <td class='name'>{$fb['fs_name']}</td>
                <td class='name'>{$fb['fi_name']}</td>
                <td class='name'>{$in}</td>
                <td class='name'>{$out}</td>
                <td class='name'>{$fb['fb_summary']}</td>
            </tr>";
}
echo '</tbody></table>';

mixfs_bottom(); // 框架页面底部

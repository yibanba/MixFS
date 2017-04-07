<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

mixfs_top('产成品订单业务', $_SESSION['acc_name']);

global $wpdb;

$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';
$list_total = 15;

if (!isset($_SESSION['rate'])) { // 设置当地货币转美元的汇率
    $_SESSION['rate'] = 1.000;
}

if ($_POST['js_submit']) {
    //var_dump($_POST);
}
?>
<form action="" method="post">
    <table id="InputsWrapper" style="border: 1px #999 solid; margin: 20px 0px; padding: 10px;">
        <thead>
            <tr><th>序号</th><th>品名</th><th>件数</th><th>单价</th><th>小计</th></tr>
        </thead>
        <tfoot>
            <tr><th>序号</th><th>件数</th><th>单价</th><th>小计</th></tr>
        </tfoot>
        <tr>
            <td>1</td>
            <td><input type="text" name="atext[]" class="goodsbiz_order" value="" /></td>
            <td><input type="text" name="quantity[]" class="per_pack" value="" /></td>
            <td><input type="text" name="price[]" id="field_c_1" value="" /></td>
            <td><input type="text" name="sum[]" id="field_d_1" value="" disabled="disabled" /></td>
            <!-- <td class="removeclass"> &nbsp; </td> -->
        </tr>
        <tr>
            <td>2</td>
            <td><input type="text" name="atext[]" class="goodsbiz_order" value="" /></td>
            <td><input type="text" name="quantity[]" class="per_pack" value="" /></td>
            <td><input type="text" name="price[]" id="field_c_1" value="" /></td>
            <td><input type="text" name="sum[]" id="field_d_1" value="" disabled="disabled" /></td>
            <!-- <td class="removeclass"> &nbsp; </td> -->
        </tr>
        <tr>
            <td>3</td>
            <td><input type="text" name="atext[]" class="goodsbiz_order" value="" /></td>
            <td><input type="text" name="quantity[]" class="per_pack" value="" /></td>
            <td><input type="text" name="price[]" id="field_c_1" value="" /></td>
            <td><input type="text" name="sum[]" id="field_d_1" value="" disabled="disabled" /></td>
            <!-- <td class="removeclass"> &nbsp; </td> -->
        </tr>
        <tr>
            <td>4</td>
            <td><input type="text" name="atext[]" class="goodsbiz_order" value="" /></td>
            <td><input type="text" name="quantity[]" class="per_pack" value="" /></td>
            <td><input type="text" name="price[]" id="field_c_1" value="" /></td>
            <td><input type="text" name="sum[]" id="field_d_1" value="" disabled="disabled" /></td>
            <!-- <td class="removeclass"> &nbsp; </td> -->
        </tr>
        <tr>
            <td>5</td>
            <td><input type="text" name="atext[]" class="goodsbiz_order" value="" /></td>
            <td><input type="text" name="quantity[]" class="per_pack" value="" /></td>
            <td><input type="text" name="price[]" id="field_c_1" value="" /></td>
            <td><input type="text" name="sum[]" id="field_d_1" value="" disabled="disabled" /></td>
            <!-- <td class="removeclass"> &nbsp; </td> -->
        </tr>
    </table>
    <div class="ui-widget">
        <label for="tags">数量合计：</label><input class="ti" id="amount"> 
        <label for="tags">金额合计：</label><input class="ti" id="total">
    </div><br /><br />
    <input type="submit" value="提交" name="js_submit">
    <input type="button" value="添加" id="btn_add">
</form>


<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var total_qty = 0;
        var total_sum = 0;

        $("#InputsWrapper").keyup(function () {
            var arr_price = $("input[name='price[]']").toArray();
            var arr_qty = $("input[name='quantity[]']").toArray();
            var arr_sums = $("input[name='sum[]']").toArray();

            var i = 0;
            var amount = 0;
            var total = 0;
            $("input[name='sum[]']").each(function () {
                var p = (arr_price[i].value > 0) ? arr_price[i].value : 0;
                var q = (arr_qty[i].value > 0) ? arr_qty[i].value : 0;
                $(this).val(parseInt(p * q));
                i++;
                amount += q * 1;
                total += p * q;
            });
            $("#amount").val(amount);
            $("#total").val(total);
        });
    });
</script>
<?php
// 自动完成文本框，选择产成品名称
$get_cols = $wpdb->get_results("SELECT gs_name, gn_name, gn_id, gn_per_pack FROM {$acc_prefix}goods_name, {$acc_prefix}goods_series "
        . " WHERE gn_gs_id=gs_id ORDER BY gn_gs_id, gn_name", ARRAY_A);

$cols_str = '';
foreach ($get_cols as $value) {
    $cols_str .= '{ label: "' . $value['gn_name'] . '", category: "' . $value['gs_name'] . ' 系列", per_pack:"' . $value['gn_per_pack'] . '"},';
}
$cols_format = rtrim($cols_str, ',');

ordercompletejs($cols_format, 'goodsbiz_order');
?>
<?php
mixfs_bottom();

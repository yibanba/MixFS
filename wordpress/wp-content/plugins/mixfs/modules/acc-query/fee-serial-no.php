<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

mixfs_top('费用业务流水', $_SESSION['acc_name']);

global $wpdb;
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

isset($_SESSION['fee_qry_date']['date1']) ?: $_SESSION['fee_qry_date']['date1'] = date("Y-m-d", strtotime("-1 months"));
isset($_SESSION['fee_qry_date']['date2']) ?: $_SESSION['fee_qry_date']['date2'] = date("Y-m-d");


if (isset($_POST['btn_fee_biz'])) {
    $_SESSION['fee_qry_date']['date1'] = $_POST['fee_qry_date1'];
    $_SESSION['fee_qry_date']['date2'] = $_POST['fee_qry_date2'];
    form_qry_goods($_SESSION['fee_qry_date']['date1'], $_SESSION['fee_qry_date']['date2']);
    feebiz_list($acc_prefix, $_SESSION['fee_qry_date']['date1'], $_SESSION['fee_qry_date']['date2']);
} else {
    form_qry_goods($_SESSION['fee_qry_date']['date1'], $_SESSION['fee_qry_date']['date2']);
}
?>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $(".alternate").click(function (e) {
            if ($(this).find(":checkbox").is(":checked")) {
                $(this).find(":checkbox").attr("checked", false);
            } else {
                $(this).find(":checkbox").attr("checked", true);
            }
        })
    });
</script>
<?php
mixfs_bottom(); // 框架页面底部
//******************************************************************************

/**
 * 默认查询表单
 */
function form_qry_goods($startday, $endday) {
    ?>
    <form action="" method="post">
        <div class="manage-menus">
            <!--# 汇总查询库存和销售 -->
            <div class="alignleft actions" id="sale_inventory">
                <label for="fee_qry_date1">指定起始日期
                    <input name="fee_qry_date1" type="text" id="fee_qry_date1" value="<?php echo $startday; ?>">
                </label>
                <label for="fee_qry_date2">指定截止日期
                    <input name="fee_qry_date2" type="text" id="fee_qry_date2" value="<?php echo $endday; ?>">
                </label>
                <?php
                date_from_to("fee_qry_date1", "fee_qry_date2");
                ?>
                <input type="submit" name="btn_fee_biz" id="btn_fee_biz" class="button button-primary" value="费用业务流水查询"  />
            </div>

            <br class="clear" />
        </div>
        <br />
    </form>
    <?php
} // function form_qry_goods()

/**
 * 显示最近提交业务流水
 * 所有页面显示的最近 10 条业务流水
 */
function feebiz_list($acc_prefix, $startday, $endday) {
    global $wpdb;


    /**
     * 资金往来业务列表
     */
    echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="width:50px;"></th>
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
                    <th class='manage-column' style="width:50px;"></th>
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
            . " WHERE fb_date BETWEEN '{$startday}' AND '{$endday}' AND fi_fs_id = fs_id AND fb_fi_id = fi_id"
            . " ORDER BY fb_date, fb_id DESC ", ARRAY_A);

    foreach ($results_feebiz as $fb) {
        $container = id2name("c_no", "{$acc_prefix}container", $fb['fb_c_id'], "c_id");
        $in = ($fb['fb_in'] == 0) ? '' : number_format($fb['fb_in'], 2);
        $out = ($fb['fb_out'] == 0) ? '' : number_format($fb['fb_out'], 2);
        echo "<tr class='alternate'>
                <td class='name' style='width:50px;'><input type='checkbox'></td>
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
} // function goodsbiz_list($acc_prefix, $startday, $endday)
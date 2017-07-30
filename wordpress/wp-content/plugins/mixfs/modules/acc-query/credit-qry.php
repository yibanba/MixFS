<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

mixfs_top('赊销业务查询', $_SESSION['acc_name']);

global $wpdb;
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

isset($_SESSION['qry_date']['date1']) ?: $_SESSION['qry_date']['date1'] = date("Y-m-d", strtotime("-1 months"));
isset($_SESSION['qry_date']['date2']) ?: $_SESSION['qry_date']['date2'] = date("Y-m-d");

if (isset($_POST['btn_qry_key']) && $_POST['credit_key']=='') {
    form_qry_credit();
    echo "<div id='message' class='updated'><p>请输入至少 1 个字符，建议输入销售单后几位数或客户名称</p></div>";
}
elseif (isset($_POST['btn_qry_key']) && $_POST['credit_key']!='') {
    $_SESSION['qry_date']['date1'] = $_POST['qry_date1'];
    $_SESSION['qry_date']['date2'] = $_POST['qry_date2'];

    form_qry_credit();
    echo "<div id='message' class='updated'><p>下表为 {$_SESSION['qry_date']['date1']} —— {$_SESSION['qry_date']['date2']} 期间指定项目汇总！现金增加为存入定金；现金减少为赊销业务！</p></div>";
    qry_fee_credit($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2'], $_POST['credit_key']);
} else {

    form_qry_credit();
} // $_REQUES Processing is complete


mixfs_bottom(); // 框架页面底部
//******************************************************************************

function form_qry_credit() {
    ?>
    <form action="" method="post">
        <div class="manage-menus">
            <div class="alignleft actions" id="sale_inventory">
                <label for="qry_date1">指定起始日期
                    <input name="qry_date1" type="text" id="qry_date1" value="<?php echo $_SESSION['qry_date']['date1']; ?>">
                </label>
                <label for="qry_date2">指定截止日期
                    <input name="qry_date2" type="text" id="qry_date2" value="<?php echo $_SESSION['qry_date']['date2']; ?>">
                </label>
                <?php
                date_from_to("qry_date1", "qry_date2");
                ?>

            </div>

            <div class="alignright actions">
                <label for="credit_key">请输入销售单号后4位或客户名称
                    <input type="text" id="credit_key" name="credit_key" value="" maxlength="20" size="30" />
                </label>

                <input type="submit" name="btn_qry_key" id="btn_qry_key" class="button button-primary" value="查询"  />
            </div>
            <br class="clear" />
        </div>
        <br />
    </form>
    <?php
}

// function form_qry_credit()

/**
 * 费用汇总查询
 */
function qry_fee_credit($acc_prefix, $startday, $endday, $key = '') {
    global $wpdb;

    $sql = "SELECT fb_id, fb_date, fi_name, fb_out, fb_summary 
                FROM {$acc_prefix}fee_biz, {$acc_prefix}fee_item 
                WHERE fb_date BETWEEN  '{$startday}' AND  '{$endday}' 
                       AND fb_summary LIKE '%{$key}%' AND fb_fi_id = fi_id
                ORDER BY fb_date, fb_id";
    $fee_items = $wpdb->get_results($sql, ARRAY_N);

    if (count($fee_items) > 0) {
        echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="">流水号</th>
                    <th class='manage-column'  style="">日期</th>
                    <th class='manage-column' style="">费用项目</th>
                    <th class='manage-column' style="">现金增加</th>
                    <th class='manage-column'  style="">现金减少</th>
                    <th class='manage-column'  style="width:300px;">项目说明</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class='manage-column' style="">流水号</th>
                    <th class='manage-column'  style="">日期</th>
                    <th class='manage-column' style="">费用项目</th>
                    <th class='manage-column' style="">现金增加</th>
                    <th class='manage-column'  style="">现金减少</th>
                    <th class='manage-column'  style="width:300px;">项目说明</th>
                </tr>
            </tfoot>
            <tbody>
Form_HTML;

        $fb_l = 0; $fb_r = 0;
        foreach ($fee_items as $fi) {
            $fb_out += $fi[3];
            $l='';$r=''; // $left定金<0, $right赊账>0
            $fi[3] < 0 ? $l=-1 * $fi[3] : $r=$fi[3];
            $fb_l+=$l; $fb_r+=$r;
            echo "<tr class='alternate'>
                    <td class='name'>{$fi[0]}</td>
                    <td class='name'>{$fi[1]}</td>
                    <td class='name'>{$fi[2]}</td>
                    <td class='name'>" . mix_num($l, 2) . "</td>
                    <td class='name'>" . mix_num($r, 2) . "</td>
                    <td class='name'>{$fi[4]}</td>
                </tr>";
        } // foreach
        echo '</tbody></table>';

        $balance = mix_num(($fb_l - $fb_r),2);
        $fb_l=mix_num($fb_l,2);
        $fb_r=mix_num($fb_r,2);
        echo <<<Form_HTML
        <br />
    <table class="wp-list-table widefat fixed users" cellspacing="1">
    <thead>
        <tr>
            <th class='manage-column' style="width:150px;">本期现金增加总额: </th>
            <th class='manage-column' style="">{$fb_l}</th>
            <th class='manage-column'  style="width:150px;">本期现金减少总额: </th>
            <th class='manage-column'  style="">{$fb_r}</th>
            <th class='manage-column'  style="width:150px;">本期现金收支净额($): </th>
            <th class='manage-column'  style="">{$balance}</th>
        </tr>
    </thead>
Form_HTML;
        echo '</table><br />';
    } else {
        echo "<div id='message' class='updated'><p> {$_SESSION['qry_date']['date1']} —— {$_SESSION['qry_date']['date2']} 本期间没有业务，请重新选择起止时间</p></div>";
    }
}

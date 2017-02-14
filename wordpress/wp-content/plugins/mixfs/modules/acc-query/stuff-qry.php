<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

mixfs_top('原材料查询', $_SESSION['acc_name']);

global $wpdb;
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

isset($_SESSION['qry_date']['date1']) ? : $_SESSION['qry_date']['date1'] = date("Y-m-d", strtotime("-1 months"));
isset($_SESSION['qry_date']['date2']) ? : $_SESSION['qry_date']['date2'] = date("Y-m-d");

if (isset($_GET["sn_id"])) {             // 原料名 链接参数
    $snid = ($_GET["sn_id"] > 0) ? $_GET["sn_id"] : FALSE;
} else if (isset($_GET["c_id"])) {      // 货柜 链接参数
    $cid = ($_GET["c_id"] > 0) ? $_GET["c_id"] : FALSE;
} else if (isset($_GET["p_id"])) {      // 供应商 链接参数
    $pid = ($_GET["p_id"] > 0) ? $_GET["p_id"] : FALSE;
}

if ($pid) {
    $pname = $wpdb->get_var("SELECT p_name FROM {$acc_prefix}provider WHERE p_id={$pid}");
    echo '<div class="manage-menus">
            <div class="alignleft actions">
            供应商【 ' . $pname . ' 】 ' . $_SESSION['qry_date']['date1'] . ' —— ' . $_SESSION['qry_date']['date2'] . ' 期间业务明细
            </div>
            <div class="alignright actions">
    <input type="button" name="btn_return" id="btn_return" class="button" value="返回上级查询" onclick="history.back();" />
            </div>
        </div>
        <br />';
    qry_stuff_provider($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2'], $pid);
    qry_stuff_detail($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2'], 0, $pid, 0);
    
} elseif ($cid) {

    $cno = $wpdb->get_var("SELECT c_no FROM {$acc_prefix}container WHERE c_id={$cid}");
    echo '<div class="manage-menus">
            <div class="alignleft actions">
            货柜号【 ' . $cno . ' 】 ' . $_SESSION['qry_date']['date1'] . ' —— ' . $_SESSION['qry_date']['date2'] . ' 期间业务明细
            </div>
            <div class="alignright actions">
    <input type="button" name="btn_return" id="btn_return" class="button" value="返回上级查询" onclick="history.back();" />
            </div>
        </div>
        <br />';
    qry_stuff_container($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2'], $cid);
    qry_stuff_detail($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2'], 0, 0, $cid);
    
} elseif ($snid) {

    $sname = $wpdb->get_var("SELECT sn_name FROM {$acc_prefix}stuff_name WHERE sn_id={$snid}");
    echo '<div class="manage-menus">
            <div class="alignleft actions">
            原材料【 ' . $sname . ' 】 ' . $_SESSION['qry_date']['date1'] . ' —— ' . $_SESSION['qry_date']['date2']
    . ' 期间业务明细，库存金额合计 = 指定截止日期的库存价值
            </div>
            <div class="alignright actions">
    <input type="button" name="btn_return" id="btn_return" class="button" value="返回上级查询" onclick="history.back();" />
            </div>
        </div>
        <br />';
    qry_stuff_total($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2'], $snid);
    qry_stuff_detail($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2'], $snid);
    
} elseif (isset($_POST['btn_qry_stuff'])) {

    $_SESSION['qry_date']['date1'] = $_POST['qry_date1'];
    $_SESSION['qry_date']['date2'] = $_POST['qry_date2'];

    form_qry_stuff();
    echo "<div id='message' class='updated'><p>下表为 {$_SESSION['qry_date']['date1']} —— {$_SESSION['qry_date']['date2']} 期间所有原材料业务汇总, 前期余额 = {$_SESSION['qry_date']['date1']} 日之前的该项目余额，库存金额合计 = 指定截止日期的库存价值</p></div>";
    qry_stuff_total($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2']);
    
} elseif (isset($_POST['btn_qry_provider'])) {

    $_SESSION['qry_date']['date1'] = $_POST['qry_date1'];
    $_SESSION['qry_date']['date2'] = $_POST['qry_date2'];

    form_qry_stuff();
    echo "<div id='message' class='updated'><p>下表为 {$_SESSION['qry_date']['date1']} —— {$_SESSION['qry_date']['date2']} 期间所有原材料业务汇总, 前期余额为 {$_SESSION['qry_date']['date1']} 日之前的该项目余额</p></div>";
    qry_stuff_provider($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2']);
    
} elseif (isset($_POST['btn_qry_container'])) {

    $_SESSION['qry_date']['date1'] = $_POST['qry_date1'];
    $_SESSION['qry_date']['date2'] = $_POST['qry_date2'];

    form_qry_stuff();
    echo "<div id='message' class='updated'><p>下表为 {$_SESSION['qry_date']['date1']} —— {$_SESSION['qry_date']['date2']} 期间所有原材料业务汇总, 前期余额为 {$_SESSION['qry_date']['date1']} 日之前的该项目余额</p></div>";
    qry_stuff_container($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2']);
    
} else {

    form_qry_stuff();
} // $_REQUES Processing is complete


mixfs_bottom(); // 框架页面底部


//******************************************************************************

function qry_stuff_container($acc_prefix, $startday, $endday, $cid=0) {
    global $wpdb;
    
    $subwhere = ($cid > 0) ? " AND sb_c_id = {$cid} " : " GROUP BY c_id ";

    $sql = "SELECT c_id, c_no, sb_date, SUM(sb_money), c_summary
            FROM {$acc_prefix}stuff_biz, {$acc_prefix}container
            WHERE sb_date BETWEEN '{$startday}' AND '{$endday}' AND sb_c_id = c_id 
            {$subwhere}
            ORDER BY c_no";

    $items = $wpdb->get_results($sql, ARRAY_N);

    if (count($items)) {
        echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="">代码</th>
                    <th class='manage-column' style="">货柜号码</th>
                    <th class='manage-column'  style="">业务日期</th>
                    <th class='manage-column'  style="">金额合计</th>
                    <th class='manage-column'  style="">业务摘要</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class='manage-column' style="">代码</th>
                    <th class='manage-column' style="">货柜号码</th>
                    <th class='manage-column'  style="">业务日期</th>
                    <th class='manage-column'  style="">金额合计</th>
                    <th class='manage-column'  style="">业务摘要</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

        foreach ($items as $item) {
            $url_c_id = "onclick=\"javascript:location.href=location.href + '&c_id={$item[0]}'\"";
            
            echo "<tr class='alternate'>
                <td class='name'>{$item[0]}</td>
                <td class='name'><span {$url_c_id}>{$item[1]}</span></td>
                <td class='name'>" . $item[2] . "</td>
                <td class='name'>" . mix_num($item[3], 2) . "</td>
                <td class='name'>" . mix_num($item[4], 2) . "</td></tr>";
        } // foreach
        echo '</tbody></table>';

    }
    echo '</table><br />';
}

function qry_stuff_provider($acc_prefix, $startday, $endday, $pid) {
    global $wpdb;
    
    $subwhere = ($pid > 0) ? " AND sb_p_id = {$pid} " : " GROUP BY p_id ";

    $sql = "SELECT p_id, p_name,
                SUM( if(sb_date < '{$startday}', sb_money, 0)),
                SUM( if(sb_date <= '{$endday}', sb_money, 0)),
                p_summary
            FROM {$acc_prefix}provider, {$acc_prefix}stuff_biz
            WHERE p_id = sb_p_id 
            {$subwhere}
            ORDER BY p_id";

    $items = $wpdb->get_results($sql, ARRAY_N);

    if (count($items)) {
        echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="">代码</th>
                    <th class='manage-column' style="">供应商</th>
                    <th class='manage-column'  style="">前期订货金额</th>
                    <th class='manage-column'  style="">本期订货金额</th>
                    <th class='manage-column'  style="">库存金额合计</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class='manage-column' style="">代码</th>
                    <th class='manage-column' style="">供应商</th>
                    <th class='manage-column'  style="">前期订货金额</th>
                    <th class='manage-column'  style="">本期订货金额</th>
                    <th class='manage-column'  style="">库存金额合计</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

        foreach ($items as $item) {
            $url_p_id = "onclick=\"javascript:location.href=location.href + '&p_id={$item[0]}'\"";
            
            echo "<tr class='alternate'>
                <td class='name'>{$item[0]}</td>
                <td class='name'><span {$url_p_id}>{$item[1]}</span></td>
                <td class='name'>" . mix_num($item[2], 2) . "</td>
                <td class='name'>" . mix_num(($item[3] - $item[2]), 2) . "</td>
                <td class='name'>" . mix_num($item[3], 2) . "</td></tr>";
        } // foreach
        echo '</tbody></table>';

    }
    echo '</table><br />';
}

function form_qry_stuff() {
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
                <input type="submit" name="btn_qry_stuff" id="btn_qry_stuff" class="button button-primary" value="原材料汇总查询"  />
                <input type="submit" name="btn_qry_provider" id="btn_qry_provider" class="button button-primary" value="分供应商查询"  />
                <input type="submit" name="btn_qry_container" id="btn_qry_container" class="button button-primary" value="分货柜号查询"  />
            </div>

            <br class="clear" />
        </div>
        <br />
    </form>
    <?php
} // function form_qry_stuff()

/**
 * 指定条件的业务流水
 * @global type $wpdb
 * @param type $acc_prefix
 * @param type $startday
 * @param type $endday
 * @param type $snid
 * @param type $pid
 * @param type $cid
 */
function qry_stuff_detail($acc_prefix, $startday, $endday, $snid=0, $pid=0, $cid=0) {
    global $wpdb;

    if ($snid > 0) {
        $where = " AND sb_sn_id = {$snid} ";
    } elseif ($pid > 0) {
        $where = " AND sb_p_id = {$pid} ";
    } elseif ($cid > 0) {
        $where = " AND sb_c_id = {$cid} ";
    } else {
        $where = '';
    }

    $sql = "SELECT sb_id, sb_date, ss_name, sn_name, sb_in, sb_out, sb_p_id, sb_c_id, sb_summary, sb_money
            FROM {$acc_prefix}stuff_biz, {$acc_prefix}stuff_name, {$acc_prefix}stuff_series
            WHERE sb_date BETWEEN '{$startday}' AND '{$endday}' AND
                  sn_ss_id = ss_id AND sb_sn_id = sn_id 
                {$where}
            ORDER BY sb_date, sb_id";
    $stuff_items = $wpdb->get_results($sql, ARRAY_N);

    if (count($stuff_items) > 0) {
        echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="">流水号</th>
                    <th class='manage-column'  style="">日期</th>
                    <th class='manage-column' style="">总分类</th>
                    <th class='manage-column'  style="">原材料名</th>
                    <th class='manage-column' style="">本期入库</th>
                    <th class='manage-column'  style="">本期出库</th>
                    <th class='manage-column'  style="">金额</th>
                    <th class='manage-column'  style="">供货商</th>
                    <th class='manage-column'  style="">货柜号</th>
                    <th class='manage-column'  style="">业务摘要</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class='manage-column' style="">流水号</th>
                    <th class='manage-column'  style="">日期</th>
                    <th class='manage-column' style="">总分类</th>
                    <th class='manage-column'  style="">原材料名</th>
                    <th class='manage-column' style="">本期入库</th>
                    <th class='manage-column'  style="">本期出库</th>
                    <th class='manage-column'  style="">金额</th>
                    <th class='manage-column'  style="">供货商</th>
                    <th class='manage-column'  style="">货柜号</th>
                    <th class='manage-column'  style="">业务摘要</th>
                </tr>
            </tfoot>
            <tbody>
Form_HTML;

        foreach ($stuff_items as $si) {

            echo "<tr class='alternate'>
                    <td class='name'>{$si[0]}</td>
                    <td class='name'>{$si[1]}</td>
                    <td class='name'>{$si[2]}</td>
                    <td class='name'>{$si[3]}</td>
                    <td class='name'>" . mix_num($si[4], 1) . "</td>
                    <td class='name'>" . mix_num($si[5], 1) . "</td>
                    <td class='name'>{$si[9]}</td>
                    <td class='name'>" . id2name('p_name', "{$acc_prefix}provider", $si[6], "p_id")  . "</td>
                    <td class='name'>" . id2name('c_no', "{$acc_prefix}container", $si[7], "c_id")  . "</td>
                    <td class='name'>{$si[8]}</td>
                </tr>";
        } // foreach
        echo '</tbody></table>';

    } else {
        echo "<div id='message' class='updated'><p> {$_SESSION['qry_date']['date1']} —— {$_SESSION['qry_date']['date2']} 本期间没有业务，请重新选择起止时间</p></div>";
    }
}

/**
 * 原材料查询
 * $snid=0, $pid=0, $cid=0
 * 指定材料、供应商、货柜号ID查询
 */
function qry_stuff_total($acc_prefix, $startday, $endday, $snid=0) {

    global $wpdb;

    $subwhere = ($snid > 0) ? " AND sb_sn_id = {$snid} " : " GROUP BY sn_id ";

    $sql = "SELECT sn_id, ss_name, sn_name,
                SUM( if(sb_date < '{$startday}', sb_in, 0) ),
                SUM( if(sb_date < '{$startday}', sb_out, 0) ),
                SUM( if(sb_date <= '{$endday}', sb_in, 0) ),
                SUM( if(sb_date <= '{$endday}', sb_out, 0) ),
                SUM( if(sb_date <= '{$endday}' && sb_in != 0, sb_money, 0) ),
                SUM( if(sb_date <= '{$endday}' && sb_out != 0, sb_money, 0) )
            FROM {$acc_prefix}stuff_biz, {$acc_prefix}stuff_name, {$acc_prefix}stuff_series
            WHERE sb_sn_id = sn_id AND sn_ss_id = ss_id 
                {$subwhere}
            ORDER BY sn_ss_id, sn_name";

    $items = $wpdb->get_results($sql, ARRAY_N);

    if (count($items)) {
        echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="">代码</th>
                    <th class='manage-column' style="">总分类</th>
                    <th class='manage-column'  style="">原材料名</th>
                    <th class='manage-column'  style="">前期库存</th>
                    <th class='manage-column' style="">本期入库</th>
                    <th class='manage-column' style="">本期出库</th>
                    <th class='manage-column'  style="">当前库存</th>
                    <th class='manage-column'  style="">库存金额合计</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class='manage-column' style="">代码</th>
                    <th class='manage-column' style="">总分类</th>
                    <th class='manage-column'  style="">原材料名</th>
                    <th class='manage-column'  style="">前期库存</th>
                    <th class='manage-column' style="">本期入库</th>
                    <th class='manage-column' style="">本期出库</th>
                    <th class='manage-column'  style="">当前库存</th>
                    <th class='manage-column'  style="">库存金额合计</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;

        foreach ($items as $item) {
            $url_sn_id = "onclick=\"javascript:location.href=location.href + '&sn_id={$item[0]}'\"";
            
            echo "<tr class='alternate'>
                <td class='name'>{$item[0]}</td>
                <td class='name'>{$item[1]}</td>
                <td class='name'><span {$url_sn_id}>{$item[2]}</span></td>
                <td class='name'>" . mix_num(($item[3] - $item[4]), 1) . "</td>
                <td class='name'>" . mix_num(($item[5] - $item[3]), 1) . "</td>
                <td class='name'>" . mix_num(($item[6] - $item[4]), 1) . "</td>
                <td class='name'>" . mix_num(($item[5] - $item[6]), 1) . "</td>
                <td class='name'>" . mix_num(($item[7] - $item[8]), 2) . "</td></tr>";
        } // foreach
        echo '</tbody></table>';

    }
    echo '</table><br />';
}

<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

mixfs_top('产成品业务', $_SESSION['acc_name']);

global $wpdb;
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

isset($_SESSION['qry_date']['date1']) ?: $_SESSION['qry_date']['date1'] = date("Y-m-d", strtotime("-1 months"));
isset($_SESSION['qry_date']['date2']) ?: $_SESSION['qry_date']['date2'] = date("Y-m-d");

if (isset($_GET['gn_id'])) { // 指定产品的业务明细
    
    $gn_name = $wpdb->get_var("SELECT gn_name FROM {$acc_prefix}goods_name WHERE gn_id={$_GET['gn_id']}");
    echo '<div class="manage-menus">
            <div class="alignleft actions">
            【 ' . $gn_name . ' 】 '
    . $_SESSION['qry_date']['date1'] . ' —— ' . $_SESSION['qry_date']['date2']
    . ' 期间业务明细
            </div>
            <div class="alignright actions">
    <input type="button" name="btn_return" id="btn_return" class="button" value="返回上级查询" onclick="history.back();" />
            </div>
        </div>
        <br />';
    sales($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2'], $_GET['gn_id']);
    biz_of_place($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2'], $_GET['gn_id']);
    
}elseif (isset($_GET['gp_id'])) { // 指定仓库的业务明细
    
    $gp_name = $wpdb->get_var("SELECT gp_name FROM {$acc_prefix}goods_place WHERE gp_id={$_GET['gp_id']}");
    echo '<div class="manage-menus">
            <div class="alignleft actions">
            【 ' . $gp_name . ' 】 '
    . $_SESSION['qry_date']['date1'] . ' —— ' . $_SESSION['qry_date']['date2']
    . ' 期间业务明细
            </div>
            <div class="alignright actions">
    <input type="button" name="btn_return" id="btn_return" class="button" value="返回上级查询" onclick="history.back();" />
            </div>
        </div>
        <br />';
    sales($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2'], '', $_GET['gp_id']);
    //biz_of_place($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2'], $_GET['gn_id']);
    
} elseif (isset($_POST['btn_qry_detail'])) { // 全部产品明细查询
    
    $_SESSION['qry_date']['date1'] = $_POST['qry_date1'];
    $_SESSION['qry_date']['date2'] = $_POST['qry_date2'];
    form_qry();
    qry_detail($acc_prefix);
    
} elseif (isset($_POST['btn_qry_total'])) { // 汇总产品、销售查询
    
    $_SESSION['qry_date']['date1'] = $_POST['qry_date1'];
    $_SESSION['qry_date']['date2'] = $_POST['qry_date2'];
    form_qry();
    echo '<div id="message" class="updated"><p>点击"产品"名称查询明细</p></div>';

    sales($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2']);
    qry_total($acc_prefix, $_SESSION['qry_date']['date1'], $_SESSION['qry_date']['date2']);
} else {
    
    form_qry();
    
}


mixfs_bottom(); // 框架页面底部
//******************************************************************************

/**
 * 某个仓库或店面，指定时间内的业务明细
 * 
 */
function biz_of_place($acc_prefix, $startday, $endday, $gn_id) {
    global $wpdb;

    $places = $wpdb->get_results("SELECT gp_id, gp_name FROM {$acc_prefix}goods_place", ARRAY_A); // 全部仓库

    foreach ($places as $p) {
        $sql = "SELECT gb_id, gb_date, gs_name, gn_name, gb_in, gb_out, gb_money, gb_gp_id, gb_summary "
                . " FROM {$acc_prefix}goods_biz, {$acc_prefix}goods_name, {$acc_prefix}goods_series "
                . " WHERE gb_date BETWEEN '{$startday}' AND '{$endday}' "
                . " AND gb_gp_id = {$p['gp_id']} AND gb_gn_id = gn_id AND gn_gs_id = gs_id AND gb_gn_id={$gn_id}"
                . " ORDER BY gb_date";
        $results_goodsbiz = $wpdb->get_results($sql, ARRAY_A);
        if(count($results_goodsbiz) == 0) {
            continue; // 如果某个仓库没有业务略过
        } else {
            echo "<span style='font-weight: bold;'>{$p['gp_name']}：</span>";
        }
        echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="">流水号</th>
                    <th class='manage-column' style="">日期</th>
                    <th class='manage-column'  style="">系列</th>
                    <th class='manage-column'  style="">型号</th>
                    <th class='manage-column'  style="">仓库</th>
                    <th class='manage-column'  style="">入库</th>
                    <th class='manage-column'  style="">出库</th>
                    <th class='manage-column'  style="">金额</th>
                    <th class='manage-column'  style="">业务摘要</th>
                </tr>
            </thead>
            <tbody>
Form_HTML;
        
        foreach ($results_goodsbiz as $gb) {
            $in_number = ( $gb['gb_in'] == 0 ) ? '' : number_format($gb['gb_in'], 0);
            $out_number = ( $gb['gb_out'] == 0 ) ? '' : number_format($gb['gb_out'], 0);
            $money = ($gb['gb_money'] == 0) ? '' : number_format($gb['gb_money'], 2);
            echo "<tr class='alternate'>
                    <td class='name'>{$gb['gb_id']}</td>
                    <td class='name'>{$gb['gb_date']}</td>
                    <td class='name'>{$gb['gs_name']}</td>
                    <td class='name'>{$gb['gn_name']}</td>
                    <td class='name'>{$p['gp_name']}</td>
                    <td class='name'>{$in_number}</td>
                    <td class='name'>{$out_number}</td>
                    <td class='name'>{$money}</td>
                    <td class='name'>{$gb['gb_summary']}</td>
                </tr>";
        } // foreach ($results_goodsbiz as $gb)

        echo '</tbody></table><br />';
    }
} // function biz_of_place

/**
 * 查询指定日期每个仓库库存
 * @global type $wpdb
 */
function qry_detail($acc_prefix) {
    echo '<div id="message" class="updated"><p>点击"产品"和"仓库"名称查询明细</p></div>';
    global $wpdb;
    $places = $wpdb->get_results("SELECT gp_id, gp_name FROM {$acc_prefix}goods_place", ARRAY_A); // 全部仓库

    $title = "<th class='manage-column' style=''>代码</th><th class='manage-column' style=''>产品系列</th><th class='manage-column' style=''>产品名称</th>";
    $place_kv = array(); //仓库代码=>仓库名称 键值对
    $sql_spare = '';
    foreach ($places as $p) {
        $url = "onclick=\"javascript:location.href=location.href + '&gp_id={$p['gp_id']}'\"";
        $title .= "<th><span {$url}>{$p['gp_name']}</span></th>";
        $place_kv[$p['gp_id']] = $p['gp_name'];
        $sql_spare .= ", SUM(if(gb_gp_id={$p['gp_id']}, gb_in, 0)),  SUM(if(gb_gp_id={$p['gp_id']}, gb_out, 0)) ";
    }
    $cols = count($places); // 仓库数量

    $thead = '<thead><tr>' . $title . '</tr></thead>';
    $tfoot = '<tfoot><tr>' . $title . '</tr></tfoot>';

    $sql = "SELECT gn_id, gs_name, gn_name " . $sql_spare
            . " FROM {$acc_prefix}goods_series, {$acc_prefix}goods_name LEFT JOIN {$acc_prefix}goods_biz "
            . " ON gn_id = gb_gn_id "
            . " WHERE gb_date <= '{$_SESSION['qry_date']['date2']}' AND gn_gs_id=gs_id"
            . " GROUP BY gn_name "
            . " ORDER BY gn_gs_id, gn_name ";

    $inventory = $wpdb->get_results($sql, ARRAY_N); // 全部仓库

    $col_limit = 3 + $cols * 2; // 代码 + 系列 + 品名 + ( sum(gb_in) - sum(gb_out) ) * 2列
    $tbl = '';
    foreach ($inventory as $fields) {
        $url = "onclick=\"javascript:location.href=location.href + '&gn_id={$fields[0]}'\"";
        $tbl .= "<tr class='alternate'>";
        $tbl .= "<td class='name'>{$fields[0]}</td>";
        $tbl .= "<td class='name'>{$fields[1]}</td>";
        $tbl .= "<td class='name'><span {$url}>{$fields[2]}</span></td>";
        for ($i = 3; $i < $col_limit; $i += 2) {
            $temp = $fields[$i] - $fields[$i + 1];
            $tbl .= "<td class='name'>" . (($temp == 0) ? '' : $temp) . "</td>";
        }
        $tbl .= "</tr>";
    }

    echo '<table class="wp-list-table widefat fixed users" cellspacing="1">'
    . $thead . $tfoot
    . '<tbody>'
    . $tbl
    . '</tbody></table>';
    
} // function qry_detail


/**
 * 汇总库存和销售
 */
function qry_total($acc_prefix, $startday, $endday, $gp_id=0) {
    global $wpdb;
    
    $sql = "SELECT gn_id, gs_name, gn_name, gn_price,
                SUM( if(gb_date < '{$startday}', gb_in, 0) ),
                SUM( if(gb_date < '{$startday}' , gb_out, 0) ),
                SUM( if(gb_date <= '{$endday}', gb_in, 0) ),
                SUM( if(gb_date <= '{$endday}' , gb_out, 0) ),
                SUM( if(gb_date >= '{$startday}' && gb_date <= '{$endday}' && gb_money <> 0, gb_out, 0) ),
                SUM( if(gb_date >= '{$startday}' && gb_date <= '{$endday}', gb_money, 0) )
            FROM {$acc_prefix}goods_biz, {$acc_prefix}goods_name, {$acc_prefix}goods_series
            WHERE gb_gn_id = gn_id AND gn_gs_id = gs_id
            GROUP BY gn_id
            ORDER BY gn_gs_id, gn_name";
    $inventory = $wpdb->get_results($sql, ARRAY_N); // 全部仓库
    
    if(count($inventory)) {
        echo <<<Form_HTML
        <table class="wp-list-table widefat fixed users" cellspacing="1">
            <thead>
                <tr>
                    <th class='manage-column' style="">代码</th>
                    <th class='manage-column'  style="">系列</th>
                    <th class='manage-column'  style="">型号</th>
                    <th class='manage-column' style="">前期库存</th>
                    <th class='manage-column'  style="">本期入库</th>
                    <th class='manage-column'  style="">本期出库</th>
                    <th class='manage-column'  style="">当前库存</th>
                    <th class='manage-column'  style="">本期销售金额</th>
                    <th class='manage-column'  style="">最近售价</th>
                    <th class='manage-column'  style="">预计库存销售额</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class='manage-column' style="">代码</th>
                    <th class='manage-column'  style="">系列</th>
                    <th class='manage-column'  style="">型号</th>
                    <th class='manage-column' style="">前期库存</th>
                    <th class='manage-column'  style="">本期入库</th>
                    <th class='manage-column'  style="">本期出库</th>
                    <th class='manage-column'  style="">当前库存</th>
                    <th class='manage-column'  style="">本期销售金额</th>
                    <th class='manage-column'  style="">最近售价</th>
                    <th class='manage-column'  style="">预计库存销售额</th>
                </tr>
            </tfoot>

            <tbody>
Form_HTML;
        $stock = 0;
        $cash = 0;
        foreach ($inventory as $fields) {
            $stock += ($fields[6] - $fields[7]);
            $cash += ($fields[3] * ($fields[6] - $fields[7]));
            $url = "onclick=\"javascript:location.href=location.href + '&gn_id={$fields[0]}'\"";
            echo "<tr class='alternate'>"
            . "<td class='name'>{$fields[0]}</td>"
            . "<td class='name'>{$fields[1]}</td>"
            . "<td class='name'><span {$url}>{$fields[2]}</span></td>"
            . "<td class='name'>" . ($fields[4] - $fields[5]) . "</td>"
            . "<td class='name'>" . ($fields[6] -$fields[7] + $fields[8] - $fields[4] + $fields[5]) . "</td>"
            . "<td class='name'>{$fields[8]}</td>"
            . "<td class='name'>" . ($fields[6] -$fields[7]) . "</td>"
            . "<td class='name'>{$fields[9]}</td>"
            . "<td class='name'>{$fields[3]}</td>"
            . "<td class='name'>" . ($fields[3] * ($fields[6] - $fields[7])) . "</td>";
        }
        echo '</tbody></table>';
        
        $cash = number_format($cash,2);
        echo <<<Form_HTML
        <br />
    <table class="wp-list-table widefat fixed users" cellspacing="1">
    <thead>
        <tr>
            <th class='manage-column' style="width:100px;">汇总截止日期: </th>
            <th class='manage-column' style="">{$endday}</th>
            <th class='manage-column'  style="width:140px;">全部型号库存合计(双): </th>
            <th class='manage-column'  style="">{$stock}</th>
            <th class='manage-column'  style="width:120px;">预计销售合计($): </th>
            <th class='manage-column'  style="">{$cash}</th>
        </tr>
    </thead>
Form_HTML;
    }
    echo '</table><br />';
} // function qry_total($acc_prefix)

function qg_all_place($dis_btn = 1) {

        $r = run_sql("SELECT gn_id, gs_name, gn_name,
                                    SUM( if(gb_date < '{$_SESSION["startdate"]}', gb_in, 0) ),
                                    SUM( if(gb_date < '{$_SESSION["startdate"]}' , gb_out, 0) ),
                                    SUM( if(gb_date <= '{$_SESSION["enddate"]}', gb_in, 0) ),
                                    SUM( if(gb_date <= '{$_SESSION["enddate"]}' , gb_out, 0) ),
		        SUM( if(gb_date >= '{$_SESSION["startdate"]}' && gb_date <= '{$_SESSION["enddate"]}' && gb_money <> 0, gb_out, 0) ),
                                    SUM( if(gb_date >= '{$_SESSION["startdate"]}' && gb_date <= '{$_SESSION["enddate"]}', gb_money, 0) ),
		        gn_price
                                FROM {$_SESSION["submod"]}_goods_biz, {$_SESSION["submod"]}_goods_name, {$_SESSION["submod"]}_goods_series
		    WHERE gb_gn_id = gn_id AND gn_gs_id = gs_id
                                GROUP BY gn_id
                                ORDER BY gn_gs_id, gn_name"
                        , "查询 所有仓库、指定期间 的出入库及总库存情况");

        if(mysql_num_rows($r) > 0) {
            echo "<table id='report'>
                                <caption>下表中的 库存统计 是： {$_SESSION["startdate"]} —— {$_SESSION["enddate"]} 的数据，(含所有赊销且未结账的产成品数)；销售单价随最近销售动态调整</caption>
		    <tr><th>代码</th><th>系列</th><th>品名</th>
                                        <th>前期库存</th><th>本期入库</th><th>本期出库</th><th>当前库存</th>
                                        <th>本期销售金额</th><th>最近售价</th><th>预计库存销售额</th>
                                </tr>";

            $stock = 0;
            $cash = 0;
            $counter = 0;
            $head = "<tr><th>代码</th><th>系列</th><th>品名</th>
                                            <th>前期库存</th><th>本期入库</th><th>本期出库</th><th>当前库存</th>
                                            <th>本期销售金额</th><th>最近售价</th><th>预计库存销售额</th>
                                    </tr>"; // 用于重复20行打印表头

            while($row = mysql_fetch_row($r)) {

                $stock += ($row[5] - $row[6]);
                $cash += ($row[9] * ($row[5] - $row[6]));
		echo "<tr><td>$row[0]</td><td>$row[1]</td><td>";

                if($dis_btn) {
                    echo "<input type='button' name='return_btn'  id='return_btn' style='height: 22px; margin: 0; padding: 0 25px; width: 110px; '
		onClick=\"javascript:location.href='{$_SERVER['PHP_SELF']}?gnid={$row[0]}'\" value='{$row[2]}' />";
                } else {
                    echo "[ $row[2] ]";
                }

                echo "</td>
                            <td>" . my_num( $row[3] - $row[4] ) . "</td>
                            <td>" . my_num( $row[5] -$row[6] + $row[7] - $row[3] + $row[4]) . "</td>
                            <td>" . my_num( (int)$row[7] ) . "</td>
                            <td>" . my_num(  $row[5] -$row[6] ) . "</td>
                            <td>" . my_num($row[8]) . "</td>
                            <td>" . my_num($row[9]) . "</td>
                            <td>" . my_num($row[9] * ($row[5] - $row[6])) . "</td>
                          </tr>";

                if((++$counter) % 33 == 0) {
                    echo $head;
                }

            }
            echo '</table>';
            echo "<br /><table id='report'><tr>
                                    <th>所有仓库当前库存合计( 双 )：</th><th>" . my_num($stock) . "</th>
                                    <th>预计库存销售额合计：</th><th>" . my_num($cash) . "</th>
                                    </tr></table>";

        } else {
	    page_msg("本表中没有数据");
	}
    }
    
/**
 * 默认查询表单
 */
function form_qry() {
    ?>
    <form action="" method="post">
        <div class="manage-menus">
            <!--# 汇总查询库存和销售 -->
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
                <input type="submit" name="btn_qry_detail" id="btn_qry_detail" class="button button-primary" value="分仓库查询库存"  />
                <input type="submit" name="btn_qry_total" id="btn_qry_total" class="button button-primary" value="汇总查询库存与销售"  />
            </div>

            <br class="clear" />
        </div>
        <br />
    </form>
    <?php
} // function form_qry()

function sales($acc_prefix, $startday, $endday, $gn_id=0, $gp_id=0) {
    global $wpdb;
    
    if($gn_id) {
        $title = id2name("gn_name", "{$acc_prefix}goods_name", $gn_id, "gn_id");
        $sub_where = " AND gb_gn_id = {$gn_id}";
    } elseif ($gp_id) {
        $title = id2name("gp_name", "{$acc_prefix}goods_place", $gp_id, "gp_id");
        $sub_where = " AND gb_gp_id = {$gp_id}";
    } else {
        $title = "本期销售汇总";
        $sub_where = '';
    }

    $sql = "SELECT SUM( if(gb_money > 0, gb_money, 0) ), SUM( if(gb_money < 0, gb_money, 0) )
                FROM {$acc_prefix}goods_biz
                WHERE gb_date BETWEEN '{$startday}' AND '{$endday}' {$sub_where}";
    $sale_item = $wpdb->get_row($sql, ARRAY_N);

    if(count($sale_item)) {
    echo <<<Form_HTML
    <table class="wp-list-table widefat fixed users" cellspacing="1">
    <thead>
        <tr>
            <th class='manage-column' style="">项目名称</th>
            <th class='manage-column' style="">日期</th>
            <th class='manage-column'  style="">销售收入</th>
            <th class='manage-column'  style="">销售退回</th>
            <th class='manage-column'  style="">销售余额</th>
        </tr>
    </thead>
    <tbody>
Form_HTML;
    
            $in_number = ( $sale_item[0] == 0 ) ? '' : number_format($sale_item[0], 2);
            $out_number = ( $sale_item[1] == 0 ) ? '' : number_format($sale_item[1], 2);
            $balance = number_format( ($sale_item[0] + $sale_item[1]), 2);
            echo "<tr class='alternate'>
                    <td class='name'>{$title}</td>
                    <td class='name'>{$startday} —— {$endday}</td>
                    <td class='name'>{$in_number}</td>
                    <td class='name'>{$out_number}</td>
                    <td class='name'>{$balance}</td>
                </tr>";

        echo '</tbody></table><br />';
    }
}

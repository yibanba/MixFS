<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

mixfs_top('产成品业务', $_SESSION['acc_name']);

global $wpdb;
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';


if (isset($_POST['btn_qry_detail'])) {
    $_SESSION['qry_date']['date1'] = $_POST['qry_date1'];
    $_SESSION['qry_date']['date2'] = $_POST['qry_date2'];
    form_qry();
    qry_detail($acc_prefix);
} elseif (isset($_POST['btn_qry_total'])) {
    $_SESSION['qry_date']['date1'] = $_POST['qry_date1'];
    $_SESSION['qry_date']['date2'] = $_POST['qry_date2'];
    form_qry();
} elseif (isset($_GET['gn_id'])) {
    return_btn();
} else {
    isset($_SESSION['qry_date']['date1']) ?: $_SESSION['qry_date']['date1'] = date("Y-m-d", strtotime("-1 months"));
    isset($_SESSION['qry_date']['date2']) ?: $_SESSION['qry_date']['date2'] = date("Y-m-d");
    form_qry();
}


mixfs_bottom(); // 框架页面底部

//******************************************************************************


function return_btn() {
    echo <<<Button
        <div class="manage-menus">
            <div class="alignleft actions">
    <input type="button" name="btn_return" id="btn_return" class="button" value="返回查询" onclick="history.back();" />
            </div>
        </div>
        <br />
Button;
}
/**
 * 查询指定日期每个仓库库存
 * @global type $wpdb
 */
function qry_detail($acc_prefix) {
    echo '<div id="message" class="updated"><p>点击产品名称可以查询明细</p></div>';
    global $wpdb;
    $places = $wpdb->get_results("SELECT gp_id, gp_name FROM {$acc_prefix}goods_place", ARRAY_A); // 全部仓库

    $title = "<th class='manage-column' style=''>代码</th><th class='manage-column' style=''>产品系列</th><th class='manage-column' style=''>产品名称</th>";
    $place_kv = array(); //仓库代码=>仓库名称 键值对
    $sql_spare = '';
    foreach ($places as $p) {
        $title .= "<th>{$p['gp_name']}</th>";
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
            $tbl .= "<td class='name'>" . (($temp==0) ? '' : $temp) . "</td>";
        }
        $tbl .= "</tr>";
    }

    echo '<table class="wp-list-table widefat fixed users" cellspacing="1">'
    . $thead . $tfoot
    . '<tbody>'
    . $tbl
    . '</tbody></table>';
}


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
}

// function form_qry()

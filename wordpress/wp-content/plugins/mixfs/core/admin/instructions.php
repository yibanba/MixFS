<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * 选择账套后跳转到本页
 */
if (!empty($_SESSION['mas'][$_GET['mas']])) {
    list($_SESSION['acc_tbl'], $_SESSION['acc_name']) = explode('|', $_SESSION['mas'][$_GET['mas']]);
    mixfs_top('进销存概况', $_SESSION['acc_name']);
} elseif (isset($_SESSION['acc_tbl']) && isset($_SESSION['acc_name'])) {
    mixfs_top('进销存概况', $_SESSION['acc_name']);
} else {
    $url_entrance = admin_url('admin.php?page=mixfs-entrance'); // 所有页面共用的返回入口链接URL == mixfs-entrance
    echo "<script type='text/javascript'>location.href='$url_entrance'</script>";
    exit();
}

global $wpdb, $current_user;
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';

// 统计访问账套的：人员、时间、IP
if( ! isset($_SESSION['login_log']) ) {
    $wpdb->insert( $wpdb->prefix . "mixfs_user_log", 
            array( 'uid' => $current_user->ID,
                   'ip' => $_SERVER['REMOTE_ADDR'],
                   'account' => $_SESSION['acc_name']
                ), array( '%s', '%s', '%s' )
            );
    $_SESSION['login_log'] = TRUE;
}

$limit = 20; //排行榜显示数量
if($_POST['btn_jxc']) {
    $_SESSION['overview']['startdate'] = $_POST['overview_date1'];
    $_SESSION['overview']['enddate'] = $_POST['overview_date2'];
} elseif ($_POST['btn_one']) {
    $_SESSION['overview']['enddate'] = date("Y-m-d");
    $_SESSION['overview']['startdate'] = date("Y-m-d", strtotime("-1 months"));
} elseif ($_POST['btn_three']) {
    $_SESSION['overview']['enddate'] = date("Y-m-d");
    $_SESSION['overview']['startdate'] = date("Y-m-d", strtotime("-3 months"));
} elseif ($_POST['btn_year']) {
    $_SESSION['overview']['enddate'] = date("Y-m-d");
    $_SESSION['overview']['startdate'] = date("Y-m-d", strtotime("-12 months"));
}
if (!isset($_SESSION['overview']['startdate'])) {
    $enddate = date("Y-m-d");
    $startdate = date("Y-m-d", strtotime("-1 months"));
} else {
    $startdate = $_SESSION['overview']['startdate'];
    $enddate = $_SESSION['overview']['enddate'];
}
?>
<form action="" method="post">
    <div class="manage-menus">

        <div class="alignleft actions">
            <label for="overview_date1">起始日期
                <input name="overview_date1" type="text" id="overview_date1" value="<?php echo $startdate; ?>">
            </label>
            <label for="overview_date2">结束日期
                <input name="overview_date2" type="text" id="overview_date2" value="<?php echo $enddate; ?>">
            </label>
            <?php
            date_from_to("overview_date1", "overview_date2");
            ?>
            <input type="submit" name="btn_jxc" id="btn_jxc" class="button button-primary" value="进销存排行查询"  />
            <input type="submit" name="btn_one" id="btn_jxc" class="button" value="近一个月"  />
            <input type="submit" name="btn_three" id="btn_jxc" class="button" value="近三个月"  />
            <input type="submit" name="btn_year" id="btn_jxc" class="button" value="最近一年"  />
        </div>
        <br class="clear" />
    </div>
    <br />
</form>
<div id="dashboard-widgets-wrap">
    <div id="dashboard-widgets" class="metabox-holder columns-3">

        <div id="postbox-container-1" class="postbox-container">

            <table class="wp-list-table widefat" cellspacing="1"  style="width:95%; margin:0px auto;">
                <thead>
                    <tr><td colspan="4"><h3>销售数量排行</h3></td></tr>
                    <tr>
                        <th class='manage-column' style="">序号</th>
                        <th class='manage-column' style="">系列</th>
                        <th class='manage-column' style="">品名</th>
                        <th class='manage-column' style="">数量(双)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php sales_volume($limit, $acc_prefix, $startdate, $enddate); ?>
                </tbody>
            </table>

        </div>
        <div id="postbox-container-2" class="postbox-container">

            <table class="wp-list-table widefat" cellspacing="1"  style="width:95%; margin:0px auto;">
                <thead>
                    <tr><td colspan="4"><h3>销售金额排行</h3></td></tr>
                    <tr>
                        <th class='manage-column' style="">序号</th>
                        <th class='manage-column' style="">系列</th>
                        <th class='manage-column' style="">品名</th>
                        <th class='manage-column' style="">金额($)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php sales_amount($limit, $acc_prefix, $startdate, $enddate); ?>
                </tbody>
            </table>

        </div>
        <div id="postbox-container-3" class="postbox-container">

            <table class="wp-list-table widefat" cellspacing="1"  style="width:95%; margin:0px auto;">
                <thead>
                    <tr><td colspan="4"><h3>库存数量排行(按指定的结束时间统计)</h3></td></tr>
                    <tr>
                        <th class='manage-column' style="">序号</th>
                        <th class='manage-column' style="">系列</th>
                        <th class='manage-column' style="">品名</th>
                        <th class='manage-column' style="">数量(双)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php inventory($limit, $acc_prefix, $enddate); ?>
                </tbody>
            </table>

        </div>

    </div>
</div>

<?php
mixfs_bottom(); // 框架页面底部

//******************************************************************************

/**
 * 销售量
 * @param type $num 排行榜显示数量
 */
function sales_volume ($limit, $acc_prefix, $startdate, $enddate) {
    global $wpdb;
    $sql = "SELECT gs_name, gn_name, SUM( gb_out ) AS volume
            FROM {$acc_prefix}goods_series, 
                {$acc_prefix}goods_name LEFT JOIN {$acc_prefix}goods_biz 
                ON gn_id = gb_gn_id
            WHERE gb_date BETWEEN '{$startdate}' AND '{$enddate}' AND gn_gs_id = gs_id AND gb_money != 0
            GROUP BY gn_id
            ORDER BY volume DESC 
            LIMIT {$limit}";

    $results = $wpdb->get_results($sql, ARRAY_A);
    $row_num = count($results);
    $count = 0;
    foreach ($results as $fields) {
        ++$count;
        echo "<tr class='alternate'>
                <td>{$count}</td>
                <td>{$fields['gs_name']}</td>
                <td>{$fields['gn_name']}</td>
                <td>{$fields['volume']}</td>
                    </tr>";
        if($count == $limit || $count ==$row_num) {
            break;
        }
    }
    
    while( ++$count <= $limit) {
        echo "<tr class='alternate'>
                        <td>{$count}</td><td></td><td></td><td></td>
                    </tr>";
    }
}

/**
 * 销售额
 * @param type $num 排行榜显示数量
 */
function sales_amount ($limit, $acc_prefix, $startdate, $enddate) {
    global $wpdb;
    $sql = "SELECT gs_name, gn_name, SUM( gb_money ) AS amount
            FROM {$acc_prefix}goods_series, 
                {$acc_prefix}goods_name LEFT JOIN {$acc_prefix}goods_biz 
                ON gn_id = gb_gn_id
            WHERE gb_date BETWEEN '{$startdate}' AND '{$enddate}' AND gn_gs_id = gs_id AND gb_out != 0
            GROUP BY gn_id
            ORDER BY amount DESC 
            LIMIT {$limit}";

    $results = $wpdb->get_results($sql, ARRAY_A);
    $row_num = count($results);
    $count = 0;
    foreach ($results as $fields) {
        ++$count;
        echo "<tr class='alternate'>
                <td>{$count}</td>
                <td>{$fields['gs_name']}</td>
                <td>{$fields['gn_name']}</td>
                <td>{$fields['amount']}</td>
                    </tr>";
        if($count == $limit || $count ==$row_num) {
            break;
        }
    }
    
    while( ++$count <= $limit) {
        echo "<tr class='alternate'>
                        <td>{$count}</td><td></td><td></td><td></td>
                    </tr>";
    }    
}

/**
 * 库存排行
 */

function inventory($limit, $acc_prefix, $enddate) {
    global $wpdb;
    $sql = "SELECT gs_name, gn_name, SUM(gb_in) - SUM(gb_out) AS stock
            FROM {$acc_prefix}goods_series, 
                {$acc_prefix}goods_name LEFT JOIN {$acc_prefix}goods_biz 
                ON gn_id = gb_gn_id
            WHERE gb_date <= '{$enddate}' AND gn_gs_id=gs_id
            GROUP BY gn_id
            ORDER BY stock DESC
            LIMIT {$limit}";

    $results = $wpdb->get_results($sql, ARRAY_A);
    
    $row_num = count($results);
    $count = 0;
    foreach ($results as $fields) {
        ++$count;
        echo "<tr class='alternate'>
                <td>{$count}</td>
                <td>{$fields['gs_name']}</td>
                <td>{$fields['gn_name']}</td>
                <td>{$fields['stock']}</td>
                    </tr>";
        if($count == $limit || $count == $row_num) {
            break;
        }
    }
    
    while( ++$count <= $limit) {
        echo "<tr class='alternate'>
                        <td>{$count}</td><td></td><td></td><td></td>
                    </tr>";
    }
}

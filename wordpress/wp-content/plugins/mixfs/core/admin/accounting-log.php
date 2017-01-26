<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly


/**
 * 用户登录日志表
 */
$url_entrance = admin_url('admin.php?page=mixfs-entrance'); // 所有页面共用的返回入口链接URL == mixfs-entrance

$html = '<div class="wrap">'
        . '<div id="icon-themes" class="icon32"><br></div>'
        . '<h2 class="nav-tab-wrapper">';
$html .= '<a href="' . $url_entrance . '" class="nav-tab">财务软件入口</a>';
$html .= '<a href="' . $url_entrance . '" class="nav-tab nav-tab-active">登录日志</a>';

echo $html . '<a href="' . wp_logout_url() . '" class="nav-tab">退出软件</a></h2><br />';

global $wpdb, $current_user;
$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';


$limit = 20; //排行榜显示数量
if ($_POST['btn_log']) {
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
            <input type="submit" name="btn_log" id="btn_log" class="button button-primary" value="日志查询"  />
            <input type="submit" name="btn_one" id="btn_one" class="button" value="近一个月"  />
            <input type="submit" name="btn_three" id="btn_three" class="button" value="近三个月"  />
            <input type="submit" name="btn_year" id="btn_year" class="button" value="最近一年"  />
        </div>
        <br class="clear" />
    </div>
    <br />
</form>
<table class="wp-list-table widefat fixed users" cellspacing="1">
    <thead>
        <tr>
            <th class='manage-column' style="width: 100px;">流水号</th>
            <th class='manage-column' style="">用户名</th>
            <th class='manage-column'  style="">登录时间</th>
            <th class='manage-column'  style="">IP 地址</th>
            <th class='manage-column'  style="">账套名称</th>
        </tr>
    </thead>

    <tfoot>
        <tr>
            <th class='manage-column' style="width: 100px;">流水号</th>
            <th class='manage-column' style="">用户名</th>
            <th class='manage-column'  style="">登录时间</th>
            <th class='manage-column'  style="">IP 地址</th>
            <th class='manage-column'  style="">账套名称</th>
        </tr>
    </tfoot>

    <tbody>
<?php acc_log($acc_prefix, $startdate, $enddate); ?>
    </tbody>
</table>

<?php
mixfs_bottom(); // 框架页面底部
//******************************************************************************


function acc_log($acc_prefix, $startdate, $enddate) {
    global $wpdb;

    $sql = "SELECT log_id, user_login, logintime, ip, account
            FROM {$wpdb->prefix}mixfs_user_log, {$wpdb->prefix}users
            WHERE logintime BETWEEN '{$startdate} 00:00:00' AND '{$enddate} 23:59:59' AND uid = ID
            ORDER BY log_id DESC ";

    $results = $wpdb->get_results($sql, ARRAY_A);
    $count = 0;
    foreach ($results as $fields) {
        echo "<tr class='alternate'>
                <td>{$fields['log_id']}</td>
                <td>{$fields['user_login']}</td>
                <td>{$fields['logintime']}</td>
                <td>{$fields['ip']}</td>
                <td>{$fields['account']}</td>
              </tr>";
    }
}

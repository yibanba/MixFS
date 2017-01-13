<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

mixfs_top('产成品业务', $_SESSION['acc_name']);

global $wpdb;

$acc_prefix = $wpdb->prefix . 'mixfs_' . $_SESSION['acc_tbl'] . '_';
date_from_to("from", "to");
?>
<div style="margin: auto 30px; width: 300px;">
    <label for="from">From</label>
    <input type="text" id="from" name="from">
    <label for="to">to</label>
    <input type="text" id="to" name="to">
</div>

<label for="Mixtags">Tags: </label>
<input type="text" id="Mixtags">



<?php
$get_cols = $wpdb->get_results("SELECT gn_name, gs_name, gn_id FROM {$acc_prefix}goods_name, {$acc_prefix}goods_series "
        . " WHERE gn_gs_id=gs_id ORDER BY gn_gs_id, gn_name", ARRAY_A);

$cols_str = '';
foreach ($get_cols as $value) {
    $cols_str .= '{ label: "' . $value['gn_name'] . '", category: "' . $value['gs_name'] . '"},';
}
$cols_format = rtrim($cols_str, ',');

autocompletejs($cols_format, 'Mixtags');

date_from_to("from1");
?>

<label for="from1">From1</label>
    <input type="text" id="from1" name="from1">
    
<?php
mixfs_bottom(); // 框架页面底部

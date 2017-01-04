<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!empty($_SESSION['mas'][$_GET['mas']])) {
    list($_SESSION['acc_tbl'], $_SESSION['acc_name']) = explode('|', $_SESSION['mas'][$_GET['mas']]);
    mixfs_top('账套使用说明', $_SESSION['acc_name']);
} elseif(isset ($_SESSION['acc_tbl']) && isset ($_SESSION['acc_name']) ) {
    mixfs_top('账套使用说明', $_SESSION['acc_name']);
}


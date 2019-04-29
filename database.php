<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function edin_create_plugin_table() {
  global $wpdb;
  $tablename = $wpdb->prefix . 'percents';
  $charset_collate = $wpdb->get_charset_collate();
// controllo se la tabella essite o meno nel db
  if ($wpdb->get_var("show tables like '$tablename'") != $tablename) {
    $sql = "CREATE TABLE $tablename (
id int(5) NOT NULL AUTO_INCREMENT,
percx float(4) NOT NULL,
percy float(4) NOT NULL,
percw float(4) NOT NULL,
perch float(4) NOT NULL,
PRIMARY KEY id (id)
)$charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
  }
}

register_activation_hook(dirname(__FILE__) . "/wpvacancy.php", 'edin_create_plugin_table');

<?php

/*
  * Plugin Name: Elastic Email Sender
  * Text Domain: elastic-email-sender
  * Domain Path: /languages
  * Plugin URI: https://wordpress.org/plugins/elastic-email-sender/
  * Description: This plugin reconfigures the wp_mail() function to send email using REST API (via Elastic Email) instead of SMTP and creates an options page that allows you to specify various options.
  * Author: Elastic Email
  * Author URI: https://elasticemail.com
  * Version: 1.0.7
  * License: GPLv2 or later
  * Elastic Email Inc. for WordPress
  * Copyright (C) 2017
 */

/* Version check */
global $wp_version;
$exit_msg = 'ElasticEmail Sender requires WordPress 4.1 or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress"> Please update!</a>';

if (version_compare($wp_version, "4.1", "<")) {
    exit($exit_msg);
}

require_once('class/eemail.php');
eemail::on_load(__DIR__);

/* ----------- ADMIN ----------- */
if (is_admin()) {

    function deactivate() {
        delete_option('ee_options');
    }

    register_deactivation_hook(__FILE__, 'deactivate');
    require_once 'class/eeadmin.php';
    $ee_admin = new eeadmin(__DIR__);
}


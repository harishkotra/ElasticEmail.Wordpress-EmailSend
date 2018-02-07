<?php
/*
 * Plugin Name: Elastic Email Sender
 * Text Domain: elastic-email-sender
 * Domain Path: /languages
 * Plugin URI: https://wordpress.org/plugins/elastic-email-sender/
 * Description: This plugin reconfigures the wp_mail() function to send email using REST API (via Elastic Email) instead of SMTP and creates an options page that allows you to specify various options.
 * Author: Elastic Email
 * Author URI: https://elasticemail.com
 * Version: 1.0.11
 * License: GPLv2 or later
 * Elastic Email Inc. for WordPress
 * Copyright (C) 2018
*/

/* Version check */
global $wp_version;
$exit_msg = 'ElasticEmail Sender requires WordPress 4.1 or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress"> Please update!</a>';

if (version_compare($wp_version, "4.1", "<")) {
    exit($exit_msg);
}

require_once('class/ees_mail.php');
eemail::on_load(__DIR__);


/* ----------- ADMIN ----------- */
if (is_admin()) {

    /* deactivate */

    function elasticemailsender_deactivate() {
        update_option('elastic-email-sender-status', false);
    }

    register_deactivation_hook(__FILE__, 'elasticemailsender_deactivate');


    /* uninstall */

    function elasticemailsender_activate() {
        update_option('elastic-email-sender-status', true);
        register_uninstall_hook(__FILE__, 'elasticemailsender_uninstall');
    }

    register_activation_hook(__FILE__, 'elasticemailsender_activate');

    function elasticemailsender_uninstall() {
        delete_option('elastic-email-sender-status');
        delete_option('ee_publicaccountid');
        delete_option('ee_options');
    }

    require_once 'class/ees_admin.php';
    $ee_admin = new eeadmin(__DIR__);
}


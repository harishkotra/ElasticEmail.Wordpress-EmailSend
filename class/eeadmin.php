<?php

define('EE_ADMIN', true);

/**
 * Description of eeadmin
 *
 * @author ElasticEmail
 */
class eeadmin {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options, $initAPI = false;
    public $theme_path;

    /**
     * Start up
     */
    public function __construct($pluginpath) {
        $this->theme_path = $pluginpath;
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_init', array($this, 'init_options'));
    }

    public function add_menu() {
        $this->options = get_option('ee_options');
        add_action('admin_enqueue_scripts', array($this, 'custom_admin_scripts'));
        add_menu_page('Elastic Email Sender', 'Elastic Email Sender', 'manage_options', 'elasticemail-settings', array($this, 'show_settings'), plugins_url('ElasticEmailSender/assets/images/icon.png'));
    }

    public function custom_admin_scripts() {
        wp_enqueue_style('eesender-css', plugins_url('/ElasticEmailSender/assets/css/admin.css'), array(), null, 'all');
        wp_enqueue_script('eesender-init', plugins_url('/ElasticEmailSender/assets/js/admin.js'), array('jquery'), null, true);
    }

    public function show_settings() {
        $this->initAPI();
        $error = false;
        $account = null;
        try {
            $accountAPI = new \ElasticEmailClient\Account();
            $account = $accountAPI->Load();
        } catch (ElasticEmailClient\ApiException $e) {
            $error = $e->getMessage();
        }
        require_once ($this->theme_path . '/template/settingsadmin.php');
        return;
    }

    public function initAPI() {
        if ($this->initAPI === true) {
            return;
        }
        require_once($this->theme_path . '/api/ElasticEmailClient.php');
        $options = get_option('ee_options');
        if (empty($this->options['ee_apikey']) === false) {
            \ElasticEmailClient\ApiClient::SetApiKey($options['ee_apikey']);
        }
        $this->initAPI = true;
    }

    public function init_options() {
        register_setting(
                'ee_option_group', // Option group
                'ee_options' // Option name
        );
        //INIT SECTION
        add_settings_section('setting_section_id', null, null, 'ee-settings');

        //INIT FIELD
        add_settings_field('ee_enable', 'Select mailer:', array($this, 'enable_input'), 'ee-settings', 'setting_section_id', array('input_name' => 'ee_enable'));
        add_settings_field('ee_apikey', 'Elastic Email API Key:', array($this, 'input_callback'), 'ee-settings', 'setting_section_id', array('input_name' => 'ee_apikey', 'width' => 280));

    }

    /**
     * Get the settings option array and print one of its values
     */
    public function input_callback($arg) {
        printf(
                '<input type="text" id="title" name="ee_options[' . $arg['input_name'] . ']" value="%s" style="%s"/>', isset($this->options[$arg['input_name']]) ? esc_attr($this->options[$arg['input_name']]) : '', (isset($arg['width']) && $arg['width'] > 0) ? 'width:' . $arg['width'] . 'px' : ''
        );
    }

    public function enable_input($arg) {
        if (!isset($this->options[$arg['input_name']]) || empty($this->options[$arg['input_name']])) {
            $valuel = 'yes';
        } else {
            $valuel = $this->options[$arg['input_name']];
        }
        echo'<div style="margin-bottom:15px;"><label><input type="radio" name="ee_options[' . $arg['input_name'] . ']" value="yes" ' . (((strlen($this->options['ee_apikey']) > 20) === true) ? 'checked' : '') . '/><span>Send all WordPress emails via Elastic Email API.</span><label></div>';
        echo'<label><input type="radio" name="ee_options[' . $arg['input_name'] . ']" value="no"  ' . (((strlen($this->options['ee_apikey']) > 20) === false) ? 'checked' : '') . '/><span>Use the original Wordpress function to send emails.</span><label>';
        }

    public $errordesc = array(
        "Ignore" => "Delivery was not attempted",
        "Spam" => "Considered spam by the recipient or their email service provider",
        "BlackListed" => "Domain or IP is potentially on a blacklist",
        "NoMailbox" => "Email address does not exist",
        "GreyListed" => "Temporarily rejected.  Retrying will likely be accepted",
        "Throttled" => "Too many emails for the same domain were detected",
        "Timeout" => "A timeout occured trying to send this email",
        "ConnectionProblem" => "A connection problem occured trying to send this email",
        "SPFProblem" => "The domain that sent this email does not have SPF validated properly",
        "AccountProblem" => "Recipient account problem like over quota or disabled",
        "DNSProblem" => "There is a problem with the DNS of the recipients domain",
        "WhitelistingProblem" => "Recipient's email service provider requires a white listing of the IP sending the email",
        "CodeError" => "An unexpected error has occurred",
        "ManualCancel" => "User cancelled the in progress email.",
        "ConnectionTerminated" => "Status is unknown due the connection being terminated by the recipients server",
        "NotDelivered" => "Recipient is on your bounce/blocked recipients list",
        "Unsubscribed" => "Recipient is on your unsubscribed list",
        "AbuseReport" => "Recipient is on your Abuse list",
    );

}

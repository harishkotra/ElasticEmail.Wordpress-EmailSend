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
    private $defaultOptions = array('ee_enable' => 'no', 'ee_apikey' => null),
            $options,
            $initAPI = false;
    public $theme_path;

    /**
     * Start up
     */
    public function __construct($pluginpath) {
        $this->theme_path = $pluginpath;
        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_init', array($this, 'init_options'));
        $this->options = get_option('ee_options', $this->defaultOptions);
    }

    //Added admin menu
    public function add_menu() {
        add_action('admin_enqueue_scripts', array($this, 'custom_admin_scripts'));
        add_menu_page('Elastic Email Sender', 'Elastic Email Sender', 'manage_options', 'elasticemail-settings', array($this, 'show_settings'), plugins_url('/assets/images/icon.png', dirname(__FILE__)));
    }

    //Added custom admin scripts
    public function custom_admin_scripts() {
        wp_enqueue_style('eesender-css', plugins_url('/assets/css/admin.css', dirname(__FILE__)), array(), null, 'all');
    }

    //Load Elastic Email settings
    public function show_settings() {
        $this->initAPI();
        try {
            $accountAPI = new \ElasticEmailClient\Account();
            $error = null;
            $account = $accountAPI->Load();
        } catch (ElasticEmailClient\ApiException $e) {
            $error = $e->getMessage();
            $account = array();
        }
        //Loads the settings template
        require_once ($this->theme_path . '/template/settingsadmin.php');
        return;
    }

    //Initialization Elastic Email API
    public function initAPI() {

        if ($this->initAPI === true) {
            return;
        }
        //Loads Elastic Email Client
        require_once($this->theme_path . '/api/ElasticEmailClient.php');
        if (empty($this->options['ee_apikey']) === false) {
            \ElasticEmailClient\ApiClient::SetApiKey($this->options['ee_apikey']);
        }
        $this->initAPI = true;
    }

    //Initialization custom options
    public function init_options() {
        register_setting(
                'ee_option_group', // Option group
                'ee_options', // Option name
                array($this, 'valid_options')   // Santize Callback
        );
        //INIT SECTION
        add_settings_section('setting_section_id', null, null, 'ee-settings');
        //INIT FIELD
        add_settings_field('ee_enable', 'Select mailer:', array($this, 'enable_input'), 'ee-settings', 'setting_section_id', array('input_name' => 'ee_enable'));
        add_settings_field('ee_apikey', 'Elastic Email API Key:', array($this, 'input_apikey'), 'ee-settings', 'setting_section_id', array('input_name' => 'ee_apikey', 'width' => 280));
    }

    /**
     * Validation plugin options during their update data
     * @param type $input
     * @return type
     */
    public function valid_options($input) {
        //If api key have * then use old api key
        if (strpos($input['ee_apikey'], '*') !== false) {
            $input['ee_apikey'] = $this->options['ee_apikey'];
        } else {
            $input['ee_apikey'] = sanitize_key($input['ee_apikey']);
        }

        if ($input['ee_enable'] !== 'yes') {
            $input['ee_enable'] = 'no';
        }
        return $input;
    }

    /**
     * Get the apikey option and print one of its values
     */
    public function input_apikey($arg) {
        $apikey = $this->options[$arg['input_name']];
        if (empty($apikey) === false) {
            $apikey = substr($apikey, 0, 15) . '***************';
        }
        printf('<input type="text" id="title" name="ee_options[' . $arg['input_name'] . ']" value="' . $apikey . '" style="%s"/>', (isset($arg['width']) && $arg['width'] > 0) ? 'width:' . $arg['width'] . 'px' : '');
    }

    //Displays the settings items
    public function enable_input($arg) {
        if (!isset($this->options[$arg['input_name']]) || empty($this->options[$arg['input_name']])) {
            $valuel = 'no';
        } else {
            $valuel = $this->options[$arg['input_name']];
        }

        echo'<div style="margin-bottom:15px;"><label><input type="radio" name="ee_options[' . $arg['input_name'] . ']" value="yes" ' . (($valuel === 'yes') ? 'checked' : '') . '/><span>Send all WordPress emails via Elastic Email API.</span><label></div>';
        echo'<label><input type="radio" name="ee_options[' . $arg['input_name'] . ']" value="no"  ' . (($valuel === 'no') ? 'checked' : '') . '/><span>Use the defaults Wordpress function to send emails.</span><label>';
    }

}
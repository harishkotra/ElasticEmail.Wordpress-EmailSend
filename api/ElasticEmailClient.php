<?php

/*
  The MIT License (MIT)

  Copyright (c) 2016-2017 Elastic Email, Inc.

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in all
  copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
  SOFTWARE.
 */

namespace ElasticEmailClient;

use ApiTypes;

class ApiClient {

    private static $apiKey = null;
    private static $ApiUri = "https://api.elasticemail.com/v2/";
    private static $postbody, $boundary;

    public static function Request($target, $data = array(), $method = "GET", array $attachments = array()) {
        self::$postbody = array();
        self::$boundary = hash('sha256', uniqid('', true));
        $url = self::$ApiUri . $target;
        $data['apikey'] = self::$apiKey;

        if (empty(self::$apiKey)) {
            throw new ApiException($url, $method, 'ApiKey is not set.');
        }

        self::parseData($data);
        self::parseAttachments($attachments);
        array_push(self::$postbody, '--' . self::$boundary . '--');
        try {
            $response = wp_remote_post($url, array(
                'method' => 'POST',
                'headers' => array(
                    'content-type' => 'multipart/form-data; boundary=' . self::$boundary
                ),
                'body' => implode("", self::$postbody)));

            if ($response['response']['code'] !== 200) {
                return "Code Error: " . $response['response']['code'];
            }
            
            $jsonresponse = json_decode($response['body'], true);
            if ($jsonresponse['success'] === true) {
                return true;
            } else {
                return $response['body'];
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    //Set Elastic Email Api Key
    public static function SetApiKey($apiKey) {
        self::$apiKey = $apiKey;
    }

    //Parsing data
    private static function parseData($data) {
        foreach ($data as $key => $item) {

            if (empty($item)) {
                continue;
            }

            if (is_array($item)) {
                self::parseData($item);
            } else {
                array_push(self::$postbody, '--' . self::$boundary . "\r\n" . 'Content-Disposition: form-data; name=' . $key . '' . "\r\n\r\n" . $item . "\r\n");
            }
        }
    }

    //Parsing attachments
    private static function parseAttachments($attachments) {
        if (empty($attachments) === true) {
            return;
        }

        foreach ($attachments as $i => $attpath) {
            if (empty($attpath) === true) {
                continue;
            }

            //Extracting the file name
            $filenameonly = explode("/", $attpath);
            $fname = $filenameonly[sizeof($filenameonly) - 1];
            
            array_push(self::$postbody, '--' . self::$boundary . "\r\n");
            array_push(self::$postbody, 'Content-Disposition: form-data; name="attachments' . ($i + 1) . '"; filename="' . $fname . '"' . "\r\n\r\n");

            //Loading attachment
            $handle = fopen($attpath, "r");
            if ($handle) {
                $fileContent = '';
                while (($buffer = fgets($handle, 4096)) !== false) {
                    $fileContent .= $buffer;
                }
                fclose($handle);
            }
            array_push(self::$postbody, $fileContent . "\r\n");
        }
    }
}

class ApiException extends \Exception {

    public $url;
    public $method;
    public $rawResponse;

    /**
     * @param string $url
     * @param string $method
     * @param string $message
     * @param string $rawResponse
     */
    public function __construct($url, $method, $message = "", $rawResponse = "") {
        $this->url = $url;
        $this->method = $method;
        $this->rawResponse = $rawResponse;
        parent::__construct($message);
    }

    public function __toString() {
        return strtoupper($this->method) . ' ' . $this->url . ' returned: ' . $this->getMessage();
    }
}

/**
 * Methods for managing your account and subaccounts.
 */
class Account {

    /**
     * Create new subaccount and provide most important data about it.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $email Proper email address.
     * @param string $password Current password.
     * @param string $confirmPassword Repeat new password.
     * @param bool $requiresEmailCredits True, if account needs credits to send emails. Otherwise, false
     * @param bool $enableLitmusTest True, if account is able to send template tests to Litmus. Otherwise, false
     * @param bool $requiresLitmusCredits True, if account needs credits to send emails. Otherwise, false
     * @param int $maxContacts Maximum number of contacts the account can havelkd
     * @param bool $enablePrivateIPRequest True, if account can request for private IP on its own. Otherwise, false
     * @param bool $sendActivation True, if you want to send activation email to this account. Otherwise, false
     * @param string $returnUrl URL to navigate to after account creation
     * @param ?ApiTypes\SendingPermission $sendingPermission Sending permission setting for account
     * @param ?bool $enableContactFeatures True, if you want to use Advanced Tools.  Otherwise, false
     * @param string $poolName Private IP required. Name of the custom IP Pool which Sub Account should use to send its emails. Leave empty for the default one or if no Private IPs have been bought
     * @return string
     */
    public function AddSubAccount($email, $password, $confirmPassword, $requiresEmailCredits = false, $enableLitmusTest = false, $requiresLitmusCredits = false, $maxContacts = 0, $enablePrivateIPRequest = true, $sendActivation = false, $returnUrl = null, $sendingPermission = null, $enableContactFeatures = null, $poolName = null) {
        return ApiClient::Request('account/addsubaccount', array(
                    'email' => $email,
                    'password' => $password,
                    'confirmPassword' => $confirmPassword,
                    'requiresEmailCredits' => $requiresEmailCredits,
                    'enableLitmusTest' => $enableLitmusTest,
                    'requiresLitmusCredits' => $requiresLitmusCredits,
                    'maxContacts' => $maxContacts,
                    'enablePrivateIPRequest' => $enablePrivateIPRequest,
                    'sendActivation' => $sendActivation,
                    'returnUrl' => $returnUrl,
                    'sendingPermission' => $sendingPermission,
                    'enableContactFeatures' => $enableContactFeatures,
                    'poolName' => $poolName
        ));
    }

    /**
     * Add email, template or litmus credits to a sub-account
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param int $credits Amount of credits to add
     * @param string $notes Specific notes about the transaction
     * @param ApiTypes\CreditType $creditType Type of credits to add (Email or Litmus)
     * @param string $subAccountEmail Email address of sub-account
     * @param string $publicAccountID Public key of sub-account to add credits to. Use subAccountEmail or publicAccountID not both.
     */
    public function AddSubAccountCredits($credits, $notes, $creditType = ApiTypes\CreditType::Email, $subAccountEmail = null, $publicAccountID = null) {
        return ApiClient::Request('account/addsubaccountcredits', array(
                    'credits' => $credits,
                    'notes' => $notes,
                    'creditType' => $creditType,
                    'subAccountEmail' => $subAccountEmail,
                    'publicAccountID' => $publicAccountID
        ));
    }

    /**
     * Change your email address. Remember, that your email address is used as login!
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $sourceUrl URL from which request was sent.
     * @param string $newEmail New email address.
     * @param string $confirmEmail New email address.
     */
    public function ChangeEmail($sourceUrl, $newEmail, $confirmEmail) {
        return ApiClient::Request('account/changeemail', array(
                    'sourceUrl' => $sourceUrl,
                    'newEmail' => $newEmail,
                    'confirmEmail' => $confirmEmail
        ));
    }

    /**
     * Create new password for your account. Password needs to be at least 6 characters long.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $currentPassword Current password.
     * @param string $newPassword New password for account.
     * @param string $confirmPassword Repeat new password.
     */
    public function ChangePassword($currentPassword, $newPassword, $confirmPassword) {
        return ApiClient::Request('account/changepassword', array(
                    'currentPassword' => $currentPassword,
                    'newPassword' => $newPassword,
                    'confirmPassword' => $confirmPassword
        ));
    }

    /**
     * Deletes specified Subaccount
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param bool $notify True, if you want to send an email notification. Otherwise, false
     * @param string $subAccountEmail Email address of sub-account
     * @param string $publicAccountID Public key of sub-account to delete. Use subAccountEmail or publicAccountID not both.
     */
    public function DeleteSubAccount($notify = true, $subAccountEmail = null, $publicAccountID = null) {
        return ApiClient::Request('account/deletesubaccount', array(
                    'notify' => $notify,
                    'subAccountEmail' => $subAccountEmail,
                    'publicAccountID' => $publicAccountID
        ));
    }

    /**
     * Returns API Key for the given Sub Account.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $subAccountEmail Email address of sub-account
     * @param string $publicAccountID Public key of sub-account to retrieve sub-account API Key. Use subAccountEmail or publicAccountID not both.
     * @return string
     */
    public function GetSubAccountApiKey($subAccountEmail = null, $publicAccountID = null) {
        return ApiClient::Request('account/getsubaccountapikey', array(
                    'subAccountEmail' => $subAccountEmail,
                    'publicAccountID' => $publicAccountID
        ));
    }

    /**
     * Lists all of your subaccounts
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @return Array<ApiTypes\SubAccount>
     */
    public function GetSubAccountList() {
        return ApiClient::Request('account/getsubaccountlist');
    }

    /**
     * Loads your account. Returns detailed information about your account.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @return ApiTypes\Account
     */
    public function Load() {
        return ApiClient::Request('account/load');
    }

    /**
     * Load advanced options of your account
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @return ApiTypes\AdvancedOptions
     */
    public function LoadAdvancedOptions() {
        return ApiClient::Request('account/loadadvancedoptions');
    }

    /**
     * Lists email credits history
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @return Array<ApiTypes\EmailCredits>
     */
    public function LoadEmailCreditsHistory() {
        return ApiClient::Request('account/loademailcreditshistory');
    }

    /**
     * Lists litmus credits history
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @return Array<ApiTypes\LitmusCredits>
     */
    public function LoadLitmusCreditsHistory() {
        return ApiClient::Request('account/loadlitmuscreditshistory');
    }

    /**
     * Shows queue of newest notifications - very useful when you want to check what happened with mails that were not received.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @return Array<ApiTypes\NotificationQueue>
     */
    public function LoadNotificationQueue() {
        return ApiClient::Request('account/loadnotificationqueue');
    }

    /**
     * Lists all payments
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param int $limit Maximum of loaded items.
     * @param int $offset How many items should be loaded ahead.
     * @param DateTime $fromDate Starting date for search in YYYY-MM-DDThh:mm:ss format.
     * @param DateTime $toDate Ending date for search in YYYY-MM-DDThh:mm:ss format.
     * @return Array<ApiTypes\Payment>
     */
    public function LoadPaymentHistory($limit, $offset, $fromDate, $toDate) {
        return ApiClient::Request('account/loadpaymenthistory', array(
                    'limit' => $limit,
                    'offset' => $offset,
                    'fromDate' => $fromDate,
                    'toDate' => $toDate
        ));
    }

    /**
     * Lists all referral payout history
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @return Array<ApiTypes\Payment>
     */
    public function LoadPayoutHistory() {
        return ApiClient::Request('account/loadpayouthistory');
    }

    /**
     * Shows information about your referral details
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @return ApiTypes\Referral
     */
    public function LoadReferralDetails() {
        return ApiClient::Request('account/loadreferraldetails');
    }

    /**
     * Shows latest changes in your sending reputation
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param int $limit Maximum of loaded items.
     * @param int $offset How many items should be loaded ahead.
     * @return Array<ApiTypes\ReputationHistory>
     */
    public function LoadReputationHistory($limit = 20, $offset = 0) {
        return ApiClient::Request('account/loadreputationhistory', array(
                    'limit' => $limit,
                    'offset' => $offset
        ));
    }

    /**
     * Shows detailed information about your actual reputation score
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @return ApiTypes\ReputationDetail
     */
    public function LoadReputationImpact() {
        return ApiClient::Request('account/loadreputationimpact');
    }

    /**
     * Returns detailed spam check.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param int $limit Maximum of loaded items.
     * @param int $offset How many items should be loaded ahead.
     * @return Array<ApiTypes\SpamCheck>
     */
    public function LoadSpamCheck($limit = 20, $offset = 0) {
        return ApiClient::Request('account/loadspamcheck', array(
                    'limit' => $limit,
                    'offset' => $offset
        ));
    }

    /**
     * Lists email credits history for sub-account
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $subAccountEmail Email address of sub-account
     * @param string $publicAccountID Public key of sub-account to list history for. Use subAccountEmail or publicAccountID not both.
     * @return Array<ApiTypes\EmailCredits>
     */
    public function LoadSubAccountsEmailCreditsHistory($subAccountEmail = null, $publicAccountID = null) {
        return ApiClient::Request('account/loadsubaccountsemailcreditshistory', array(
                    'subAccountEmail' => $subAccountEmail,
                    'publicAccountID' => $publicAccountID
        ));
    }

    /**
     * Loads settings of subaccount
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $subAccountEmail Email address of sub-account
     * @param string $publicAccountID Public key of sub-account to load settings for. Use subAccountEmail or publicAccountID not both.
     * @return ApiTypes\SubAccountSettings
     */
    public function LoadSubAccountSettings($subAccountEmail = null, $publicAccountID = null) {
        return ApiClient::Request('account/loadsubaccountsettings', array(
                    'subAccountEmail' => $subAccountEmail,
                    'publicAccountID' => $publicAccountID
        ));
    }

    /**
     * Lists litmus credits history for sub-account
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $subAccountEmail Email address of sub-account
     * @param string $publicAccountID Public key of sub-account to list history for. Use subAccountEmail or publicAccountID not both.
     * @return Array<ApiTypes\LitmusCredits>
     */
    public function LoadSubAccountsLitmusCreditsHistory($subAccountEmail = null, $publicAccountID = null) {
        return ApiClient::Request('account/loadsubaccountslitmuscreditshistory', array(
                    'subAccountEmail' => $subAccountEmail,
                    'publicAccountID' => $publicAccountID
        ));
    }

    /**
     * Shows usage of your account in given time.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param DateTime $from Starting date for search in YYYY-MM-DDThh:mm:ss format.
     * @param DateTime $to Ending date for search in YYYY-MM-DDThh:mm:ss format.
     * @return Array<ApiTypes\Usage>
     */
    public function LoadUsage($from, $to) {
        return ApiClient::Request('account/loadusage', array(
                    'from' => $from,
                    'to' => $to
        ));
    }

    /**
     * Manages your apikeys.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $apiKey APIKey you would like to manage.
     * @param ApiTypes\APIKeyAction $action Specific action you would like to perform on the APIKey
     * @return Array<string>
     */
    public function ManageApiKeys($apiKey, $action) {
        return ApiClient::Request('account/manageapikeys', array(
                    'apiKey' => $apiKey,
                    'action' => $action
        ));
    }

    /**
     * Shows summary for your account.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @return ApiTypes\AccountOverview
     */
    public function Overview() {
        return ApiClient::Request('account/overview');
    }

    /**
     * Shows you account's profile basic overview
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @return ApiTypes\Profile
     */
    public function ProfileOverview() {
        return ApiClient::Request('account/profileoverview');
    }

    /**
     * Remove email, template or litmus credits from a sub-account
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param ApiTypes\CreditType $creditType Type of credits to add (Email or Litmus)
     * @param string $notes Specific notes about the transaction
     * @param string $subAccountEmail Email address of sub-account
     * @param string $publicAccountID Public key of sub-account to remove credits from. Use subAccountEmail or publicAccountID not both.
     * @param ?int $credits Amount of credits to remove
     * @param bool $removeAll Remove all credits of this type from sub-account (overrides credits if provided)
     */
    public function RemoveSubAccountCredits($creditType, $notes, $subAccountEmail = null, $publicAccountID = null, $credits = null, $removeAll = false) {
        return ApiClient::Request('account/removesubaccountcredits', array(
                    'creditType' => $creditType,
                    'notes' => $notes,
                    'subAccountEmail' => $subAccountEmail,
                    'publicAccountID' => $publicAccountID,
                    'credits' => $credits,
                    'removeAll' => $removeAll
        ));
    }

    /**
     * Request a private IP for your Account
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param int $count Number of items.
     * @param string $notes Free form field of notes
     */
    public function RequestPrivateIP($count, $notes) {
        return ApiClient::Request('account/requestprivateip', array(
                    'count' => $count,
                    'notes' => $notes
        ));
    }

    /**
     * Update sending and tracking options of your account.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param ?bool $enableClickTracking True, if you want to track clicks. Otherwise, false
     * @param ?bool $enableLinkClickTracking True, if you want to track by link tracking. Otherwise, false
     * @param ?bool $manageSubscriptions True, if you want to display your labels on your unsubscribe form. Otherwise, false
     * @param ?bool $manageSubscribedOnly True, if you want to only display labels that the contact is subscribed to on your unsubscribe form. Otherwise, false
     * @param ?bool $transactionalOnUnsubscribe True, if you want to display an option for the contact to opt into transactional email only on your unsubscribe form. Otherwise, false
     * @param ?bool $skipListUnsubscribe True, if you do not want to use list-unsubscribe headers. Otherwise, false
     * @param ?bool $autoTextFromHtml True, if text BODY of message should be created automatically. Otherwise, false
     * @param ?bool $allowCustomHeaders True, if you want to apply custom headers to your emails. Otherwise, false
     * @param string $bccEmail Email address to send a copy of all email to.
     * @param string $contentTransferEncoding Type of content encoding
     * @param ?bool $emailNotificationForError True, if you want bounce notifications returned. Otherwise, false
     * @param string $emailNotificationEmail Specific email address to send bounce email notifications to.
     * @param string $webNotificationUrl URL address to receive web notifications to parse and process.
     * @param ?bool $webNotificationForSent True, if you want to send web notifications for sent email. Otherwise, false
     * @param ?bool $webNotificationForOpened True, if you want to send web notifications for opened email. Otherwise, false
     * @param ?bool $webNotificationForClicked True, if you want to send web notifications for clicked email. Otherwise, false
     * @param ?bool $webNotificationForUnsubscribed True, if you want to send web notifications for unsubscribed email. Otherwise, false
     * @param ?bool $webNotificationForAbuseReport True, if you want to send web notifications for complaint email. Otherwise, false
     * @param ?bool $webNotificationForError True, if you want to send web notifications for bounced email. Otherwise, false
     * @param string $hubCallBackUrl URL used for tracking action of inbound emails
     * @param string $inboundDomain Domain you use as your inbound domain
     * @param ?bool $inboundContactsOnly True, if you want inbound email to only process contacts from your account. Otherwise, false
     * @param ?bool $lowCreditNotification True, if you want to receive low credit email notifications. Otherwise, false
     * @param ?bool $enableUITooltips True, if account has tooltips active. Otherwise, false
     * @param ?bool $enableContactFeatures True, if you want to use Advanced Tools.  Otherwise, false
     * @param string $notificationsEmails Email addresses to send a copy of all notifications from our system. Separated by semicolon
     * @param string $unsubscribeNotificationsEmails Emails, separated by semicolon, to which the notification about contact unsubscribing should be sent to
     * @param string $logoUrl URL to your logo image.
     * @param ?bool $enableTemplateScripting True, if you want to use template scripting in your emails {{}}. Otherwise, false
     * @return ApiTypes\AdvancedOptions
     */
    public function UpdateAdvancedOptions($enableClickTracking = null, $enableLinkClickTracking = null, $manageSubscriptions = null, $manageSubscribedOnly = null, $transactionalOnUnsubscribe = null, $skipListUnsubscribe = null, $autoTextFromHtml = null, $allowCustomHeaders = null, $bccEmail = null, $contentTransferEncoding = null, $emailNotificationForError = null, $emailNotificationEmail = null, $webNotificationUrl = null, $webNotificationForSent = null, $webNotificationForOpened = null, $webNotificationForClicked = null, $webNotificationForUnsubscribed = null, $webNotificationForAbuseReport = null, $webNotificationForError = null, $hubCallBackUrl = null, $inboundDomain = null, $inboundContactsOnly = null, $lowCreditNotification = null, $enableUITooltips = null, $enableContactFeatures = null, $notificationsEmails = null, $unsubscribeNotificationsEmails = null, $logoUrl = null, $enableTemplateScripting = true) {
        return ApiClient::Request('account/updateadvancedoptions', array(
                    'enableClickTracking' => $enableClickTracking,
                    'enableLinkClickTracking' => $enableLinkClickTracking,
                    'manageSubscriptions' => $manageSubscriptions,
                    'manageSubscribedOnly' => $manageSubscribedOnly,
                    'transactionalOnUnsubscribe' => $transactionalOnUnsubscribe,
                    'skipListUnsubscribe' => $skipListUnsubscribe,
                    'autoTextFromHtml' => $autoTextFromHtml,
                    'allowCustomHeaders' => $allowCustomHeaders,
                    'bccEmail' => $bccEmail,
                    'contentTransferEncoding' => $contentTransferEncoding,
                    'emailNotificationForError' => $emailNotificationForError,
                    'emailNotificationEmail' => $emailNotificationEmail,
                    'webNotificationUrl' => $webNotificationUrl,
                    'webNotificationForSent' => $webNotificationForSent,
                    'webNotificationForOpened' => $webNotificationForOpened,
                    'webNotificationForClicked' => $webNotificationForClicked,
                    'webNotificationForUnsubscribed' => $webNotificationForUnsubscribed,
                    'webNotificationForAbuseReport' => $webNotificationForAbuseReport,
                    'webNotificationForError' => $webNotificationForError,
                    'hubCallBackUrl' => $hubCallBackUrl,
                    'inboundDomain' => $inboundDomain,
                    'inboundContactsOnly' => $inboundContactsOnly,
                    'lowCreditNotification' => $lowCreditNotification,
                    'enableUITooltips' => $enableUITooltips,
                    'enableContactFeatures' => $enableContactFeatures,
                    'notificationsEmails' => $notificationsEmails,
                    'unsubscribeNotificationsEmails' => $unsubscribeNotificationsEmails,
                    'logoUrl' => $logoUrl,
                    'enableTemplateScripting' => $enableTemplateScripting
        ));
    }

    /**
     * Update settings of your private branding. These settings are needed, if you want to use Elastic Email under your brand.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param bool $enablePrivateBranding True: Turn on or off ability to send mails under your brand. Otherwise, false
     * @param string $logoUrl URL to your logo image.
     * @param string $supportLink Address to your support.
     * @param string $privateBrandingUrl Subdomain for your rebranded service
     * @param string $smtpAddress Address of SMTP server.
     * @param string $smtpAlternative Address of alternative SMTP server.
     * @param string $paymentUrl URL for making payments.
     */
    public function UpdateCustomBranding($enablePrivateBranding = false, $logoUrl = null, $supportLink = null, $privateBrandingUrl = null, $smtpAddress = null, $smtpAlternative = null, $paymentUrl = null) {
        return ApiClient::Request('account/updatecustombranding', array(
                    'enablePrivateBranding' => $enablePrivateBranding,
                    'logoUrl' => $logoUrl,
                    'supportLink' => $supportLink,
                    'privateBrandingUrl' => $privateBrandingUrl,
                    'smtpAddress' => $smtpAddress,
                    'smtpAlternative' => $smtpAlternative,
                    'paymentUrl' => $paymentUrl
        ));
    }

    /**
     * Update http notification URL.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $url URL of notification.
     * @param string $settings Http notification settings serialized to JSON 
     */
    public function UpdateHttpNotification($url, $settings = null) {
        return ApiClient::Request('account/updatehttpnotification', array(
                    'url' => $url,
                    'settings' => $settings
        ));
    }

    /**
     * Update your profile.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $firstName First name.
     * @param string $lastName Last name.
     * @param string $address1 First line of address.
     * @param string $city City.
     * @param string $state State or province.
     * @param string $zip Zip/postal code.
     * @param int $countryID Numeric ID of country. A file with the list of countries is available <a href="http://api.elasticemail.com/public/countries"><b>here</b></a>
     * @param string $deliveryReason Why your clients are receiving your emails.
     * @param bool $marketingConsent True if you want to receive newsletters from Elastic Email. Otherwise, false.
     * @param string $address2 Second line of address.
     * @param string $company Company name.
     * @param string $website HTTP address of your website.
     * @param string $logoUrl URL to your logo image.
     * @param string $taxCode Code used for tax purposes.
     * @param string $phone Phone number
     */
    public function UpdateProfile($firstName, $lastName, $address1, $city, $state, $zip, $countryID, $deliveryReason = null, $marketingConsent = false, $address2 = null, $company = null, $website = null, $logoUrl = null, $taxCode = null, $phone = null) {
        return ApiClient::Request('account/updateprofile', array(
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'address1' => $address1,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'countryID' => $countryID,
                    'deliveryReason' => $deliveryReason,
                    'marketingConsent' => $marketingConsent,
                    'address2' => $address2,
                    'company' => $company,
                    'website' => $website,
                    'logoUrl' => $logoUrl,
                    'taxCode' => $taxCode,
                    'phone' => $phone
        ));
    }

    /**
     * Updates settings of specified subaccount
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param bool $requiresEmailCredits True, if account needs credits to send emails. Otherwise, false
     * @param int $monthlyRefillCredits Amount of credits added to account automatically
     * @param bool $requiresLitmusCredits True, if account needs credits to send emails. Otherwise, false
     * @param bool $enableLitmusTest True, if account is able to send template tests to Litmus. Otherwise, false
     * @param int $dailySendLimit Amount of emails account can send daily
     * @param int $emailSizeLimit Maximum size of email including attachments in MB's
     * @param bool $enablePrivateIPRequest True, if account can request for private IP on its own. Otherwise, false
     * @param int $maxContacts Maximum number of contacts the account can havelkd
     * @param string $subAccountEmail Email address of sub-account
     * @param string $publicAccountID Public key of sub-account to update. Use subAccountEmail or publicAccountID not both.
     * @param ?ApiTypes\SendingPermission $sendingPermission Sending permission setting for account
     * @param ?bool $enableContactFeatures True, if you want to use Advanced Tools.  Otherwise, false
     * @param string $poolName Name of your custom IP Pool to be used in the sending process
     */
    public function UpdateSubAccountSettings($requiresEmailCredits = false, $monthlyRefillCredits = 0, $requiresLitmusCredits = false, $enableLitmusTest = false, $dailySendLimit = 50, $emailSizeLimit = 10, $enablePrivateIPRequest = false, $maxContacts = 0, $subAccountEmail = null, $publicAccountID = null, $sendingPermission = null, $enableContactFeatures = null, $poolName = null) {
        return ApiClient::Request('account/updatesubaccountsettings', array(
                    'requiresEmailCredits' => $requiresEmailCredits,
                    'monthlyRefillCredits' => $monthlyRefillCredits,
                    'requiresLitmusCredits' => $requiresLitmusCredits,
                    'enableLitmusTest' => $enableLitmusTest,
                    'dailySendLimit' => $dailySendLimit,
                    'emailSizeLimit' => $emailSizeLimit,
                    'enablePrivateIPRequest' => $enablePrivateIPRequest,
                    'maxContacts' => $maxContacts,
                    'subAccountEmail' => $subAccountEmail,
                    'publicAccountID' => $publicAccountID,
                    'sendingPermission' => $sendingPermission,
                    'enableContactFeatures' => $enableContactFeatures,
                    'poolName' => $poolName
        ));
    }

}

/**
 * Managing attachments uploaded to your account.
 */
class Attachment {

    /**
     * Permanently deletes attachment file from your account
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param long $attachmentID ID number of your attachment.
     */
    public function EEDelete($attachmentID) {
        return ApiClient::Request('attachment/delete', array(
                    'attachmentID' => $attachmentID
        ));
    }

    /**
     * Gets address of chosen Attachment
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $fileName Name of your file.
     * @param long $attachmentID ID number of your attachment.
     * @return File
     */
    public function Get($fileName, $attachmentID) {
        return ApiClient::getFile('attachment/get', array(
                    'fileName' => $fileName,
                    'attachmentID' => $attachmentID
        ));
    }

    /**
     * Lists your available Attachments in the given email
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $msgID ID number of selected message.
     * @return Array<ApiTypes\Attachment>
     */
    public function EEList($msgID) {
        return ApiClient::Request('attachment/list', array(
                    'msgID' => $msgID
        ));
    }

    /**
     * Lists all your available attachments
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @return Array<ApiTypes\Attachment>
     */
    public function ListAll() {
        return ApiClient::Request('attachment/listall');
    }

    /**
     * Permanently removes attachment file from your account
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $fileName Name of your file.
     */
    public function Remove($fileName) {
        return ApiClient::Request('attachment/remove', array(
                    'fileName' => $fileName
        ));
    }

    /**
     * Uploads selected file to the server using http form upload format (MIME multipart/form-data) or PUT method. The attachments expire after 30 days.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param File $attachmentFile Content of your attachment.
     * @return ApiTypes\Attachment
     */
    public function Upload($attachmentFile) {
        return ApiClient::Request('attachment/upload', array(), "POST", $attachmentFile);
    }

}

/**
 * Sending and monitoring progress of your Campaigns
 */
class Campaign {

    /**
     * Adds a campaign to the queue for processing based on the configuration
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param ApiTypes\Campaign $campaign Json representation of a campaign
     * @return int
     */
    public function Add($campaign) {
        return ApiClient::Request('campaign/add', array(
                    'campaign' => $campaign
        ));
    }

    /**
     * Copy selected campaign
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param int $channelID ID number of selected Channel.
     */
    public function EECopy($channelID) {
        return ApiClient::Request('campaign/copy', array(
                    'channelID' => $channelID
        ));
    }

    /**
     * Delete selected campaign
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param int $channelID ID number of selected Channel.
     */
    public function EEDelete($channelID) {
        return ApiClient::Request('campaign/delete', array(
                    'channelID' => $channelID
        ));
    }

    /**
     * Export selected campaigns to chosen file format.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param array<int> $channelIDs List of campaign IDs used for processing
     * @param ApiTypes\ExportFileFormats $fileFormat 
     * @param ApiTypes\CompressionFormat $compressionFormat FileResponse compression format. None or Zip.
     * @param string $fileName Name of your file.
     * @return ApiTypes\ExportLink
     */
    public function Export(array $channelIDs = array(), $fileFormat = ApiTypes\ExportFileFormats::Csv, $compressionFormat = ApiTypes\CompressionFormat::None, $fileName = null) {
        return ApiClient::Request('campaign/export', array(
                    'channelIDs' => (count($channelIDs) === 0) ? null : join(';', $channelIDs),
                    'fileFormat' => $fileFormat,
                    'compressionFormat' => $compressionFormat,
                    'fileName' => $fileName
        ));
    }

    /**
     * List all of your campaigns
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $search Text fragment used for searching.
     * @param int $offset How many items should be loaded ahead.
     * @param int $limit Maximum of loaded items.
     * @return Array<ApiTypes\CampaignChannel>
     */
    public function EEList($search = null, $offset = 0, $limit = 0) {
        return ApiClient::Request('campaign/list', array(
                    'search' => $search,
                    'offset' => $offset,
                    'limit' => $limit
        ));
    }

    /**
     * Updates a previously added campaign.  Only Active and Paused campaigns can be updated.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param ApiTypes\Campaign $campaign Json representation of a campaign
     * @return int
     */
    public function Update($campaign) {
        return ApiClient::Request('campaign/update', array(
                    'campaign' => $campaign
        ));
    }

}

/**
 * SMTP and HTTP API channels for grouping email delivery.
 */
class Channel {

    /**
     * Manually add a channel to your account to group email
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $name Descriptive name of the channel
     * @return string
     */
    public function Add($name) {
        return ApiClient::Request('channel/add', array(
                    'name' => $name
        ));
    }

    /**
     * Delete the channel.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $name The name of the channel to delete.
     */
    public function EEDelete($name) {
        return ApiClient::Request('channel/delete', array(
                    'name' => $name
        ));
    }

    /**
     * Export channels in CSV file format.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param array<string> $channelNames List of channel names used for processing
     * @param ApiTypes\CompressionFormat $compressionFormat FileResponse compression format. None or Zip.
     * @param string $fileName Name of your file.
     * @return File
     */
    public function ExportCsv($channelNames, $compressionFormat = ApiTypes\CompressionFormat::None, $fileName = null) {
        return ApiClient::getFile('channel/exportcsv', array(
                    'channelNames' => (count($channelNames) === 0) ? null : join(';', $channelNames),
                    'compressionFormat' => $compressionFormat,
                    'fileName' => $fileName
        ));
    }

    /**
     * Export channels in JSON file format.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param array<string> $channelNames List of channel names used for processing
     * @param ApiTypes\CompressionFormat $compressionFormat FileResponse compression format. None or Zip.
     * @param string $fileName Name of your file.
     * @return File
     */
    public function ExportJson($channelNames, $compressionFormat = ApiTypes\CompressionFormat::None, $fileName = null) {
        return ApiClient::getFile('channel/exportjson', array(
                    'channelNames' => (count($channelNames) === 0) ? null : join(';', $channelNames),
                    'compressionFormat' => $compressionFormat,
                    'fileName' => $fileName
        ));
    }

    /**
     * Export channels in XML file format.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param array<string> $channelNames List of channel names used for processing
     * @param ApiTypes\CompressionFormat $compressionFormat FileResponse compression format. None or Zip.
     * @param string $fileName Name of your file.
     * @return File
     */
    public function ExportXml($channelNames, $compressionFormat = ApiTypes\CompressionFormat::None, $fileName = null) {
        return ApiClient::getFile('channel/exportxml', array(
                    'channelNames' => (count($channelNames) === 0) ? null : join(';', $channelNames),
                    'compressionFormat' => $compressionFormat,
                    'fileName' => $fileName
        ));
    }

    /**
     * List all of your channels
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @return Array<ApiTypes\Channel>
     */
    public function EEList() {
        return ApiClient::Request('channel/list');
    }

    /**
     * Rename an existing channel.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $name The name of the channel to update.
     * @param string $newName The new name for the channel.
     * @return string
     */
    public function Update($name, $newName) {
        return ApiClient::Request('channel/update', array(
                    'name' => $name,
                    'newName' => $newName
        ));
    }

}

/**
 * Methods used to manage your Contacts.
 */
class Contact {

    /**
     * Activate contacts that are currently blocked.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param bool $activateAllBlocked Activate all your blocked contacts.  Passing True will override email list and activate all your blocked contacts.
     * @param array<string> $emails Comma delimited list of contact emails
     */
    public function ActivateBlocked($activateAllBlocked = false, array $emails = array()) {
        return ApiClient::Request('contact/activateblocked', array(
                    'activateAllBlocked' => $activateAllBlocked,
                    'emails' => (count($emails) === 0) ? null : join(';', $emails)
        ));
    }

    /**
     * Add a new contact and optionally to one of your lists.  Note that your API KEY is not required for this call.
     * @param string $publicAccountID Public key for limited access to your account such as contact/add so you can use it safely on public websites.
     * @param string $email Proper email address.
     * @param array<string> $publicListID ID code of list
     * @param array<string> $listName Name of your list.
     * @param string $title Title
     * @param string $firstName First name.
     * @param string $lastName Last name.
     * @param string $phone Phone number
     * @param string $mobileNumber Mobile phone number
     * @param string $notes Free form field of notes
     * @param string $gender Your gender
     * @param ?DateTime $birthDate Date of birth in YYYY-MM-DD format
     * @param string $city City.
     * @param string $state State or province.
     * @param string $postalCode Zip/postal code.
     * @param string $country Name of country.
     * @param string $organizationName Name of organization
     * @param string $website HTTP address of your website.
     * @param ?int $annualRevenue Annual revenue of contact
     * @param string $industry Industry contact works in
     * @param ?int $numberOfEmployees Number of employees
     * @param ApiTypes\ContactSource $source Specifies the way of uploading the contact
     * @param string $returnUrl URL to navigate to after account creation
     * @param string $sourceUrl URL from which request was sent.
     * @param string $activationReturnUrl The url to return the contact to after activation.
     * @param string $activationTemplate 
     * @param bool $sendActivation True, if you want to send activation email to this account. Otherwise, false
     * @param ?DateTime $consentDate Date of consent to send this contact(s) your email. If not provided current date is used for consent.
     * @param string $consentIP IP address of consent to send this contact(s) your email. If not provided your current public IP address is used for consent.
     * @param array<string, string> $field Custom contact field like firstname, lastname, city etc. Request parameters prefixed by field_ like field_firstname, field_lastname 
     * @param string $notifyEmail Emails, separated by semicolon, to which the notification about contact subscribing should be sent to
     * @return string
     */
    public function Add($publicAccountID, $email, array $publicListID = array(), array $listName = array(), $title = null, $firstName = null, $lastName = null, $phone = null, $mobileNumber = null, $notes = null, $gender = null, $birthDate = null, $city = null, $state = null, $postalCode = null, $country = null, $organizationName = null, $website = null, $annualRevenue = 0, $industry = null, $numberOfEmployees = 0, $source = ApiTypes\ContactSource::ContactApi, $returnUrl = null, $sourceUrl = null, $activationReturnUrl = null, $activationTemplate = null, $sendActivation = true, $consentDate = null, $consentIP = null, array $field = array(), $notifyEmail = null) {
        return ApiClient::Request('contact/add', array(
                    'publicAccountID' => $publicAccountID,
                    'email' => $email,
                    'publicListID' => (count($publicListID) === 0) ? null : join(';', $publicListID),
                    'listName' => (count($listName) === 0) ? null : join(';', $listName),
                    'title' => $title,
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'phone' => $phone,
                    'mobileNumber' => $mobileNumber,
                    'notes' => $notes,
                    'gender' => $gender,
                    'birthDate' => $birthDate,
                    'city' => $city,
                    'state' => $state,
                    'postalCode' => $postalCode,
                    'country' => $country,
                    'organizationName' => $organizationName,
                    'website' => $website,
                    'annualRevenue' => $annualRevenue,
                    'industry' => $industry,
                    'numberOfEmployees' => $numberOfEmployees,
                    'source' => $source,
                    'returnUrl' => $returnUrl,
                    'sourceUrl' => $sourceUrl,
                    'activationReturnUrl' => $activationReturnUrl,
                    'activationTemplate' => $activationTemplate,
                    'sendActivation' => $sendActivation,
                    'consentDate' => $consentDate,
                    'consentIP' => $consentIP,
                    'field' => $field,
                    'notifyEmail' => $notifyEmail
        ));
    }

    /**
     * Manually add or update a contacts status to Abuse, Bounced or Unsubscribed status (blocked).
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $email Proper email address.
     * @param ApiTypes\ContactStatus $status Name of status: Active, Engaged, Inactive, Abuse, Bounced, Unsubscribed.
     */
    public function AddBlocked($email, $status) {
        return ApiClient::Request('contact/addblocked', array(
                    'email' => $email,
                    'status' => $status
        ));
    }

    /**
     * Change any property on the contact record.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $email Proper email address.
     * @param string $name Name of the contact property you want to change.
     * @param string $value Value you would like to change the contact property to.
     */
    public function ChangeProperty($email, $name, $value) {
        return ApiClient::Request('contact/changeproperty', array(
                    'email' => $email,
                    'name' => $name,
                    'value' => $value
        ));
    }

    /**
     * Changes status of selected Contacts. You may provide RULE for selection or specify list of Contact IDs.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param ApiTypes\ContactStatus $status Name of status: Active, Engaged, Inactive, Abuse, Bounced, Unsubscribed.
     * @param string $rule Query used for filtering.
     * @param array<string> $emails Comma delimited list of contact emails
     * @param bool $allContacts True: Include every Contact in your Account. Otherwise, false
     */
    public function ChangeStatus($status, $rule = null, array $emails = array(), $allContacts = false) {
        return ApiClient::Request('contact/changestatus', array(
                    'status' => $status,
                    'rule' => $rule,
                    'emails' => (count($emails) === 0) ? null : join(';', $emails),
                    'allContacts' => $allContacts
        ));
    }

    /**
     * Returns number of Contacts, RULE specifies contact Status.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $rule Query used for filtering.
     * @param bool $allContacts True: Include every Contact in your Account. Otherwise, false
     * @return ApiTypes\ContactStatusCounts
     */
    public function CountByStatus($rule = null, $allContacts = false) {
        return ApiClient::Request('contact/countbystatus', array(
                    'rule' => $rule,
                    'allContacts' => $allContacts
        ));
    }

    /**
     * Permanantly deletes the contacts provided.  You can provide either a qualified rule or a list of emails (comma separated string).
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $rule Query used for filtering.
     * @param array<string> $emails Comma delimited list of contact emails
     * @param bool $allContacts True: Include every Contact in your Account. Otherwise, false
     */
    public function EEDelete($rule = null, array $emails = array(), $allContacts = false) {
        return ApiClient::Request('contact/delete', array(
                    'rule' => $rule,
                    'emails' => (count($emails) === 0) ? null : join(';', $emails),
                    'allContacts' => $allContacts
        ));
    }

    /**
     * Export selected Contacts to JSON.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param ApiTypes\ExportFileFormats $fileFormat 
     * @param string $rule Query used for filtering.
     * @param array<string> $emails Comma delimited list of contact emails
     * @param bool $allContacts True: Include every Contact in your Account. Otherwise, false
     * @param ApiTypes\CompressionFormat $compressionFormat FileResponse compression format. None or Zip.
     * @param string $fileName Name of your file.
     * @return ApiTypes\ExportLink
     */
    public function Export($fileFormat = ApiTypes\ExportFileFormats::Csv, $rule = null, array $emails = array(), $allContacts = false, $compressionFormat = ApiTypes\CompressionFormat::None, $fileName = null) {
        return ApiClient::Request('contact/export', array(
                    'fileFormat' => $fileFormat,
                    'rule' => $rule,
                    'emails' => (count($emails) === 0) ? null : join(';', $emails),
                    'allContacts' => $allContacts,
                    'compressionFormat' => $compressionFormat,
                    'fileName' => $fileName
        ));
    }

    /**
     * Finds all Lists and Segments this email belongs to.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $email Proper email address.
     * @return ApiTypes\ContactCollection
     */
    public function FindContact($email) {
        return ApiClient::Request('contact/findcontact', array(
                    'email' => $email
        ));
    }

    /**
     * List of Contacts for provided List
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $listName Name of your list.
     * @param int $limit Maximum of loaded items.
     * @param int $offset How many items should be loaded ahead.
     * @return Array<ApiTypes\Contact>
     */
    public function GetContactsByList($listName, $limit = 20, $offset = 0) {
        return ApiClient::Request('contact/getcontactsbylist', array(
                    'listName' => $listName,
                    'limit' => $limit,
                    'offset' => $offset
        ));
    }

    /**
     * List of Contacts for provided Segment
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $segmentName Name of your segment.
     * @param int $limit Maximum of loaded items.
     * @param int $offset How many items should be loaded ahead.
     * @return Array<ApiTypes\Contact>
     */
    public function GetContactsBySegment($segmentName, $limit = 20, $offset = 0) {
        return ApiClient::Request('contact/getcontactsbysegment', array(
                    'segmentName' => $segmentName,
                    'limit' => $limit,
                    'offset' => $offset
        ));
    }

    /**
     * List of all contacts. If you have not specified RULE, all Contacts will be listed.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $rule Query used for filtering.
     * @param bool $allContacts True: Include every Contact in your Account. Otherwise, false
     * @param int $limit Maximum of loaded items.
     * @param int $offset How many items should be loaded ahead.
     * @return Array<ApiTypes\Contact>
     */
    public function EEList($rule = null, $allContacts = false, $limit = 20, $offset = 0) {
        return ApiClient::Request('contact/list', array(
                    'rule' => $rule,
                    'allContacts' => $allContacts,
                    'limit' => $limit,
                    'offset' => $offset
        ));
    }

    /**
     * Load blocked contacts
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param array<ApiTypes\ContactStatus> $statuses List of comma separated message statuses: 0 or all, 1 for ReadyToSend, 2 for InProgress, 4 for Bounced, 5 for Sent, 6 for Opened, 7 for Clicked, 8 for Unsubscribed, 9 for Abuse Report
     * @param string $search List of blocked statuses: Abuse, Bounced or Unsubscribed
     * @param int $limit Maximum of loaded items.
     * @param int $offset How many items should be loaded ahead.
     * @return Array<ApiTypes\BlockedContact>
     */
    public function LoadBlocked($statuses, $search = null, $limit = 0, $offset = 0) {
        return ApiClient::Request('contact/loadblocked', array(
                    'statuses' => (count($statuses) === 0) ? null : join(';', $statuses),
                    'search' => $search,
                    'limit' => $limit,
                    'offset' => $offset
        ));
    }

    /**
     * Load detailed contact information
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $email Proper email address.
     * @return ApiTypes\Contact
     */
    public function LoadContact($email) {
        return ApiClient::Request('contact/loadcontact', array(
                    'email' => $email
        ));
    }

    /**
     * Shows detailed history of chosen Contact.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $email Proper email address.
     * @param int $limit Maximum of loaded items.
     * @param int $offset How many items should be loaded ahead.
     * @return Array<ApiTypes\ContactHistory>
     */
    public function LoadHistory($email, $limit = 0, $offset = 0) {
        return ApiClient::Request('contact/loadhistory', array(
                    'email' => $email,
                    'limit' => $limit,
                    'offset' => $offset
        ));
    }

    /**
     * Add new Contact to one of your Lists.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param array<string> $emails Comma delimited list of contact emails
     * @param string $firstName First name.
     * @param string $lastName Last name.
     * @param string $title Title
     * @param string $organization Name of organization
     * @param string $industry Industry contact works in
     * @param string $city City.
     * @param string $country Name of country.
     * @param string $state State or province.
     * @param string $zip Zip/postal code.
     * @param string $publicListID ID code of list
     * @param string $listName Name of your list.
     * @param ApiTypes\ContactStatus $status Name of status: Active, Engaged, Inactive, Abuse, Bounced, Unsubscribed.
     * @param string $notes Free form field of notes
     * @param ?DateTime $consentDate Date of consent to send this contact(s) your email. If not provided current date is used for consent.
     * @param string $consentIP IP address of consent to send this contact(s) your email. If not provided your current public IP address is used for consent.
     * @param string $notifyEmail Emails, separated by semicolon, to which the notification about contact subscribing should be sent to
     */
    public function QuickAdd($emails, $firstName = null, $lastName = null, $title = null, $organization = null, $industry = null, $city = null, $country = null, $state = null, $zip = null, $publicListID = null, $listName = null, $status = ApiTypes\ContactStatus::Active, $notes = null, $consentDate = null, $consentIP = null, $notifyEmail = null) {
        return ApiClient::Request('contact/quickadd', array(
                    'emails' => (count($emails) === 0) ? null : join(';', $emails),
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'title' => $title,
                    'organization' => $organization,
                    'industry' => $industry,
                    'city' => $city,
                    'country' => $country,
                    'state' => $state,
                    'zip' => $zip,
                    'publicListID' => $publicListID,
                    'listName' => $listName,
                    'status' => $status,
                    'notes' => $notes,
                    'consentDate' => $consentDate,
                    'consentIP' => $consentIP,
                    'notifyEmail' => $notifyEmail
        ));
    }

    /**
     * Update selected contact. Omitted contact's fields will be reset by default (see the clearRestOfFields parameter)
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $email Proper email address.
     * @param string $firstName First name.
     * @param string $lastName Last name.
     * @param string $organizationName Name of organization
     * @param string $title Title
     * @param string $city City.
     * @param string $state State or province.
     * @param string $country Name of country.
     * @param string $zip Zip/postal code.
     * @param string $birthDate Date of birth in YYYY-MM-DD format
     * @param string $gender Your gender
     * @param string $phone Phone number
     * @param ?bool $activate True, if Contact should be activated. Otherwise, false
     * @param string $industry Industry contact works in
     * @param int $numberOfEmployees Number of employees
     * @param string $annualRevenue Annual revenue of contact
     * @param int $purchaseCount Number of purchases contact has made
     * @param string $firstPurchase Date of first purchase in YYYY-MM-DD format
     * @param string $lastPurchase Date of last purchase in YYYY-MM-DD format
     * @param string $notes Free form field of notes
     * @param string $websiteUrl Website of contact
     * @param string $mobileNumber Mobile phone number
     * @param string $faxNumber Fax number
     * @param string $linkedInBio Biography for Linked-In
     * @param int $linkedInConnections Number of Linked-In connections
     * @param string $twitterBio Biography for Twitter
     * @param string $twitterUsername User name for Twitter
     * @param string $twitterProfilePhoto URL for Twitter photo
     * @param int $twitterFollowerCount Number of Twitter followers
     * @param int $pageViews Number of page views
     * @param int $visits Number of website visits
     * @param bool $clearRestOfFields States if the fields that were omitted in this request are to be reset or should they be left with their current value
     * @param array<string, string> $field Custom contact field like firstname, lastname, city etc. Request parameters prefixed by field_ like field_firstname, field_lastname 
     * @return ApiTypes\Contact
     */
    public function Update($email, $firstName = null, $lastName = null, $organizationName = null, $title = null, $city = null, $state = null, $country = null, $zip = null, $birthDate = null, $gender = null, $phone = null, $activate = null, $industry = null, $numberOfEmployees = 0, $annualRevenue = null, $purchaseCount = 0, $firstPurchase = null, $lastPurchase = null, $notes = null, $websiteUrl = null, $mobileNumber = null, $faxNumber = null, $linkedInBio = null, $linkedInConnections = 0, $twitterBio = null, $twitterUsername = null, $twitterProfilePhoto = null, $twitterFollowerCount = 0, $pageViews = 0, $visits = 0, $clearRestOfFields = true, array $field = array()) {
        return ApiClient::Request('contact/update', array(
                    'email' => $email,
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'organizationName' => $organizationName,
                    'title' => $title,
                    'city' => $city,
                    'state' => $state,
                    'country' => $country,
                    'zip' => $zip,
                    'birthDate' => $birthDate,
                    'gender' => $gender,
                    'phone' => $phone,
                    'activate' => $activate,
                    'industry' => $industry,
                    'numberOfEmployees' => $numberOfEmployees,
                    'annualRevenue' => $annualRevenue,
                    'purchaseCount' => $purchaseCount,
                    'firstPurchase' => $firstPurchase,
                    'lastPurchase' => $lastPurchase,
                    'notes' => $notes,
                    'websiteUrl' => $websiteUrl,
                    'mobileNumber' => $mobileNumber,
                    'faxNumber' => $faxNumber,
                    'linkedInBio' => $linkedInBio,
                    'linkedInConnections' => $linkedInConnections,
                    'twitterBio' => $twitterBio,
                    'twitterUsername' => $twitterUsername,
                    'twitterProfilePhoto' => $twitterProfilePhoto,
                    'twitterFollowerCount' => $twitterFollowerCount,
                    'pageViews' => $pageViews,
                    'visits' => $visits,
                    'clearRestOfFields' => $clearRestOfFields,
                    'field' => $field
        ));
    }

    /**
     * Upload contacts in CSV file.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param int $listID ID number of selected list.
     * @param File $contactFile Name of CSV file with Contacts.
     * @param ApiTypes\ContactStatus $status Name of status: Active, Engaged, Inactive, Abuse, Bounced, Unsubscribed.
     * @param ?DateTime $consentDate Date of consent to send this contact(s) your email. If not provided current date is used for consent.
     * @param string $consentIP IP address of consent to send this contact(s) your email. If not provided your current public IP address is used for consent.
     * @return int
     */
    public function Upload($listID, $contactFile, $status = ApiTypes\ContactStatus::Active, $consentDate = null, $consentIP = null) {
        return ApiClient::Request('contact/upload', array(
                    'listID' => $listID,
                    'status' => $status,
                    'consentDate' => $consentDate,
                    'consentIP' => $consentIP
                        ), "POST", $contactFile);
    }

}

/**
 * Managing sender domains. Creating new entries and validating domain records.
 */
class Domain {

    /**
     * Add new domain to account
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $domain Name of selected domain.
     */
    public function Add($domain) {
        return ApiClient::Request('domain/add', array(
                    'domain' => $domain
        ));
    }

    /**
     * Deletes configured domain from account
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $domain Name of selected domain.
     */
    public function EEDelete($domain) {
        return ApiClient::Request('domain/delete', array(
                    'domain' => $domain
        ));
    }

    /**
     * Lists all domains configured for this account.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @return Array<ApiTypes\DomainDetail>
     */
    public function EEList() {
        return ApiClient::Request('domain/list');
    }

    /**
     * Verification of email addres set for domain.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $domain Default email sender, example: mail@yourdomain.com
     */
    public function SetDefault($domain) {
        return ApiClient::Request('domain/setdefault', array(
                    'domain' => $domain
        ));
    }

    /**
     * Verification of DKIM record for domain
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $domain Name of selected domain.
     */
    public function VerifyDkim($domain) {
        return ApiClient::Request('domain/verifydkim', array(
                    'domain' => $domain
        ));
    }

    /**
     * Verification of MX record for domain
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $domain Name of selected domain.
     */
    public function VerifyMX($domain) {
        return ApiClient::Request('domain/verifymx', array(
                    'domain' => $domain
        ));
    }

    /**
     * Verification of SPF record for domain
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $domain Name of selected domain.
     */
    public function VerifySpf($domain) {
        return ApiClient::Request('domain/verifyspf', array(
                    'domain' => $domain
        ));
    }

    /**
     * Verification of tracking CNAME record for domain
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $domain Name of selected domain.
     */
    public function VerifyTracking($domain) {
        return ApiClient::Request('domain/verifytracking', array(
                    'domain' => $domain
        ));
    }

}

/**
 * 
 */
class Email {

    /**
     * Get email batch status
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $transactionID Transaction identifier
     * @param bool $showFailed Include Bounced email addresses.
     * @param bool $showDelivered Include Sent email addresses.
     * @param bool $showPending Include Ready to send email addresses.
     * @param bool $showOpened Include Opened email addresses.
     * @param bool $showClicked Include Clicked email addresses.
     * @param bool $showAbuse Include Reported as abuse email addresses.
     * @param bool $showUnsubscribed Include Unsubscribed email addresses.
     * @param bool $showErrors Include error messages for bounced emails.
     * @param bool $showMessageIDs Include all MessageIDs for this transaction
     * @return ApiTypes\EmailJobStatus
     */
    public function GetStatus($transactionID, $showFailed = false, $showDelivered = false, $showPending = false, $showOpened = false, $showClicked = false, $showAbuse = false, $showUnsubscribed = false, $showErrors = false, $showMessageIDs = false) {
        return ApiClient::Request('email/getstatus', array(
                    'transactionID' => $transactionID,
                    'showFailed' => $showFailed,
                    'showDelivered' => $showDelivered,
                    'showPending' => $showPending,
                    'showOpened' => $showOpened,
                    'showClicked' => $showClicked,
                    'showAbuse' => $showAbuse,
                    'showUnsubscribed' => $showUnsubscribed,
                    'showErrors' => $showErrors,
                    'showMessageIDs' => $showMessageIDs
        ));
    }

    /**
     * Submit emails. The HTTP POST request is suggested. The default, maximum (accepted by us) size of an email is 10 MB in total, with or without attachments included. For suggested implementations please refer to https://elasticemail.com/support/http-api/
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $subject Email subject
     * @param string $from From email address
     * @param string $fromName Display name for from email address
     * @param string $sender Email address of the sender
     * @param string $senderName Display name sender
     * @param string $msgFrom Optional parameter. Sets FROM MIME header.
     * @param string $msgFromName Optional parameter. Sets FROM name of MIME header.
     * @param string $replyTo Email address to reply to
     * @param string $replyToName Display name of the reply to address
     * @param array<string> $to List of email recipients (each email is treated separately, like a BCC). Separated by comma or semicolon. We suggest using the "msgTo" parameter if backward compatibility with API version 1 is not a must.
     * @param array<string> $msgTo Optional parameter. Will be ignored if the 'to' parameter is also provided. List of email recipients (visible to all other recipients of the message as TO MIME header). Separated by comma or semicolon.
     * @param array<string> $msgCC Optional parameter. Will be ignored if the 'to' parameter is also provided. List of email recipients (visible to all other recipients of the message as CC MIME header). Separated by comma or semicolon.
     * @param array<string> $msgBcc Optional parameter. Will be ignored if the 'to' parameter is also provided. List of email recipients (each email is treated seperately). Separated by comma or semicolon.
     * @param array<string> $lists The name of a contact list you would like to send to. Separate multiple contact lists by commas or semicolons.
     * @param array<string> $segments The name of a segment you would like to send to. Separate multiple segments by comma or semicolon. Insert "0" for all Active contacts.
     * @param string $mergeSourceFilename File name one of attachments which is a CSV list of Recipients.
     * @param string $channel An ID field (max 191 chars) that can be used for reporting [will default to HTTP API or SMTP API]
     * @param string $bodyHtml Html email body
     * @param string $bodyText Text email body
     * @param string $charset Text value of charset encoding for example: iso-8859-1, windows-1251, utf-8, us-ascii, windows-1250 and moreÄ‚Ë?Ă�?â€šÂ¬Ă‚Â¦
     * @param string $charsetBodyHtml Sets charset for body html MIME part (overrides default value from charset parameter)
     * @param string $charsetBodyText Sets charset for body text MIME part (overrides default value from charset parameter)
     * @param ApiTypes\EncodingType $encodingType 0 for None, 1 for Raw7Bit, 2 for Raw8Bit, 3 for QuotedPrintable, 4 for Base64 (Default), 5 for Uue  note that you can also provide the text version such as "Raw7Bit" for value 1.  NOTE: Base64 or QuotedPrintable is recommended if you are validating your domain(s) with DKIM.
     * @param string $template The name of an email template you have created in your account.
     * @param array<File> $attachmentFiles Attachment files. These files should be provided with the POST multipart file upload, not directly in the request's URL. Should also include merge CSV file
     * @param array<string, string> $headers Optional Custom Headers. Request parameters prefixed by headers_ like headers_customheader1, headers_customheader2. Note: a space is required after the colon before the custom header value. headers_xmailer=xmailer: header-value1
     * @param string $postBack Optional header returned in notifications.
     * @param array<string, string> $merge Request parameters prefixed by merge_ like merge_firstname, merge_lastname. If sending to a template you can send merge_ fields to merge data with the template. Template fields are entered with {firstname}, {lastname} etc.
     * @param string $timeOffSetMinutes Number of minutes in the future this email should be sent
     * @param string $poolName Name of your custom IP Pool to be used in the sending process
     * @param bool $isTransactional True, if email is transactional (non-bulk, non-marketing, non-commercial). Otherwise, false
     * @return ApiTypes\EmailSend
     */
    public function Send($subject = null, $from = null, $fromName = null, $sender = null, $senderName = null, $msgFrom = null, $msgFromName = null, $replyTo = null, $replyToName = null, array $to = array(), array $msgTo = array(), array $msgCC = array(), array $msgBcc = array(), array $lists = array(), array $segments = array(), $mergeSourceFilename = null, $channel = null, $bodyHtml = null, $bodyText = null, $charset = null, $charsetBodyHtml = null, $charsetBodyText = null, $encodingType = ApiTypes\EncodingType::None, $template = null, array $attachmentFiles = array(), array $headers = array(), $postBack = null, array $merge = array(), $timeOffSetMinutes = null, $poolName = null, $isTransactional = false) {
        return ApiClient::Request('email/send', array(
                    'subject' => $subject,
                    'from' => $from,
                    'fromName' => $fromName,
                    'sender' => $sender,
                    'senderName' => $senderName,
                    'msgFrom' => $msgFrom,
                    'msgFromName' => $msgFromName,
                    'replyTo' => $replyTo,
                    'replyToName' => $replyToName,
                    'to' => (count($to) === 0) ? null : join(';', $to),
                    'msgTo' => (count($msgTo) === 0) ? null : join(';', $msgTo),
                    'msgCC' => (count($msgCC) === 0) ? null : join(';', $msgCC),
                    'msgBcc' => (count($msgBcc) === 0) ? null : join(';', $msgBcc),
                    'lists' => (count($lists) === 0) ? null : join(';', $lists),
                    'segments' => (count($segments) === 0) ? null : join(';', $segments),
                    'mergeSourceFilename' => $mergeSourceFilename,
                    'channel' => $channel,
                    'bodyHtml' => $bodyHtml,
                    'bodyText' => $bodyText,
                    'charset' => $charset,
                    'charsetBodyHtml' => $charsetBodyHtml,
                    'charsetBodyText' => $charsetBodyText,
                    'encodingType' => $encodingType,
                    'template' => $template,
                    'headers' => $headers,
                    'postBack' => $postBack,
                    'merge' => $merge,
                    'timeOffSetMinutes' => $timeOffSetMinutes,
                    'poolName' => $poolName,
                    'isTransactional' => $isTransactional
                        ), "POST", $attachmentFiles);
    }

    /**
     * Detailed status of a unique email sent through your account. Returns a 'Email has expired and the status is unknown.' error, if the email has not been fully processed yet.
     * @param string $apikey ApiKey that gives you access to our SMTP and HTTP API's.
     * @param string $messageID Unique identifier for this email.
     * @return ApiTypes\EmailStatus
     */
    public function Status($messageID) {
        return ApiClient::Request('email/status', array(
                    'messageID' => $messageID
        ));
    }

    /**
     * View email
     * @param string $messageID Message identifier
     * @return ApiTypes\EmailView
     */
    public function View($messageID) {
        return ApiClient::Request('email/view', array(
                    'messageID' => $messageID
        ));
    }

}

namespace ApiTypes;

/**
 * Detailed information about your account
 */
class Account {

    /**
     * Code used for tax purposes.
     */
    public /* string */ $TaxCode;

    /**
     * Public key for limited access to your account such as contact/add so you can use it safely on public websites.
     */
    public /* string */ $PublicAccountID;

    /**
     * ApiKey that gives you access to our SMTP and HTTP API's.
     */
    public /* string */ $ApiKey;

    /**
     * Second ApiKey that gives you access to our SMTP and HTTP API's.  Used mainly for changing ApiKeys without disrupting services.
     */
    public /* string */ $ApiKey2;

    /**
     * True, if account is a subaccount. Otherwise, false
     */
    public /* bool */ $IsSub;

    /**
     * The number of subaccounts this account has.
     */
    public /* long */ $SubAccountsCount;

    /**
     * Number of status: 1 - Active
     */
    public /* int */ $StatusNumber;

    /**
     * Account status: Active
     */
    public /* string */ $StatusFormatted;

    /**
     * URL form for payments.
     */
    public /* string */ $PaymentFormUrl;

    /**
     * URL to your logo image.
     */
    public /* string */ $LogoUrl;

    /**
     * HTTP address of your website.
     */
    public /* string */ $Website;

    /**
     * True: Turn on or off ability to send mails under your brand. Otherwise, false
     */
    public /* bool */ $EnablePrivateBranding;

    /**
     * Address to your support.
     */
    public /* string */ $SupportLink;

    /**
     * Subdomain for your rebranded service
     */
    public /* string */ $PrivateBrandingUrl;

    /**
     * First name.
     */
    public /* string */ $FirstName;

    /**
     * Last name.
     */
    public /* string */ $LastName;

    /**
     * Company name.
     */
    public /* string */ $Company;

    /**
     * First line of address.
     */
    public /* string */ $Address1;

    /**
     * Second line of address.
     */
    public /* string */ $Address2;

    /**
     * City.
     */
    public /* string */ $City;

    /**
     * State or province.
     */
    public /* string */ $State;

    /**
     * Zip/postal code.
     */
    public /* string */ $Zip;

    /**
     * Numeric ID of country. A file with the list of countries is available <a href="http://api.elasticemail.com/public/countries"><b>here</b></a>
     */
    public /* ?int */ $CountryID;

    /**
     * Phone number
     */
    public /* string */ $Phone;

    /**
     * Proper email address.
     */
    public /* string */ $Email;

    /**
     * URL for affiliating.
     */
    public /* string */ $AffiliateLink;

    /**
     * Numeric reputation
     */
    public /* double */ $Reputation;

    /**
     * Amount of emails sent from this account
     */
    public /* long */ $TotalEmailsSent;

    /**
     * Amount of emails sent from this account
     */
    public /* ?long */ $MonthlyEmailsSent;

    /**
     * Amount of emails sent from this account
     */
    public /* decimal */ $Credit;

    /**
     * Amount of email credits
     */
    public /* int */ $EmailCredits;

    /**
     * Amount of emails sent from this account
     */
    public /* decimal */ $PricePerEmail;

    /**
     * Why your clients are receiving your emails.
     */
    public /* string */ $DeliveryReason;

    /**
     * URL for making payments.
     */
    public /* string */ $AccountPaymentUrl;

    /**
     * Address of SMTP server.
     */
    public /* string */ $Smtp;

    /**
     * Address of alternative SMTP server.
     */
    public /* string */ $SmtpAlternative;

    /**
     * Status of automatic payments configuration.
     */
    public /* string */ $AutoCreditStatus;

    /**
     * When AutoCreditStatus is Enabled, the credit level that triggers the credit to be recharged.
     */
    public /* decimal */ $AutoCreditLevel;

    /**
     * When AutoCreditStatus is Enabled, the amount of credit to be recharged.
     */
    public /* decimal */ $AutoCreditAmount;

    /**
     * Amount of emails account can send daily
     */
    public /* int */ $DailySendLimit;

    /**
     * Creation date.
     */
    public /* DateTime */ $DateCreated;

    /**
     * True, if you have enabled link tracking. Otherwise, false
     */
    public /* bool */ $LinkTracking;

    /**
     * Type of content encoding
     */
    public /* string */ $ContentTransferEncoding;

    /**
     * Amount of Litmus credits
     */
    public /* decimal */ $LitmusCredits;

    /**
     * Enable advanced tools on your Account.
     */
    public /* bool */ $EnableContactFeatures;

    /**
     * 
     */
    public /* bool */ $NeedsSMSVerification;

}

/**
 * 
 * Enum class
 */
abstract class APIKeyAction {

    /**
     * Add an additional APIKey to your Account.
     */
    const Add = 1;

    /**
     * Change this APIKey to a new one.
     */
    const Change = 2;

    /**
     * Delete this APIKey
     */
    const EEDelete = 3;

}

/**
 * Attachment data
 */
class Attachment {

    /**
     * Name of your file.
     */
    public /* string */ $FileName;

    /**
     * ID number of your attachment
     */
    public /* string */ $ID;

    /**
     * Size of your attachment.
     */
    public /* int */ $Size;

}

/**
 * 
 */
class EmailSend {

    /**
     * ID number of transaction
     */
    public /* string */ $TransactionID;

    /**
     * Unique identifier for this email.
     */
    public /* string */ $MessageID;

}

/**
 * Status information of the specified email
 */
class EmailStatus {

    /**
     * Email address this email was sent from.
     */
    public /* string */ $From;

    /**
     * Email address this email was sent to.
     */
    public /* string */ $To;

    /**
     * Date the email was submitted.
     */
    public /* DateTime */ $Date;

    /**
     * Value of email's status
     */
    public /* ApiTypes\LogJobStatus */ $Status;

    /**
     * Name of email's status
     */
    public /* string */ $StatusName;

    /**
     * Date of last status change.
     */
    public /* DateTime */ $StatusChangeDate;

    /**
     * Detailed error or bounced message.
     */
    public /* string */ $ErrorMessage;

    /**
     * ID number of transaction
     */
    public /* Guid */ $TransactionID;

}

/**
 * Email details formatted in json
 */
class EmailView {

    /**
     * Body (text) of your message.
     */
    public /* string */ $Body;

    /**
     * Default subject of email.
     */
    public /* string */ $Subject;

    /**
     * Starting date for search in YYYY-MM-DDThh:mm:ss format.
     */
    public /* string */ $From;

}

/**
 * Encoding type for the email headers
 * Enum class
 */
abstract class EncodingType {

    /**
     * Encoding of the email is provided by the sender and not altered.
     */
    const UserProvided = -1;

    /**
     * No endcoding is set for the email.
     */
    const None = 0;

    /**
     * Encoding of the email is in Raw7bit format.
     */
    const Raw7bit = 1;

    /**
     * Encoding of the email is in Raw8bit format.
     */
    const Raw8bit = 2;

    /**
     * Encoding of the email is in QuotedPrintable format.
     */
    const QuotedPrintable = 3;

    /**
     * Encoding of the email is in Base64 format.
     */
    const Base64 = 4;

    /**
     * Encoding of the email is in Uue format.
     */
    const Uue = 5;

}

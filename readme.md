![alt screenshot](assets/sender_header.png)

# Elastic Email Sender

This plugin reconfigures the wp_mail() function to send email using REST API (via Elastic Email) instead of SMTP and creates an options page that allows you to specify various options.

## Description

Elastic Email Sender allows you to connect your WordPress with powerful, low-cost Elastic Email API and send up to 150,000 emails free per month!
Please follow the information below and find out more about how we can help you send your emails in a more efficient way.
In case of any questions or concerns, feel free to contact us anytime.

## What is Elastic Email Sender plugin?

Elastic Email Sender is an easy way to maintain all the aspects related to your email campaigns. From creating and sending your emails to monitoring and managing campaigns stats.
Elastic Email Sender replaces WordPress default wp_mail() function by using an API integration with Elastic Email to send an outgoing email from your WordPress installation.
Thanks to this, you can track all the parameters of your delivery, use Private IP addresses to get full control over your sending IP address, maintain reputation and delivery, and secure your data better than ever. You can also use your own domain and analyze your data with ease.

Elastic Email Sender is compatible with almost every solution available on the market including WooCommerce and Contact Form 7.

## How to get started?

Just sign up to your [Elastic Email account](https://elasticemail.com/account/#/settings/apiconfiguration), copy the API Key and then, please log in to your WordPress dashboard, add the Elastic Email Sender plugin and paste there the API Key from your Elastic Email account.


### Installing

To connect WordPress to Elastic Email:
1. Log in to your WordPress dashboard and click Plugins in the left sidebar.
2. Click Add New at the top of the page and then, search for “Elastic Email Sender” and click “Install Now”.
3. Alternatively, download the plugin and upload the content of “elastic-email-sender.zip” to your plugins directory, which usually is “/wp-content/plugins/”.
4. Click Activate Plugin.
5. Enter your [Elastic Email API key](https://elasticemail.com/account/#/settings/apiconfiguration) in the plugin settings, and click Save Changes.
6. If you do it successfully, you will see “You are connected as” and new options will appear under “Elastic Email” in the left sidebar.

## Frequently Asked Questions

### Where can I find more details?
Please take a look at the [Elastic Email resources](https://elasticemail.com/support/) first.
If you can’t find the answer, please contact our [Support Team](http://support.elasticemail.com/discussion/new).

### How to get started with Elastic Email?
Start with Elastic Email by creating a new account on our [website](https://elasticemail.com/).

### What do I have to do to get 150,000 free emails per month?
To get the 150,000 emails free per month, you need to sign up for a free Elastic Email account, complete your personal data and successfully verify your sender domain.

### How to setup a domain to start sending?
Find out [how to verify your domain](https://elasticemail.com/support/user-interface/settings/your-domain/) on our Resources page.

### Where do I find Elastic Email API Key?
You can find the Elastic Email API Key in Settings/SMTP/API [account](https://elasticemail.com/account/#/settings/apiconfiguration).

### Where can I find private IP address settings?
All the details about the private IPs are available in Settings/Private IPs [account](https://elasticemail.com/account/#/settings/privateips).

### I can’t send any attachments.
Make sure you [allowed for the use of custom headers](https://elasticemail.com/account/#/settings/sending) by checking "Allow Custom Headers" option in Settings/Sending.

### Are my mail available also as a plain text?
Please make sure that you have the “Auto Create Text Body” turned on in your [Sending Settings](https://elasticemail.com/account/#/settings/sending). If yes, your emails are also available as a plain text.

### Where do I find delivery status and statistics?
All the data about your delivery statuses and campaigns stats are available in Reports [account](https://elasticemail.com/account/#/reports).

### Where can I find out what was sent?
You can see your reports and sending history in Reports/Email logs. Keep in mind that logs older than 35 days are not stored. [account](https://elasticemail.com/account/#/reports/emails).

## Versioning

###### 1.0.11
* Buxfix -integration with Elastic Email Subscribe Form

###### 1.0.10
* Integration with Elastic Email Subscribe Form

###### 1.0.9
* Visual changes and adding compatibility with the Elastic Email Subscribe Form

###### 1.0.8
* Bugfix - WP_Bakery conflict
* Bugfix - WooCommerce: undefined variables

###### 1.0.7
* Internationalization

###### 1.0.6
* Isolating styles in admin
* Checking account limite

###### 1.0.5
* Bugfix - overwriting styles

###### 1.0.4
* Bugfix - wp_error friendly message

###### 1.0.3
* Bugfix - returning false

###### 1.0.2
* Added reports panel
* Added checking the status and limit of the account
* Performance improvement
* Bugfix

###### 1.0.1
* Bugfix in sending html and text messages.

###### 1.0
* Public release

## Authors
Elastic Email Inc.

## License
MIT License

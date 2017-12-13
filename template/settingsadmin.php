<?php
defined('EE_ADMIN') OR die('No direct access allowed.');

if (isset($_GET['settings-updated'])):
    ?>
    <div id="message" class="updated">
        <p><strong><?php _e('Settings saved.') ?></strong></p>
    </div>
<?php endif; ?>
<div class="row eecontainer">
    <div class="col-7" >
        <div class="header">
            <div class="logo">
                <?php echo '<img src="' . esc_url(plugins_url('/assets/images/icon.png', dirname(__FILE__))) . '" > ' ?>
            </div>
            <div class="pagetitle">
                <h1>General Settings</h1>
            </div>
        </div>
        <h4 class="eeh4">
            Welcome to Elastic Email WordPress Plugin!<br/> From now on, you can send your emails in the fastest and most reliable way!<br/>
            Just one quick step and you will be ready to rock your subscribers' inbox.<br/><br/>
            Fill in the details about the main configuration of Elastic Email connections.
        </h4>

        <form method="post" action="options.php">
            <?php
            settings_fields('ee_option_group');
            do_settings_sections('ee-settings');
            ?>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row">Connection Test:</th>
                        <td> <span class="<?= (empty($error) === true) ? 'ee_success' : 'ee_error' ?>">
                                <?= (empty($error) === true) ? 'Connected' : 'Connection error, check your API key. <a href="https://elasticemail.com/support/user-interface/settings/smtp-api/" target="_blank">Read more</a' ?>
                            </span></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Account status:</th>
                        <td>
                            <?php
                            if (isset($accountstatus)) {
                                if ($accountstatus === 1) {
                                    $accountstatusname = '<span class="account-status-active">Active</span>';
                                } else {
                                    $accountstatusname = '<span class="account-status-deactive">Please conect to Elastic Email API or complete the profile <a href="https://elasticemail.com/account/#/account/profile"> Complete your profile </a> or connect to Elastic Email API to start using the plugin.</span>';
                                }
                            } else {
                                $accountstatusname = '<span class="account-status-deactive">Please conect to Elastic Email API or complete the profile <a href="https://elasticemail.com/account/#/account/profile"> Complete your profile </a> or connect to Elastic Email API to start using the plugin.</span>';
                            }
                            echo $accountstatusname;
                            ?>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Account limit:</th>
                        <td>
                            <?php
                            if (isset($accountdailysendlimit)) {
                                if ($accountdailysendlimit < 5) {
                                    $accountlimitspan = '<span class="ok-account">No limit</span>';
                                    $tooltip = 'Lucky you! Your account has no daily limits! Check out our other <a href="http://elasticemail.com/pricing"> pricing plans </a> and discover unlimited possibilities of your account.</a>';
                                } else {
                                    if ($accountdailysendlimit <= 50 && $accountdailysendlimit > 5) {
                                        $accountlimitspan = '<span class="standard-account">5</span>';
                                        $tooltip = 'Oops! Seems that your daily limit exceeded. Fill out your profile to get unlimited possibilities.';
                                    } else {
                                        if ($accountdailysendlimit <= 5000) {
                                            $accountlimitspan = '<span class="standard-account">5 000</span>';
                                            $tooltip = 'Your account is limited to 5,000 free emails per day. Check out our <a href="http://elasticemail.com/pricing"> pricing plans </a> and take your campaigns to the next level!</a>';
                                        }
                                    }
                                }
                            } else {
                                $accountlimit = '';
                                $accountlimitspan = '-------';
                                $tooltip = 'Seems that you might have some limits on your account. Please check out your account settings to unlock more options.';
                            }

                            echo $accountlimitspan;
                            ?>

                            <div class="tooltip"><?php echo '<img class="tootlip-icon" src="' . esc_url(plugins_url('/assets/images/info.svg', dirname(__FILE__))) . '" > ' ?>
                                <span class="tooltiptext">
                                    <?php echo $tooltip; ?>
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button(); ?>
        </form>
        <?php if (empty($error) === false) { ?> Do not have an account yet? <a href="https://elasticemail.com/account#/create-account" target="_blank" title="First 1000 emails for free.">Create your account now</a>!<br/>
            <a href="http://elasticemail.com/transactional-email" target="_blank">Tell me more about it</a>
        <?php } ?>
        <!-- add link -->
        <h4>
            Want to use this plugin in a different language version? <a href="http://support.elasticemail.com/">Let us know or help us translate it!</a>
        </h4>
        <h4 class="h4footer">
            Share your experience of using Elastic Email WordPress Plugin by <a href="https://wordpress.org/support/plugin/elastic-email-sender/reviews/#new-post">rating us here.</a> Thanks!
        </h4>
    </div>
    <div class="col-5 eemarketing">
        <h2 class="eeh2">Let us help you send better emails!</h2>
        <h4 class="footertext">
            If you are new to Elastic Email, feel free to visit our <a href="https://elasticemail.com">website</a> and find out how our comprehensive set of tools will help you reach your goals or get premium email marketing tools at a fraction of what you're paying now!
        </h4>
        <hr>
        <h4 class="eeh4">If you already use Elastic Email to send your emails, you can subscribe to our monthly updates to start receiving the latest email news, tips, best practices and more.</h4>
        <?php if (isset($_GET['subscribe']) === false) { ?>
            <form action="https://api.elasticemail.com/contact/add?version=2" method="post">
                <fieldset style="border:none;">
                    <input type="hidden" name="publicaccountid" value="49540e0f-2e09-4101-a05d-5032842b99d3">
                    <input type="hidden" name="returnUrl" value="<?php echo admin_url('/admin.php?page=elasticemail-settings&subscribe=true'); ?>">
                    <input type="hidden" name="activationReturnUrl" value="">
                    <input type="hidden" name="activationTemplate" value="Subscription_from_blog">
                    <input type="hidden" name="source" value="WebForm">
                    <input type="hidden" name="notifyEmail" value="">
                    <div class="inputs">
                        <span id="email" style="width: 100%;">
                            <label for="email" style="padding-right: 5px;">Email Address</label>
                            <input maxlength="40" class="form-control" name="email" size="20" type="email" required="" style="width: 60%;"> 
                        </span>
                        <br/><br/>
                        <span id="field_firstname" style="width: 100%;">
                            <label for="field_firstname" style="padding-right: 51px;">Name</label>
                            <input maxlength="40" class="form-control" name="field_firstname" size="20" type="string" style="width: 60%;">
                        </span>
                        <br/>
                        <br/>
                        <br/>
                    </div>
                    <ul class="lists" style="list-style:none;display:none;">
                        <li>
                            <input type="checkbox" name="publiclistid" id="AWMifhLm" value="7db916f4-9a46-4655-be56-ec781bd74968" checked="checked">
                            <label class="publiclistlabel" for="AWMifhLm">Subscription_from_blog</label>
                        </li>
                    </ul>
                    <input type="submit" name="submit" value="Subscribe">
                </fieldset>
            </form>
            <?php
        } else {
            echo '<h3 style="color: green; font: bold;">Thank you for subscribing to our newsletter!</h3>
            <h5 style="color: green;">You will start receiving our email marketing newsletter, as soon as you confirm your subscription.</h5>';
        }
        ?>
        <br/>
        <hr>
        <br/>
        <h2 class="eeh2">How we can help you?</h2>
        <h4 class="eeh4">If you would like to boost your email marketing campaigns or improve your email delivery, check out our helpful guides to get you started!</h4>
        <ul style="padding-left: 40px;">
            <li type="circle"><a href="https://elasticemail.com/support/">Guides and resources</a></li>
            <li type="circle"><a href="https://elasticemail.com/api-documentation-and-libraries/">Looking for code? Check our API</a></li>
            <li type="circle"><a href="https://elasticemail.com/contact/">Want to talk with a live person? Contact us</a></li>
        </ul>
        <br/>
        <h4 class="eeh4">Remember that in case of any other questions or feedback, you can always contact our friendly <a href="http://support.elasticemail.com/">Support Team.</a></h4>
    </div>
</div>
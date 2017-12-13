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
            <?php
            if (isset($_POST['daterange'])) {
                $daterangeselect = $_POST['daterange'];
                if ($daterangeselect === 'last-mth') {
                    $datarangename = ' - last month';
                }
                if ($daterangeselect === 'last-wk') {
                    $datarangename = ' - last week';
                }
                if ($daterangeselect === 'last-2wk') {
                    $datarangename = ' - last two weeks';
                }
            } else {
                if ((empty($total) === true || $total === 0)) {
                    $datarangename = '';
                } else {
                    $datarangename = ' - last month';
                }
            }
            ?>
            <div class="pagetitle">
                <h1>Reports <?php echo $datarangename; ?></h1>
            </div>
        </div>

        <?php
        if ((empty($total) === true || $total === 0)) {
            $total = '<span class="default-text">' . 150000 . '</span>';
            $delivered = '<span class="default-text">' . 100000 . '</span>';
            $opened = '<span class="default-text">' . 85000 . '</span>';
            $bounced = '<span class="default-text">' . 4000 . '</span>';
            $clicked = '<span class="default-text">' . 95000 . '</span>';
            $unsubscribed = '<span class="default-text">' . 4000 . '</span>';
            $info = '<div class="connect-alert"><h1>Please note, that the data below is an example. Send your first campaign, to get the real statistics.</h1></div>';
        }

        if ((empty($error)) === TRUE) {
            ?>

            <div class="select-form-box">
                <form name="form" id="daterange" action="" method="post">
                    Date range:
                    <select id="daterange-select" name="daterange" onchange="this.form.submit()">
                        <option>Select data range</option>
                        <option value="last-mth">Last month</option>
                        <option value="last-wk">Last week</option>
                        <option value="last-2wk">Last two weeks</option>
                    </select>
                </form>
            </div>

            <?php
            if (!empty($info)) {
                echo $info;
            }
            ?>
            <div class="reports-container">
                <table class="report-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="background: rgba(102, 163, 163, 0.2);">Submitted</th>
                            <th style="background: rgba(0, 153, 255, 0.2);">Delivered</th>
                            <th style="background: rgba(0, 128, 0, 0.2);">Opened</th>
                            <th style="background: rgba(255, 159, 64, 0.2);">Clicked</th>
                            <th style="background: rgba(255, 162, 0, 0.2);">Unsubscribed</th>
                            <th style="background: rgba(255, 0, 0, 0.2);">Bounced</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php if(is_numeric($total)){ echo number_format($total); } else { echo $total; } ?></td>
                            <td><?php if(is_numeric($delivered)){ echo number_format($delivered); } else { echo $delivered; } ?></td>
                            <td><?php if(is_numeric($opened)){ echo number_format($opened); } else { echo $opened; } ?></td>
                            <td><?php if(is_numeric($clicked)){ echo number_format($clicked); } else { echo $clicked; } ?></td>
                            <td><?php if(is_numeric($unsubscribed)){ echo number_format($unsubscribed); } else { echo $unsubscribed; } ?></td>
                            <td><?php if(is_numeric($bounced)){ echo number_format($bounced); } else { echo $bounced; } ?></td>
                        </tr>
                    </tbody>
                </table>

                <div class="reports-list">
                    <div id="canvas-holder" style="width:80%;">
                        <canvas id="chart-area" />
                    </div>
                    <script>

                        var config = {
                            type: 'doughnut',
                            data: {
                                labels: ["Delivered", "Opened", "Clicked", "Unsubscribed", "Bounced"],
                                datasets: [{
                                        label: '# of Votes',
                                        data: [
                                            <?php
                                                if (is_numeric($delivered)) {
                                                    echo $delivered;
                                                } else {
                                                    echo 100000;
                                                }
                                                ?>,
                                                <?php
                                                if (is_numeric($opened)) {
                                                    echo $opened;
                                                } else {
                                                    echo 85000;
                                                }
                                                ?>,
                                                <?php
                                                if (is_numeric($clicked)) {
                                                    echo $clicked;
                                                } else {
                                                    echo 95000;
                                                }
                                                ?>,
                                                <?php
                                                if (is_numeric($unsubscribed)) {
                                                    echo $unsubscribed;
                                                } else {
                                                    echo 4000;
                                                }
                                                ?>,
                                                <?php
                                                if (is_numeric($bounced)) {
                                                    echo $bounced;
                                                } else {
                                                    echo 4000;
                                                }
                                            ?>],
                                        backgroundColor: [
                                            'rgba(0, 153, 255, 0.4)',
                                            'rgba(0, 128, 0, 0.4)',
                                            'rgba(255, 159, 64, 0.4)',
                                            'rgba(255, 162, 0, 0.4)',
                                            'rgba(255, 0, 0, 0.4)'
                                        ],
                                        borderColor: [
                                            'rgba(241, 241, 241, 1)',
                                            'rgba(241, 241, 241, 1)',
                                            'rgba(241, 241, 241, 1)',
                                            'rgba(241, 241, 241, 1)',
                                            'rgba(241, 241, 241, 1)'
                                        ],
                                        borderWidth: 1.5
                                    }]
                            },
                            options: {
                                responsive: true
                            }
                        };
                        window.onload = function () {
                            var ctx = document.getElementById("chart-area").getContext("2d");
                            window.myPie = new Chart(ctx, config);
                        };
                    </script>
                </div>
            </div>
        <?php } else { ?>

            <div class="">
                <div class="" style="text-align: center; padding-top: 10%; padding-bottom: 5%;">
                    <img src="<?php echo esc_url(plugins_url('/assets/images/connect_apikey.png', dirname(__FILE__))) ?>" >
                </div>
                <div class="connect-alert">
                    <h1>
                        Oops! Your Elastic Email account has not been connected. Configure the settings to start using the plugin.
                    </h1>
                </div>
            </div>

        <?php } ?>

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
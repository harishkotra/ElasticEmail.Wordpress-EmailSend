//TODO: add a Translation
//TODO: Checking the connection with the account in the EE

var am = function() {
    var that = {};
    var apipath = 'admin-ajax.php?';
    var elements = {logs: jQuery('#ee_logs tbody'), indi: jQuery('#ee_indi'), overlay: jQuery('#ee_overlay')};
    that.data = {};
    that.init = function() {
        //Search filter
        jQuery("#ee_search").click(function() {
            that.data.query = {
                from: jQuery('input[name="from-date"]').val().trim() + " " + jQuery('input[name="from-time"]').val().trim(),
                to: jQuery('input[name="to-date"]').val().trim() + " " + jQuery('input[name="to-time"]').val().trim(),
                limit: jQuery('select[name="limit"]').val().trim(),
                status: jQuery('select[name="status"]').val().trim(),
                channelname: jQuery('select[name="channelname"]').val().trim(),
                action: "get_log"
            };
            request(that.data.query, printlogs);
            return false;
        });
        //Download buttons
        jQuery('#download a').click(function() {
            shutter();
            window.location = apipath + parameterize({
                from: jQuery('input[name="from-date"]').val().trim() + " " + jQuery('input[name="from-time"]').val().trim(),
                to: jQuery('input[name="to-date"]').val().trim() + " " + jQuery('input[name="to-time"]').val().trim(),
                status: jQuery('select[name="status"]').val().trim(),
                format: jQuery(this).data('format'),
                channelname: jQuery('select[name="channelname"]').val().trim(),
                action: "download_log"
            });
            shutter();
        });
        //View email
        viewemail();
    };

    var viewemail = function() {

        elements.logs.find('.btn').unbind().click(function() {

            var button = jQuery(this);
            var row = that.data.list[button.data('index')];
            var form = jQuery('<div id="ee_dialogbox">\n\
<table>\n\
<tr><th>From</th><td>' + row.channel + '</td></tr>\n\
<tr><th>To</th><td>' + row.to + '</td></tr>\n\
<tr><th>Subject</th><td>' + row.subject + '</td></tr>\n\
</table>\n\
<iframe style="width:900px;height:500px;" src="https://api.elasticemail.com/view?notracking=true&amp;msgid=' + button.data('href') + '"></iframe>\n\
</div>');
            var dialog = form.dialog({width: 920, beforeClose: function(){elements.overlay.toggle();}, title: row.date + " [ " + row.status + " ]"});
            elements.overlay.toggle().click(function() {
                dialog.dialog('close');
                elements.overlay.unbind();
            });
        });
    };
    var request = function(data, callback) {
        jQuery.ajax({
            type: "get", url: apipath,
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                shutter();
            },
            data: parameterize(data),
            success: callback,
            timeout: 30000,
            complete: function() {
                shutter();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR, textStatus, errorThrown);
            }
        });
    };
    shutter = function() {
        elements.indi.toggle();
        elements.overlay.toggle();
    };
    parameterize = function(obj) {
        var params = "";
        if (obj === null)
            return "";
        for (var id in obj) {
            var val = obj[id] + "";
            params += "&" + encodeURIComponent(id) + "=" + encodeURIComponent(val);
        }
        return params.substring(1);
    };
    printlogs = function(data) {
        elements.logs.html('');
        if (data.list) {
            var tbody = '';
            jQuery.each(data.list, function(index, el) {
                tbody += '<tr>\n\
<td>' + el.date + '</td>\n\
<td><span class="ee_to">To: ' + el.to + '</span><span class="ee_from">From: ' + el.channel + '</span></td>\n\
<td>' + el.subject + '</td>\n\
<td class="ee_text-small ee_text-center">' + ((el.status === 'Error') ? '[' + el.bouncecategory + ']<div>' + ((error[el.bouncecategory]) ? error[el.bouncecategory] : '') + '</div>' : el.status) + '</td>\n\
            <td class="ee_text-right"><div data-index="' + index + '" data-href="' + el.msgid + '" class="btn">View</div></td>\n\
</tr>';
            });
            if (tbody.length === 0) {
                jQuery('#older').hide();
                tbody = '<tr><td colspan="5">Results not found.</td></tr>'
            }
            that.data.list = data.list;
            elements.logs.html(tbody);
            viewemail();
        }
    };
    that.init();

    var error = {
        Unknown: "Unique error that has not been categorized by our system",
        Ignore: "Delivery was not attempted",
        Spam: "Considered spam by the recipient or their email service provider",
        BlackListed: "Domain or IP is potentially on a blacklist",
        NoMailbox: "Email address does not exist",
        GreyListed: "Temporarily rejected.  Retrying will likely be accepted",
        Throttled: "Too many emails for the same domain were detected",
        Timeout: "A timeout occured trying to send this email",
        ConnectionProblem: "A connection problem occured trying to send this email",
        SPFProblem: "The domain that sent this email does not have SPF validated properly",
        AccountProblem: "Recipient account problem like over quota or disabled",
        DNSProblem: "There is a problem with the DNS of the recipients domain",
        WhitelistingProblem: "Recipient's email service provider requires a white listing of the IP sending the email",
        CodeError: "An unexpected error has occurred",
        ManualCancel: "User cancelled the in progress email.",
        ConnectionTerminated: "Status is unknown due the connection being terminated by the recipients server",
        NotDelivered: "Recipient is on your bounce/blocked recipients list",
        Unsubscribed: "Recipient is on your unsubscribed list",
        AbuseReport: "Recipient is on your Abuse list"
    };

    return that;
};

jQuery(document).ready(function() {
    am = new am();
    jQuery("#ee_search").trigger('click');
    jQuery('.datapicker').datepicker({
        dateFormat: 'mm/dd/yy'
    });
});
function set_status(element, message) {
	if (message.length > 0) {
		if (message.indexOf("ERROR") >= 0) {
		    $(element).css('background', 'url(assets/img/status_ERROR.png) no-repeat left center');
		} else {
		    $(element).css('background', 'url(assets/img/status_OK.png) no-repeat left center');
		}
		$(element).css('background-size', '30px 30px');
		$(element).html(message).fadeIn();
	}
}

$(document).ready(function() {
   
    var status = $('.status'),
        percent = $('.percent'),
        bar = $('.bar'),
        progress = $('#upload_progress');

    $('form').ajaxForm({
        dataType: 'json',

        beforeSend: function() {
            status.fadeOut();
            progress.fadeIn();
            $('#floatingCirclesG').fadeIn();
	    	bar.width('0%');
            percent.html('0%');
        },

        uploadProgress: function(event, position, total, percentComplete) {
            var pVel = percentComplete + '%';
            bar.width(pVel);
            percent.html(pVel);
        },

        complete: function(data) {

			// Hide loader
			$('#floatingCirclesG').fadeOut();
            
            // Check messages
            set_status('#email_status', data.responseJSON.email);
            set_status('#bed_status', data.responseJSON.bed);
            set_status('#read_status', data.responseJSON.read);
            set_status('#hotspot_status', data.responseJSON.hotspot);
            set_status('#job_status', data.responseJSON.status);
            var token = data.responseJSON.token;
            
            // Error
            if (token.length == 0) {
            	
            	// Remove everything
            	$.post("actions/remove.php", { token: token});
            
            // Success
            } else {
            	
            	// Start job
            	$.post("actions/start.php", { token: token });
            	
            	// Send mail
            	$.post("actions/send_mail.php", { token: token });
            }
            
        }
    });
    
});

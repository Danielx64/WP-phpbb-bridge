/** 
*
* @package WP-United
* @version $Id: 0.9.1.5  2012/12/28 John Wells (Jhong) Exp $
* @copyright (c) 2006-2013 wp-united.com
* @license http://opensource.org/licenses/gpl-license.php GNU Public License  
* @author John Wells
* 
* JavaScript for the WP-United settings panels
*/
var $wpu = jQuery.noConflict();
(function($wpu) {
    $wpu.QueryString = (function(a) {
        if (a == "") return {};
        var b = {};
        for (var i = 0; i < a.length; ++i)
        {
            var p=a[i].split('=');
            if (p.length != 2) continue;
            b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
        }
        return b;
    })(window.location.search.substr(1).split('&'))
})(jQuery);


/**
 * Creates a file tree for the user to select the phpBB location
 */
function createFileTree() {
	$wpu('#phpbbpath').fileTree({ 
		root: '/',
		script: ajaxurl,
		multiFolder: false,
		loadMessage: fileTreeLdgText
	}, function(file) { 
		var parts = file.split('/');
		if ((parts.length) > 1) {
			file = parts.pop();
		}
		if(file=='config.php') {
			var pth = parts.join('/') + '/'; 
			$wpu('#phpbbpathshow').html(pth).css('color', 'green');
			$wpu('#wpupathfield').val(pth);
			$wpu('#phpbbpathgroup').hide('fade');
			$wpu('#txtchangepath').show();
			$wpu('#txtselpath').hide();
			$wpu('#wpucancelchange').hide();
			$wpu('#phpbbpathchooser').show('fade');
			$wpu('#wpusetup-submit').show();
			window.scrollTo(0,0);
		}
	});
	
	$wpu('#wpubackupentry').bind('keyup', function() {
		wpu_update_backuppath(true);
	});
	$wpu('#phpbbdocroot').bind('keyup', function() {
		wpu_update_backuppath(true);
	});	
}



// Resize text boxes dynamically
function resize_text_field($field) {
	var measure = $wpu('#wpu-measure');
	measure.text($field.val());
	var w = measure.width() + 16;
	if(w < 25) w = 25;
	$field.css('width', w + 'px');
}

function wpu_update_backuppath(changeColor) {
	var $docRoot = $wpu('#phpbbdocroot');
	var $bckEntry = $wpu('#wpubackupentry');
	var pth = $docRoot.val() + $bckEntry.val();
	resize_text_field($docRoot);
	resize_text_field($bckEntry);
	pth = pth.replace(/\\/g, '/').replace(/\/\//g,'/');
	$wpu('#wpupathfield').val(pth);
	var $p = $wpu('#phpbbpathshow').html(pth);
	if(changeColor) {
		$p.css('color', 'orange');
	}
}

// Triggered on filetree load, so we can intercept if nothing useful is returned.
var wpuUsingBackupEntry=false;
var wpuForceBackupEntry=false;
function wpu_filetree_trigger(data) {
	
	if(data.length < 50) {
		// FileTree isn't showing any useful data, abandon it and fall back to textbox entry
		wpuForceBackupEntry = true;
		wpuSwitchEntryType();
	} else {
		$wpu('#phpbbpath').show();
		$wpu('#wpubackupgroup').hide();	
	}
}

function wpuSwitchEntryType() {
	
	if(!wpuUsingBackupEntry) {
		// switch to manual text entry
		wpuUsingBackupEntry = true;
		$wpu('#phpbbpath').hide();
		$wpu('#wpubackupgroup').show();
		$wpu('#wpuentrytype').text(autoText);
		wpu_update_backuppath(!wpuForceBackupEntry);
		$wpu('#wpusetup-submit').show();
	} else {
		if(!wpuForceBackupEntry) {
			// switch to filechooser
			wpuUsingBackupEntry = false;
			$wpu('#phpbbpath').show();
			$wpu('#wpubackupgroup').hide();
			$wpu('#wpuentrytype').text(manualText);
			$wpu('#wpusetup-submit').hide();
		}
	}
	
	return false;
}



/**
 * Sets the form fields up when a valid phpBB path is chosen in the filetree
 */
function setPath(type) {
	if(type=='setup') {
		$wpu('#phpbbpathgroup').hide();
		$wpu('#phpbbpathchooser').button();
		$wpu('#phpbbpathchooser').show();
		$wpu('#txtchangepath').show();
		$wpu('#txtselpath').hide();
	}
	$wpu("#phpbbpathshow").html(phpbbPath).css('color', 'green');
	$wpu("#wpupathfield").val(phpbbPath);
}


/**
 * Re-displays the file tree when the user wants to change the phpBB path
 */
function wpuChangePath() {
	$wpu('#phpbbpathgroup').show('fade');
	$wpu('#phpbbpathchooser').hide('fade');
	$wpu('#txtchangepath').hide();
	$wpu('#txtselpath').show();
	$wpu('#wpucancelchange').show();
	$wpu('#wpucancelchange').button();
	if(!wpuUsingBackupEntry) {
		$wpu('#wpusetup-submit').hide();
	} else {
		$wpu('#wpusetup-submit').show();
	}
	return false;
}

/**
 * Resets the fields and filetree when the user cancels changing the phpBB path
 */
function wpuCancelChange() {
	$wpu('#phpbbpathgroup').hide('fade');
	$wpu('#phpbbpathchooser').show('fade');
	$wpu('#txtchangepath').show();
	$wpu('#txtselpath').hide();
	$wpu('#wpucancelchange').hide();
	$wpu('#wpusetup-submit').hide();			
	return false;
}


/**
 * Sends the settings to phPBB
 */
function wpu_transmit(type, formID, urlToRefresh) {
	$wpu('#wpustatus').hide();
	window.scrollTo(0,0);
	$wpu('#wputransmit').dialog({
		modal: true,
		title: 'Connecting...',
		width: 360,
		height: 160,
		draggable: false,
		disabled: true,
		closeOnEscape: false,
		resizable: false,
		show: 'puff'
	});
	$wpu('.ui-dialog-titlebar').hide();
	var formData;
	
	// update the backup entry method if needed
	if((type=='wp-united-setup') && wpuUsingBackupEntry) {
		wpu_update_backuppath(true);
	}
	
	
	wpu_setup_errhandler();
	
	formData = $wpu('#' + formID).serialize() +'&action=wpu_settings_transmit&type=' + type + '&_ajax_nonce=' + transmitNonce;
	$wpu.post(ajaxurl, formData, function(response) { 
		response = $wpu.trim(response);
		var responseMsg;
		if(response.length >= 2) responseMsg = response.substr(0, 2);
		if(responseMsg == 'OK') {
			// the settings were applied
			window.location = 'themes.php?page=' + type + '&msg=success';
			return;
		}
		wpu_process_error(response);
	});
	return false;
}

/**
 * Listen for ajax errors
 */
 var wpu_handling_error = false;
function wpu_setup_errhandler() {
	$wpu(document).ajaxError(function(e, xhr, settings, exception) {
		
		if(!wpu_handling_error) {
			wpu_handling_error = true;
			if(exception == undefined) {
				var exception = 'Server ' + xhr.status + ' error. Please check your server logs for more information.';
			}
			var resp = '<br />There was no page output.<br />';
			if(typeof xhr.responseText !== 'undefined') {
				
				// extract any head, etc from the page.
				var mResp = xhr.responseText.split(/<body/i);
				if(mResp.length) { 
					resp = '<div ' + mResp[1];
					mResp = resp.split(/<\/body>/i);
				}
				resp = (mResp.length) ? mResp[0] + '</div>' : resp;
			
			
				resp = '<br />The page output was:<br /><div>' + resp + '</div>';
			}
			wpu_process_error(errMsg = 'WP-United caught an error: ' + settings.url + ' returned: ' + exception + resp);
		}
	});
	
}

/**
 * Processes various types of errors received during the ajax call
 * Messges prefixed with [ERROR] are handled errors
 * Other types are PHP errors, or server responses with unexpected content
 * Finally we also process non-300 rsponses from jQuery's ajaxError
 */
function wpu_process_error(transmitMessage) { 
	// there was an uncatchable error, send a disable request
	if  (transmitMessage.indexOf('[ERROR]') == -1) { 
		var disable = '&wpudisable=1&action=wpu_disable&_ajax_nonce=' + disableNonce;
		if(transmitMessage == '') {
			transmitMessage = blankPageMsg;
		}
		// prevent recursive ajax error:
		$wpu(document).ajaxError(function() {
			// TODO: if server 500 error or disable, try direct delete method
			send_back_msg('themes.php?page=wp-united-setup&msg=fail', transmitMessage);
		}); 
		$wpu.post(ajaxurl, disable, function(response) {
			// the connection has been disabled, redirect
			send_back_msg('themes.php?page=wp-united-setup&msg=fail', transmitMessage);
		});
	} else {
		// we caught the error, redirect to setup page
		transmitMessage = transmitMessage.replace(/\[ERROR\]/g, '');
		send_back_msg('themes.php?page=wp-united-setup&msg=fail', transmitMessage);
	}
}

// We have to send messages back by POST as URI vars are too long
function send_back_msg(uri, msg) {

	// escape any html in error messages
	$wpu('<div id="escapetext"> </div>').appendTo('body');

	$wpu('<form action="' + uri + '" method="post"><input type="hidden" name="msgerr" value="' + Base64.encode($wpu('#escapetext').text(msg).html()) + '"></input></form>').appendTo('body').submit();
}


/**
 *	Deferred script loading -- called twice, once on jquery document.ready, and once by timeout. 
 *  This prevents other plugin's scripts that die on the ready event from killing ours
 */
var wpuHasInited = false;
function wpu_hardened_init() {
	if(!wpuHasInited) {
		wpuHasInited = true;
		wpu_hardened_init_tail();
	}
}
	
/**
 * Base64 encode/decode for passing messages
 */
var Base64 = {
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;

		input = Base64._utf8_encode(input);
		while (i < input.length) {
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);

			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;

			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}

			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

		}

		return output;
	},
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;

		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

		while (i < input.length) {

			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));

			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;

			output = output + String.fromCharCode(chr1);

			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}

		}

		output = Base64._utf8_decode(output);

		return output;

	},
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
		}
		return utftext;
	},

	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
		while ( i < utftext.length ) {
			c = utftext.charCodeAt(i);
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
		}
		return string;
	}
}

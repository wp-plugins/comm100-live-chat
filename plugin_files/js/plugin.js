var comm100_script_id = 0;
function comm100_script_request(params, success, error) {
	function request() {
		var _id = 'comm100_script_' + comm100_script_id++;
		var _success;
		var _timer_timeout;

		function _append_script(id, src) {
			var scr = document.createElement('script');
			scr.src = src;
			scr.id = '_' + _id;
			scr.type = 'text/javascript';
			document.getElementsByTagName('head')[0].appendChild(scr);
		}
		this.send = function _send (url, success, error) {
			_append_script(_id, url + '&callback=' + _id + '.onresponse');
			_timer_timeout = setTimeout(function() {
				error('Operation timeout.');
			}, 30 * 1000);

			_success = success || function() {};		
		}
		this.onresponse = function _onresponse(response) {
			window[_id] = null;
			var scr = document.getElementById('_' + _id);
			document.getElementsByTagName('head')[0].removeChild(scr);

			clearTimeout(_timer_timeout);

			_success(response);
		}
		window[_id] = this;
	}

	var req = new request();
	req.send('https://hosted.comm100.com/AdminPluginService/(S(' + comm100livechat_session + '))/livechatplugin.ashx' + params, success, error);
}

var comm100_plugin = (function() {
	function _onexception (msg) {
		document.getElementById('login_submit_img').style.display = 'none';
		document.getElementById('login_submit').disabled = false;

		document.getElementById('register_submit_img').style.display = 'none';
		document.getElementById('register_submit').disabled = false;

		alert(msg);
	}

	function _get_timezone() {
		return ((new Date()).getTimezoneOffset()/-60.0).toString();
	}
	function _register() {
		document.getElementById('register_submit_img').style.display = '';
		document.getElementById('register_submit').disabled = true;

		var edition = encodeURIComponent(document.getElementById('register_edition').value);
		var name = encodeURIComponent(document.getElementById('register_name').value);
		var email = encodeURIComponent(document.getElementById('register_email').value);
		var password = encodeURIComponent(document.getElementById('register_password').value);
		var phone = encodeURIComponent(document.getElementById('register_phone').value);
		var website = encodeURIComponent(document.getElementById('register_website').value);
		var ip = encodeURIComponent(document.getElementById('register_ip').value);
		var timezone = encodeURIComponent(_get_timezone());
		var verification_code = encodeURIComponent(document.getElementById('register_verification_code').value);
		var referrer = encodeURIComponent(window.location.href);

		comm100_script_request('?action=register&edition=' + edition + '&name=' + name + '&email=' + email +
			'&password=' + password + '&phone=' + phone + '&website=' + website + '&ip=' + ip + '&timezone=' + timezone + '&verificationCode=' + verification_code + '&referrer=' + referrer
			, function(response){
				if (response.success) {
					document.getElementById('site_id').value = response.response;
					document.forms['site_id_form'].submit();
				}
				else {
					document.getElementById('register_error').style.display = '';
					document.getElementById('register_error_text').innerHTML = response.error;
					
					document.getElementById('register_verification_code_image').src = 'https://hosted.comm100.com/AdminPluginService/(S(' + comm100livechat_session + '))/livechatplugin.ashx?action=verification_code';
				}
				
				document.getElementById('register_submit_img').style.display = 'none';
				document.getElementById('register_submit').disabled = false;

		}, function(message) {
			document.getElementById('register_submit_img').style.display = 'none';
			document.getElementById('register_submit').disabled = false;

			document.getElementById('register_error').style.display = '';
			document.getElementById('register_error_text').innerHTML = response.error;
		});
	}
	function _login() {
		document.getElementById('login_submit_img').style.display = '';
		document.getElementById('login_submit').disabled = true;

		var site_id = encodeURIComponent(document.getElementById('login_site_id').value);
		var email = encodeURIComponent(document.getElementById('login_email').value);
		var password = encodeURIComponent(document.getElementById('login_password').value);
		var timezone = encodeURIComponent(_get_timezone());

		comm100_script_request('?action=login&siteId=' + site_id + '&email=' + email + '&password=' + password
			, function(response){
				if (response.success) {
					document.getElementById('site_id').value = site_id;
					document.forms['site_id_form'].submit();				
				}
				else {
					document.getElementById('login_error_').style.display = '';
					document.getElementById('login_error_text').innerHTML = response.error;
				}
				document.getElementById('login_submit_img').style.display = 'none';
				document.getElementById('login_submit').disabled = false;
		}, function(message) {
			document.getElementById('login_submit_img').style.display = 'none';
			document.getElementById('login_submit').disabled = false;

			document.getElementById('login_error_').style.display = '';
			document.getElementById('login_error_text').innerHTML = response.error;
		});
	}
	return {
		register: _register,
		login: _login
	};
})();




function hide_element(id) {
	document.getElementById(id).style.display = 'none';
}
function show_element(id, display) {
	document.getElementById(id).style.display = display || '';
}
function is_empty(str) {
	return (!str || /^\s*$/.test(str));
}
function is_email(str) {
	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(str);
}
function is_input_empty(input_id) {
	return is_empty(document.getElementById(input_id).value);
}
function is_input_email(input_id) {
	return is_email(document.getElementById(input_id).value);
}
function show_login() {
	show_element("comm100livechat_have_account");
	show_element("comm100livechat_login");
	hide_element("comm100livechat_register");
	//hide_element("comm100livechat_settings");
}
function show_registger() {
	show_element("comm100livechat_have_account");
	hide_element("comm100livechat_login");
	show_element("comm100livechat_register");
	//hide_element("comm100livechat_settings");
}
function show_settings() {
	//hide_element("comm100livechat_have_account");
	//hide_element("comm100livechat_login");
	//hide_element("comm100livechat_register");
	show_element("comm100livechat_settings");
}
function validate_register_input(name) {
	if (is_input_empty("register_" + name)) {
		show_element("register_" + name + "_required");
		return false;
	} else {
		hide_element("register_" + name + "_required");
		return true;
	}
}
function validate_register_input_email(name) {
	if (is_input_empty("register_" + name)) {
		show_element("register_" + name + "_required");
		hide_element("register_" + name + "_valid");
		return false;
	} else if (!is_input_email("register_" + name)) {
		hide_element("register_" + name + "_required");
		show_element("register_" + name + "_valid");
		return false;
	} else {
		hide_element("register_" + name + "_required");
		hide_element("register_" + name + "_valid");
		return true;
	}
}
function validate_register_inputs() {
	var fields = ["name", "password", "phone", "verification_code"];
	var pass = true;

	for (var i = 0, len = fields.length; i < len; i++) {
		if (!validate_register_input(fields[i])) {
			pass = false;
		}
	}

	if (!validate_register_input_email("email")) {
		pass = false;
	}

	return pass;
}

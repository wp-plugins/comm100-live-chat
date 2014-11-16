String.prototype.trim = function()
{
    return this.replace(/(^[\s]*)|([\s]*$)/g, "");
}

function html_encode(html) {
	var div=document.createElement("div");
	var txt=document.createTextNode(html);
	div.appendChild(txt);
	return div.innerHTML;
}
if (typeof comm100_script_id == 'undefined')
	comm100_script_id = 0;

function comm100_script_route(params, success, error) {
	comm100_script_request(params, success, error, "route.comm100.com/routeserver/pluginHandler.ashx", "route1.comm100.com/routeserver/pluginHandler.ashx");
}
function comm100_script_cpanel(params, success, error) {
	comm100_script_request(params, success, error, (comm100_cpanel_domain || "hosted.comm100.com") + "/AdminPluginService/livechatplugin.ashx");
}
function comm100_script_request(params, success, error, url, backup_url) {
	function request() {
		var _id,
			_success, _error,
			_timer_timeout,
			_self = this;

		function _append_script(src, timeout) {
			_id = 'comm100_script_' + comm100_script_id++;
			window[_id] = _self;

			var scr = document.createElement('script');
			scr.src = src + '&callback=' + _id + '.onresponse';
			scr.id = '_' + _id;
			scr.type = 'text/javascript';
			document.getElementsByTagName('head')[0].appendChild(scr);
			_timer_timeout = setTimeout(timeout, 10*1000);
		}
		function _remove_script() {
			if (_timer_timeout) clearTimeout(_timer_timeout);

			window[_id] = null;
			var scr = document.getElementById('_' + _id);
			document.getElementsByTagName('head')[0].removeChild(scr);			
		}
		this.send = function (url, backup_url, success, error) {
			_append_script(url, function() {
				if (backup_url) {
					_remove_script();
					
					_append_script(backup_url, function() {
						_error('Unexpected error. Please have a live chat with our support team or email to support@comm100.com.');
					});
				} else {
					_error('Unexpected error. Please have a live chat with our support team or email to support@comm100.com.');
				}
			});

			_error = error || function() {};
			_success = success || function() {};		
		};
		this.onresponse = function _onresponse(response) {
			_remove_script();

			_success(response);
		};
	}

	var req = new request();
	if (backup_url) backup_url = 'https://' + backup_url + params;
	req.send('https://' + url + params, backup_url, success, error);
}

var comm100_plugin = (function() {
    function _onexception(msg) {
        document.getElementById('login_submit_img').style.display = 'none';
        document.getElementById('login_submit').disabled = false;

        document.getElementById('register_submit_img').style.display = 'none';
        document.getElementById('register_submit').disabled = false;

        alert(msg);
    }

    function _get_timezone() {
        return ((new Date()).getTimezoneOffset() / -60.0).toString();
    }
    function _register() {
    }
    function _submit_site_form (site_id, email, plan_id, plan_type) {
        document.getElementById('site_id').value = site_id;
        document.getElementById('email').value = email;
        document.getElementById('plan_id').value = plan_id;
        document.getElementById('plan_type').value = plan_type;
        document.forms['site_id_form'].submit();
    }
    function _get_plan_type(plan) {
    	if (plan.button_type == 2) {
    		return 0;  //monitor
    	} else if (plan.button_type == 0 && plan.button_float) /*float*/ {
    		return 1; //float image
    	} else {
    		return 2; //others,  need widget
    	}
    }
    function _login(success, error) {
		var email = encodeURIComponent(document.getElementById('login_email').value);
        var password = encodeURIComponent(document.getElementById('login_password').value);
        var timezone = encodeURIComponent(_get_timezone());

        var site_id = encodeURIComponent(document.getElementById('site_id').value.trim());
        document.getElementById('email').value = email;
        
        comm100_script_cpanel('?action=login&siteId=' + site_id + '&email=' + email + '&password=' + password
			, function(response) {
			    if(response.success) {
			    	_get_plans(site_id, function(response) {
			    		var plans = response;
			    		if (plans.length == 1) {
					        _submit_site_form(site_id, email, plans[0].id, _get_plan_type(plans[0]));
				    	} else {
				    		_show_plans(plans);
				    	}
			    	});
			    }
			    else {
				    error(response.error);
			    }
			}, function(message) {
				error(message);
			});
    }
    function _get_plans(site_id, success, error) {
        comm100_script_cpanel('?action=plans&siteId=' + site_id, function(response) {
            if(response.error) {
                if (typeof error != 'undefined')
                    error('Comm100 Live Chat is not added to your site yet as you haven\'t linked up any Comm100 account.<br/><a href="admin.php?page=comm100livechat_settings">Link Up your account now</a> and start chatting with your visitors.');
            } else {
                success(response.response);
            }
        });
    }

    function _show_plans(plans) {
		var html = '<select style="width:300px;" id="settings_select_plans_control" onchange="settings_select_change();"><option value="0">--select a code plan--</option>';
		for (var i= 0, len=plans.length; i<len; i++) {
    		var p = plans[i];			

			html += '<option value="'+p.id+'_'+_get_plan_type(p)+'">'+p.name+'</option>'
    	}
		html += '</select>';

		document.getElementById('settings_select_plans').innerHTML = html;
    	show_element('comm100livechat_choose_plan');
    	hide_element('comm100livechat_choose_site');
    	hide_element('comm100livechat_login');
    }
    function _get_code(site_id, plan_id, callback) {
        comm100_script_cpanel('?action=code&siteId=' + site_id + '&planId=' + plan_id, function(response) {
            callback(response.response);
        });
    }
    function _get_editions(callback) {
    }

    function _show_sites(sites) {
    	var html = ''
    	for (var i = 0, len = sites.length; i < len; i++) {
    		var s = sites[i];

    		html += '<div style="padding: 0 0 15px 0"><input name="comm100site" type="radio" id="site'+s.id+'"';
    		html += ' onclick="document.getElementById(\'site_id\').value='+s.id+';"';
    		if (i == 0) html += 'checked ';
    		html += '/> <label for="site'+s.id+'">Site Id: <span style="color: #000;font-weight: bold;font-size: larger;">';
    		html += s.id;
    		if (s.inactive) html+= '<span style="color: red; font-size: x-small;padding: 0 0 3px 3px;">(Inactive)</span>';
    		html += '</span><span style="padding: 0 0 0 7px;">Last Login: ';
    		html += s.last_login_time;
    		html += '</span><span style="padding: 0 0 0 7px;">Account Created: '
    		html += s.register_time + '</span></label></div>';
    	}

    	document.getElementById('login_sites').innerHTML = html;

    	hide_element('comm100livechat_login');
    	hide_element('comm100livechat_choose_plan');
    	show_element('comm100livechat_choose_site');

    	document.getElementById('num_sites').innerHTML = sites.length;
    }
    function _choose_site() {
        show_element('choose_site_submit_img');
        document.getElementById('choose_site_submit').disabled = true;

        _login(function () {
	        hide_element('choose_site_submit_img');
	        document.getElementById('choose_site_submit').disabled = false;
        }, function (error) {
	        hide_element('choose_site_submit_img');
	        document.getElementById('choose_site_submit').disabled = false;

		    document.getElementById('choose_site_error_').style.display = '';
		    document.getElementById('choose_site_error_text').innerHTML = error;        	
        })
    }

    function _show_error(error) {
        show_element('login_error_');
        document.getElementById('login_error_text').innerHTML = error;
        
	    hide_element('login_submit_img');
	    document.getElementById('login_submit').disabled = false;    		
    }

    function _sites () {
        show_element('login_submit_img');
        document.getElementById('login_submit').disabled = true;

    	var email = encodeURIComponent(document.getElementById('login_email').value);
        var password = encodeURIComponent(document.getElementById('login_password').value);

        if (email.trim() == '') {
        	_show_error('Email is required');
        	return;
        }
        if (password == '') {
        	_show_error('Password is required');
        	return;
        }
        
    	comm100_script_route('?action=sites&email='+email+'&password='+password+'&timezoneoffset='+(new Date()).getTimezoneOffset(), 
    	function (response) {
    		if (response.success) {
    			document.getElementById('cpanel_domain').value = response.cpanel_domain;
    			document.getElementById('main_chatserver_domain').value = response.main_chatserver_domain;
    			document.getElementById('standby_chatserver_domain').value = response.standby_chatserver_domain;
    			comm100_cpanel_domain = response.cpanel_domain;

    			var sites = response.response;
    			if (sites.length == 0) {
    				return;
    			}

    			document.getElementById('site_id').value = sites[0].id;
    			if (sites.length > 1) {
    				_show_sites(response.response);
    			} else {
			        _login(function () {
				        hide_element('login_submit_img');
				        document.getElementById('login_submit').disabled = false;
			        }, function (error) {
				        hide_element('login_submit_img');
				        document.getElementById('login_submit').disabled = false;

					    document.getElementById('login_error_').style.display = '';
					    document.getElementById('login_error_text').innerHTML = error;        	
			        })
    			}
    		} else {
		        show_element('login_error_');
		        document.getElementById('login_error_text').innerHTML = response.error;
		        
			    hide_element('login_submit_img');
			    document.getElementById('login_submit').disabled = false;
			}
    	}, function(error){
	        show_element('login_error_');
	        document.getElementById('login_error_text').innerHTML = error;
	        
		    hide_element('login_submit_img');
		    document.getElementById('login_submit').disabled = false;    		
    	});
    }
    return {
        register: _register,
        login: _login,
        get_plans: _get_plans,
        get_code: _get_code,
        get_editions: _get_editions,
        sites: _sites,
        choose_site: _choose_site,
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

function settings_select_change() {
	var selected = settings_get_selected_plan();
	document.getElementById('plan_id').value=selected.val;
	document.getElementById('plan_type').value=selected.type;
}
function settings_get_selected_plan() {
	var sel = document.getElementById('settings_select_plans_control');
	if (sel != null) {
		var options = document.getElementsByTagName('OPTION');
		for (var i = options.length - 1; i >= 0; i--) {
			var opt = options[i];
			if (opt.selected) {
				var vals = opt.value.split('_');
				return { 'val':  vals[0], 'type': vals[1]};
			}
		}
	}

	return { 'val': '0', 'type': '0'}
}
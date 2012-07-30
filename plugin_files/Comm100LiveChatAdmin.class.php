<?php

require_once('Comm100LiveChat.class.php');

if( !class_exists( 'WP_Http' ) )
    include_once( ABSPATH . WPINC. '/class-http.php' );


final class Comm100LiveChatAdmin extends Comm100LiveChat
{
	/**
	 * Plugin's version
	 */
	protected $plugin_version = null;


	/**
	 * Starts the plugin
	 */
	protected function __construct()
	{
		parent::__construct();
		
		add_action('admin_menu', array($this, 'admin_menu'));
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if (isset($_POST['settings_reset'])) {
				$this->reset_options();
			}
			elseif (isset($_POST['site_id'])) {
				$this->update_site_id($_POST['site_id']);
			}
		}
	}

	public static function get_instance()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}


	/**
	 * Set error reporting for debugging purposes
	 */
	public function error_reporting()
	{
		error_reporting(E_ALL & ~E_USER_NOTICE);
	}

	/**
	 * Returns this plugin's version
	 *
	 * @return string
	 */
	public function get_plugin_version()
	{
		if (is_null($this->plugin_version))
		{
			if (!function_exists('get_plugins'))
			{
				require_once(ABSPATH.'wp-admin/includes/plugin.php');
			}

			$plugin_folder = get_plugins('/'.plugin_basename(dirname(__FILE__).'/..'));
			$this->plugin_version = $plugin_folder['comm100livechat.php']['Version'];
		}

		return $this->plugin_version;
	}

	public function admin_menu()
	{
		add_menu_page(
			'Live Chat',
			'Live Chat',
			'administrator',
			'comm100livechat',
			array($this, 'livechat_settings_page'),
			$this->get_plugin_url().'/images/favicon.png'
		);

		add_submenu_page(
			'comm100livechat',
			'Settings',
			'Settings',
			'administrator',
			'comm100livechat_settings',
			array($this, 'livechat_settings_page')
		);

		add_submenu_page(
			'comm100livechat',
			'Online & Chat',
			'Online & Chat',
			'administrator',
			'comm100livechat_online',
			array($this, 'visitor_monitor_page')
		);

		add_submenu_page(
			'comm100livechat',
			'Control panel',
			'Control panel',
			'administrator',
			'comm100livechat_control_panel',
			array($this, 'control_panel_page')
		);

		// remove the submenu that is automatically added
		if (function_exists('remove_submenu_page'))
		{
			remove_submenu_page('comm100livechat', 'comm100livechat');
		}

		// Settings link
		add_filter('plugin_action_links', array($this, 'livechat_settings_link'), 10, 2);
	}

	private function get_post_data($key) {
		return isset($_POST[$key]) ? $_POST[$key] : '';
	}


	/**
	 * Displays settings page
	 */
	public function livechat_settings_page()
	{
		$base = Comm100LiveChat::get_instance()->get_plugin_url();

		$site_id = $this->get_site_id();
	?>
		<script type="text/javascript" src="<?php echo $base ?>/js/plugin.js">
		</script>

		<div style="padding-top:20px;padding-left:5px;">
			<img src="<?php echo $base ?>/images/logo.gif"/>
			<span><b>100% Communication, 100% Success</b></span>
		</div>
		<div class="wrap">
			<form method="POST" action="?page=comm100livechat" name="site_id_form">
				<input type="hidden" name="site_id" id="site_id" />
			</form>
		<?php if (!$this->is_installed()) { ?>
			<script type="text/javascript" src="<?php echo Comm100LiveChat::$service_url; ?>?action=session"></script>

			<div id="comm100livechat_have_account" class="metabox-holder">
				<div class="postbox">
					<h3>Do you already have a Comm100 account?</h3>
					<div class="postbox_content" style="padding-left:10px;">
						<ul id="choice_account">
							<li><input onclick="show_login()" type="radio" name="choice_account" id="choice_account_1" checked="checked" > <label for="choice_account_1">Yes, I already have a Comm100 account</label></li>
							<li><input onclick="show_registger()" type="radio" name="choice_account" id="choice_account_0"> <label for="choice_account_0">No, I want to create one</label></li>
						</ul>
					</div>
				</div>
			</div>

			<div id="comm100livechat_login" class="metabox-holder">
				<div class="postbox">
					<h3>Comm100 account</h3>
					<div class="postbox_content">
						
						<div style="padding:10px;display:none;" id="login_error_">
							<div style="border:1px solid #c00;background-color:#ffebe8;padding:10px;">
								<b>Error</b>:&nbsp;<span id="login_error_text"></span>
							</div>
						</div>

						<table class="form-table">
							<tr>
								<th scope="row"><label for="login_site_id" style="font-size:12px;">Site Id:</label></th>
								<td><input type="text" name="login_site_id" id="login_site_id" value="<?php echo $this->get_post_data('login_site_id'); ?>"></td>
								<td><a href="https://hosted.comm100.com/Admin/ForgotSiteId.aspx" target="_blank" tabindex="-1">Forgot Site Id?</a></td>
							</tr>
							<tr>
								<th scope="row"><label for="login_email" style="font-size:12px;">Email:</label></th>
								<td><input type="text" name="login_email" id="login_email" value="<?php echo $this->get_post_data('login_email'); ?>"></td>
								<td></td>
							</tr>
							<tr>
								<th scope="row"><label for="login_password" style="font-size:12px;">Password:</label></th>
								<td><input type="password" name="login_password" id="login_password"></td>
								<td><a href="https://hosted.comm100.com/Admin/ForgotPassword.aspx" target="_blank" tabindex="-1">Forgot your password?</a></td>
							</tr>
						</table>

						<p class="submit" style="padding-left:10px;">
							<input type="hidden" name="login_form" value="1">
							<input type="submit" id="login_submit" class="button-primary" name="login_submit" value="Login" 
							onclick="comm100_plugin.login();return false;">
							<img id="login_submit_img" src="<?php echo $base ?>/images/ajax_loader.gif" title="waitting" style="display:none;"/>
						</p>
					</div>
				</div>
			</div>

			<div id="comm100livechat_register" class="metabox-holder" style="display:none;">
				<div class="postbox" id="comm100livechat_register_loading">Loading...</div>
				<div class="postbox" id="comm100livechat_register_content" style="display:none;">
					<h3>Create a new Comm100 account:</h3>
					<div class="postbox_content">
						<div style="padding:10px;display:none;" id="register_error">
							<div style="border:1px solid #c00;background-color:#ffebe8;padding:10px;">
								<b>Error</b>:&nbsp;<span id="register_error_text"></span>
							</div>
						</div>

						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="register_edition" style="font-size:12px;">Edition: </label>
								</th>
								<td>
									<script type="text/javascript">
										comm100_plugin.get_editions(function(editions) {
											document.getElementById('comm100livechat_register_loading').style.display = 'none';
											document.getElementById('comm100livechat_register_content').style.display = '';

											var list_editions = document.getElementById('register_edition');

											for (var i = 0; i < editions.length; i++) {
												var opt = document.createElement('OPTION');
												opt.innerHTML = editions[i].name.replace('Comm100 Live Chat ', '') + ' $' + editions[i].price + ' / month';
												opt.value = editions[i].id;
												if (16 == editions[i].id) {
													opt.selected = 'selected';
												}
												list_editions.appendChild(opt);
											};
										});
									</script>
									<select id="register_edition" name="register_edition" style="width:230px"></select> (15 days free trail)
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="register_name" style="font-size:12px;">Full Name:</label>
								</th>
								<td>
									<input id="register_name" name="register_name" type="text" style="width:230px"
										onblur="validate_register_input('name')" value="<?php echo $this->get_post_data('register_name'); ?>"/>
									<span style="color:red">* </span><span id="register_name_required" style="color:red;display:none;">Required</span>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="register_email" style="font-size:12px;">Email:</label>
								</th>
								<td>
									<input id="register_email" name="register_email" type="text" style="width:230px" onblur="validate_register_input_email('email');" value="<?php echo $this->get_post_data('register_email'); ?>"/>
									<span style="color:red">* </span>
									<span id="register_email_required" style="color:red;display:none;">Required</span>
									<span id="register_email_valid" style="color:red;display:none;">Invalid Email</span>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="register_password" style="font-size:12px;">Password:</label>
								</th>
								<td>
									<input id="register_password" name="register_password" type="password" style="width:230px" onblur="validate_register_input('password')"/>
									<span style="color:red">* </span><span id="register_password_required" style="color:red;display:none;">Required</span>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="register_phone" style="font-size:12px;">Telephone:</label>
								</th>
								<td>
									<input id="register_phone" name="register_phone" type="text" style="width:230px" onblur="validate_register_input('phone')" value="<?php echo $this->get_post_data('register_phone'); ?>"/>
									<span style="color:red">* </span><span id="register_phone_required" style="color:red;display:none;">Required</span>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="register_website" style="font-size:12px;">Website:</label>
								</th>
								<td>
									<input id="register_website" name="register_website" type="text" style="width:230px" value="<?php echo $this->get_post_data('register_website'); ?>"/>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="register_verification_code" style="font-size:12px;">Verification Code:</label>
								</th>
								<td>
									<div>
										<input id="register_verification_code" name="register_verification_code" type="text" style="width: 150px;float: left;margin-right: 6px;" onblur="validate_register_input('verification_code')"/>
										<span style="float: left;margin-right: 4px;">
                                            <img title="Click to change a verification code." alt="Verification Code" 
                                                style="cursor:pointer;border: solid 1px #DFDFDF;-webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;" 
                                                onclick="this.src = 'https://hosted.comm100.com/AdminPluginService/(S(' + comm100livechat_session + '))/livechatplugin.ashx?action=verification_code&r='+(Math.random() * 100);" id="register_verification_code_image" src=""/>
                                        </span>
										<script type="text/javascript">
											setTimeout(function() {
												document.getElementById('register_verification_code_image').src = 'https://hosted.comm100.com/AdminPluginService/(S(' + comm100livechat_session + '))/livechatplugin.ashx?action=verification_code';
											}, 100);
										</script>
										
										<input id="register_ip" type="hidden" value="<?php echo $_SERVER['REMOTE_ADDR']?>"/>

										<span style="color:red">* </span><span id="register_verification_code_required" style="color:red;display:none;">Required</span>
									</div>
								</td>
							</tr>
						</table>

						<div class="submit" style="padding-left:10px;">
							<input type="submit" id="register_submit" name="register_submit" value="Create Account" class="button-primary" onclick="if (validate_register_inputs()){comm100_plugin.register();}return false;"/>
							<img id="register_submit_img" src="<?php echo $base ?>/images/ajax_loader.gif" title="Please wait..." alt="waitting" style="display:none;"/>
						
                            <div style="padding:5px 0 0 4px;font-size: smaller;">
                                By clicking "Create Account", you agree to Comm100 <a href="http://hosted.comm100.com/admin/help/Comm100-Agreement.htm" id="aHref" target="_blank">Hosted Service Agreement</a> and <a href="http://www.comm100.com/privacy/" target="_blank">Privacy Policy</a>.
                            </div>
                        </div>
					</div>
				</div>
			</div>

		<?php } else { ?>
			<div id="comm100livechat_settings" class="metabox-holder">
				<div class="postbox">
				<form method="POST" action="?page=comm100livechat" name="settings_form">
					<h3>Your live chat has been installed successfully.</h3>
					<div class="postbox_content" style="padding:10px;">
						<div style="padding-bottom:10px;">Your Comm100 account info: Site Id: <?php echo $this->get_site_id(); ?>.</div>
						<div style="padding-bottom:10px;">Go to the <a href="widgets.php">Widgets</a> page to activate live chat widget and set personal customizations for your own live chat.</div>
						<div class="submit">
							<input type="submit" name="settings_reset" value="Reset your settings" class="button-primary" onclick="if (!confirm('Are you sure you wish to reset your settings?'))return false;"/>
						</div>
					</div>
				</form>
				</div>
			</div>
		<?php } ?>
		</div>
<?php
	}

	/**
	 * Displays control panel page
	 */
	public function control_panel_page()
	{
		$site_id = $this->get_site_id();
		$cpanel_url = "https://hosted.comm100.com/adminmanage/login.aspx?apptype=1&siteId=" . $site_id;
        
		$base = Comm100LiveChat::get_instance()->get_plugin_url();

		echo <<<HTML
		    <script type="text/javascript" src="{$base}/js/page.js">
		    </script>
			<iframe id="control_panel" src="{$cpanel_url}" frameborder="0" width="100%" height="700"></iframe>
			<p>Optionally, open the Control panel in an <a href="{$cpanel_url}" target="_blank">external window</a>.</p>
HTML;
	}
    
	public function visitor_monitor_page()
	{
		$site_id = $this->get_site_id();
		$cpanel_url = "https://hosted.comm100.com/livechat/visitormonitor.aspx";
        if ($site_id > 0)
            $cpanel_url = $cpanel_url . '?siteId=' . $site_id;
            
		$base = Comm100LiveChat::get_instance()->get_plugin_url();

		echo <<<HTML
		    <script type="text/javascript" src="{$base}/js/page.js">
		    </script>
			<iframe id="control_panel" src="{$cpanel_url}" frameborder="0" width="100%" height="700" style="margin:10px 0 0px 0"></iframe>
			<div>Optionally, open the Control panel in an <a href="{$cpanel_url}" target="_blank">external window</a>.</div>
HTML;
	}

	public function livechat_settings_link($links, $file)
	{
		if (basename($file) !== 'comm100livechat.php')
		{
			return $links;
		}

		$settings_link = sprintf('<a href="admin.php?page=comm100livechat_settings">%s</a>', __('Settings'));
		array_unshift ($links, $settings_link); 
		return $links;
	}


	protected function reset_options()
	{
		delete_option('comm100livechat_site_id');
		$this->site_id = 0;
	}

	protected function update_site_id($site_id)
	{
		update_option('comm100livechat_site_id', $site_id);
		$this->site_id = $site_id;
	}
}
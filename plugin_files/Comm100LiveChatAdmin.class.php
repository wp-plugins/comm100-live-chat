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
    protected $show_success = FALSE;

	/**
	 * Starts the plugin
	 */
	protected function __construct() {
		parent::__construct();
		
		add_action('admin_menu', array($this, 'admin_menu'));

		//$show_success = TRUE;

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if (isset($_POST['site_id'])) {
				$this->update_site_id($_POST['site_id']);
				$this->update_email($_POST['email']);
				$this->update_plan_id($_POST['plan_id']);
				$this->update_plan_type($_POST['plan_type']);
				$this->update_cpanel_domain($_POST['cpanel_domain']);
				$this->update_main_chatserver_domain($_POST['main_chatserver_domain']);
				$this->update_standby_chatserver_domain($_POST['standby_chatserver_domain']);
                $show_success = TRUE;
			}
		} else {
			if (isset($_GET['reset'])) {
				$this->reset_options();
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
			'Account Setup',
			'Account Setup',
			'administrator',
			'comm100livechat_settings',
			array($this, 'livechat_settings_page')
		);

		add_submenu_page(
			'comm100livechat',
			'Get Online & Chat',
			'Get Online & Chat',
			'administrator',
			'comm100livechat_online',
			array($this, 'visitor_monitor_page')
		);

		add_submenu_page(
			'comm100livechat',
			'Control Panel',
			'Control Panel',
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
		$cpanel_domain = $this->cpanel_domain;

		$query_site_id = $_GET['siteId'];
		$query_email = $_GET['email'];
		
	?>
		<script type="text/javascript">
			var comm100_cpanel_domain = "<?php echo $cpanel_domain ?>";
		</script>
		<script type="text/javascript" src="<?php echo $base ?>/js/plugin.js?v=1">
		</script>

		<div style="padding-top:20px;padding-left:5px;">
			<img alt="Comm100" title="Comm100" src="<?php echo $base ?>/images/logo.gif" />
			<span><b>100% Communication, 100% Success</b></span>
		</div>
		<div class="wrap">
			<form method="POST" action="?page=comm100livechat&show_success=true" name="site_id_form">
				<input type="hidden" name="site_id" id="site_id" />
				<input type="hidden" name="email" id="email" />
				<input type="hidden" name="plan_id" id="plan_id" value="0" />
				<input type="hidden" name="plan_type" id="plan_type" />
				<input type="hidden" name="code" id="code" />
				<input type="hidden" name="cpanel_domain" id="cpanel_domain" />
				<input type="hidden" name="main_chatserver_domain" id="main_chatserver_domain" />
				<input type="hidden" name="standby_chatserver_domain" id="standby_chatserver_domain" />
			</form>
		<?php if (!$this->is_installed()) { ?>
			<div id="comm100livechat_login" class="metabox-holder" >
				<div class="postbox">
					<h3>Set up Your Comm100 Live Chat</h3>
					<div class="postbox_content">
						
						<div style="padding:10px;display:none;" id="login_error_">
							<div style="border:1px solid #c00;background-color:#ffebe8;padding:10px;border-radius: 3px;">
								<b>Error</b>:&nbsp;<span id="login_error_text"></span>
							</div>
						</div>

						<div style="padding:10px 0 10px 10px;">
							<input id="login1" type="radio" name="login" onclick="document.getElementById('login_new').style.display='block';document.getElementById('login_old').style.display='none';" 
							<?php if (!isset($_GET['email'])) echo 'checked="checked"'; ?> />
							<label for="login1" style="padding-left:0px;">I'm new to Comm100</label>
						</div>

                        <div style="padding: 5px 0 0 30px;<?php if (isset($_GET['email'])) echo 'display:none;'; ?>" id="login_new">
                        	<input type="submit" value="Sign Up" class="button-primary" onclick="window.location.href='https://www.comm100.com/secure/siteregister.aspx?language=0&source=wordpress&planId=74&code=841791C5&return=' + encodeURIComponent(window.location.href)"/>
                        </div>

						<div style="padding:15px 0 10px 10px;">				
							<input id="login2" <?php if (isset($_GET['email'])) echo 'checked="checked"'; ?>
							 type="radio" name="login" onclick="document.getElementById('login_new').style.display='none';document.getElementById('login_old').style.display='block';"/>
							<label for="login2">I already have a Comm100 Live Chat account</label>
						</div>
						<div id="login_old" style="padding:0 0 0 25px;<?php if (!isset($_GET['email'])) echo 'display:none;'; ?>">
							<table class="form-table">
							<!-- 	<tr>
									<th scope="row" style="width: 100px;"><label for="login_site_id" style="font-size:12px;">Site ID:</label></th>
									<td><input type="text" style="width: 230px;" name="login_site_id" id="login_site_id" value="<?php echo $query_site_id ?>"></td>
									<td></td>
								</tr> -->
								<tr>
									<th scope="row" style="width: 100px;"><label for="login_email" style="font-size:12px;">Email:</label></th>
									<td><input type="text" style="width: 230px;" name="login_email" id="login_email" value="<?php echo $query_email ?>"></td>
									<td></td>
								</tr>
								<tr>
									<th scope="row" style="width: 100px;"><label for="login_password" style="font-size:12px;">Password:</label></th>
									<td><input type="password" style="width: 230px;" name="login_password" id="login_password">
	                                    <span style="padding-left: 5px;">
	                                        <a href="https://www.comm100.com/secure/forgotpassword.aspx" target="_blank" tabindex="-1">Forgot your password?</a>
	                                    </span>
	                                </td>
								</tr>
							</table>

							<p class="submit" style="padding-left:10px;">
								<input type="hidden" name="login_form" value="1">
								<input type="submit" id="login_submit" class="button-primary" name="login_submit" value="Link Up" 
									onclick="comm100_plugin.sites();return false;">
								<img id="login_submit_img" src="<?php echo $base ?>/images/ajax_loader.gif" title="waitting" style="display:none;"/>
							</p>
						</div>
					</div>
				</div>
			</div>

			<div id="comm100livechat_choose_site" class="metabox-holder" style="display:none;">
                 <div class="postbox">   
				    <h3>There are 
				    	<span style="color: #EFC44C;font-weight: bold;font-size: larger;" id="num_sites"></span> 
				    	accounts associated with this operator email. Please choose one to link up.
				    </h3>
				    <div class="postbox_content" style="padding:0px 0 10px 20px;">            		
						<div style="padding:10px;display:none;" id="choose_site_error_">
							<div style="border:1px solid #c00;background-color:#ffebe8;padding:10px;border-radius: 3px;">
								<b>Error</b>:&nbsp;<span id="choose_site_error_text"></span>
							</div>
						</div>
                        <div style="color: #464646;padding: 20px 0 10px 0" id="login_sites">
                        </div>
						<p class="submit" style="padding:5px 0 0 0;">
							<input type="submit" id="choose_site_submit" class="button-primary" name="login_submit" value="Link Up" 
								onclick="comm100_plugin.choose_site();return false;">
							<img id="choose_site_submit_img" src="<?php echo $base ?>/images/ajax_loader.gif" title="waitting" style="display:none;"/>
						</p>
				    </div>
                </div>
            </div>

            <div class="metabox-holder" id="comm100livechat_choose_plan" style="display:none;">
            	<div class="postbox">
					<h3>Select a Code Plan</h3>
					<div class="postbox_content" style="padding:15px 10px 10px 10px;">
	                    We noticed that you have multiple code plans in your Comm100 account. Please select one for this site.
	                    <div style="padding: 6px 0 10px 0px;" id="settings_select_plans">
	                    </div>

	                    <input type="hidden" value="<?php echo $this->get_plan_id()?>" name="plan_id" id="settings_plan_id"/>
	                    <input type="hidden" value="" name="plan_type" id="settings_plan_type"/>
	     				<script type="text/javascript">
	     					function all_submit_click() {
	     						var plan = document.getElementById('plan_id').value;
	     						if ((plan == '0') || (plan == '')) {
	     							alert('Please select a code plan.');
	     							return false;
	     						}
	     						document.forms['site_id_form'].submit();
	     						return true;
	     					}
	     				</script>
	                    <input type="button" onclick="return all_submit_click();" class="button-primary" value="Save"/>
					</div>
				</div>
            </div>
		<?php } else { ?>
			<div id="comm100livechat_settings" class="metabox-holder">
                <div id="success" style="display:none;background-color: #E1FDC5;border-radius:3px;-moz-border-radius:3px;-webkit-border-radius:3px; border:solid 1px #6AC611;margin: 5px 0 15px;padding: 10px 0 10px 15px;">
                    Congratulations! Your Comm100 Live Chat account has been set up successfully.
                </div>
                <script type="text/javascript">
                    setTimeout(function() {
                        if(window.location.href.indexOf('show_success') >= 0) {
                            var element = document.getElementById('success');
                            if(element) {
                                element.style.display = '';
                            }
                        } 
                    }, 10);
               </script>
				<div class="postbox">
					<h3>Your Linked Comm100 Account</h3>
					<div class="postbox_content" style="padding:10px;">
						<div style="padding-bottom:10px;"> 
                            <!-- <div style="padding: 10px 0 0 0px;display:none;">Site Id: <?php echo $this->get_site_id(); ?></div> -->
                            <div style="padding: 10px 0 0 0px;">Email: <?php echo urldecode($this->get_email()); ?></div>
                        </div>
                        <div style="padding:10px 0 10px 0;<?php if ($this->get_plan_type()==2) echo 'display:none;';?>">
                        	Now you can:
					    	<ol>
					    		<li>
					    			<a target="_blank" href="<?php echo get_site_url(); ?>">
					    				See how the chat button looks on your site
					    			</a>
					    		</li>
					    		<li>
					    			<a target="_blank" href="https://<?php echo $this->get_cpanel_domain(); ?>/LiveChat/VisitorMonitor.aspx?siteId=<?php echo Comm100LiveChat::get_instance()->get_site_id(); ?>">
					    				Get online and chat with your visitors
					    			</a>
					    		</li>
					    		<li>
					    			<a target="_blank" href="http://<?php echo $this->get_cpanel_domain(); ?>/LiveChatFunc/PlanDetailManage.aspx?codePlanId=<?php echo Comm100LiveChat::get_instance()->get_plan_id()?>&ifEditPlan=true&siteid=<?php echo Comm100LiveChat::get_instance()->get_site_id()?>">
					    				Customize your live chat
					    			</a>
					    		</li>
					    	</ol>
                        </div>
                        <div style="<?php if ($this->get_plan_type()!=2) echo 'display:none;';?>">
							<div style="padding:10px 0 10px 0;">Activate the Comm100 Live Chat widget now and start chatting with your visitors!</div>
                        	<input type="button" class="button-primary" style="" value="Activate Now" onclick="window.location.href='widgets.php'"/>
                    	</div>
					</div>
				</div>

                <p style="color: #999;font-size: smaller;margin-top: -15px;">
                    Something went wrong? <a style="color: #999;" href="?page=comm100livechat_settings&amp;reset=1" 
                    onclick="if (!confirm('Are you sure you wish to reset your account?'))return false;">Reset your account</a>.
                </p>
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
		$cpanel_domain = $this->get_cpanel_domain();
        
		$base = Comm100LiveChat::get_instance()->get_plugin_url();

		echo <<<HTML
		    <script type="text/javascript" src="{$base}/js/page.js">
		    </script>
			<iframe id="control_panel" src="https://www.comm100.com/secure/login.aspx" frameborder="0" width="100%" height="700"></iframe>
			<div>You may also <a href="https://www.comm100.com/secure/login.aspx" target="_blank">access the Control Panel in a new window</a>.</div>
HTML;
	}
    
	public function visitor_monitor_page()
	{
		$site_id = $this->get_site_id();
		$cpanel_url = 'https://' . $this->get_cpanel_domain() . '/livechat/visitormonitor.aspx';
        if ($site_id > 0)
            $cpanel_url = $cpanel_url . '?siteId=' . $site_id;
            
		$base = Comm100LiveChat::get_instance()->get_plugin_url();

		echo <<<HTML
		    <script type="text/javascript" src="{$base}/js/page.js">
		    </script>
			<iframe id="control_panel" src="{$cpanel_url}" frameborder="0" width="100%" height="700" style="margin:10px 0 0px 0"></iframe>
			<div>You may also <a href="{$cpanel_url}" target="_blank">get online and chat in a new window</a>.</div>
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
		delete_option('comm100livechat_email');
		$this->email = '';
		delete_option('comm100livechat_code');
		$this->code = '';
		delete_option('comm100livechat_plan_id');
		$this->plan_id = 0;
		delete_option('comm100livechat_plan_type');
		$this->plan_type = 0;
		delete_option('comm100livechat_cpanel_domain');
		$this->cpanel_domain = '';
		delete_option('comm100livechat_main_chatserver_domain');
		$this->main_chatserver_domain = '';
		delete_option('comm100livechat_standby_chatserver_domain');
		$this->standby_chatserver_domain = '';
	}

	protected function update_main_chatserver_domain($main_chatserver_domain)
	{
		update_option('comm100livechat_main_chatserver_domain', $main_chatserver_domain);
		$this->main_chatserver_domain = $main_chatserver_domain;
	}
	
	protected function update_standby_chatserver_domain($standby_chatserver_domain)
	{
		update_option('comm100livechat_standby_chatserver_domain', $standby_chatserver_domain);
		$this->standby_chatserver_domain = $standby_chatserver_domain;
	}

	protected function update_cpanel_domain($cpanel_domain) 
	{
		update_option('comm100livechat_cpanel_domain', $cpanel_domain);
		$this->cpanel_domain = $cpanel_domain;
	}
	protected function update_site_id($site_id)
	{
		update_option('comm100livechat_site_id', $site_id);
		$this->site_id = $site_id;
	}
	protected function update_email($email)
	{
		update_option('comm100livechat_email', $email);
		$this->email = $email;
	}
	protected function update_code($code)
	{
		update_option('comm100livechat_code', $code);
		$this->code = $code;
	}
	protected function update_plan_id($plan_id)
	{
		update_option('comm100livechat_plan_id', $plan_id);
		$this->plan_id = $plan_id;
	}
	protected function update_plan_type($plan_type)
	{
		update_option('comm100livechat_plan_type', $plan_type);
		$this->plan_type = $plan_type;
	}
}
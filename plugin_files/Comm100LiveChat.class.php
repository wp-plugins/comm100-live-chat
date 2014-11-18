<?php

if(!function_exists('_log')){
  function _log( $message ) {
    if( WP_DEBUG === true ){
      if( is_array( $message ) || is_object( $message ) ){
        error_log( print_r( $message, true ) );
      } else {
        error_log( $message );
      }
    }
  }
}


class Comm100LiveChat
{
	// singleton pattern
	protected static $instance;
	// public static $service_url = 'https://hosted.comm100.com/AdminPluginService/livechatplugin.ashx';
	//public static $service_url = 'http://192.168.8.48/plugin/livechatplugin.ashx';

	/**
	 * Absolute path to plugin files
	 */
	protected $plugin_url = null;
	protected $site_id = null;
    protected $email = null;
	protected $plan_id = null;
    protected $plan_type = null;   //float button 0, monitor 1, others 2
    protected $cpanel_domain = null;
    protected $main_chatserver_domain = null;
    protected $standby_chatserver_domain = null;

	/**
	 * Starts the plugin
	 */
	protected function __construct()
	{
		add_action('widgets_init', create_function('', 'register_widget("Comm100LiveChatWidget");'));
		// add_action ('wp_footer', array($this, 'write_button_code'));
		add_action ('wp_head', array($this, 'write_button_code'));
	}
	
	public function write_button_code()
	{
		if ($this->is_installed()) {
			$plan_type = $this->get_plan_type();
			
			if (($plan_type == 0) || ($plan_type == 1)) {
				echo '<script type="text/javascript">
				    var Comm100API=Comm100API||{chat_buttons:[]};
				    Comm100API.chat_buttons.push({code_plan:'.$this->get_plan_id().',div_id:\'comm100-button-'.$this->get_plan_id().'\'});
					Comm100API.site_id='.$this->get_site_id().';Comm100API.main_code_plan='.$this->get_plan_id().';
				    (function(){
						function write_code(){ 
					    	if (document.body) {
					        	var lc=document.createElement(\'script\'); 
					        	lc.type=\'text/javascript\';lc.async=true;
					        	lc.src=\'https://'.$this->get_main_chatserver_domain().'/livechat.ashx?siteId=\'+Comm100API.site_id;
					        	var s=document.getElementsByTagName(\'script\')[0];s.parentNode.insertBefore(lc,s);

					        	setTimeout(function() {
					        		if (!Comm100API.loaded) {
							            var lc1 = document.createElement(\'script\');
							            lc1.type = \'text/javascript\';
							            lc1.async = true;
							            lc1.src = \'https://'.$this->get_standby_chatserver_domain().'/livechat.ashx?siteId=\' + Comm100API.site_id;
							            var s1 = document.getElementsByTagName(\'script\')[0];
							            s1.parentNode.insertBefore(lc1, s1);
					        		}
					        	}, 5000)
					        } else {
					        	setTimeout(write_code, 500);
					        }
						}
						setTimeout(write_code, 500);
				    })();</script>';
			} else {
				echo '<script type="text/javascript">
			    var Comm100API=Comm100API||{chat_buttons:[]};
			    Comm100API.chat_buttons.push({code_plan:'.$this->get_plan_id().',div_id:\'comm100-button-'.$this->get_plan_id().'\'});
				Comm100API.site_id='.$this->get_site_id().';Comm100API.main_code_plan='.$this->get_plan_id().';
				(function(){
					function write_code(){ 
						var div = document.getElementById(\'comm100-button-'.$this->get_plan_id().'\');
						if (div) {
					        var lc=document.createElement(\'script\'); 
					        lc.type=\'text/javascript\';lc.async=true;
					        lc.src=\'https://'.$this->get_main_chatserver_domain().'/livechat.ashx?siteId=\'+Comm100API.site_id;
					        var s=document.getElementsByTagName(\'script\')[0];s.parentNode.insertBefore(lc,s);

		                	setTimeout(function() {
		                		if (!Comm100API.loaded) {
		        		            var lc1 = document.createElement(\'script\');
		        		            lc1.type = \'text/javascript\';
		        		            lc1.async = true;
		        		            lc1.src = \'https://'.$this->get_standby_chatserver_domain().'/livechat.ashx?siteId=\' + Comm100API.site_id;
		        		            var s1 = document.getElementsByTagName(\'script\')[0];
		        		            s1.parentNode.insertBefore(lc1, s1);
		                		}
		                	}, 5000)
						} else {
							setTimeout(write_code, 500);		
						}
					}
					setTimeout(write_code, 500);
				})();</script>';
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
	 * Returns plugin files absolute path
	 *
	 * @return string
	 */
	public function get_plugin_url()
	{
		if (is_null($this->plugin_url))
		{
			$this->plugin_url = WP_PLUGIN_URL.'/comm100-live-chat/plugin_files';
		}

		return $this->plugin_url;
	}

	public function is_installed()
	{
		return $this->get_site_id() > 0 && $this->get_plan_id() > 0;
	}

	public function get_site_id()
	{
		if (is_null($this->site_id))
		{
			$this->site_id = get_option('comm100livechat_site_id');
		}

		// siteId must be >= 0
		// also, this prevents from NaN values
		$this->site_id = max(0, $this->site_id);

		return $this->site_id;
	}
	public function get_email()
	{
		if (is_null($this->email))
		{
			$this->email = get_option('comm100livechat_email');
		}

		return $this->email;
	}	
	public function get_plan_id()
	{
		if (is_null($this->plan_id))
		{
			$this->plan_id = get_option('comm100livechat_plan_id');
		}

		return $this->plan_id;
	}
	public function get_plan_type()
	{
		if (is_null($this->plan_type))
		{
			$this->plan_type = get_option('comm100livechat_plan_type');
		}

		return $this->plan_type;
	}
	public function get_cpanel_domain() 
	{
		if (is_null($this->cpanel_domain))
		{
			$this->cpanel_domain = get_option('comm100livechat_cpanel_domain');
		}
		return $this->cpanel_domain;
	}
	public function get_main_chatserver_domain() 
	{
		if (is_null($this->main_chatserver_domain))
		{
			$this->main_chatserver_domain = get_option('comm100livechat_main_chatserver_domain');
		}
		
		if (is_null($this->main_chatserver_domain) || $this->main_chatserver_domain == '') {
			$this->main_chatserver_domain = 'chatserver.comm100.com';
		}
		
		return $this->main_chatserver_domain;
	}
	public function get_standby_chatserver_domain() 
	{
		if (is_null($this->standby_chatserver_domain))
		{
			$this->standby_chatserver_domain = get_option('comm100livechat_standby_chatserver_domain');
		}

		if (is_null($this->standby_chatserver_domain) || $this->standby_chatserver_domain == '') {
			$this->standby_chatserver_domain = 'hostedmax.comm100.com/chatserver';
		}

		return $this->standby_chatserver_domain;
	}
}




class Comm100LiveChatWidget extends WP_Widget
{
	public function __construct() {
		parent::__construct('comm100livechat_widget', 'Comm100 Live Chat', 
			array('description' => 'Add a chat button to your site and start chatting with your visitors.'));
	}

	public function update( $new_instance, $old_instance ) {
		return $old_instance;
	}

	public function form($instance)
	{
?>
		Your live chat widget has been activated successfully!
		<div style="padding:10px 0 10px 0;">
	    	Now you can:
	    	<ol>
	    		<li>
	    			<a target="_blank" href="<?php echo get_site_url(); ?>">
	    				See how the chat button looks on your site
	    			</a>
	    		</li>                        	
	    		<li>
	    			<a target="_blank" href="https://<?php echo Comm100LiveChat::get_instance()->get_cpanel_domain(); ?>/LiveChat/VisitorMonitor.aspx?siteId=<?php echo Comm100LiveChat::get_instance()->get_site_id(); ?>">
	    				Get online and chat with your visitors
	    			</a>
	    		</li>                       
	    		<li>
	    			<a target="_blank" href="http://<?php echo Comm100LiveChat::get_instance()->get_cpanel_domain(); ?>/LiveChatFunc/PlanDetailManage.aspx?codePlanId=<?php echo Comm100LiveChat::get_instance()->get_plan_id()?>&ifEditPlan=true&siteid=<?php echo Comm100LiveChat::get_instance()->get_site_id()?>">
	    				Customize your live chat
	    			</a>
	    		</li>
	    	</ol>
	    </div>
<?php
	}

	public function widget($args, $instance)
	{
		if (Comm100LiveChat::get_instance()->is_installed()) {
			echo '<div id="comm100-button-'.Comm100LiveChat::get_instance()->get_plan_id().'"></div>';	
		}		
	}
}


?>
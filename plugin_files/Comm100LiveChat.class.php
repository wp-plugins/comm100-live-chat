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
	public static $service_url = 'https://hosted.comm100.com/AdminPluginService/livechatplugin.ashx';
	//public static $service_url = 'http://192.168.8.48/plugin/livechatplugin.ashx';

	/**
	 * Absolute path to plugin files
	 */
	protected $plugin_url = null;
	protected $site_id = null;
    protected $email = null;
	protected $plan_id = null;
    protected $plan_type = null;   //float button 0, monitor 1, others 2

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
			$url = 'https://chatserver.comm100.com/js/livechat.js?siteId='.$this->get_site_id().'&planId='.$this->get_plan_id();
			
			$plan_type = $this->get_plan_type();
			
			if ($plan_type == 0) {
				echo '<script type="text/javascript" src="'.$url.'"></script>';
			} else if ($plan_type == 1) {
				echo '<script type="text/javascript">
				(function(){
					setTimeout(function(){
						var div = document.createElement(\'div\');
						div.id = \'LiveChatDiv\';
						document.body.insertBefore(div, document.body.firstChild);
						var script = document.createElement(\'script\');
						script.type = \'text/javascript\';
						script.async = true;
						script.src = "'.$url.'";
						document.body.insertBefore(script, document.body.firstChild);
					}, 800);
				})();</script>';
			} else {
				echo '<script type="text/javascript">
				(function(){
					function write_code(){ 
						var div = document.getElementById(\'LiveChatDiv\');
						if (div) {
							var script = document.createElement(\'script\');
							script.type = \'text/javascript\';
							script.src = "'.$url.'";
							script.async = true;
							document.body.insertBefore(script, document.body.firstChild);
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
	    			<a target="_blank" href="https://hosted.comm100.com/LiveChat/VisitorMonitor.aspx?siteId=<?php echo Comm100LiveChat::get_instance()->get_site_id(); ?>">
	    				Get online and chat with your visitors
	    			</a>
	    		</li>                       
	    		<li>
	    			<a target="_blank" href="http://hosted.comm100.com/LiveChatFunc/PlanDetailManage.aspx?codePlanId=<?php echo Comm100LiveChat::get_instance()->get_plan_id()?>&ifEditPlan=true&siteid=<?php echo Comm100LiveChat::get_instance()->get_site_id()?>">
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
			echo '<div id="LiveChatDiv"></div>';	
		}		
	}
}


?>
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

	/**
	 * Starts the plugin
	 */
	protected function __construct()
	{
		//add_action('widgets_init', create_function('', 'register_widget("Comm100LiveChatWidget");'));
		add_action ('wp_footer', array($this, 'write_button_code'));
	}

	public function write_button_code()
	{
		echo str_replace('\\"', '"', $this->get_code());
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
		return $this->get_site_id() > 0;
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
	public function get_code()
	{
		if (is_null($this->code))
		{
			$this->code = get_option('comm100livechat_code');
		}

		return $this->code;
	}
}
?>
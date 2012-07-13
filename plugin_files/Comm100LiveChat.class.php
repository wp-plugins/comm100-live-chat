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
	public static $service_url = 'http://hosted.comm100.com/AdminPluginService/livechatplugin.ashx';
	//public static $service_url = 'http://192.168.8.48/plugin/livechatplugin.ashx';

	/**
	 * Absolute path to plugin files
	 */
	protected $plugin_url = null;
	protected $site_id = null;
	protected $plan_id = null;

	/**
	 * Starts the plugin
	 */
	protected function __construct()
	{
		add_action('widgets_init', create_function('', 'register_widget("Comm100LiveChatWidget");'));
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
}

class Comm100LiveChatWidget extends WP_Widget
{
	public function __construct() {
		parent::__construct('comm100livechat_widget', 'Comm100 Live Chat', 
			array('description' => 'Install Chat Button to let your visitors start a chat with you.'));
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['plan_id'] = strip_tags($new_instance['plan_id']);

		$site_id = Comm100LiveChat::get_instance()->get_site_id();

		$instance['code'] = strip_tags($new_instance['code']);
		
		return $instance;
	}

	public function form($instance)
	{
		$plan_id = isset($instance['plan_id']) ? $instance['plan_id'] : '0';
		$site_id = Comm100LiveChat::get_instance()->get_site_id();
		$code = isset($instance['code']) ? $instance['code'] : '';

		
		$base = Comm100LiveChat::get_instance()->get_plugin_url();
?>
		<script type="text/javascript" src="<?php echo Comm100LiveChat::$service_url; ?>?action=session"></script>
		<script type="text/javascript" src="<?php echo $base ?>/js/plugin.js"></script>

		<script type="text/javascript">
			function html_code(html) {
				var div=document.createElement("div");
				var txt=document.createTextNode(html);
				div.appendChild(txt);return div.innerHTML;
			}
			comm100_plugin.get_plans(<?php echo $site_id?>, function(plans) {
				if (plans.length == 0) {
					alert('Error: no code plan.');
					return;
				}


				if (plans.length > 1) {
					var list_plans = document.getElementById('select_<?php echo $this->get_field_id('plan_id'); ?>');

					for (var i = 0; i < plans.length; i++) {
						var opt = document.createElement('OPTION');
						opt.innerHTML = plans[i].name;
						opt.value = plans[i].id;
						if ('<?php echo $plan_id ?>' == plans[i].id) {
							opt.selected = 'selected';
						}
						list_plans.appendChild(opt);
					};

					list_plans.onchange = function() {
						document.getElementById("loading_<?php echo $this->get_field_id('plan_id'); ?>").style.display = '';
						document.getElementById("multi_plan_<?php echo $this->get_field_id('plan_id'); ?>").style.display = 'none';

						document.getElementById("<?php echo $this->get_field_id('plan_id'); ?>").value = this.value;
						comm100_plugin.get_code(<?php echo $site_id?>, this.value, function(code) {
							document.getElementById("loading_<?php echo $this->get_field_id('plan_id'); ?>").style.display = 'none';
							document.getElementById("multi_plan_<?php echo $this->get_field_id('plan_id'); ?>").style.display = '';

							document.getElementById("<?php echo $this->get_field_id('code'); ?>").value = encodeURIComponent(code);
						});
					}

					document.getElementById("loading_<?php echo $this->get_field_id('plan_id'); ?>").style.display = 'none';
					document.getElementById("multi_plan_<?php echo $this->get_field_id('plan_id'); ?>").style.display = '';
					document.getElementById("single_plan_<?php echo $this->get_field_id('plan_id'); ?>").style.display = 'none';
				}
				else {
					comm100_plugin.get_code(<?php echo $site_id?>, plans[0].id, function(code) {
						document.getElementById("<?php echo $this->get_field_id('code'); ?>").value = encodeURIComponent(code);

						document.getElementById("loading_<?php echo $this->get_field_id('plan_id'); ?>").style.display = 'none';
						document.getElementById("<?php echo $this->get_field_id('plan_id'); ?>").value = plans[0].id;
						document.getElementById("a_<?php echo $this->get_field_id('plan_id'); ?>").href = 
							'http://hosted.comm100.com/LiveChatFunc/PlanDetailManage.aspx?siteId=<?php echo $site_id; ?>&ifEditPlan=true&codeplanId=' + plans[0].id;
						
						document.getElementById("multi_plan_<?php echo $this->get_field_id('plan_id'); ?>").style.display = 'none';
						document.getElementById("single_plan_<?php echo $this->get_field_id('plan_id'); ?>").style.display = '';
					});
				}
			});
		</script>
		<div id="loading_<?php echo $this->get_field_id('plan_id'); ?>">
			Loading...
		</div>
		<div style="display:none;" id="multi_plan_<?php echo $this->get_field_id('plan_id'); ?>">
			<div>
				Select which code plan to use on my site: 
				<select id="select_<?php echo $this->get_field_id('plan_id'); ?>" style="width:110px">
				</select>
			</div>
			<div style="margin-top:5px;">
				<script type="text/javascript">
					function open_customize(plan_field_id) {
						window.open('http://hosted.comm100.com/LiveChatFunc/PlanDetailManage.aspx?siteId=<?php echo $site_id; ?>&codeplanId='
							+ document.getElementById(plan_field_id).value + '&ifEditPlan=true');
					}
				</script>
				Click <a href="#" onclick="open_customize('<?php echo $this->get_field_id('plan_id'); ?>');return false;" target="_blank">here</a> to customize this code plan.
			</div>
		</div>

		<div style="display:none;" id="single_plan_<?php echo $this->get_field_id('plan_id'); ?>">
			<div><a id="a_<?php echo $this->get_field_id('plan_id'); ?>" target="_blank">Customize</a> my live chat to better match the look and feel of my website.</div>
			
		</div>
		<input type="hidden" id="<?php echo $this->get_field_id('plan_id'); ?>" 
			value="<?php echo $plan_id; ?>" name="<?php echo $this->get_field_name('plan_id'); ?>"/>
		<input type="hidden" value='<?php echo $code; ?>' id="<?php echo $this->get_field_id('code'); ?>" name="<?php echo $this->get_field_name('code'); ?>"/>
<?php

	}

	public function widget($args, $instance)
	{
		if (Comm100LiveChat::get_instance()->is_installed()) {
			echo urldecode($instance['code']);	
		}		
	}
}




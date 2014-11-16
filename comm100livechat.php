<?php
/*
Plugin Name: Comm100 Live Chat - Chat Plugin for WordPress 
Plugin URI: http://www.comm100.com/livechat/wordpresschat.aspx
Description: Quickly install Comm100 Live Chat onto your WordPress site and engage your website/blog visitors in real time.
Author: Comm100 Live Chat
Version: 2.3
Author URI: http://www.comm100.com/livechat/
*/

if (is_admin())
{
	require_once(dirname(__FILE__).'/plugin_files/Comm100LiveChatAdmin.class.php');
	Comm100LiveChatAdmin::get_instance();
}
else
{
	require_once(dirname(__FILE__).'/plugin_files/Comm100LiveChat.class.php');
	Comm100LiveChat::get_instance();
}

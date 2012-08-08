<?php
/*
Plugin Name: Live Chat by Comm100
Plugin URI: http://livechat.comm100.com/wordpress-chat-plugin.aspx
Description: Quickly install Comm100 Live Chat onto your WordPress site and engage your website/blog visitors in real time.
Author: Comm100 Live Chat
Version: 1.2
Author URI: http://livechat.comm100.com/
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

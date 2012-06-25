<?php
/*
Plugin Name: Comm100 Live Chat
Plugin URI: http://www.comm100.com/livechat/wordpressplugin.aspx
Description: This plugin allows to quickly install Comm100 Live Chat on any WordPress website.
Author: Comm100 Live Chat
Version: 1.0
Author URI: http://www.comm100.com/livechat
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

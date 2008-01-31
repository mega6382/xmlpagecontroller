<?php

// Set language var
$lang = isset( $_COOKIE['lang'] ) ? $_COOKIE['lang'] : 'en';

if( isset( $_GET['lang'] ) )
{
	$lang =  $_GET['lang'];
	$url = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '?';
	setcookie('lang', $lang, time() + 3600 * 24, '/' );
	header('Refresh: 1; url='.$url );
	
	echo 'Switch page language, please wait!';
	exit;
}

require_once 'modules/xmlsite.php';
// Create XML page controller instanse and parse file
$site = new XML_site('example.xml', $lang, true );
// Out result
echo $site->out();
// Out log if $_GET['log'] isset
if( isset( $_GET['log'] ) ) echo $site->log_print();
?>
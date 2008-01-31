<?php
//header('Content-Type: text/html; charset=utf-8');
require_once 'modules/xmlsite.php';
// Set language var
$lang = 'en';
// Create XML page controller instanse and parse file
$site = new XML_site('example.xml', $lang, true );
// Out result
echo $site->out();
// Out log if $_GET['log'] isset
if( isset( $_GET['log'] ) ) echo $site->log_print();
?>
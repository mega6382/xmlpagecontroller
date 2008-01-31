<?php

/**
    This file is part of script XML Page Controller.

    Foobar is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Foobar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * XML Page Controller
 * 
 *  Displays Web Pages based on web query, using the conditions described in xml document.
 *
 * @author Andrew S. Saponenko <roguevoo@gmail.com>
 * @copyright Copyright (c) 2005-2007, Andrew S. Saponenko
 *
 * @version 0.1.0
 */

/* Requre XML parser class */
require_once 'xmlparser.php';
	
	/** SIMPLE TEMPLATE FUNCTIONS  **/
	/**
		Function _gett
			Description: Get template from file
			Args:
				filename - Template filename
			Return:
				Return content of file or null if file not exist
			Usage: 
				$file_content = _gett("templates/mytemplate.htm");
	**/
	
	function _gett( $filename )
	{
		if( empty($filename) || !file_exists($filename) ){
			return 0;
		}	
		$ret = file_get_contents($filename);
		return $ret;
	}

	/** 
		Function _sett
			Description: Fill template values from array
			Args:
				template	- Template string
				array	- Associated array with values
			Return:
				Return the result of combining associative array with a template or same template if array is not array
			Usage: 
				$data = array( "text" => "Hello world" );
				$template = "<h1>[TEXT]</h1>";
				$result = _sett($template, $data );
				
				Out:
					<h1>Hello world</h1>
	**/
	function _sett( $template, $array, $empty = '' ){
		if( !is_array( $array ) ) return $template;
		$text = $template;
		foreach($array as $key => $value){
			$KEY	= '[' . strtoupper( $key ) . ']';
			$value	= ( strlen($value) )? $value : $empty; 
			$text	= str_replace($KEY, stripslashes($value), $text);
		}
		return $text;
	}
	
	/** TODO: make doñunmentation **/
	class URL_parser{
		var $url_parsed	= array();
		var $url_root = '';
		var $url_now = '';
	
		function URL_parser( $root = '' ){
			$this->url_now		= $_SERVER["REQUEST_URI"];
			$this->url_parsed	= explode('/', $_SERVER["REQUEST_URI"] );
			$this->url_root		= $root;
			unset( $this->url_parsed[0] );
			foreach( $this->url_parsed as $k => $v ){
				if ( $v != $root ){
					unset( $this->url_parsed[$k] );
				}else{
					unset( $this->url_parsed[$k] );
					break;
				}
			}
			$this->url_parsed = array_values( $this->url_parsed );
		}
	}
	
	/** TODO: make doñunmentation **/
	class XML_site{
		var $templates = array();
		var $options = array();
		var $locale = array();
		var $include_script = array();
		var $include_style = array();
		var $inline_style = array();
		var $inline_script = array();
		var $_log = array();
		var $_debug = array();
		var $localedata = array();
		var $outstr;
		var $outdata;
		var $main_file;
		var $xml;
		var $debugmode;
		var $lang;
		var $stack;
		var $uri;
		
		function log( $message, $stack = 0 ){
			for( $i = 0; $i < $this->stack + $stack; $i++ ) $message = '&nbsp;&nbsp;' . $message;
			array_push( $this->_log, $message );			
			return count( $this->_log );
		}
		
		function log_print()
		{
			$out = '';
			foreach($this->_log as $text){
				$out .= $text . '<br />';
			}
			return $out;
		}
		
		function debug( $message ){
			if( !$debugmode ) return 0;
			array_push( $_debug, $message );
			return count( $_debug );
		}
		
		function readScript( $node ){
			if( !$node ) return 0;
			$ret = 0;
			$type = isset( $node->tagAttrs['type'] ) ? $node->tagAttrs['type'] : 'inline';
			$this->log( 'Read ' . $type . 'script' );
			switch( $type ){
				default:
				case 'inline':
					array_push( $this->inline_script, $node->tagData );
				break;
				
				case 'file':
					$script = isset( $this->options['script_dir'] ) ? $this->options['script_dir'].$node->tagData : $node->tagData; 
					if( is_file( $script ) ) array_push( $this->include_script, $script );
				break;
			}
		}

		function readStyle( $node ){
			if( !$node ) return 0;
			$ret = 0;
					
			$type = isset( $node->tagAttrs['type'] ) ? $node->tagAttrs['type'] : 'inline';
			$this->log( 'Read ' . $type . 'style' );
			switch( $type ){
				default:		
				case 'inline':
					array_push( $this->inline_style, $node->tagData );
				break;
				
				case 'file':
					$style = isset( $this->options['style_dir'] ) ? $this->options['style_dir'] . $node->tagData : $node->tagData; 
					if( is_file( $style ) ) array_push( $this->include_style, $style );					
				break;
			}
		}
		
		function attach( $src, $dest ){
			foreach( $src as $key => $val ){
				
				if( isset( $dest[$key] ) ){
					$dest[$key] .= $val;
				}
				else{
					$dest[ $key ] = $val;
				}
			}
		}
		
		function readCondition( $node, $result ){
			if( !$node ) return 0;
			$ret = 0;
			
			if( isset( $node->tagAttrs['uriset'] ) ){
				$ret = isset( $this->url[ $node->tagAttrs['uriset'] ] );
				$this->log('if isset"'.$node->tagAttrs['uriset'].'" result: ' . $ret, 1);
			}
			
			if( isset( $node->tagAttrs['urinoset'] ) ){
				$ret = !isset( $this->url[ $node->tagAttrs['urinoset'] ] );
				$this->log('if noset"'.$node->tagAttrs['urinoset'].'" result: ' . $ret, 1);
			}
			
			$isset = isset( $node->tagAttrs['isset'] ) ? $node->tagAttrs['isset'] : 0;
			if( $isset ){
				$ret = isset( $_GET[ $isset ] );
				$this->log('if get_isset"'.$isset.'" result: ' . $ret, 1);
			}
		
			$noset = isset( $node->tagAttrs['noset'] ) ? $node->tagAttrs['noset'] : 0;
			if( $noset ){
				$ret = !isset( $_GET[ $noset ] );
				$this->log('if get noset"'.$noset.'" result: ' . $ret, 1);
			}
			
			$param_ = isset( $node->tagAttrs['param'] ) ? $node->tagAttrs['param'] : 0;
			
			if( $param_ ){
				list($param,$cond,$value) = split(' ', $param_, 3);
				if( !$param || !$cond || !$value ){
					$ret = 0;
				}
				else{
					$now_value = ( isset( $_GET[$param] ) ) ? $_GET[$param] : 0;
					$ret = eval('return ("'.$value.'" ' . $cond . ' "' . $now_value . '");');
				}
				$this->log('if "'.$param_.'" result: ' . $ret, 1);
			}
			if( !$ret ) return 0;
			
			foreach( $node->tagChildren as $_n ){
				if( !$this->readNode( &$_n, &$result ) ) continue;
			}
			return $ret;
		}
		
		function readValue( $node, $_____result ){
			if( !$node ) return 0;
			$ret = 0;
			$name = isset( $node->tagAttrs['name'] ) ? $node->tagAttrs['name'] : 0;
			if( !$name ) return 0;
			
			$template = isset( $node->tagAttrs['template'] ) ? $node->tagAttrs['template'] : 0;
			$newreslt = array();
			$lang = $this->lang;
			
			$l = isset( $node->tagAttrs['lang'] ) ? $node->tagAttrs['lang'] : 0;
			if( $l && $l != $this->lang ) return 0;
			
			if( !$template ){
				$type = isset( $node->tagAttrs['type'] ) ? $node->tagAttrs['type'] : 'inline';
				$this->log('Read value "'.$name.'", type: '.$type, 1); 
				
				//$this->log('+ ' . strto$type .'"', 2);
				
				switch( $type )
				{
					default:
					
					case 'inline':
						$newreslt[ $name ] = $node->tagData;
					break;
					
					case 'file':
						$template = isset( $this->options['template_dir'] ) ? $this->options['template_dir'] . $node->tagData : $node->tagData;
						if( is_file( $template ) ){
							$this->log('+ Template file found: ' . $template, 2);
							$newreslt[ $name ] = _gett( $template );
						}else{
							$this->log('- Template file not found: '.$template, 2);
						}
					break;
					
					case 'script':
						$module = isset( $this->options['module_dir'] ) ? $this->options['module_dir'] . $node->tagData : $node->tagData; 
						if( is_file( $module ) ) $newreslt[ $name ] = include_once( $module );
					break;
					
					case 'template':
						$layout = isset( $this->options['layout_dir'] ) ? $this->options['layout_dir'] . $node->tagData : $node->tagData;
						if( is_file( $layout ) )
						{
							$this->log('+ Layout file found: '.$layout, 2);
							$tmp = new XML_site($layout, $this->lang, $this->debugmode, 2 );
							$this->_log = array_merge($this->_log, $tmp->_log);
							
							$this->include_script	= array_merge( $this->include_script,	$tmp->include_script	);
							$this->include_style	= array_merge( $this->include_style,	$tmp->include_style		);
							
							$this->inline_style		= array_merge( $this->inline_style,		$tmp->inline_style		);
							$this->inline_script	= array_merge( $this->inline_script,	$tmp->inline_script		);
							
							$tmp->options = $this->options;
							
							$o = _sett( $tmp->outstr,
								array_merge(
									$tmp->outdata,
									$tmp->locale
								)
							);
							
							$newreslt[ $name ] = $tmp->out();
						}else{
							$this->log('- Layout file not found: '.$layout , 2);
						}
					break;
				}
				if( $newreslt ) $this->attach( &$newreslt, &$_____result );

				return 1;
			}
			else{
				$_t = isset( $this->templates[$template] ) ? $this->templates[$template] : 0;
				if( $_t ){
					$this->log( 'Frame templated: template found' );
					foreach( $node->tagChildren as $_n ){
						if( !$this->readNode( &$_n, &$newreslt ) ) continue;
					}
					
					if( $newreslt ){
						$this->attach( array( $name => _sett($_t, $newreslt ) ), &$_____result );
					}
					
					return 1;
				}
				$this->log( 'Frame templated: template "'.$template.'" not found' );
				return 0;
			}
			return $ret;
		}
		
		function readTemplate( $node ){
			if( !$node ) return 0;
			$ret = 0;
			$name = isset( $node->tagAttrs['name'] ) ? $node->tagAttrs['name'] : 0;
			if( !$name ) return 0;
			
			$type = isset( $node->tagAttrs['type'] ) ? $node->tagAttrs['type'] : 'inline';
			$log = 'DEFINE INLINE TEMPLATE: "' . $name . '", type: ' . $type;
				
			switch( $type )
			{
				default:
				case 'inline':
					$this->templates[ $node->tagAttrs['name'] ] = $node->tagData;	
				break;
				
				case 'file':
					if( is_file( $node->tagData ) )
					{
						$this->templates[ $node->tagAttrs['name'] ] = _gett( $node->tagData );
						$log .= ' file found at: ' . $node->tagData;
					}
					else
					{
						$log .= ' file not found';
					}
				break;
			}
			$this->log( $log, 1 );
		}
		
		function readNode( $node, $result ){
			if( !$node ) return 0;
			$ret = 0;
			switch( $node->tagName ){
				case 'if':
					$ret = $this->readCondition( &$node, &$result );
				break;
				/*****/
				case 'value':
					$ret = $this->readValue( &$node, &$result );
				break;
				
				case 'frame':
					$ret = $this->readValue( &$node, &$result );
				break;
				
				case 'out':
					$ret = $this->readValue( &$node, &$result );
				break;
				/****/
				case 'script':
					$ret = $this->readScript( &$node );
				break;
				
				case 'style':
					$ret = $this->readStyle( &$node );
				break;
				
				case 'template':
					$ret = $this->readTemplate( &$node );
				break;
			}
			return $ret;
		}
		
		function parseFile($filename){
			if( !$filename ){
				$this->log( 'File is null' );
			}
			if( !is_file( $filename ) ){
				$this->log( 'File not exist: ' . $filename );
			}
			$this->log( '========= Parse XML: "'. $filename.'"========', 0 );
			$xmltext = _gett($filename);
			$this->xml = new XMLParser( $xmltext );
			$this->xml->Parse();
			$lang = $this->lang;
			
			$xmldoc = &$this->xml->document;
			
			$_options = isset( $xmldoc->options ) ? $xmldoc->options : array();
			foreach( $_options as $_opt )
			{
				$this->log( 'Find ' . count($_opt->tagChildren) . ' options', 1 );
				
				foreach( $_opt->tagChildren as $_option ){
					if( $_option->tagName != 'option' ) break;
					if( !isset( $_option->tagAttrs['name'] ) ) break;
					$this->options[ $_option->tagAttrs['name'] ] = $_option->tagData;
					$this->log( ' +option: "' . $_option->tagAttrs['name'] . '"', 2 );	
				}	
			}
			if( isset( $_GET['style'] ) ){
				$out = _gett( $_GET['style'] );
				header( 'Content-Type: text/css' );
				echo _sett( $out, array('img'=>$this->options['image_dir'] ) );
				exit;
			}

			if( isset( $_GET['script'] ) ){
				$out = _gett( $_GET['script'], false );
				header( 'Content-Type: text/javascript' );
				echo _sett( $out, array('img'=>$this->options['image_dir'] ) );
				exit;
			}
			
			$this->log( 'Total options read: ' . count($this->options), 1 );

			$_container = isset( $xmldoc->container ) ? $xmldoc->container : array();
			$this->log( 'Find ' . count($_container) . ' container', 1 );

			foreach( $_container as $_cont ){
				$type = isset( $_cont->tagAttrs['type'] ) ? $_cont->tagAttrs['type'] : 'inline';
				$this->log(' +read container type: ' . $type, 2 );
				switch( $type )
				{
					default:
					
					case 'inline':
						$this->outstr = $_cont->tagData;	
					break;
					
					case 'file':
						if( is_file( $_cont->tagData ) )
						{
							$this->outstr = _gett( $_cont->tagData );
						}
					break;
					
					case 'script':
						if( is_file( $_cont->tagData ) )
						{
							$this->outstr = require_once( $_cont->tagData );
						}
					break;
				}
			}
			$this->log( 'Total container length: ' . strlen( $this->outstr ), 1 );

			$_templates = isset( $xmldoc->templates ) ? $xmldoc->templates : array();
			foreach( $_templates as $_tem )
			{
				$this->log( 'Find ' . count($_tem->tagChildren) . ' templates', 1 );
				foreach( $_tem->tagChildren as $_template )
				{
					if( $_template->tagName != 'template' ) break;
					if( !isset( $_template->tagAttrs['name'] ) ) break;
					
					$this->log( 'Template: "' . $_template->tagAttrs['name'] . '"', 2 );
					$type = isset( $_template->tagAttrs['type'] ) ? $_template->tagAttrs['type'] : 'inline';
					$this->log(' type: ' . $type, 2 );
					
					switch( $type )
					{
						default:
						case 'inline':
							$this->templates[ $_template->tagAttrs['name'] ] = $_template->tagData;	
						break;
						
						case 'file':
							if( is_file( $_template->tagData ) )
							{
								$this->templates[ $_template->tagAttrs['name'] ] = _gett( $_template->tagData );
							}
						break;
					}
					$this->log(' lenght: ' . strlen( $_template->tagAttrs['name'] ), 2 );
				}
			}
			$_pages = isset( $xmldoc->pages ) ? $xmldoc->pages : array();
			foreach( $_pages as $_ps ){
				if( $_ps->tagName != 'pages') break;
				$this->log( 'Find ' . count( $_ps->tagChildren ) . ' frames', 1 );
				foreach( $_ps->tagChildren as $_page ){
					$_node = $this->readNode( &$_page, &$this->outdata );
				}
			}

			$_locale = isset( $xmldoc->locale ) ? $xmldoc->locale : array();
			foreach( $_locale as $_loc ){
				if( !isset( $_loc->tagAttrs['lang'] ) || $_loc->tagAttrs['lang'] != $this->lang ) continue;
				foreach( $_loc->tagChildren as $_item ){
					if( !isset( $_item->tagAttrs['name'] ) || $_item->tagName != 'item' ) continue;
					$this->log( 'Locale: "' . $_item->tagAttrs['name'] . '"', 2 );
					$type = isset( $_item->tagAttrs['type'] ) ? $_item->tagAttrs['type'] : 'inline';
					switch( $type )
					{
						default:
						case 'inline':
							$this->locale[ $_item->tagAttrs['name'] ] = $_item->tagData;
						break;
						
						case 'file':
							if( is_file( $_template->tagData ) )
							{
								$this->locale[ $_item->tagAttrs['name'] ] = _gett( $_item->tagData );
							}
						break;
					}
					$this->log(' lenght: ' . strlen( $_template->tagAttrs['name'] ), 2 );
				}
			}
			$this->log( '/*========= End parse XML: "'. $filename.'"========*/', 0 );
		}
		
		function XML_site( $filename, $lang, $debugmode )
		{
			//$up		= new URL_parser('de');
			//$this->url			= $up->url_parsed;
			//$lang	= isset( $urls[0] ) ? ( $urls[0] == 'ru' || $urls[0] == 'en' ) ? $urls[0] : 'ru' : 'ru';
			
			//$this->dir			= $basedir;
			$this->stack		= 0;
			$this->lang			= $lang;
			$this->debugmode	= $debugmode;
			$this->main_file	= $filename;
			$this->parseFile( $this->main_file );
		}
		
		function out(){
			$includescript = '';
			foreach( $this->include_script as $script ){
				$includescript .= _sett("<script type=\"text/javascript\" src=\"?script=[SCRIPT]\"></script>\n", array( 'script' => $script ) );
			}
			$includestyle = '';
			foreach( $this->include_style as $style ){
				$includestyle .= _sett("<link rel=\"stylesheet\" type=\"text/css\" href=\"?style=[STYLE]\" />\n", array( 'style' => $style ) );
			}
			
			$inlinescript = '';
			if( count( $this->inline_script ) > 0 ){
				$inlinescript = "<script type=\"text/javascript\">\n<!--\n/* ====== GENERATED INLINE SCRIPT BEGIN ===========*/";
				foreach( $this->inline_script as $script )	$inlinescript .= "\n" . $script;
				$inlinescript .= "/* ====== GENERATED INLINE SCRIPT END ===========*/\n-->\n</script>\n";
			}
			
			$inlinestyle = '';
			if( count( $this->inline_style ) > 0 ){
				$inlinestyle = "\n<style type=\"text/css\">\n/* ====== GENERATED INLINE STYLE BEGIN ===========*/";
				foreach( $this->inline_style as $script )	$inlinestyle .= "\n" . $script;
				$inlinestyle .= "/* ====== GENERATED INLINE STYLE END ===========*/\n</style>\n";
			}
			
			$this->outstr = _sett($this->outstr, array_merge(
				$this->outdata,
				$this->locale,
				array(
					'page:include_script'	=> $includescript,
					'page:include_style'	=> $includestyle,
					'page:inline_style'		=> $inlinestyle,
					'page:inline_script'	=> $inlinescript
				)
			));
			
			return _sett( $this->outstr, array('lang'=>$this->lang, 'img'=> isset( $this->options['image_dir'] ) ? $this->options['image_dir'] : 'img/'));
		}
	};
?>
<?php
	/*
	* Include XML parser class
	*/
	require_once 'xmlparser.php';

	/*
	* XML page controller - class
	*/
	class xmlpage
	{
		var $options = array();
		var $searchDir = array(
			''
		);
		
		var $defaultTempateTags = array('[',']');
		var $templates = array();
		
		var $inline_script = array();
		var $include_script = array();
		
		var $inline_style = array();
		var $include_style = array();
		var $_log = array();
		var $locale = array();
		var $lang = 'en';
		var $debug = false;
		var $container = '[CONTENT]';
		var $outdata = array();
		var $output, $xml, $logstack, $node, $nowloc;
			
		function log( $message, $stack = 0 )
		{
			if( !$this->debug ) return 0;
			for( $i = 0; $i < $this->logstack + $stack; $i++ ) $message = '&nbsp;&nbsp;' . $message;
			array_push( $this->_log, $message );			
			return count( $this->_log );
		}
		
		function findindir($file)
		{
			$dirs = $this->searchDir;
			foreach( $this->searchDir as $v )
			{
				if( file_exists( $v.$file ) ) return $v.$file;
			}
			return 0;
		}
		
		function parseValue($node)
		{
			$lang = $this->lang;
			$attrs = &$node->tagAttrs;
			$type_attr = isset( $attrs['type'] ) ? $attrs['type'] : 'inline';
			$ret = "";
			switch( $type_attr )
			{
				default:
				case 'inline':
					if( !isset( $attrs['template'] ) )
					return $node->tagData;
				break;
				
				case 'template':
				case 'layout':
				case 'xml':
				{
					$t = $this->findindir($node->tagData);
					if( $t )
					{
						$xml_					= new xmlpage($t, $this->lang, $this->debug, $this->defaultTempateTags);
						
						$xml_->options			= &$this->options;
						$xml_->searchDir		= &$this->searchDir;
						$xml_->templates		= &$this->templates;
						$xml_->logstack			= $this->logstack + 1;
						$ret = $xml_->out();
						
						$this->_log				= array_merge(&$this->_log, &$xml_->_log);
						
						$this->include_script	= array_merge(&$this->include_script,	&$xml_->include_script	);
						$this->include_style	= array_merge(&$this->include_style,	&$xml_->include_style	);
						$this->inline_script	= array_merge(&$this->inline_script,	&$xml_->inline_script	);
						$this->inline_style		= array_merge(&$this->inline_style,		&$xml_->inline_style	);
					}
				}
				break;
				
				case 'file':
					
					$ret = $this->findindir($node->tagData);
					if( $ret ){
						$this->log('Read file: '. $ret);
						$ret = file_get_contents( $ret );
					}
				break;
				
				case 'php':
				case 'module':
				case 'script':
					$ret = $this->findindir($node->tagData);
					if( $ret ) $ret = require( $ret );
					
				break;
			}
			
			if( isset( $attrs['template'] ) && isset( $this->templates[ $attrs['template'] ] ) )
			{
				$xml_				= new xmlpage('___none___', $this->lang, $this->debug, $this->defaultTempateTags);
				$xml_->container	= $this->templates[ $attrs['template'] ];
				$xml_->xml			= &$node;
				$xml_->options		= &$this->options;
				$xml_->searchDir	= &$this->searchDir;
				$xml_->templates	= &$this->templates;
				$xml_->logstack		= $this->logstack + 1;
				
				$ret = $xml_->out();
				
				$this->_log = array_merge(&$this->_log, &$xml_->_log);
				
				$this->include_script	= array_merge(&$this->include_script,	&$xml_->include_script	);
				$this->include_style	= array_merge(&$this->include_style,	&$xml_->include_style	);
				$this->inline_script	= array_merge(&$this->inline_script,	&$xml_->inline_script	);
				$this->inline_style		= array_merge(&$this->inline_style,		&$xml_->inline_style	);
			}
			return $ret;
		}
		
		function parseRecursive($node)
		{
			if( !$node ) return;
			$this->logstack += 2;
			foreach( $node->tagChildren as $child ) $this->parseNode( $child );
			$this->logstack -= 2;
		}
		
		function condition( $node, $arg )
		{
			if( !$node ) return 0;
			//if( !$arr ) return 0;
			$ret = 0;
			$l = '';
			if( isset( $node->tagAttrs['isset'] ) )
			{
				$l = 'isset( "'.$node->tagAttrs['isset'].'")';
				$ret = isset( $arg[ $node->tagAttrs['isset'] ] );
			}
			
			if( isset( $node->tagAttrs['noset'] ) )
			{
				$l = 'noset( "'.$node->tagAttrs['noset'].'")';
				$ret = !isset( $arg[ $node->tagAttrs['noset'] ] );
			}
			
			if( isset( $node->tagAttrs['param'] ) )
			{
				//print_r($ret);
				list($var, $con, $val) = split(' ', $node->tagAttrs['param'] );
				$var = $arg[$var];
				$l = 'Eval ' . '("'.$var.'" ' . $con . ' "' . $val . '");';
				$ret = eval('return ("'.$var.'" ' . $con . ' "' . $val . '");');
			}
			if( $ret ){
				$this->log( 'Condition "'. $node->tagName .'" : '.$l );
			}
			return $ret;
		}
		
		function parseNode($node)
		{
			if( !is_object($node) ) return;
			$this->node = $node;
			$name = $node->tagName;
			$attr = &$node->tagAttrs;
			$data = &$node->tagData;
			switch( $name )
			{
				default:
					$this->parseRecursive(&$node);
				break;
				
				case 'container':
					
					$res = $this->parseValue(&$node);
					if( $res )
					{
						$this->container = $res;
						$this->log('Container len: '.strlen($this->container));
					}
				break;
				
				case 'option':
				{
					if( !isset( $attr['name'] ) )
					{
						$this->log('<b>Invalid option name with value:</b> '.$data );
						break;
					}
					switch( $attr['name'] )
					{
						default:
							$this->options[ $attr['name'] ] = $data;
							$this->log('Option "'.$attr['name'].'": '.$data);
						break;
						
						case 'searchDir':
							array_push( $this->searchDir, $data );
							$this->log('Add search dir: '.$data);
						break;
					}
				}
				break;
				
				case 'template':
					$res = $this->parseValue(&$node);
					if( $res )
					{
						$this->templates[ $attr['name'] ] = $res;
						$this->log('Template "'.$attr['name'].'" len: '.strlen($res));
					}
				break;
				
				case 'css':
				case 'style':
					$type = isset( $attr['type'] ) ? $attr['type'] : 'inline';
					switch($type)
					{
						default:
						case 'inline':
							array_push( $this->inline_style, $data );
							$this->log('Inline style len: '. strlen($data));
						break;
						
						case 'file':
							$fil = $this->findindir($data);
							if( $fil )
							{
								array_push( $this->include_style, $fil );
								$this->log('Include style: "'.$fil.'"');
							}
						break;
					}
				break;
				
				case 'js':
				case 'script':
					$type = isset( $attr['type'] ) ? $attr['type'] : 'inline';
					switch($type)
					{
						default:
						case 'inline':
							array_push( $this->inline_script, $data );
							$this->log('Inline script len: '. strlen($data));
						break;
						
						case 'file':
							$fil = $this->findindir($data);
							if( $fil )
							{
								array_push( $this->include_script, $fil );
								$this->log('Include script: "'.$fil.'"');
							}
						break;
					}
				break;
				
				case 'out':
				case 'frame':
				case 'value':
					$res = $this->parseValue(&$node);
					if( isset( $this->outdata[ $attr['name'] ] ) )
					{
						$this->outdata[ strtolower( $attr['name'] ) ] .= $res;
					}
					else
					{
						$this->outdata[ strtolower( $attr['name'] ) ] = $res;
					}
					$this->log( 'Value "'. $attr['name']/* .'" len: '.strlen($res)*/ );
				break;
				
				case 'if':
				case 'if:get':
					if( $this->condition(&$node, &$_GET) ) $this->parseRecursive(&$node);
				break;
				
				case 'if:post':
					if( $this->condition(&$node, &$_POST) ) $this->parseRecursive(&$node);
				break;

				case 'if:session':
					if( $this->condition(&$node, &$_SESSION) ) $this->parseRecursive(&$node);
				break;
				
				case 'if:server':
					if( $this->condition(&$node, &$_SERVER) ) $this->parseRecursive(&$node);
				break;
				
				case 'locale':
					if( isset( $attr['lang'] ) )
					{
						$this->nowlang = $attr['lang'];
						$this->locale[ $this->nowlang ] = array();
						$this->log('Read "'.$this->nowlang.'" locale collection');
						$this->parseRecursive(&$node);
					}
				break;
				
				case 'item':
					$res = $this->parseValue(&$node);
					if( $res && isset($attr['name']) )
					{
						$this->locale[ $this->nowlang ][ $attr['name'] ] = $res;
						$this->log('Read locale "'.$attr['name'].'" len: '.strlen($res));
					}
				break;
			}
		}
		
		/*
		* CONSTRUCTOR
		*/
		function xmlpage($filename, $language = "en", $debugmode = false, $templateTag = array('[',']') )
		{
			if( $filename && is_file($filename) )
			{
				$xmldoc	= new XMLParser( file_get_contents($filename) );
				$xmldoc->Parse();
				$this->xml = $xmldoc->document;
			}
			
			$this->lang		= $language;
			$this->debug	= $debugmode;
			$this->defaultTempateTags = $templateTag;
		}
		
		function out()
		{			
			if( !$this->xml ) return 'Content not found.';
			///print_r($xmldoc);
			$this->parseRecursive( $this->xml );
			$output = $this->container;
			$output = $this->apply($output,$this->outdata);

			$includescript = '';
			foreach( $this->include_script as $script )
			{
				$includescript .= $this->apply('<script type="text/javascript" src="[SCRIPT]"></script>', array( 'script' => $script ) );
			}
			$includestyle = '';
			foreach( $this->include_style as $style )
			{
				$includestyle .= $this->apply('<link rel="stylesheet" type="text/css" href="[STYLE]" />', array( 'style' => $style ) );
			}
			
			$inlinescript = '';
			if( count( $this->inline_script ) > 0 )
			{
				$inlinescript = '<script type="text/javascript">';
				foreach( $this->inline_script as $script )	$inlinescript .= "\n" . $script;
				$inlinescript .= "</script>";
			}
			
			$inlinestyle = '';
			if( count( $this->inline_style ) > 0 ){
				$inlinestyle = '<style type="text/css">';
				foreach( $this->inline_style as $script )	$inlinestyle .= "\n" . $script;
				$inlinestyle .= "</style>";
			}
			$output = $this->apply( $output, array(
				'page:include_script'	=> $includescript,
				'page:include_style'	=> $includestyle,
				'page:inline_style'		=> $inlinestyle,
				'page:inline_script'	=> $inlinescript
			));
			
			$output = $this->apply($output,array('lang'=>$this->lang,'img'=>$this->options['image_dir']));
			$locale = isset( $this->locale[ $this->lang ] ) ? $this->locale[ $this->lang ] : 0;
			if($locale) $output = $this->apply($output,$locale);
			
			return $output;
		}

		function apply( $temp, $data )
		{
			if( !is_array( $data ) ) return $temp;
			$start	= isset( $this->options['templateTagBegin'] )	? $this->options['templateTagBegin']	: $this->defaultTempateTags[0];
			$end	= isset( $this->options['templateTagEnd'] )		? $this->options['templateTagEnd']		: $this->defaultTempateTags[1];

			$output = $temp;
			foreach($data as $key => $value){
				$key		= $start . strtoupper( $key ) . $end; 
				$output		= str_replace($key, stripslashes($value), $output);
			}
			return $output;
		}
		
		function log_print()
		{
			$output = '<br />';
			foreach( $this->_log as $val )
			{
				$output .= $val . '<br />';
			}
			return $output;
		}
	}
?>
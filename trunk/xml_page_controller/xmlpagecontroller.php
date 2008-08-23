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
	/*
		* XML defined options
		*/
	var $options = array(
	"image_dir"=>'',
	"usecache" => true,
	"cachedir"=>'cache/'
	);
	
	/*
		* File search directories from XML defined options
		*/
	var $searchDir = array(
		'' // Current dir
	);
	
	var $values = array();
	
	/*
		* Default template tags, begin and end
		*/
	var $defaultTempateTags = array('[',']');
	
	/*
		* Contain temtlated HTML or etc from XML defines
		*/
	var $templates = array();
	
	/*
		* Strings from Inline defined scripts
		*/
	var $inline_script = array();
	
	/*
		* Include script file names
		*/
	var $include_script = array();
	
	/*
		* Url address of remote script
		*/
	var $remote_script = array();
	
	/*
		*  Strings from inline defined scripts
		*/
	var $inline_style = array();
	
	/*
		* Include script file names
		*/
	var $include_style = array();
	
	/*
		* Url address of remote scripts
		*/
	var $remote_style = array();
	
	/*
		* Debug information collection
		*/
	var $_log = array();
	
	/*
		* Strings of localed words defined in XML
		*/
	var $locale = array();
	
	/*
		* Defined language
		*/
	var $lang = 'en';
	
	/*
		* Debug flag
		*/
	var $debug = false;
	
	/*
		* Templated container
		*/
	var $container = '[CONTENT]';
	
	/*
		* All output named values
		*/
	var $outdata = array();
	
	var $filestat;
	var $cachefilename;
	
	/*
		* Other script variables
		*/
	var $output, $xml, $logstack, $node, $nowloc;
	
	/*
		* function log (for inner usage) - Put message to log stack
		* args
		*     $message (string) - Message to output
		*    $stack(number) - Count of whitespace before message line
		* return
		*    Number lines of log
		*/
	function log( $message, $stack = 0 )
	{
		if( !$this->debug ) return 0;
		for( $i = 0; $i < $this->logstack + $stack; $i++ ) $message = '&nbsp;&nbsp;' . $message;
		array_push( $this->_log, $message );            
		return count( $this->_log );
	}
	
	/*
		* function findindir (for inner usage) - Find file in directories
		* args
		*    $filename (string) - Name of file to find
		* return
		*    0 - if file not found
		*    Or file path as string
		*/
	function findindir($filename)
	{
		$dirs = $this->searchDir;
		foreach( $this->searchDir as $v )
		{
			if( is_file( $v.$filename ) ) return $v.$filename;
		}
		return 0;
	}
	
	/*
		* function parseValue(for inner usage) - Get result of tag
		* args
		*    $node(XMLTag) - Node
		* return
		*    String
		*/
	function parseValue($node)
	{
		$lang = $this->lang;
		$attrs = &$node->tagAttrs;
		$type_attr = isset( $attrs['type'] ) ? $attrs['type'] : 'inline';
		$ret = "";
		
		if( isset( $attrs['lang'] ) )
		{
			if( $attrs['lang'] != $this->lang ) return '';
		}
		
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
					$xml_                    = new xmlpage($t, $this->lang, $this->debug, $this->defaultTempateTags);
					
					$xml_->options            = &$this->options;
					$xml_->searchDir        = &$this->searchDir;
					$xml_->templates        = &$this->templates;
					$xml_->logstack            = $this->logstack + 1;
					$ret = $xml_->out();
					
					$this->_log                = array_merge(&$this->_log, &$xml_->_log);
					
					$this->include_script    = array_merge(&$this->include_script,    &$xml_->include_script    );
					$this->include_style    = array_merge(&$this->include_style,    &$xml_->include_style    );
					$this->inline_script    = array_merge(&$this->inline_script,    &$xml_->inline_script    );
					$this->inline_style        = array_merge(&$this->inline_style,        &$xml_->inline_style    );
				}else{
					$this->log('<b>File not found</b>: '. $node->tagData);
				}
			}
			break;
			
		case 'file':
			
			$ret = $this->findindir($node->tagData);
			if( $ret ){
				$this->log('Read file: '. $ret);
				$ret = file_get_contents( $ret );
			}else{
				$this->log('<b>File not found</b>: '. $node->tagData);
			}
			break;
			
		case 'php':
		case 'module':
		case 'script':
			$ret = $this->findindir($node->tagData);
			if( $ret ) $ret = require( $ret );
			else{
				$this->log('<b>File not found</b>: '. $node->tagData);
			}
			break;
		}
		
		if( isset( $attrs['template'] ) && isset( $this->templates[ $attrs['template'] ] ) )
		{
			$xml_				= new xmlpage('___none___', $this->lang, $this->debug, $this->defaultTempateTags);
			$xml_->container	= $this->templates[ $attrs['template'] ];
			$xml_->xml			= &$node;
			$xml_->options		= &$this->options;
			$xml_->searchDir	= &$this->searchDir;
			$xml_->values		= &$this->values;
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
	
	/*
		* function parseRecursive (for inner usage) - Recursive iteration in tag nodes
		* args
		*    $node(XMLTag) - Node where start iteration
		*/
	function parseRecursive($node)
	{
		if( !$node ) return;
		$this->logstack += 2;
		foreach( $node->tagChildren as $child )
		{
			$this->parseNode( $child, $node->tagName );
		}
		$this->logstack -= 2;
	}
	
	/*
		* function contion (for inner usage) - Execute XML defined condition
		* args
		*    $node (XMLTag) - Node wher located condition
		*    $node(array) - Variables to look
		* return
		*    0 - if condition == false or his failed
		*    1 - if condition == true
		*/
	function condition( $node, $arg )
	{
		if( !$node ) return 0;
		
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
			list($var, $con, $val) = split(' ', $node->tagAttrs['param'] );
			if( isset( $arg[$var] ) )
			{
				$var = $arg[$var];
				$l = 'Condition: ("'.$var.'" ' . $con . ' "' . $val . '");';
				//$ret = eval('return ("'.$var.'" ' . $con . ' "' . $val . '");');
				switch( $con )
				{
				case "=":
				case "==":
					{
						$ret = ( $var == $val );
					}
					break;
					
				case "!=":
					{
						$ret = ( $var != $val );
					}
					break;
					
				case ">":
					{
						$ret = ( $var > $val );
					}
					break;
					
				case "<":
					{
						$ret = ( $var < $val );
					}
					break;
					
				case "<=":
					{
						$ret = ( $var <= $val );
					}
					break;
					
				case ">=":
					{
						$ret = ( $var >= $val );
					}
					break;
				}
			}
			else
			{
				$ret = 0;
			}
		}
		if( $ret ) $this->log( 'Condition "'. $node->tagName .'" : '.$l );
		return $ret;
	}
	
	/*
		* function parseNode(for inner usage) - Parsing tag nodes
		* args
		*    $node(XMLTag) - Tag node to parse
		*/
	function parseNode($node, $parent = '')
	{
		if( !is_object($node) ) return;
		$this->node = $node;
		$name = $node->tagName;
		$attr = &$node->tagAttrs;
		$data = &$node->tagData;
		
		switch( $name )
		{
		default:
			{
				$this->parseRecursive(&$node);
			}
			break;
			
		case 'container':
			{
				$res = $this->parseValue(&$node);
				if( $res )
				{
					$this->container = $res;
					$this->log('Container size: ' . strlen($this->container));
				}
			}
			break;
			
		case 'template':
			{
				if( $parent != 'templates' ) break;
				$res = $this->parseValue(&$node);
				if( $res )
				{
					$this->templates[ $attr['name'] ] = $res;
					$this->log('Template "'.$attr['name'].'" len: '.strlen($res));
				}
			}
			break;
			
		case 'css':
		case 'style':
		case 'ssheet':
		case 'stylesheet':
			{
				$type = isset( $attr['type'] ) ? $attr['type'] : 'inline';
				switch($type)
				{
				default:
				case 'inline':
					{
						array_push( $this->inline_style, $data );
						$this->log('Inline style len: '. strlen($data));
					}
					break;
					
				case 'file':
					{
						$fil = $this->findindir($data);
						if( $fil )
						{
							array_push( $this->include_style, $fil );
							$this->log('Include style: "'.$fil.'"');
						}
					}
					break;
					
				case 'remote':
				case 'url':
					{
						array_push( $this->remote_style, $data );
						$this->log('Url script: "'.$data.'"');
					}
					break;
				}
			}
			break;
			
		case 'js':
		case 'jscript':
		case 'javascript':
		case 'script':
			{
				$type = isset( $attr['type'] ) ? $attr['type'] : 'inline';
				switch($type)
				{
				default:
				case 'inline':
					{
						array_push( $this->inline_script, $data );
						$this->log('Inline javascript len: '. strlen($data));
					}
					break;
					
				case 'file':
					{
						$fil = $this->findindir($data);
						if( $fil )
						{
							array_push( $this->include_script, $fil );
							$this->log('Include javascript: "'.$fil.'"');
						}
					}
					break;
					
				case 'remote':
				case 'url':
					{
						array_push( $this->remote_script, $data );
						$this->log('Url javascript: "'.$data.'"');
					}
					break;
				}
			}    
			break;
			
		case 'out':
		case 'frame':
		case 'value':
			{
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
			}
			break;
			
		case 'module':
		case 'php':
			{
				$res = $this->parseValue(&$node);
			}
			break;
			
		case 'if':
		case 'if:get':
			{
				if( $this->condition(&$node, &$_GET) ) $this->parseRecursive(&$node);
			}
			break;
			
		case 'if:post':
			{
				if( $this->condition(&$node, &$_POST) ) $this->parseRecursive(&$node);
			}
			break;

		case 'if:session':
			{
				if( $this->condition(&$node, &$_SESSION) ) $this->parseRecursive(&$node);
			}
			break;
			
		case 'if:server':
			{
				if( $this->condition(&$node, &$_SERVER) ) $this->parseRecursive(&$node);
			}
			break;
			
		case 'if:cookies':
			{
				if( $this->condition(&$node, &$_SERVER) ) $this->parseRecursive(&$node);
			}
			break;
			
		case 'locale':
			{
				if( isset( $attr['lang'] ) )
				{
					$this->nowlang = $attr['lang'];
					$this->locale[ $this->nowlang ] = array();
					$this->log('Read "'.$this->nowlang.'" locale collection');
					$this->parseRecursive( &$node );
				}
			}
			break;
			
		case 'item':
			{
				$res = $this->parseValue(&$node);
				if( $res && !isset($attr['name']) ) break;
				
				switch( $parent )
				{
				case 'locale':
					{
						$this->locale[ $this->nowlang ][ $attr['name'] ] = $res;
						$this->log('Read locale "'.$attr['name'].'", size: '.strlen($res));
					}
					break;
					
				case 'values':
					{
						$this->values[ $attr['name'] ] = $res;
						$this->log('Read value "'.$attr['name'].'", size: '.strlen($res));
					}
					break;
				}
			}
			break;
		}
	}
	
	/*
		* CLASS CONSTRUCTOR
		* args
		*    $filename(string)(optional) - XML configuration filename
		*    $language(string)(default=en) - Language definion for reading right locales
		*    $debugmode(bool)(default=false) - Enabling/Disabling loging of class work
		*    $templateTag(array)(default=array('[',']')) - Begin and end template indeficators
		*/
	function xmlpage($filename, $language = "en", $debugmode = false, $templateTag = array('[',']') )
	{
		$this->filename = $filename;
		if( $filename && is_file($filename) )
		{
			$fhandle = fopen( $filename, "r" );
			$filedata = "";
			if( $fhandle == FALSE )
			{
			}
			else
			{
				$this->filestat = fstat($fhandle);
				
				$time = $this->filestat['mtime'];
				
				$filestamp = md5( $this->filename .'_'. $this->lang . '_' . $_SERVER['QUERY_STRING'] . $time );
				$this->cachefilename = $this->options['cachedir'] . $filestamp .'.csh';
				
				if( isset( $this->options['usecache'] ) && $this->options['usecache'] && file_exists( $this->cachefilename )  )
				{
				
				}
				else
				{
					$filedata = fread($fhandle, filesize($filename) );
					$xmldoc = new XMLParser( $filedata );
					$xmldoc->Parse();
					$this->xml = $xmldoc->document;
					$this->parseOptions();
				}
				fclose($fhandle);
			}
		}
		
		$this->lang		= $language;
		$this->debug    = $debugmode;
		$this->defaultTempateTags = $templateTag;
	}
	
	function parseOptions()
	{
		if( !$this->xml->tagChildren  ) return;
		
		$options = isset( $this->xml->tagChildren ) && !empty( $this->xml->tagChildren[0] ) ? $this->xml->tagChildren[0] : 0;
		
		if( !$options ) return;
		
		//print_r($options);
		foreach( $options->tagChildren as $item )
		{
			if( $item->tagName != "option" ) continue;
			
			$attr = &$item->tagAttrs;
			$data = $item->tagData;
			
			if( !isset( $attr['name'] ) )
			{
				$this->log('<b>Invalid option name:</b> '.$data );
				continue;
			}
			
			$name = strtolower( $attr['name'] );
			switch( $name )
			{
			default:
				{
				}
				break;
				
			case 'directory':
			case 'search dir':
			case 'files dir':
			case 'search_dir':
			case 'files_dir':
			case 'searchDir':
			case 'filesDir':
				{
					array_push( $this->searchDir, $data );
					$this->log('Add search dir: '.$data);
				}
				break;
			}
		}
	}
	
	function makeCache( $output )
	{
		if ( isset( $this->options['usecache'] ) && $this->options['usecache'] == "false" ) return false;
		if( !file_exists( $this->cachefilename ) )
		{
			$fhandle = fopen( $this->cachefilename, "x" );
			if( $fhandle == FALSE )
			$this->log('Generate cache file FAILED: '.$this->cachefilename);
			else{
				fputs($fhandle,$output);
				fclose($fhandle);
				$this->log('Generate cache file SUCCESS: '.$this->cachefilename);
			}
		}
	}
	
	function getCache()
	{
		if ( isset( $this->options['usecache'] ) && $this->options['usecache'] == "false" ) return false;
		if( file_exists( $this->cachefilename ) )
		{
			$this->log('Read file from cache: '.$this->cachefilename);
			return file_get_contents( $this->cachefilename );
		}
		return false;
	}
	
	/*
			* function out
			* return
			*    String combined data of script work
			*/
	function out()
	{
		//print_r( $this->filestat );
		$cache = $this->getCache();
		if( $cache ) return $cache;
		
		if( !$this->xml ) return 'Content not found.';
		$this->parseRecursive( $this->xml );
		$output = $this->container;
		$output = $this->apply($output,&$this->outdata);
		
		$locale = isset( $this->locale[ $this->lang ] ) ? $this->locale[ $this->lang ] : 0;
		if($locale) $output = $this->apply( $output, &$locale );
		
		$includescript = '';
		foreach( $this->include_script as $script )
		{
			$includescript .= $this->apply("\n<script type=\"text/javascript\" src=\"[SCRIPT]\"></script>", array( 'script' => $script ) );
		}
		
		$includestyle = '';
		foreach( $this->include_style as $style )
		{
			$includestyle .= $this->apply("\n<link rel=\"stylesheet\" type=\"text/css\" href=\"[STYLE]\" />", array( 'style' => $style ) );
		}
		
		$remotescript = '';
		foreach( $this->remote_script as $script )
		{
			$remotescript .= $this->apply("\n<script type=\"text/javascript\" src=\"[SCRIPT]\"></script>", array( 'script' => $script ) );
		}
		
		$remotestyle = '';
		foreach( $this->remote_style as $style )
		{
			$remotestyle .= $this->apply("\n<link rel=\"stylesheet\" type=\"text/css\" href=\"[STYLE]\" />", array( 'style' => $style ) );
		}
		
		$inlinescript = '';
		if( count( $this->inline_script ) > 0 )
		{
			$inlinescript = "\n<script type=\"text/javascript\">\n";
			foreach( $this->inline_script as $script )    $inlinescript .= $script . "\n";
			$inlinescript .= "</script>";
		}
		
		$inlinestyle = '';
		if( count( $this->inline_style ) > 0 )
		{
			$inlinestyle = "\n<style type=\"text/css\">";
			foreach( $this->inline_style as $script )    $inlinestyle .= $script . "\n";
			$inlinestyle .= "</style>";
		}
		
		$output = $this->apply(
			$output,
			array(
				'page:include_script'    => $includescript.$remotescript,
				'page:include_style'    => $includestyle.$remotestyle,
				'page:inline_style'     => $inlinestyle,
				'page:inline_script'    => $inlinescript
			)
		);
		
		$output = $this->apply(
			$output,
			array(
				'lang'=> $this->lang
			)                
		);
		
		$output = $this->apply(
			$output,
			&$this->values            
		);
		
		$output = $this->apply(
			$output,
			&$_GET,
			array('GET[',']'),
			true    
		);
		
		$output = $this->apply(
			$output,
			&$_POST,
			array('POST[',']'),
			true    
		);
		
		$output = $this->apply(
			$output,
			&$_SESSION,
			array('SESSION[',']'),
			true    
		);
		
		$output = $this->apply(
			$output,
			&$_SERVER,
			array('SERVER[',']'),
			true    
		);
		
		$output = $this->apply(
			$output,
			&$_COOKIE,
			array('COOKIE[',']'),
			true    
		);
		$this->makeCache( $output );
		return $output;
	}
	
	/*
			* function apply
			* args
			*    $temp(string) - String of template
			*    $data(array) - Assocciated array with data for apply to template
			* return
			*    String - Applied data
			*/
	function apply( $output, $data, $tags = array(), $case = false )
	{
		if( !is_array( $data ) )
		return $output;
		
		if( !$tags && isset( $this->options['templateTagBegin'] ) )
			$start = $this->options['templateTagBegin'];
		else if( $tags && is_array( $tags ) && isset( $tags[0] )  )
			$start = $tags[0];
		else
			$start = $this->defaultTempateTags[0];
		
		if( !$tags && isset( $this->options['templateTagEnd'] ) )
			$end = $this->options['templateTagEnd'];
		else if( $tags && is_array( $tags ) && isset( $tags[1] )  )
			$end = $tags[1];
		else
			$end = $this->defaultTempateTags[1];
			
		foreach($data as $key => $value)
		{
			if( is_array($value) ) continue;
			$key		= ($case) ? $start . $key . $end : strtoupper( $start . $key . $end ) ;
			$output		= str_replace($key, stripslashes($value), $output);
		}
		return $output;
	}
	
	/*
			* function log_print
			* args
			*    $delineter(string) - This insert beetween items
			* return
			*    String - Output log
			*/
	function log_print( $delimeter = '<br />')
	{
		$output = $delimeter;
		foreach( $this->_log as $val )
		{
			$output .= $val . $delimeter;
		}
		return $output;
	}
}
?>
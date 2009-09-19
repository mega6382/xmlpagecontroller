<?php

/*
    XML Page Controller
    Copyright (C) 2008 Saponenko Andrew<roguevoo@gmail.com>

    XML Page Controller is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    XML Page Controller distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with XML Page Controller.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * XML Tag class
 */
class XMLTag
{
    /**
     * @var string Tag name
     */
	var $tagName;

    /**
     *
     * @var array Tag attributes
     */
	var $tagAttrs;

    /**
     *
     * @var array Tag childrens
     */
	var $tagChildren;

    /**
     *
     * @var string Tag data
     */
	var $tagData;

    /**
     *
     * @var XMLTag Tag parent
     */
	var $tagParent;

    /**
     * Constructor
     */
	function XMLTag()
	{
		$this->tagName		= '';
		$this->tagAttrs		= array();
		$this->tagChildren	= array();
		$this->tagData		= null;
		$this->tagParent	= null;
	}

    /**
     * Set tag attribute
     * @param string $a_name Attribute name
     * @param string $a_value Attribute value
     */
	function setAttr( $a_name, $a_value )
	{
		if( is_string( $a_name ) == false )
            return;

		if( empty( $a_name ) )
            return;

		$this->tagAttrs[ $a_name ] = $a_value;
	}

    /**
     * Set tag attributes
     * @param array $a_array Array of tag attributes
     */
	function setAttrs( $a_array )
	{
		if( is_array( $a_array ) == false )
            return;

		$this->tagAttrs = $a_array;
	}

    /**
     * Set tag name
     * @param string $a_name Tag name
     */
	function setName( $a_name )
	{
		if( is_string( $a_name ) == false )
            return;

		if( empty( $a_name ) )
            return;

		$this->tagName = $a_name;
	}

    /**
     * Set tag data
     * @param mixed $a_value Tag data
     */
	function setData( $a_value )
	{
		$this->tagData = $a_value;
	}

    /**
     * Set tag children
     * @param array $a_arr Array of tag childrens
     */
	function setChildren( $a_arr )
	{
		if( is_array( $a_arr ) == false )
            return;

		$this->tagChildren = $a_arr;
	}

    /**
     * Set parent tag
     * @param XMLTag $a_node Parent tag
     */
	function setParent( $a_node )
	{
		if( is_a( $a_node, 'XMLTag' ) == false )
            return;

		$this->tagParent = $a_node;
	}

    /**
     * Add tag children
     * @param XMLTag $a_node Tag children
     */
	function addChild( $a_node )
	{
		if( is_null( $a_node ) )
		{
			$this->tagChildren = array();
			return;
		}

		if( is_a( $a_node, 'XMLTag' ) == false )
            return;

		array_push( $this->tagChildren, $a_node );
	}

    /**
     * Delete tag children
     * @param mixed $a_element Tag children as XMLTag or he key in array
     */
	function delChild( $a_element )
	{
		if( is_string( $a_element ) || is_numeric( $a_element ) )
		{
			if( isset( $this->tagChildren[ $a_element ] ) == true )
			{
				unset( $this->tagChildren[ $a_element ] );
                return;
			}
		}

		if( is_a( $a_element, 'XMLTag' ) )
		{
			foreach( $this->tagChildren as $k => $v )
			{
				if( $v == $a_element )
				{
					unset( $this->tagChildren[ $k ] );
					return;
				}
			}
		}
	}

    /**
     * Get or set tag name
     * @param string/null $a_name Name to set
     * @return string Return name if first argument is null
     */
	function name( $a_name = null )
	{
		if( $a_name )
		{
			$this->setName( $a_name );
			return;
		}
		return $this->tagName;
	}

	function attr( $a_name = null, $a_value = null )
	{
		if( is_null($a_name) == true ) return $this->tagAttrs;

		if( is_nul($a_name) == false  && is_null($a_name) == true )
		{
			if( is_string($a_name) == true ) return ( isset( $this->tagAtts[ $a_name ] ) ) ? $this->tagAttr[ $a_name ] : null;
			return null;
		}

		if( is_nul($a_name) == false && is_null($a_name) == false )
		{
			$this->setAttr( $a_name, $a_value );
			return null;
		}
	}

	function data( $a_data = null )
	{
		if( is_null( $a_data ) == true ) return $this->tagData;

		$this->tagData = $a_data;
	}

	function children()
	{
		return $this->tagChildren;
	}

	function child()
	{
		return $this->children();
	}

	function parent($a_parent = null)
	{
		if( is_null($a_parent) == true ) return $this->tagParent;
		$this->setParent($a_parent);
	}
}

class XMLParser
{

	/*
	*	XML parser handler
	*/
	var $m_parser			= null;
	var $m_allow_php		= false;
	var $m_array			= array();
	var $m_current			= null;
	var $m_result			= null;
	var $m_stacksize		= 0;

	var $m_source_data		= '';
	var $m_source_filename	= '';
	var $m_source_file		= null;

	function XMLParser( $a_file, $a_allow_php = false, $a_parse_now = false )
	{
		$this->m_allow_php = $a_allow_php;

		if( is_string( $a_file ) == false ) return $this;

		if( is_file( $a_file ) == true )
		{
			$this->m_source_filename = $a_file;
			$this->m_source_file = fopen( $this->m_source_filename, 'r' );

			if( $this->m_source_file == FALSE ) return $this;

			$file_size = filesize( $this->m_source_filename );
			$this->m_source_data = fread( $this->m_source_file, $file_size );

			fclose( $this->m_source_file );
		}
		else $this->m_source_data = $a_file;

		if( $a_parse_now )
		{
			return $this->Parse( &$this->m_source_data );
		}

		return $this;
	}

	function Parse( $a_xml_source = null )
	{
		if( $a_xml_source == null )
		{
			if( $this->m_source_data == '' ) return null;
			$a_xml_source = &$this->m_source_data;
		}

		if( is_string($a_xml_source) == false || $a_xml_source == '' ) return null;

		$this->m_parser = xml_parser_create();

		xml_set_object($this->m_parser, $this);
        xml_set_element_handler($this->m_parser, 'StartXMLElement', 'EndXMLElement');
        xml_set_character_data_handler($this->m_parser, 'CDATAElement');
		xml_set_processing_instruction_handler($this->m_parser, 'PiElement');

		xml_parser_set_option( $this->m_parser, XML_OPTION_CASE_FOLDING,	0);
		xml_parser_set_option( $this->m_parser, XML_OPTION_SKIP_TAGSTART,	0);
		xml_parser_set_option( $this->m_parser, XML_OPTION_SKIP_WHITE,		0);
		//xml_parser_set_option( $this->m_parser, XML_OPTION_TARGET_ENCODING,	'utf-8');


		if ( !xml_parse($this->m_parser, $a_xml_source ) )
		{
			die(sprintf("XML error: %s at line %d",
                xml_error_string(xml_get_error_code( $this->m_parser )),
                xml_get_current_line_number( $this->m_parser )));
		}

		xml_parser_free($this->m_parser);

		return $this->m_result;
	}

	function StartXMLElement( $a_parser, $a_name, $a_attrs)
	{

		//echo "Isset: " . isset( $this->m_current ) . "<br />";

		$l_node = new XMLTag();
		$l_node->setName( $a_name );
		$l_node->setAttrs( $a_attrs );

		if( is_null($this->m_result) )
			$this->m_result = &$l_node;

		if( isset($this->m_current) )
		{
			$p = &$this->m_current;

			//echo "Parent: " . $p->name() . "<br />";
			//$p->addChild( $l_node );
			array_push( $p->tagChildren, &$l_node );

			$l_node->tagParent = &$this->m_current;

			unset( $this->m_current );
		}
		$this->m_current = &$l_node;
	}

	function EndXMLElement( $a_parser )
	{
		if( isset($this->m_current) )
		{
			$p = &$this->m_current;
			unset( $this->m_current );

			if( $p->tagParent ) $this->m_current = &$p->tagParent;
		}
	}

	function CDATAElement( $a_parser, $a_data )
	{
		if( isset($this->m_current) == false ) return;

		$this->m_current->tagData .= trim($a_data);

		//echo '<hr />';
	}

	function PiElement( $a_parser, $a_target, $a_data )
	{
		switch( $a_target )
		{
			default:
				echo 'Hello is unknownn PI as'. $a_target .'<br />';
			break;

			case 'php':
			/*	echo 'PI is php: ' . $a_data . '<br />';*/

				$this->m_current->tagData = eval( $a_data );
			break;
		}

	}
}



class ZIPReader
{
	var $zip_installed = false;
	var $fileName = '';
	var $zip = 0;
	var $map = array();

	var $installed = false;

	function ZIPReader()
	{
		$this->zip_installed = ( function_exists('zip_open') && function_exists('zip_read') && function_exists('zip_entry_open') && function_exists('zip_entry_read') && function_exists('zip_entry_filesize')  &&  function_exists('zip_entry_name')  && function_exists('zip_entry_close') && function_exists('zip_close') );
	}

	function open( $filename = '' )
	{
		if( !$this->zip_installed ) return false;

		if( $filename == '' )
		{
			$filename = $this->fileName;
		}

		if( !is_string($filename) ) return false;

		$f = realpath( $filename );
		if( !$f ) return false;

		$this->fileName = $f;

		ob_start();
		$this->zip = zip_open( $this->fileName );
		ob_end_clean();

		if( !$this->zip || !is_resource( $this->zip ) ) return false;

		while( $zip_entry = zip_read( $this->zip ) )
		{
			$n = zip_entry_name($zip_entry);
			if( !$n ) continue;

			$this->map[ $n ] = $zip_entry;
		}
		return true;
	}

	function load( $filename = '' ){ return $this->open($filename); }


	function read( $name )
	{
		if( !$this->zip_installed ) return null;
		if( !$name || !is_string($name) ) return null;

		if( isset( $this->map[$name] ) )
		{
			$v = &$this->map[$name];
			if( !$v || !is_resource($v) ) return null;

			if( zip_entry_open($this->zip, $v, "r") )
			{
				$buf = zip_entry_read($v, zip_entry_filesize($v));
				zip_entry_close($v);
				return $buf;
			}
		}
		return null;
	}

	/*
	 * todo files
	 */
	function is_exist( $name )
	{
		if( !$name || !is_string($name) ) return false;
		return isset( $this->map[$name] );
	}

	function exist($name)
	{
		return $this->is_exist($name);
	}

	function files()
	{
		return array_keys($this->map);
	}

	function names()
	{
		return $this->files;

	}

	function get( $name )
	{
		return $this->read($name);
	}
};


/*
 * XMLConfig class
 * Description - load data from xml file
 */
class XMLConfig
{
	/*
	 * $filename (string) - f
	 */
	var $filename = '';
	var $xml = 0;
	var $map = array();

	/* CONSTRUCTOR */
	function XMLConfig()
	{
	}


	/* Return elements total */
	function count()
	{
		return count( $this->map );
	}

	/* Return elements total */
	function lenght()
	{
		return count( $this->map );
	}

	/* Return data */
	function map()
	{
		return $this->map;
	}

	/* Set new data */
	function setMap( $newMap = array() )
	{
		if( !is_array($newMap) && !is_object( $newMap ) ) return;
		$this->map = $newMap;
	}


	/* Return TRUE if value isset */
	function is_set( $valueName = "" )
	{
		if( !$valueName || !is_string($valueName) ) return false;
		return isset( $this->map[$valueName] );
	}

	/* Save data as xml file ( XMLConfig format ) */
	function save($filename = '')
	{
		if( !$filename ) return false;
		$result = new XMLTag("values", array(), count( $this->map ) );
		foreach( $this->map as $key => $value )
		{
			$result->AddChild("key", array('name'=>$key), 0 );
		}


		foreach( $result->tagChildren as $item )
		{
			//echo $item->tagAttrs['name'] . '<br />';
			if( !$item->tagAttrs['name'] ) continue;

			$value = &$this->map[ $item->tagAttrs['name'] ];
			$item->tagData = "<![CDATA[". trim(serialize( $value )) ."]]>";
		}
		file_put_contents($filename, "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".$result->GetXML() );
		return true;
	}

	/* Load data from xml file ( XMLConfig format ), return true if success*/
	function load($filename = '')
	{
		if( !$filename || !is_string($filename) ) return false;
		if( !is_file( $filename ) ) return false;

		//$xml = new XMLParser( file_get_contents($filename) );
		$xml = new XMLParser( $filename );
		$data = $xml->Parse();

		foreach( $xml->document->tagChildren as $item )
		{
			//echo $item->tagData;

			if( isset( $item->tagAttrs['name'] ) == false ) continue;

			if( substr( trim( $item->tagData ), 1, 1) == ":" )
			{
				$this->map[ $item->tagAttrs['name'] ] = unserialize($item->tagData);
			}
			else
			{
				$this->map[ $item->tagAttrs['name'] ] = $item->tagData;
			}
		}
		return true;
	}

	/* Return value by name */
	function get($name)
	{
		if( $this->is_set( $name ) ) return $this->map[$name];
		return false;
	}


	/* Set new value by name */
	function set($name, $value = 0, $save = false)
	{
		if( !$name ) return false;
		if( is_string( $name ) ) $this->setArray( array( $name => $value ), $save );
		if( is_array( $name ) ) $this->setArray( $name, $save );
	}


	/* Append new data */
	function setArray( $array, $save = false )
	{
		if( !$array || is_array($array) == false ) return false;

		foreach ( $array as $key => $value )
		{
			$this->map[$key] = $value;
		}
		if( $save == true ) $this->save();
	}
}

/*
* XML page controller - class
*/

class xmlpage
{
	/*
	* XML defined options
	*/
	var $options = array(
		"image_dir"=>''
	);

	var $userParser = array();

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
	var $debug = true;

	/*
	* Templated container
	*/
	var $container = '';

	/*
	* All output named values
	*/
	var $outdata = array();

	/*
	* Other script variables
	*/
	var $output, $xml, $logstack, $node, $nowloc, $tempnode;

	/*
	 * XMLConfigs cache
	 */
	var $configs = array();

	/*
	 * ZIP's cache
	 */
	var $zips = array();


	/*
	 *	Send headers
	 */
    var $headers = array();

	/*
	* function log (for inner usage) - Put message to log stack
	* args
	* 	$message (string) - Message to output
	*	$stack(number) - Count of whitespace before message line
	* return
	*	Number lines of log
	*/
	function log( $message, $stack = 0 )
	{
		if( !$this->debug ) return null;
		//echo 'Loggin: ' . $message . '<br />';

		for( $i = 0; $i < $this->logstack + $stack; $i++ ) $message = '&nbsp;&nbsp;' . $message;
        $this->applyGlobals($message);

		$d = date( "G:i:s",time() );

		array_push( $this->_log, "[{$d}] - {$message}" );
		return count( $this->_log );
	}

	/*
	* function findindir (for inner usage) - Find file in directories
	* args
	*	$filename (string) - Name of file to find
	* return
	*	0 - if file not found
	*	Or file path as string
	*/
	function findindir($filename)
	{
		//$dirs = $this->searchDir;
		foreach( $this->searchDir as $v )
		{
			if( is_file( $v.$filename ) ) return $v.$filename;
		}
		return 0;
	}

	function getTemplate( $templatename )
	{
		return ( isset( $this->templates[ $templatename ] ) ) ? $this->templates[ $templatename ] : '';
	}


    function parseLayer($node)
    {
        if( !$node || !is_a($node, 'XMLTag') )
        {
            $this->log("Wrong layer");
            return;
        }

        $f = $this->applyGlobals( $node->tagData, true );
        $this->log("Using layer: {$node->tagData}, {$f}");

        $dir = $this->findindir( $f  );
        if( $dir )
        {
            $this->log( "Result: file found '{$dir}'" , 2);
            $dir = file_get_contents($dir);
        }
		else
		{
            $this->log("Layer not found: '{$f}' ");
            return;
        }

        $xml_ = new XMLParser($dir);
        $this->parseRecursive( $xml_->Parse() );
    }

	/*
	* function parseValue(for inner usage) - Get result of tag
	* args
	*	$node(XMLTag) - Node
	* return
	*	String
	*/
	function parseValue($node)
	{
		$lang = $this->lang;
		$attrs = &$node->tagAttrs;

		$type_attr = isset( $attrs['type'] ) ? $attrs['type'] : 'inline';
		$name_attr = isset( $attrs['name'] ) ? $attrs['name'] : 'inline';

		$ret = ""; //

		if( isset( $attrs['lang'] ) )
		{
			if( $attrs['lang'] != $this->lang ) return '';
		}

		switch( $type_attr )
		{
			default:
			case 'inline':
			{
				if( !isset( $attrs['template'] ) )
				$ret = $node->tagData;
			}
            break;

			case 'template':
			case 'layout':
			case 'xml':
			{
				$t = $this->applyGlobals( $node->tagData );
                $this->log( 'Parse templated value from: ' . $t );

				$t = $this->findindir($t);

                if( $t )
				{
                    //$x = new XMLParser( file_get_contents($t),true);
					$x = new XMLParser($t, true);


                    $ret = $this->addXML( $x->Parse() );
				}
				else
				{
					$this->log('<b>File not found</b>: '. $node->tagData);
				}
			}
			break;

			case 'file':
			{
				$data = $this->applyGlobals($node->tagData);
				$ret = $this->findindir($data);
				if( $ret )
				{
					$this->log('Read file: '. $ret);
					$ret = file_get_contents( $ret );
				}
				else
				{
					$this->log('<b>File not found</b>: '.$data);
				}
			}
			break;


			case 'php':
			case 'module':
			case 'script':
			{
				$ret = $this->applyGlobals($node->tagData);
				$ret = $this->addPHPFile($ret, &$name_attr );
			}
			break;

			case 'config':
			{
				$f = $this->applyGlobals( $node->tagData );
				if( is_string( $f ) == false ) break;

				$c = 0;

				if( isset ( $this->configs[ $f ] ) && is_a( $this->configs[ $f ], 'XMLConfig' ) )
				{
					$c = &$this->configs[$f];
				}
				else
				{
					$c = new XMLConfig();
					$c->load( $f );

					$this->configs[ $f ] = &$c;
				}

				if( !$c )
				{
					$this->log("Config: file not found '".$f."'");
					break;
				}

				$ret = $c->get($name_attr);
				if($ret == false)
				{
					$this->log("Config value is invalid: '".$name_attr."', at file:'".$f."'");
				}
			}
			break;

			case 'gz':
			case 'zip':
			case 'zipfs':
			case 'gzfs':
			{
				$f = $this->applyGlobals( $node->tagData );
				if( is_string( $f ) == false ) break;

				$r = $this->addZip($f);
				if( $r ) $ret = $r;
			}
			break;
		}

		if( isset( $attrs['template'] ) && is_string($attrs['template']) && isset( $this->templates[ $attrs['template'] ] ) )
		{

			if( isset( $this->templates[ $attrs['template'] ] ) )
			{
                $this->log("Use template: '{$attrs['template']}'");
				$ret = $this->addXML( $node, array('container'=> $this->templates[ $attrs['template'] ] ) );
			}
			else
			{
				$tlist = "";
				foreach( $this->templates as $k => $v )
				{
					$tlist .= $k . ", ";
				}
				$this->log("Template not found: '{$attrs['template']}', from: " . $tlist . " total: " . count($this->templates) );
			}
		}
		return $this->applyGlobals($ret);
	}

	/*
	* function parseRecursive (for inner usage) - Recursive iteration in tag nodes
	* args
	*	$node(XMLTag) - Node where start iteration
	*/
	function parseRecursive($node, $parent = 0)
	{
		if( !$node ) return;
		$this->logstack += 2;
		foreach( $node->tagChildren as $child )
		{
			$this->parseNode( $child, ( $parent ) ? $parent : $node->name() );
		}
		$this->logstack -= 2;
	}

	/*
	* function contion (for inner usage) - Execute XML defined condition
	* args
	*	$node (XMLTag) - Node wher located condition
	*	$node(array) - Variables to look
	* return
	*	0 - if condition == false or his failed
	*	1 - if condition == true
	*/
	function condition( $node, $arg )
	{
		if( !$node ) return 0;

		$ret = 0;
		$l = '';

		if( isset( $node->tagAttrs['isset'] ) )
		{
            $ret = isset( $arg[ $node->tagAttrs['isset'] ] );
			$l = 'isset( "'.$node->tagAttrs['isset'].'") = ' . $ret;
		}

		if( isset( $node->tagAttrs['noset'] ) )
		{
			$ret = !isset( $arg[ $node->tagAttrs['noset'] ] );
            $l = 'noset( "'.$node->tagAttrs['noset'].'") = ' . $ret;
		}

		if( isset( $node->tagAttrs['param'] ) )
		{
			$params = split(",", $node->tagAttrs['param'] );

			//print_r( $params );
			$results = array();
			foreach( $params as $pkey => $param )
			{
				$var = "";
				$con = "";
				$val = "";
				$arr = split(' ', $param );

				if( count($arr) < 3 ) continue;

				$var = $arr[0];
				$con = $arr[1];
				$val = $arr[2];

				if( isset( $arg[$var] ) )
				{
					$var = $arg[$var];
                    eval('$ret = ("'.$var.'" '. $con . '"'.$val.'");');
					$this->log('Condition: ("'.$var.'" ' . $con . ' "' . $val . '") = '.$ret.';');
					array_push( $results, $ret );
				}
				else
				{
					if( $con == "!=" ) $ret = true;
					else $ret = false;
				}
			}
			if( count($results) > 1 )
			{
				$evalstring = '';
				foreach( $results as $r )
				{
					if( strlen( $evalstring ) > 0 ) $evalstring .= " & ";
					$evalstring .= number_format($r);
				}
				eval( '$ret = ( '.$evalstring.' );' );
				$this->log( "Eval this: " .'$ret = ( '.$evalstring.' );'  );
			}
		}
		if( $l ) $this->log( 'Condition "'. $node->name() .'" : '. $l );
		return $ret;
	}

	/*
	 * function identTag - Indentity tags
	 * args
	 *	$node (XMLTag) - node of tag to ident
	 *	$parent (XMLTag) - parent node of tag node ( default is null )
	 *
	 */
	function identTag($node,$parent="")
	{
		if( is_a($node,'XMLTag') == false ) return 'unknown';
		$parent = is_a($parent,'XMLTag') ? $parent->name() : $parent;
		$name = $node->name();

		switch( $name )
		{
			default:
				return $name;

			case 'set':
			case 'folder':
			case 'collection':
			case 'list':
			case 'enum':
				return 'set';

			case 'css':
			case 'style':
			case 'ssheet':
			case 'stylesheet':
				return 'css';

			case 'js':
			case 'jscript':
			case 'javascript':
			case 'script':
				return 'js';

			case 'out':
			case 'frame':
			case 'value':
				return 'out';

			case 'module':
			case 'php':
				return 'php';

			case 'switch':
			case 'switch:get':
			case 'switch:post':
			case 'switch:request':
			case 'switch:session':
			case 'switch:server':
			case 'switch:cookie':
			case 'switch:cookies':
			case 'switch:file':
			case 'switch:files':
			case 'switch:environment':
			case 'switch:env':
			case 'switch:var':
				return 'switch';

			case 'if':
			case 'if:get':
			case 'if:post':
            case 'if:request':
			case 'if:session':
			case 'if:server':
            case 'if:cookie':
			case 'if:cookies':
            case 'if:file':
            case 'if:files':
            case 'if:environment':
            case 'if:env':
			case 'if:var':
				return 'condition';
		}
	}

	/*
	 * function parseSwitch - Conditional switch
	 * args
	 *	$node (XMLTag) - XML node of switch
	 *	$parent (XMLTag) - XML parent node of switch node
	 */
	function parseSwitch( &$node, &$parent )
	{
		$switchName = isset( $node->tagAttrs['name'] ) ? $node->tagAttrs['name'] : null;
		if( is_null($switchName) ) return;

		$this->log( 'Parse switch' );
		$this->log( 'Switch key: ' . $switchName );

		$data = 0;

		switch( $node->name() )
		{
			default:
				$this->log( 'Unknown switch ' );
				return;
			break;

			case 'switch':
			case 'switch:get':
				$data = &$_GET;
			break;

			case 'switch:post':
				$data = &$_POST;
			break;

            case 'switch:request':
				$data = &$_REQUEST;
			break;

			case 'switch:session':
				$data = &$_SESSION;
			break;

			case 'switch:server':
				$data = &$_SERVER;
			break;

            case 'switch:cookie':
			case 'switch:cookies':
				$data = &$_COOKIE;
			break;

            case 'switch:file':
            case 'switch:files':
				$data = &$_FILES;
			break;

            case 'switch:environment':
            case 'switch:env':
				$data = &$_ENV;
			break;

			case 'switch:var':
				$data = &$this->values;
			break;
		}

		$switchValue = 0;

		if( is_array($data) || is_object($data) )
		{
			$this->log( 'Switch array: ' . print_r($data,true) );
			$switchValue = isset( $data[$switchName] ) ? $data[$switchName] : 'empty switch value';
		}
		else
		{
			$switchValue = $data;
		}

		$this->log( 'Switch value: ' . $switchValue );


		$default = 0;
		$found = false;

		foreach( $node->tagChildren as $case )
		{
			switch( $case->name() )
			{
				default:
					continue;
				break;

				case 'default':
					$default = $case;
					continue;
				break;

				case 'case':

					$name = isset( $case->tagAttrs['name'] ) ? $case->tagAttrs['name'] : 'empty case value';
					if( $name == 'empty case value' || $switchValue != $name ) continue;
					$this->log( 'Case at: "'.$name.'"', 2 );
					$found = true;
					$this->parseRecursive( &$case, $parent );

				break;
			}
		}

		if( !$found && $default )
		{
			$this->log( 'Do default action', 2 );
			$this->parseRecursive( $default, $parent );
		}
	}


	/*
	* function parseNode(for inner usage) - Parsing tag nodes
	* args
	*	$node(XMLTag) - Tag node to parse
	*/
	function parseNode($node, $parent = '')
	{
        if( is_object($node) == false ) return;
		if( is_a($node, 'XMLTag') == false ) return;

		$this->node = $node;
		$name = $node->name();

		$attr = &$node->tagAttrs;
		$data = &$node->tagData;

		$attrName = isset( $attr['name'] ) ? $attr['name'] : null;
		$attrType = isset( $attr['type'] ) ? $attr['type'] : null;

		if( function_exists('xmlpage_parser_'.$name) )
		{
			call_user_func_array( 'xmlpage_parser_' . $name , array( &$this, &$node, $parent ) );
			return;
		}

		switch( $name )
		{
			default:
			{
				$this->parseRecursive(&$node);
			}
			break;

            case 'layer':
            {
                $this->parseLayer(&$node);
            }
            break;

            case 'log':
                $this->log('XML log: ' . $data );
            break;

			case 'container':
			{
				$res = $this->parseValue( &$node );
				if( $res )
				{
					$this->container = $res;
					$this->log('Container size: ' . strlen($this->container));
				}
			}
			break;

			case 'template':
			{
				if( isset( $attr['name'] ) == false ) break;
				//if( $parent != 'templates' ) break;
				$res = $this->parseValue(&$node);
				if( $res )
				{
					$this->templates[ $attr['name'] ] = $res;
					$this->log('Template "'.$attr['name'].'" len: '.strlen($res));
				}
			}
			break;

			case 'set':
			case 'folder':
			case 'collection':
			case 'list':
			case 'enum':
			{
				$array	= &$_GET;
				$param	= isset( $attr['value'] )	? $attr['value']	: 'novalue';
				$suffix = isset( $attr['suffix'] )	? $attr['suffix']	: '';
				$prefix = isset( $attr['prefix'] )	? $attr['prefix']	: '';
				$dir	= isset( $attr['dir'] )		? $attr['dir']		: '';

				$list_options = array();
				$this->log( "List: '{$attr['name']}'"  , 1);

				foreach( $node->tagChildren as $n )
				{/*
				 * 	if( $n->name() != "item" || $n->name() != "option" ) continue;*/

					$a = $n->tagAttrs;

					if( isset( $a['name'] ) )
					{
						$this->log( "Option: '{$a['name']}'", 2 );
						$this->logstack = $this->logstack + 3;
						$list_options[ $a['name'] ] = $this->parseValue(&$n);

						$this->logstack = $this->logstack - 3;
					}
				}

				$this->log( "Param: 'Dir' = {$dir}" , 2);
				$this->log( "Param: 'Prefix' = {$prefix}" , 2);
				$this->log( "Param: 'Suffix' = {$suffix}" , 2);
				if( isset($array[$param]) )
				{
					$this->log( "Param: '{$param}' = {$array[$param]}" , 2);
					$res = '';
					if( $dir != "" )
					{
						$this->log( "Result: file found '{$dir}'" , 2);
						$res = file_get_contents($dir . $prefix . $array[$param] . $suffix);
					}
					else
					{
						$tofile = $dir . $prefix . $array[$param] . $suffix;
                        $dir = $this->findindir($tofile);
						if( $dir )
						{
							$this->log( "Result: file found '{$tofile}'" , 2);
							$res = file_get_contents($dir);
						}
						else
						{
							$this->log( "Result: file not found '{$tofile}'" , 2);
							if( isset( $list_options['not found'] ) )
							{
								//$this->log( "Result: not found size'".strlen($list_options['not found'])."'" , 2);
								$res = $list_options['not found'];
							}
						}
					}

					if( strlen($res) )
					{
						$this->log( "Result: size '". strlen( $res ) ."'" , 2);
						if(  isset( $this->outdata[ $attr['name'] ] ) )
						{
							$this->outdata[ strtolower( $attr['name'] ) ] .= $res;
						}
						else
						{
							$this->outdata[ strtolower( $attr['name'] ) ] = $res;
						}
					}
				}

			}
			break;

			case 'switch':
			case 'switch:get':
			case 'switch:post':
			case 'switch:request':
			case 'switch:session':
			case 'switch:server':
			case 'switch:cookie':
			case 'switch:cookies':
			case 'switch:file':
			case 'switch:files':
			case 'switch:environment':
			case 'switch:env':
			case 'switch:var':
				$this->parseSwitch( $node, $parent );
			break;

			case 'css':
			case 'style':
			case 'ssheet':
			case 'stylesheet':
			{
				$type = isset( $attr['type'] ) ? $attr['type'] : 'inline';
				$d = $this->applyGlobals(&$data);

				switch($type)
				{
					default:
					case 'inline':
					{
						$this->addCSS(&$d);
					}
					break;

					case 'gz':
					case 'zip':
						$c = $this->addZip($d);
						if( $c ) $this->addCSS(&$c);
					break;

					case 'file':
					{
                        $this->addCSSFile(&$d);
					}
					break;

					case 'remote':
					case 'url':
					{
						array_push( $this->remote_style, $d );
						$this->log('Url script: "'.$d.'"');
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
				//echo "HEELO SCRIPT";
				$type = isset( $attr['type'] ) ? $attr['type'] : 'inline';
				$d = $this->applyGlobals(&$data);
				switch($type)
				{
					default:
					case 'inline':
					{
						$this->addJS( &$d );
					}
					break;

					case 'gz':
					case 'zip':
						$c = $this->addZip($d);
						if( $c ) $this->addJS(&$c);
					break;

					case 'file':
					{
                        $this->addJSFile( &$d );
					}
					break;

					case 'remote':
					case 'url':
					{
						array_push( $this->remote_script, $d );
						$this->log('Url javascript: "'.$d.'"');
					}
					break;
				}
			}
			break;

			case 'out':
			case 'frame':
			case 'value':
			{
				$attrname = ( isset( $attr['name'] ) ) ? $attr['name'] : 'content';
				$this->addOut( $attrname, $this->parseValue(&$node) );
			}
			break;

			case 'module':
			case 'php':
			{
                $type = ( isset( $attr['type'] ) ) ? $attr['type'] : 'inline';
				$attrname = ( isset( $attr['name'] ) ) ? $attr['name'] : 'content';

				$args = array();
				$arg_counter = 0;
				if( isset( $node->tagChilren ) && is_array($node->tagChilren) && count( $node->tagChildren ) > 0  )
				{
					$script = '';
					foreach( $node->tagChildren as $n )
					{
						$nodeName = $n->name();

						switch( $nodeName )
						{
							default:
							break;

							case 'arg':

								if( !isset( $n->tagAttrs['name'] ) )
								{
									array_push( $args, $n->tagData );
									$arg_counter++;
								}
								else
								{
									$args[ $nodeName ] = $n->tagData;
								}

							break;

							case 'text':
							case 'value':
								$script = $n->tagData;
							break;
						}
					}

					switch( $type )
					{
						default:
						case 'inline':
							$this->log( "Add inline PHP" );
							$this->addPHP( &$script, $attrname, $args );
						break;

						case 'gz':
						case 'zip':
							$c = $this->addZip($d);
							$this->log( "Add gzip PHP" );
							if( $c ) $this->addPHP(&$c, $attrname, $args );
						break;

						case 'file':
							$this->log( "Add PHP file: " . $node->tagData );
							$this->addPHPFile( $$script, $attrname, $args );
						break;
					}
				}
				else
				{
					switch( $type )
					{
						default:
						case 'inline':
							$this->log( "Add inline PHP" );
							$this->addPHP( &$node->tagData, $attrname, $args );
						break;

						case 'file':
							$this->log( "Add PHP file: " . $node->tagData );
							$this->addPHPFile( $node->tagData, $attrname, $args );
						break;
					}
				}
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

            case 'if:request':
			{
				if( $this->condition(&$node, &$_REQUEST) ) $this->parseRecursive(&$node);
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

            case 'if:cookie':
			case 'if:cookies':
			{
				if( $this->condition(&$node, &$_COOKIE) ) $this->parseRecursive(&$node);
			}
			break;

            case 'if:file':
            case 'if:files':
			{
				if( $this->condition(&$node, &$_FILES) ) $this->parseRecursive(&$node);
			}
			break;

            case 'if:environment':
            case 'if:env':
			{
				if( $this->condition(&$node, &$_ENV) ) $this->parseRecursive(&$node);
			}
			break;

			case 'if:var':
			{
				if( $this->condition(&$node,&$this->values) ) $this->parseRecursive(&$node);
			}
			break;

			case 'locale':
			{
				if( isset( $attr['lang'] ) )
				{
					$this->nowlang = $attr['lang'];
					$this->locale[ $this->nowlang ] = array();
					$this->log('Read "'.$this->nowlang.'" locale collection');
					$this->tempnode = $name;
					$this->parseRecursive( &$node );
					$this->tempnode = '';
				}
			}
			break;

			case 'vars':
			case 'values':
			{
				if( count( $node->tagChildren ) < 1 ) break;

				foreach( $node->tagChildren as $i )
				{
					$nodeName = $i->name();
					if( $nodeName != "var" && $nodeName != 'item' ) continue;

					$this->addVar( isset( $i->tagAttrs['name'] ) ? $i->tagAttrs['name'] : null, $this->parseValue( &$i ) );
				}

			}
			break;

			case 'item':
			{
				if( !$attrName ) break;

				switch( $this->tempnode )
				{
					case 'locale':
					{
						$this->locale[ $this->nowlang ][ $attr['name'] ] = $this->parseValue(&$node);
						$this->log('Read locale "'.$attr['name'].'", size: '.strlen( $this->locale[ $this->nowlang ][ $attr['name'] ]) );
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
	*	$filename(string)(optional) - XML configuration filename
	*	$language(string)(default=en) - Language definion for reading right locales
	*	$debugmode(bool)(default=false) - Enabling/Disabling loging of class work
	*	$templateTag(array)(default=array('[',']')) - Begin and end template indeficators
	*/
	function XMLPage( $options = array() )
	{
        $language = 'en';
        $debugmode = false;
        $templateTag = array('[',']');
        $filename = 'none.xml';
        $out = false;

		$container = $templateTag[0] . "CONTENT" . $templateTag[1];

		$this->options["templateTagBegin"]  = '[';
		$this->options["templateTagEnd"]    = ']';
		$this->debug = true;

		$this->log('Construct XMLPAGE');

		if( $options && is_array($options) && count($options) > 0 )
		{

			/*
			 * Fix lang option
			 */
			if( isset($options['lang']) && is_string($options['lang']) && strlen($options['lang']) > 0 )
			{
				$language = $options['lang'];
				$this->log('+ Option "lang": ' . $language, 1);
			}

			/*
			 * Fix debug option
			 */
			if( isset($options['debug']) && is_bool($options['debug']) )
			{
				$debugmode = $options['debug'];
				$this->log('+ Option "debug": ' . $debugmode, 1);
			}

			/*
			 * Fix templateTag option
			 */
			if( isset($options['templateTag']) && is_array($options['templateTag']) && count($options['templateTag']) == 2 )
			{
				$a = $options['templateTag'];
				if( isset( $a['templateTagBegin'] ) && isset( $a['templateTagEnd'] ) )
				{
					if( is_string($a['templateTagBegin']) && is_string($a['templateTagEnd']) )
					{
						$templateTag[0] = $a['templateTagBegin'];
						$templateTag[1] = $a['templateTagEnd'];

						$this->log('+ Option "templateTag": ' . $templateTag[0] . ', '. $templateTag[1], 1);
					}
				}
			}

			/*
			 *  Fix container option
			 */
			if( isset($options['container']) && is_string($options['container']) )
			{
				$container = &$options['container'];
				$this->log('+ Option "container(custom)": '. strlen( $container ) . ' bytes' );
			}
			else
			{
				$container = $templateTag[0] . 'CONTENT' . $templateTag[1];
			}

			/*
			 *  Fix index option
			 */
			if( isset($options['index']) && is_string($options['index']) )
				$filename = $options['index'];

			/*
			 *  Fix otput option
			 */
			if( isset($options['output']) && is_bool($options['output']) )
				$out = $options['output'];
		}

        $this->filename             = $filename;
		$this->lang					= $language;
		$this->debug				= $debugmode;
		$this->defaultTempateTags	= $templateTag;
		$this->container			= $container;

		if( $out )
        {
            if( count($this->headers) > 0 )
            {
                foreach( $this->headers as $h ) header($h);
            }
            ob_start();

            $o = $this->out(true);

			$c = ob_get_contents();
			ob_end_clean();

            echo $o.$c;
        }
	}

	function parseOptions()
	{
		if( !$this->xml->tagChildren  ) return;

		$options = isset( $this->xml->tagChildren ) && !empty( $this->xml->tagChildren[0] ) ? $this->xml->tagChildren[0] : 0;

		if( !$options ) return;

		//print_r($options);
		foreach( $options->tagChildren as $item )
		{
			if( $item->name() != "option" ) continue;

			$attr = &$item->tagAttrs;
			$data = $item->tagData;

			if( !isset( $attr['name'] ) )
			{
				$this->log('<b>Invalid option name:</b>' . $data );
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
					if( is_dir( $data ) == false ) break;
					array_push( $this->searchDir, $data );
					$this->log('Add search dir: '.$data);
				}
				break;
			}
		}
	}

	function addZip( $path )
	{
		$this->log('Value ZIP: ' . $path );
		$a = explode("#", $path );

		if( count( $a ) < 2 ) return false;

		list( $file, $value ) = $a;

		$this->log('File: ' . $file, 1 );
		$this->log('Value:' . $value, 1 );

		$zip = null;
		if( isset( $this->zips[$file] ) == false )
		{
			if( is_file($file) == false ) return false;
			$this->log('File exist: ' . $file, 1 );

			$zip = new ZIPReader();
			if( $zip->open($file) == false )
			{
				$this->log('Fail when open: ' . $file, 1 );
				unset ($zip);
				return false;
			}
			$this->log('File opened: ' . $file, 1 );
			$this->zips[ $file ] = &$zip;
		}
		else
		{
			$zip = &$this->zips[$file];
		}

		if( !$zip || !is_a($zip, 'ZIPReader') ) return false;
		//$this->log('Invalid zip', 1 );

		if( $value && is_string($value) )
		{

			if( !$zip->exist( $value ) ) return false;
			//$this->log('Invalid zip', 1 );
			return $zip->read( $value );
		}
		return false;
	}

	function addJS($content)
	{
		if( !$content || !is_string( $content ) || strlen( $content ) == 0 ) return;

        array_push( $this->inline_script, "\n/********** INLINE SCRIPT ********/" . $content );
		$this->log('Inline javascript len: '. strlen($content) );

		//echo "HEELO SCRIPT inline <br />";
	}

	function addJSFile($filename)
	{

		if( !$filename || !is_string( $filename ) ) return "";

		$content = $this->findindir($filename);
		if( $content )
		{
			array_push($this->include_script, $content);
		}
		else
		{
			$this->log('Javascript file not found:'. $filename );
		}
		//echo "HEELO SCRIPT file <br />";
		return "";
	}

	function addVar( $name, $value )
	{
		if( is_string( $name ) == false  ) return;

		$this->log('Add variable "'.$name.'", size: '. strlen( $value ) . ' = ' . $value );
		$this->values[ $name ] = $value;
		//echo '&quote;' . $name . '&quote; = '. $value .'<br />';
	}

	function addLocale( $language, $name, $value )
	{

	}

	function addCSS($content)
	{
		array_push( $this->inline_style, $content );
		$this->log('Inline style len: '. strlen($content));
	}


	function addCSSFile($filename)
	{
		if( !$filename || !is_string( $filename ) ) return "";

		$content = $this->findindir($filename);
		if( $content )
		{
			//$content = file_get_contents( $content );
			//if( $content )
			//{
            //  $content .= "\n\n\n/*\n *\tFile: \"{$filename}\"\n*/\n{$content}";
			//	array_push( $this->inline_style, $content . "\n" );
			//	$this->log('File javascript len: '. strlen($content) );
			//	return $content;
			//}
			array_push($this->include_style, $content);
		}
		return "";
    }


	function addPHPFile($filename, $container = 0, $args = array() )
	{
		$file = $this->findindir($filename);
		if( $file )
		{
			$file = file_get_contents($file);
			if( $file ) $this->addPHP( $file, $container, $args );
		}
		return "";
	}

	function addPHP($data, $container = 0, $args = array() )
	{
		$ret = 0;
		if( !$data ) return $ret;

		$filename = 0;
		$phptempname = "xmlpageclass_".date("U").'.php';
		$fd = fopen($phptempname, 'w');
		if( $fd )
		{
			$writen = fwrite($fd, $data);
			fclose($fd);

			if( !$writen )
			{
				$this->log( 'Failed to write temp PHP file' );
				if( !unlink( $phptempname ) )
				{
					$this->log( 'Failed to unlink temp PHP file' );
				}

				return $ret;
			}

			if( isset($GLOBALS["xmlpageclass"]) )
			{

				$tempname = "xmlpageclass".date("U");
				$GLOBALS[$tempname] = $GLOBALS["xmlpageclass"];
				$GLOBALS["xmlpageclass"] = &$this;

				ob_start();
				$xmlpageclass = &$this;
				$xml_arguments = &$args;
				$ret = include($phptempname);
				$ret .= ob_get_contents();
				ob_end_clean();
				$GLOBALS["xmlpageclass"] = $GLOBALS[$tempname];
				unset( $GLOBALS[$tempname] );

				if( $ret && $container)
				{
					$this->addOut( $container, $ret );
				};
			}
			else
			{
				$GLOBALS["xmlpageclass"] = &$this;

				ob_start();
					$ret = include($phptempname);
					$ret = $ret == 1 ? "" : $ret;
					$ret .= ob_get_contents();
				ob_end_clean();

				unset( $GLOBALS["xmlpageclass"] );

				if( $ret && $container)
				{
					$this->addOut( $container, $ret );
				}
			}

			if( is_file( $phptempname ) )
			{
				if( !unlink( $phptempname ) )
				{
					$this->log( 'Failed to unlink temp PHP file' );
				}
			}
		}
		else
		{
			$this->log( 'Failed to create temp PHP file' );
		}
		$this->log( 'PHP add "'. $container .'": ' . strlen( $ret ) . 'bytes' );
		return $ret;
	}

	function addOut( $name = "content", $data = '' )
	{
		//$this->log( 'Value "'. $name . '": '. $data );
		$name = strtolower( $name  );
		if( isset( $this->outdata[ $name  ] ) )
		{
			$this->outdata[ $name ] .= $data;
		}
		else
		{
			$this->outdata[ $name ] = $data;
		}
	}

    function addXML( &$node, $options = array() )
    {
        $xml_ = new XMLPage( $options );


		//echo 'Do add XML';
        $xml_->xml			= $node;
        $xml_->lang         = $this->lang;
		$xml_->debug		= $this->debug;
        $xml_->options		= &$this->options;
        $xml_->searchDir	= &$this->searchDir;
        $xml_->values		= &$this->values;
        $xml_->templates	= &$this->templates;
        $xml_->logstack		= $this->logstack + 2;

        $ret = $xml_->out();

        $this->_log             = array_merge($this->_log,				$xml_->_log				);

		$this->include_script	= array_merge($this->include_script,	$xml_->include_script	);
        $this->include_style	= array_merge($this->include_style,		$xml_->include_style	);

        $this->inline_script	= array_merge($this->inline_script,		$xml_->inline_script	);
        $this->inline_style		= array_merge($this->inline_style,		$xml_->inline_style		);

        $this->remote_script	= array_merge($this->remote_script,		$xml_->remote_script	);
        $this->remote_style		= array_merge($this->remote_style,		$xml_->remote_style		);

        $this->headers          = array_merge($this->headers,			$xml_->headers			);
        $this->configs          = array_merge($this->configs,			$xml_->configs			);
        $this->zips             = array_merge($this->zips,				$xml_->zips				);

        return $ret;
    }


	function applyStyle( &$output )
	{
		$includestyle = '';
		foreach( $this->include_style as $style )
		{
			$includestyle .= $this->apply("\n<link rel=\"stylesheet\" type=\"text/css\" href=\"[STYLE]\" />", array( 'style' => $style ) );
		}

		$remotestyle = '';
		foreach( $this->remote_style as $style )
		{
			$remotestyle .= $this->apply("\n<link rel=\"stylesheet\" type=\"text/css\" href=\"[STYLE]\" />", array( 'style' => $style ) );
		}

		$inlinestyle = '';
		if( count( $this->inline_style ) > 0 )
		{
			$inlinestyle = "\n<style type=\"text/css\">";
			foreach( $this->inline_style as $script ){
                $inlinestyle .= $script . "\n";
            }
			$inlinestyle .= "</style>";
		}

		/*$output = $this->apply(
			$output,
			array(
				'page:include_style'	=> $includestyle.$remotestyle,
				'page:inline_style'		=> $inlinestyle
			)
		);*/
		$o = str_replace( array("</head","</Head", "</HEAD"), $remotestyle.$includestyle.$inlinestyle."</head", $output );

		$this->include_style	= array();
		$this->remote_style		= array();
		$this->inline_style		= array();

        return $o;
	}

	function applyScript( &$output )
	{
		//echo 'Apply scripts';

		$includescript = '';
		foreach( $this->include_script as $script )
		{
			$includescript .= "\n<script type=\"text/javascript\" src=\"{$script}\"></script>";
		}

		$remotescript = '';
		foreach( $this->remote_script as $script )
		{
			$remotescript .= "\n<script type=\"text/javascript\" src=\"{$script}\"></script>";
		}

		$inlinescript = '';
		if( count( $this->inline_script ) > 0 )
		{
			$inlinescript = "\n<script type=\"text/javascript\">\n";
			foreach( $this->inline_script as $script )	$inlinescript .= $script . "\n";
			$inlinescript .= "</script>";
		}
		/*$output = $this->apply(
			$output,
			array(
				'page:include_script'	=> $includescript.$remotescript,
				'page:inline_script'	=> $inlinescript
			)
		);*/
		$o = str_replace( array("</body", "</Body", "</BODY"), $remotescript.$includescript.$inlinescript."</body", $output);
		$this->include_script	= array();
		$this->remote_script	= array();
		$this->inline_script	= array();

		return $o;
	}

	/*
	* function out
	* return
	*	String combined data of script work
	*/
	function out( $_apply_styles = false )
	{
        if( $this->filename && is_file($this->filename) )
		{
			$this->log('Parse file: "'. $this->filename .'"');
           // $con = file_get_contents( $this->filename );
			$xmldoc	= new XMLParser( $this->filename , true );
			/*$xmldoc->Parse();
			$this->xml = $xmldoc->document;*/

			$this->xml = $xmldoc->Parse();
		}

        if( !$this->xml ) return 'Content not found.';

		$this->parseOptions();

		//print_r( $this->xml->tagChildren );

		$this->parseRecursive( $this->xml );

		$output = $this->container;
		$output = $this->apply($output, &$this->outdata);

		if( $_apply_styles == true )
		{
			$output = $this->applyStyle( $output );
			$output = $this->applyScript( $output );
		}

		$output = $this->apply(
			$output,
			array('lang'=> $this->lang )
		);

		$output = $this->apply(
			$output, &$this->values
		);

		$output = $this->applyGlobals($output, true);

		if( isset($this->locale[ $this->lang ]) )
		{
			$output = $this->apply( $output, &$this->locale[ $this->lang ] );
		}

		return $output;
	}

	/*
	* function apply
	* args
	*	$temp(string) - String of template
	*	$data(array) - Assocciated array with data for apply to template
	* return
	*	String - Applied data
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
			$start	= $this->defaultTempateTags[0];

		if( !$tags && isset( $this->options['templateTagEnd'] ) )
			$end = $this->options['templateTagEnd'];
		else if( $tags && is_array( $tags ) && isset( $tags[1] )  )
			$end = $tags[1];
		else
			$end = $this->defaultTempateTags[1];


        $text = $output;
		foreach($data as $key => $value)
		{
			if( is_array($value) ) continue;
			$KEY	= $start . $key . $end  ;
			$text	= str_replace( strtoupper($KEY), $value, $text);
		}
		return $text;
	}

    function applyGlobals( $out, $case = false )
	{
        $output = $this->apply(
            $out,
            &$_GET,
            array(
                0 => 'GET'.$this->options['templateTagBegin'],
                1 => $this->options['templateTagEnd']
            ),
            $case
        );

		$output = $this->apply(
			$output, &$_POST,
			array(0=>'POST'.$this->options['templateTagBegin'],1=>$this->options['templateTagEnd']), $case
		);

		$output = $this->apply(
			$output, &$_REQUEST,
			array(0=>'REQUEST'.$this->options['templateTagBegin'],1=>$this->options['templateTagEnd']), $case
		);

		$output = $this->apply(
			$output, &$_SESSION,
			array(0=>'SESSION'.$this->options['templateTagBegin'],1=>$this->options['templateTagEnd']), $case
		);

		$output = $this->apply(
			$output, &$_SERVER,
			array(0=>'SERVER'.$this->options['templateTagBegin'],1=>$this->options['templateTagEnd']), $case
		);

		$output = $this->apply(
			$output, &$_COOKIE,
			array(0=>'COOKIE'.$this->options['templateTagBegin'],1=>$this->options['templateTagEnd']), $case
		);

		$output = $this->apply(
			$output,
			&$_COOKIE,
			array(0=>'COOKIES'.$this->options['templateTagBegin'],1=>$this->options['templateTagEnd']), $case
		);

		$output = $this->apply(
			$output,
			&$this->values,
			array(0=>'VAR'.$this->options['templateTagBegin'],1=>$this->options['templateTagEnd']), $case
		);

       /* $trim_start = 0;
        $trim_end = 0;

        $newoutput = "";
        $e = explode("\n", $output);
        foreach( $e as $i ) $newoutput .= trim($i);*/
        return $output;
    }

	function pushContent()
	{

	}

	function popContent()
	{

	}

	/*
	* function log_print
	* args
	*	$delineter(string) - This insert beetween items
	* return
	*	String - Output log
	*/
	function log_print( $delimeter = '<br />')
	{
		$del = "\n";
		if( $delimeter && is_string($delimeter) && strlen($delimeter) ) $del = $delimeter;

		$output = "======== Log ====================" . $del;
		foreach( $this->_log as $val )
		{
			//list($usec, $sec) = explode( " ", microtime() );
			$output .= $val . $del;
		}
		return $output;
	}
}
?>
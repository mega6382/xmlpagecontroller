<?php

/*
    XML Page Controller
    Copyright (C) 2008 Saponenko Andrew<roguevoo@gmail.com>

    XML Page Controller is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    XML Page Controller is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with XML Page Controller.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 *  XML page controller - class
 */
class XMLPage
{
    /**
     * XML defined options
     */
	var $options = array("image_dir"=>'');

    /**
     * File search directories from XML defined options
     */
	var $searchDir = array(
        ''// Current dir
	);

	var $values = array();
    
    /**
     * Default template tags, begin and end
     */
	var $defaultTempate = array('[', ']', 'CONTAINER');
    
    /**
     * Contain temtlated HTML or etc from XML defines
     */
	var $templates = array();
    
    /**
     * Strings from Inline defined scripts
     */
	var $inline_script = array();
    
    /**
     * Include script file names
     */
	var $include_script = array();

    /**
     * Url address of remote script
     */
	var $remote_script = array();

    /**
     * Strings from inline defined scripts
     */
	var $inline_style = array();

    /**
     * Include script file names
     */
	var $include_style = array();
    
    /**
     * Url address of remote scripts
     */
	var $remote_style = array();
    
    /**
     * Debug information collection
     */
	var $_log = array();
    
    /**
     * Strings of localed words defined in XML
     */
	var $locale = array();
    
    /**
     * Defined language
     */
	var $lang = 'en';

    /**
     * Debug flag
     */
	var $debug = true;

    /**
     * Templated container
     */
	var $container = '';

    /**
     * All output named values
     */
	var $outdata = array();

    /**
     * Other script variables
     */
	var $output = '';
	var $xml = null;
	var $logstack = 0;
	var $node = null;
	var $nowloc = null;
	var $tempnode = null;

    /**
     * XMLConfigs cache
     */
	var $configs = array();

    /**
     * ZIP's cache
     */
	var $zips = array();
    
    /**
     * Send headers
     */
	var $headers = array();

    /**
     * Content stack
     */
	private $content_stack = array();

	/**
     * function log (for inner usage) - Put message to log stack
     * @param string $message - Message to output
     * @param number $stack Count of whitespace before message line
     * @return number Lines of log
     */
	private function log($message, $stack = 0 )
	{
		if ( !$this->debug )
            return;

		for ($i = 0; $i < $this->logstack + $stack; $i++ )
		{
			$message = '&nbsp;&nbsp;' . $this->applyGlobals($message );
		}

		$d = date("G:i:s", time() );
		array_push($this->_log, "[{$d}] {$message}" );
		return count($this->_log );
	}

    /**
     * function findindir (for inner usage) - Find file in directories
     * @param string $filename Name of file to find
     * @return mixed Return null if file not found or file path as string
     */
	public function findindir($filename)
	{
		foreach ($this->searchDir as $v )
		{
			if (is_file($v.$filename ) )
                return $v.$filename;
		}
	}

    /**
     * Get template by name
     * @param string $templatename Template name
     * @return string Return template if he found else empty string
     */
	public function getTemplate($templatename )
	{
		return(isset($this->templates[ $templatename ] ) ) ? $this->templates[ $templatename ] : '';
	}

	private function parseLayer(XMLTag & $node)
	{
		if (!$node )
		{
			return;
		}

		$f = $this->applyGlobals($node->data() );
		$this->log("Using layer: {$node->data()}, {$f}");
		$dir = $this->findindir($f );

		if ($dir )
		{
			$this->log("Result: file found '{$dir}'" , 2);
			$dir = file_get_contents($dir);
		}
		else
		{
			$this->log("Layer not found: '{$f}' ");
			return;
		}

		$xml_ = new XMLParser($dir);
		$this->parseRecursive($xml_->Parse() );
	}

    /**
     * function parseValue(for inner usage) - Get result of tag
     *
     * @param XMLTag $node
     * @return string
     */
	private function parseValue(XMLTag & $node )
	{
	    if (!$node )
		    return;

		$lang = &$this->lang;
		$attrs = $node->attr();
		$type_attr = $node->attr('type');
		$name_attr = $node->attr('name');
		$lang_attr = $node->attr('lang');
		$ret = null;

		if ($lang_attr && $lang_attr != $this->lang )
		    return '';

		if ( $node->attr('template') )
		{
		    $container= $this->defaultTempate[0].$this->defaultTempate[2].$this->defaultTempate[1];

		    $template_name= $node->attr('template');

		    if ( isset( $this->templates[ $template_name ] ) )
		    {
			$this->log("Use template: '{$template_name}'");
			$container = &$this->templates[ $template_name ];
		    }
		    else
		    {
			$tlist = "";

			foreach ($this->templates as $k => $v )
			{
			    $tlist .= $k . ", ";
			}

			$this->log("Template not found: '{$template_name}', from: " . $tlist . " total: " . count($this->templates) . 'use default: ' . $container );
		    }

		    $this->pushContent();

		    foreach ($node->children() as $item )
		    {
			$this->parseNode($item, $node );
		    }

		    $data = $this->outdata;
		    $this->popContent();
		    return $this->apply($container, $data );
		}

		switch ( $type_attr )
		{
		    default:
		    case 'inline':
			$ret = $node->data();
			break;

		    case 'template':
		    case 'layout':
		    case 'xml':
			{
			    $t = $this->applyGlobals( $node->data() );
			    $this->log('Parse templated value from: ' . $t );
			    $t = $this->findindir($t);

			    if ($t )
			    {
				$x = new XMLParser($t, true);
				$ret = $this->addXML($x->Parse() );
			    }
			    else
				$this->log('<b>File not found</b>: '. $node->data() );
			}
			break;

		    case 'file':
			    {
				$data = $this->applyGlobals($node->data() );
				$dir = $this->findindir($data);

				if ($dir )
				{
				    $this->log('Read file: '. $dir);
				    $ret = file_get_contents($dir );
				}
				else
				    $this->log('<b>File not found</b>: '.$data);
			    }
			    break;

		    case 'php':
		    case 'module':
		    case 'script':
			    {
				    $ret = $this->applyGlobals($node->data() );
				    $ret = $this->addPHPFile($ret, $name_attr );
			    }

			    break;

	    case 'config':
		    {
			    $f = $this->applyGlobals($node->data() );

			    if (is_string($f ) == false )
			    {
				    break;
			    }

			    $c = 0;

			    if (isset($this->configs[ $f ] ) && is_a($this->configs[ $f ], 'XMLConfig' ) )
			    {
				    $c = &$this->configs[$f];
			    }
			    else
			    {
				    $c = new XMLConfig();
				    $c->load($f );
				    $this->configs[ $f ] = &$c;
			    }

			    if (!$c )
			    {
				    $this->log("Config: file not found '".$f."'");
				    break;
			    }

			    $ret = $c->get($name_attr);

			    if ($ret == false)
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
			    $f = $this->applyGlobals($node->data() );//if( is_string( $f ) == false ) break;
			    $r = $this->addZip($f);

			    if ($r )
			    {
				    $ret = $r;
			    }
		    }

		    break;
	    }

	    return $this->applyGlobals($ret);
	}

	/**
     * function parseRecursive (for inner usage) - Recursive iteration in tag nodes
     * @param XMLTag $node - Node where start recursive parsing
     */
	private function parseRecursive(XMLTag & $node, $parent = null )
	{
		if (!$node )
		{
			return;
		}

		$this->logstack++;

		foreach ($node->children() as $child )
		{
			$this->parseNode($child, ( $parent != null ) ? $parent : $node->name() );
		}

		$this->logstack--;
	}

    /**
     * function contion (for inner usage) - Execute XML defined condition
     * @param XMLTag $node Node wher located condition
     * @param array $arg Variables to look
     * @return bool
     * Return 0 - if condition == false or his failed or 1 - if condition == true
     */
	private function condition( XMLTag & $node, &$arg )
	{
		if (!$node )
            return;

		$ret = 0;
		$l = '';
		$isset_ = $node->attr('isset');

		if ($isset_ )
		{
			$ret = isset($arg[ $isset_ ] );
			$this->log('isset("'.$isset_.'") = '.$ret);
			return $ret;
		}

		$noset_ = $node->attr('noset');

		if ($noset_ )
		{
			$ret = !isset($arg[ $noset_ ] );
			$this->log('noset("'.$noset_.'") = '.$ret);
			return $ret;
		}

		$param_ = $node->attr('param');

		if ( $param_ )
		{
			$params = explode(",", $param_ );
			$results = array();

			foreach ($params as $pkey => $param )
			{
				$var = "";
				$con = "";
				$val = "";
				$arr = explode(' ', $param );

				if (count($arr) < 3 )
				{
					continue;
				}

				$var = $arr[0];
				$con = $arr[1];
				$val = $arr[2];

				if (isset($arg[$var] ) )
				{
					$var = $arg[$var];
					eval('$ret = ("'.$var.'" '. $con . '"'.$val.'");
					');
					$this->log('Condition: ("'.$var.'" ' . $con . ' "' . $val . '") = '.$ret.';');
					array_push($results, $ret );
				}
				else
				{
					if ($con == "!=" )
					{
						$ret = true;
					}
					else
					{
						$ret = false;
					}
				}
			}

			if (count($results) > 1 )
			{
				$evalstring = '';

				foreach ($results as $r )
				{
					if (strlen($evalstring ) > 0 )
					{
						$evalstring .= " & ";
					}

					$evalstring .= number_format($r);
				}

				eval('$ret = ( '.$evalstring.' );' );
				$this->log("Eval this: " .'$ret = ( '.$evalstring.' );' );
			}
		}

		if ($l )
		{
			$this->log('Condition "'. $node->name() .'" : '. $l );
		}

		return $ret;
	}


    /**
     * function identTag - Indentity tags
     * @param XMLTag $node node of tag to ident
     * @return string Tag type as string
     */
	public function identTag( XMLTag & $node )
	{
		switch ($node->name() )
		{
			default:
                return 'unknown';

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


    /**
     * function parseSwitch - Conditional switch
     * @param XMLTag $node XML node of switch
     */
	private function parseSwitch ( XMLTag & $node )
	{
		$switchName= $node->attr('name');//$switchValue	= null;
		if ( !$switchName )
            return;

		$this->log('Parse switch' );
		$this->log('key: ' . $switchName, 1 );
		$switchData = null;

		switch ( $node->name() )
		{
			default:
                $this->log('Bad switch data ', 1 );
                return;

            case 'switch':
            case 'switch:get':
                $switchData = &$_GET;
                break;

            case 'switch:post':
                $switchData = &$_POST;
                break;

            case 'switch:request':
                $switchData = &$_REQUEST;
                break;

            case 'switch:session':
                $switchData = &$_SESSION;
                break;

            case 'switch:server':
                $switchData = &$_SERVER;
                break;

            case 'switch:cookie':
            case 'switch:cookies':
                $switchData = &$_COOKIE;
                break;

            case 'switch:file':
            case 'switch:files':
                $switchData = &$_FILES;
                break;

            case 'switch:environment':
            case 'switch:env':
                $switchData = &$_ENV;
                break;

            case 'switch:var':
                $switchData = &$this->values;
                break;
		}

		if (!$switchData )
		{
			$this->log('No switch data ', 1 );
		}

		$switchValue = isset($switchData[ $switchName ] ) ? $switchData[ $switchName ] : null;
		$this->log('value: ' . $switchValue, 1 );
		$default = null;

		foreach ($node->children() as $i )
		{
			if ($i->name() == 'default' )
			{
				$default = &$i;
				break;
			}
		}

		if (!$switchValue && $default )
		{
			$this->parseRecursive($default, $parent );
			return;
		}

		$default = null;
		foreach ( $node->children() as $case )
		{
			switch ($case->name() )
			{
                case 'default':
					$default = $case;
                    break;

                case 'case':
					$attrName = $case->attr('name');

					if (!$switchValue || !$attrName )
                        break;

					$op = '';

					switch (strcmp($attrName, $switchValue ) )
					{
                        case 0:
                            $this->log('('. $attrName . ' = ' . $switchValue . ')', 1 );
							$this->parseRecursive($case, $parent );
							return;

                        case 1:
                            break;

                        case -1:
                            break;

                     }

			}
		}

		if ($default )
		{
			$this->log('Do default action', 1 );
			$this->parseRecursive( $default, $parent );
		}
	}

	private function parseForeach ( XMLTag & $node )
	{
		if ( !$node )
            return;

		$this->log('Parse foreach', 0 );
		$attrName = $node->attr('name');
		$attrType = $node->attr('type');
		$foreachData = null;

		switch ($node->name() )
		{
			default:
                $this->log('Bad foreach data ', 1 );
                return;

            case 'foreach':
            case 'foreach:get':
                $foreachData = &$_GET;
                break;
            
            case 'foreach:post':
                $foreachData = &$_POST;
                break;
            
            case 'foreach:request':
                $foreachData = &$_REQUEST;
                break;
            
            case 'foreach:session':
                $foreachData = &$_SESSION;
                break;
            
            case 'foreach:server':
                $foreachData = &$_SERVER;
                break;
            
            case 'foreach:cookie':
            case 'foreach:cookies':
                $foreachData = &$_COOKIE;
                break;
            
            case 'foreach:file':
            case 'foreach:files':
                $foreachData = &$_FILES;
                break;
            
            case 'foreach:environment':
            case 'foreach:env':
                $foreachData = &$_ENV;
                break;
            
            case 'foreach:var':
            case 'foreach:vars':
                $foreachData = &$this->values;
                break;
		}

		$foreachKey = $node->attr('key');

		if (!$foreachKey )
		{
			$this->log('Foreach key is null ', 1 );
			return;
		}

		if (!isset($foreachData[ $foreachKey ] ) )
		{
			$this->log('Bad foreach key ', 1 );
			return;
		}

		$foreachValue = &$foreachData[ $foreachKey ];

		if (!$foreachValue || !is_array($foreachValue ) )
		{
			$this->log('Bad foreach value ', 1 );
			return;
		}

		$container = $this->defaultTempate[0].$this->defaultTempate[2].$this->defaultTempate[1];
		$rcontainer = null;

		foreach ($node->children() as $item )
		{
			switch ($item->name() )
			{
                case 'container':
					$container = $this->parseValue($item );
					$this->log('Container size: ' . strlen($container) , 1 );
                    break;
                
                case 'record':
                    $rcontainer = $this->parseValue($item );
					$this->log('Record size: ' . strlen($rcontainer) , 1 );
                    break;
			}
		}

		if (!$rcontainer )
		{
			$this->log('Bad foreach record ', 1 );
			return;
		}

		$out = '';

		foreach ($foreachValue as $key => $val )
		{
			$out .= $this->apply($rcontainer, array('key'=>$key, 'val'=>$val, 'value'=>$val));
		}

		$out = $this->apply($container, array('content'=>$out) );
		$this->addOut($attrName, $out);
	}

    /**
     * function parseNode(for inner usage) - Parsing tag nodes
     * @param XMLTag $node Tag node to parse
     */
	private function parseNode( XMLTag & $node, $parent)
	{
		if (!$node )
            return;

		if ( isset($this->node ) )
            unset($this->node);

		$this->node = $node;

		$name = $node->name();
		$attr = $node->attr();
		$data = $node->data();

		$attrName = ( isset($attr['name']) ) ? $attr['name'] : null;
		$attrType = ( isset($attr['type']) ) ? $attr['type'] : null;
		$attrLang = ( isset($attr['lang']) ) ? $attr['lang'] : null;

		if ( function_exists('xmlpage_parser_'.$name) )
		{
			call_user_func_array('xmlpage_parser_' . $name , array($this, $node, $parent ) );
			return;
		}

		switch ($name )
		{
			default:
                $this->parseRecursive($node );
                break;
            
            case 'layer':
                $this->parseLayer($node);
                break;
            
            case 'log':
                $this->log('XML log: ' . $data );
                break;
            
            case 'container':
                {
                    $res = $this->parseValue($node );
                
                    if ($res )
                    {
                        $this->container = $res;
                        $this->log('Container size: ' . strlen($this->container));
                    }
                }
                break;
            
            case 'template':
                {
                    if (!$attrName )
                    {
                        break;
                    }

                    if ($node->parent() && $node->parent()->name() != 'templates' )
                    {
                        break;
                    }

                    $res = $this->parseValue($node);

                    if ($res )
                    {
                        $this->templates[ $attrName ] = $res;
                        $this->log('Template "'. $attrName .'" len: '.strlen($res));
                    }
                    
                }
                break;
            
            case 'foreach':
                break;
            
            case 'set':
            case 'folder':
            case 'collection':
            case 'list':
            case 'enum':
			{
				$array= &$_GET;
				$param= $node->attr('value');
				$suffix = $node->attr('suffix');
				$prefix = $node->attr('prefix');
				$dir= $node->attr('dir');
				$list_options = array();
				$this->log("List: '{$attrName}'" , 1);

				foreach ($node->children() as $n )
				{
					$option_name = $n->attr('name');

					if (!$option_name )
					{
						continue;
					}

					$this->log("Option: '{$option_name}'", 2 );
					$this->logstack = $this->logstack + 1;
					$list_options[ $option_name ] = $this->parseValue($n);
					$this->logstack = $this->logstack - 1;
				}

				$this->log("Param: 'param' = {$param}" , 2);
				$this->log("Param: 'Dir' = {$dir}" , 2);
				$this->log("Param: 'Prefix' = {$prefix}" , 2);
				$this->log("Param: 'Suffix' = {$suffix}" , 2);

				if (isset($array[ $param ] ) )
				{
					$this->log("Param: '{$param}' = {$array[$param]}" , 2);
					$res = '';

					if (!$dir )
					{
						$this->log("Result: file found '{$dir}'" , 2);
						$res = file_get_contents($dir . $prefix . $array[$param] . $suffix);
					}
					else
					{
						$tofile = $dir . $prefix . $array[$param] . $suffix;
						$dir = $this->findindir($tofile);

						if ($dir )
						{
							$this->log("Result: file found '{$tofile}'" , 2);
							$res = file_get_contents($dir);
						}
						else
						{
							$this->log("Result: file not found '{$tofile}'" , 2);

							if (isset($list_options['not found'] ) )
							{
								$res = $list_options['not found'];
							}
						}
					}

					if (strlen($res) )
					{
						$this->log("Result: size '". strlen($res ) ."'" , 2);//$ptr = &$this->outdata[ $attrName ];
						$this->addOut($attrName, $res );
					}
				}
				else
				{
					$this->log("Error: array value not found" , 2);
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
            case 'switch:vars':
                $this->parseSwitch($node, $parent );
                break;
            
            case 'foreach':
            case 'foreach:get':
            case 'foreach:post':
            case 'foreach:request':
            case 'foreach:session':
            case 'foreach:server':
            case 'foreach:cookie':
            case 'foreach:cookies':
            case 'foreach:file':
            case 'foreach:files':
            case 'foreach:environment':
            case 'foreach:env':
            case 'foreach:var':
            case 'foreach:vars':
                $this->parseForeach($node );
                break;
            
            case 'css':
            case 'style':
            case 'ssheet':
            case 'stylesheet':
                {
                    switch ($attrType)
                    {
                        default:
                        case 'inline':
                            $this->addCSS($this->applyGlobals($data) );
                            break;
                        
                        case 'include':
                        {
                            $filename = $this->applyGlobals($data);
                            $content = $this->findindir($filename);

                            if ($content )
                            {
                                $this->addCSS(file_get_contents($filename) );
                            }
                            else
                            {
                                $this->log('- CSS File not found: "' . $filename . '"');
                            }
                        }
                        break;
                        
                        case 'gz':
                        case 'zip':
                            {
                                $c = $this->addZip($this->applyGlobals($data) );

                                if ($c )
                                {
                                    $this->addCSS($c);
                                }
                            }
                            break;
                        
                        case 'file':
                            $this->addCSSFile($this->applyGlobals($data) );
                            break;
                        
                        case 'remote':
                        case 'url':
                            {
                                $d = $this->applyGlobals($data);
                                array_push($this->remote_style, $d );
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
                    switch ($attrType )
                    {
                        default:
                        case 'inline':
                            $this->addJS( $this->applyGlobals($data) );
                            break;
                        
                        case 'include':
                            {
                                $filename = $this->applyGlobals($data);
                                $content = $this->findindir($filename);

                                if ($content )
                                {
                                    $this->addJS(file_get_contents($filename) );
                                }
                                else
                                {
                                    $this->log('- JS File not found: "' . $filename . '"');
                                }
                            }
                            break;
                        
                        case 'gz':
                        case 'zip':
                            {
                                $c = $this->addZip($this->applyGlobals($data) );

                                if ($c )
                                {
                                    $this->addJS($c);
                                }
                            }
                            break;


                        case 'file':
                            $this->addJSFile($this->applyGlobals($data) );
                            break;

                        case 'remote':
                        case 'url':
                            {
                                $d = $this->applyGlobals($data);
                                array_push($this->remote_script, $d );
                                $this->log('Url javascript: "'.$d.'"');
                            }
                            break;
                    }
                }
                break;

            case 'out':
            case 'echo':
            case 'frame':
            case 'value':
                $this->addOut($attrName, $this->parseValue($node ) );
                break;
        
            case 'module':
            case 'php':
                {
                    $args = array();
                    $arg_counter = 0;
                    $child = $node->children();

                    if ($child && is_array($child ) && count($child ) > 0 )
                    {
                        $script = '';

                        foreach ($child as $n )
                        {
                            $nodeName = $n->name();

                            switch ($nodeName )
                            {
                                case 'arg':
                                    {
                                        if (!$n->attr('name') )
                                        {
                                            array_push($args, $n->data() );
                                            $arg_counter++;
                                        }
                                        else
                                        {
                                            $args[ $nodeName ] = $n->data();
                                        }
                                    }
                                    break;

                                case 'text':
                                case 'value':
                                    {
                                        $script = $n->data();
                                    }
                                    break;
                            }
                        }

                        switch ($attrType )
                        {
                            default:
                            case 'inline':
                                $this->log("Add inline PHP" );
                                $this->addPHP($script, $attrName, $args );
                                break;

                            case 'gz':
                            case 'zip':
                                $c = $this->addZip($d);
                                $this->log("Add gzip PHP" );

                                if ($c )
                                {
                                    $this->addPHP($c, $attrName, $args );
                                }
                                break;

                            case 'file':
                                $this->log("Add PHP file: " . $node->data() );
                                $this->addPHPFile($script, $attrName, $args );
                                break;
                        }
                    }
                    else
                    {
                        switch ($attrType )
                        {
                            default: case 'inline':
                                $this->log("Add inline PHP" );
                                $this->addPHP($node->data(), $attrName, $args );
                                break;

                            case 'file':
                                $this->log("Add PHP file: " . $node->data() );
                                $this->addPHPFile($node->data(), $attrName, $args );
                                break;
                        }
                    }
                }
                break;

            case 'if':
            case 'if:get':
                {
                    if ($this->condition($node, $_GET) )
                    {
                        $this->parseRecursive($node);
                    }
                }
                break;

            case 'if:post':
                {
                    if ($this->condition($node, $_POST) )
                    {
                        $this->parseRecursive($node);
                    }
                }
                break;

            case 'if:request':
                {
                    if ($this->condition($node, $_REQUEST) )
                    {
                        $this->parseRecursive($node);
                    }
                }
                break;

            case 'if:session':
                {
                    if ($this->condition($node, $_SESSION) )
                    {
                        $this->parseRecursive($node);
                    }
                }
                break;

            case 'if:server':
                {
                    if ($this->condition($node, $_SERVER) )
                    {
                        $this->parseRecursive($node);
                    }
                }
                break;

            case 'if:cookie':
            case 'if:cookies':
                {
                    if ($this->condition($node, $_COOKIE) )
                    {
                        $this->parseRecursive($node);
                    }
                }
                break;

            case 'if:file':
            case 'if:files':
                {
                    if ($this->condition($node, $_FILES) )
                    {
                        $this->parseRecursive($node);
                    }
                }
                break;

            case 'if:env':
            case 'if:environment':
                {
                    if ($this->condition($node, $_ENV) )
                    {
                        $this->parseRecursive($node);
                    }
                }
                break;

            case 'if:var':
            case 'if:vars':
                {
                    if ($this->condition($node, $this->values) )
                    {
                        $this->parseRecursive($node);
                    }
                }
                break;

            case 'locale':
                {
                    if ( !$attrLang )
                    {
                        break;
                    }

                    if ( $attrLang != $this->lang )
                    {
                        break;
                    }

                    $this->log('Parse "'. $attrLang .'" locale collection', 0);

                    foreach ($node->children() as $i )
                    {
                        $n = $i->attr('name');

                        if (!$n )
                        {
                            continue;
                        }

                        $v = $this->parseValue($i );
                        $this->locale[ $n ] = $v;
                        $this->log('Add locale value - "'. $n .'" size: '. strlen($v), 1);
                    }
                    /*if( isset( $attr['lang'] ) )
                    {
                    $this->nowlang = $attr['lang'];
                    $this->locale[ $this->nowlang ] = array();
                    $this->log('Read "'.$this->nowlang.'" locale collection');
                    $this->tempnode = $name;
                    $this->parseRecursive( $node );
                    $this->tempnode = '';
                    }*/
                }
                break;
        
            case 'var':
                $this->addVar($attrName, $this->parseValue($node) );
                break;
                /*case 'vars':
                case 'values':
                {
                foreach( $node->children() as $i )
                {
                $nodeName = $i->name();
                if( $nodeName != "var" && $nodeName != 'item' ) continue;

                $this->addVar( $i->attr('name'), $this->parseValue( $i ) );
                }

                }
                break;*/
		}
	}

    /**
     * CLASS CONSTRUCTOR
     * @param array $options
     *
     * Options can'be:
     *  'lang' string (default=en) Language definion for reading right locales
     *  'debug' bool (default=false) Enabling/Disabling loging of class work 
     *  'templateTag' array (default=array('[',']','CONTENT') Begin and end template indeficators
     *  'container' string (default='[CONTENT]') Set default container
     *  'index' string (default='none.xml') Set index to parse
     *  'output' string (default=false) Start parse and echo result
     */
	function __construct($options = array() )
	{
		$language = 'en';
		$debugmode = false;
		$templateTag = array('[', ']', 'CONTENT');
		$filename = 'none.xml';
		$out = false;
		$this->debug = true;
		$container = "";
		if ($options && is_array($options) && count($options) > 0 )
		{
            /*
            * Fix lang option
            */
			if (isset($options['lang']) && is_string($options['lang']) && strlen($options['lang']) > 0 )
			{
				$language = $options['lang'];
				$this->log('+ Option "lang": ' . $language);
			}
            /*
            * Fix debug option
            */
			if (isset($options['debug']) && is_bool($options['debug']) )
			{
				$debugmode = $options['debug'];
				$this->log('+ Option "debug": ' . $debugmode);
			}
            
            /*
            * Fix templateTag option
            */
			if (isset($options['templateTag']) && is_array($options['templateTag']) && count($options['templateTag']) == 3 )
			{
				$templateTag[0] = $options['templateTag'][0];
				$templateTag[1] = $options['templateTag'][1];
				$templateTag[2] = $options['templateTag'][2];
				$this->log('+ Option "templateTag": ' . $templateTag[0] . ', '. $templateTag[1] . ', ' . $templateTag[2]);
			}
            
            /*
            *  Fix container option
            */
			if (isset($options['container']) && is_string($options['container']) )
			{
				$container = &$options['container'];
				$this->log('+ Option "container(custom)": '. strlen($container ) . ' bytes' );
            }
            
            /*
            *  Fix index option
            */
			if (isset($options['index']) && is_string($options['index']) )
			{
				$filename = $options['index'];
			}
            
            /*
            *  Fix otput option
            */
			if (isset($options['output']) && is_bool($options['output']) )
			{
				$out = $options['output'];
			}
		}

		$this->filename = $filename;
		$this->lang= $language;
		$this->debug= $debugmode;
		$this->defaultTempate = $templateTag;
		$this->container= ( is_string($container ) && strlen($container) ) ? $container : $templateTag[0] . $templateTag[2] . $templateTag[1];

		if ($out )
		{
			if (count($this->headers) > 0 )
			{
				foreach ($this->headers as $h )
				{
					header($h);
				}
			}

			ob_start();
			$o = $this->out(true);
			$c = ob_get_contents();
			ob_end_clean();
			echo $o.$c;
		}
	}

	private function findNode( XMLTag & $node, $nodename )
	{
		$x = ( !$node ) ? $this->xml : $node;

		if (!$x )
		{
			return null;
		}

		if ($node->name() == $nodename )
		{
			return $node;
		}

		$f = null;

		foreach ($node->children() as $n )
		{
			$f = $this->findNode($n, $nodename );
		}

		return $f;
	}

	private function parseOptions()
	{
		$node = $this->findNode($this->xml, 'options' );

		if (!$node )
		{
			return;
		}

		foreach ($node->children() as $item )
		{
			if ($item->name() != "option" )
			{
				continue;
			}

			$attr = $item->attr();
			$data = $item->data();

			if (!isset($attr['name'] ) )
			{
				$this->log('<b>Invalid option name:</b>' . $data );
				continue;
			}

			$name = strtolower($attr['name'] );

			switch ($name )
			{
				default:
                    break;

                case 'directory':
                case 'search dir':
                case 'files dir':
                case 'search_dir':
                case 'files_dir':
                case 'searchDir':
                case 'filesDir':
                    if (is_dir($data ) == false )
                        break;

                    array_push($this->searchDir, $data );
                    $this->log('Add search dir: '.$data);

                    break;
			}
		}
	}

	public function addZip($path )
	{
		$this->log('Value ZIP: ' . $path );
		$a = explode("#", $path );

		if (count($a ) < 2 )
		{
			return false;
		}

		list($file, $value ) = $a;
		$this->log('File: ' . $file, 1 );
		$this->log('Value:' . $value, 1 );
		$zip = null;

		if (isset($this->zips[$file] ) == false )
		{
			if (is_file($file) == false )
			{
				return false;
			}

			$this->log('File exist: ' . $file, 1 );
			$zip = new ZIPReader();

			if ($zip->open($file) == false )
			{
				$this->log('Fail when open: ' . $file, 1 );
				unset($zip);
				return false;
			}

			$this->log('File opened: ' . $file, 1 );
			$this->zips[ $file ] = &$zip;
		}
		else
		{
			$zip = &$this->zips[$file];
		}

		if (!$zip || !is_a($zip, 'ZIPReader') )
		{
			return false;
		}
		if ($value && is_string($value) )
		{
			if (!$zip->exist($value ) )
			{
				return false;
			}
			return $zip->read($value );
		}

		return false;
	}

	public function addJS(&$content )
	{
		if (!$content || !is_string($content) )
		{
			return;
		}

		array_push($this->inline_script, $content );
		$this->log('Inline javascript len: '. strlen($content) );
	}

	public function addJSFile($filename)
	{
		if (!$filename || !is_string($filename ) )
		{
			return "";
		}

		$content = $this->findindir($filename);

		if ($content )
		{
			array_push($this->include_script, $content);
		}
		else
		{
			$this->log('- Javascript file not found:'. $filename );
		}
		return "";
	}

	public function addVar($name, $value )
	{
		if (!$name || !is_string($name) || !strlen($value) )
		    return;

		if ( key_exists($name, $this->values) == false )
		    $this->log('Add variable "'.$name.'", size: '. strlen($value ) . ' = ' . $value );
		else
		    $this->log('Change variable "'.$name.'", size: '. strlen($value ) . ' = ' . $value );

		$this->values[ $name ] = $this->applyVars($value);
	}

	public function addCollection(XMLTag & $node )
	{
	}

	public function addLocale($language, $name, $value )
	{
	}

	public function addCSS(&$content )
	{
		if (!$content )
		{
			return;
		}

		array_push($this->inline_style, $content );
		$this->log('Inline style len: '. strlen($content));
	}

	public function addCSSFile($filename)
	{
		if (!$filename || !is_string($filename ) )
		{
			return "";
		}

		$content = $this->findindir($filename);

		if ($content )
		{
			array_push($this->include_style, $content);
		}
		else
		{
			$this->log(' - CSS File not found: "' . $filename . '"');
		}
	}

	public function addPHPFile($filename, $container = 0, $args = array() )
	{
		$file = $this->findindir($filename);

		if ($file )
		{
			$file = file_get_contents($file);

			if ($file )
			{
				$this->addPHP($file, $container, $args );
			}
		}

		return "";
	}

	public function addPHP($data, $container = null, $args = array() )
	{
		$ret = 0;

		if (!$data )
		{
			return $ret;
		}

		$filename = 0;
		$phptempname = "xmlpageclass_".date("U").'.php';
		$fd = fopen($phptempname, 'w');

		if ($fd )
		{
			$writen = fwrite($fd, $data);
			fclose($fd);

			if (!$writen )
			{
				$this->log('Failed to write temp PHP file' );

				if (!unlink($phptempname ) )
				{
					$this->log('Failed to unlink temp PHP file' );
				}

				return $ret;
			}

			if (isset($GLOBALS["xmlpageclass"]) )
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
				unset($GLOBALS[$tempname] );

				if ($ret && $container)
				{
					$this->addOut($container, $ret );
				}

				;
			}
			else
			{
				$GLOBALS["xmlpageclass"] = &$this;
				ob_start();
				$ret = include($phptempname);
				$ret = $ret == 1 ? "" : $ret;
				$ret .= ob_get_contents();
				ob_end_clean();
				unset($GLOBALS["xmlpageclass"] );

				if ($ret && $container)
				{
					$this->addOut($container, $ret );
				}
			}

			if (is_file($phptempname ) )
			{
				if (!unlink($phptempname ) )
				{
					$this->log('Failed to unlink temp PHP file' );
				}
			}
		}
		else
		{
			$this->log('Failed to create temp PHP file' );
		}

		$this->log('PHP add "'. $container .'": ' . strlen($ret ) . 'bytes' );
		return $ret;
	}

	public function addOut($name, $data )
	{
		if (!$name )
		{
		    $name = $this->defaultTempate[2];
		}//$this->log( 'Value "'. $name . '": '. strlen($data) );
		$name = strtolower($name );

		if (isset($this->outdata[ $name ] ) )
		{//echo 'Append data: '. $name .' - len: '. strlen( $this->outdata[ $name ] ) .'<br />';
			//echo 'Data is: ' . $this->outdata[ $name ] . '<br />';
			$this->outdata[ $name ] = $this->outdata[ $name ] . $data;
		}
		else
		{
			$this->outdata[ $name ] = $data;
		}
	}

	public function addXML(XMLTag & $node, $options = array() )
	{
		$options = array_merge($options, array('lang' => $this->lang, 'templateTag' => $this->defaultTempate, 'debug' => $this->debug, 'output' => false));
		$xml_ = new XMLPage($options);
		$xml_->xml= $node;
		$xml_->options= &$this->options;
		$xml_->searchDir= &$this->searchDir;
		$xml_->values= &$this->values;
		$xml_->templates= &$this->templates;
		$xml_->logstack= $this->logstack + 2;
		$ret = $xml_->out(false);
		$this->_log = array_merge($this->_log, $xml_->_log);
		$this->locale = array_merge($this->locale, $xml_->locale);
		$this->include_script= array_merge($this->include_script, $xml_->include_script);
		$this->include_style= array_merge($this->include_style, $xml_->include_style);
		$this->inline_script= array_merge($this->inline_script, $xml_->inline_script);
		$this->inline_style= array_merge($this->inline_style, $xml_->inline_style);
		$this->remote_script= array_merge($this->remote_script, $xml_->remote_script);
		$this->remote_style= array_merge($this->remote_style, $xml_->remote_style);
		$this->headers = array_merge($this->headers, $xml_->headers);
		$this->configs = array_merge($this->configs, $xml_->configs);
		$this->zips = array_merge($this->zips, $xml_->zips);
		return $ret;
	}

	private function applyStyle( &$output )
	{
		$includestyle = '';

		foreach ($this->include_style as $style )
		{
			$includestyle .= $this->apply("\n<link rel=\"stylesheet\" type=\"text/css\" href=\"[STYLE]\" />", array('style' => $style ), array('[', ']'), false );
		}

		$remotestyle = '';

		foreach ($this->remote_style as $style )
		{
			$remotestyle .= $this->apply("\n<link rel=\"stylesheet\" type=\"text/css\" href=\"[STYLE]\" />", array('style' => $style ), array('[', ']'), false );
		}

		$inlinestyle = '';

		if (count($this->inline_style ) > 0 )
		{
			$inlinestyle = "\n<style type=\"text/css\">";

			foreach ($this->inline_style as $script )
			{
				$inlinestyle .= $script . "\n";
			}

			$inlinestyle .= "</style>";
		}/*$output = $this->apply(
$output,
array(
'page:include_style'	=> $includestyle.$remotestyle,
'page:inline_style'		=> $inlinestyle
)
);*/
		$o = str_ireplace("</head", $remotestyle.$includestyle.$inlinestyle."</head", $output );
		$this->include_style= array();
		$this->remote_style= array();
		$this->inline_style= array();
		return $o;
	}

	private function applyScript(&$output )
	{
		$includescript = '';

		foreach ($this->include_script as $script )
		{
			$includescript .= "\n<script type=\"text/javascript\" src=\"{$script}\"></script>";
		}

		$remotescript = '';

		foreach ($this->remote_script as $script )
		{
			$remotescript .= "\n<script type=\"text/javascript\" src=\"{$script}\"></script>";
		}

		$inlinescript = '';

		if (count($this->inline_script ) > 0 )
		{
			$inlinescript = "\n<script type=\"text/javascript\">\n/************** INLINE SCRIPTS **************/\n";

			foreach ($this->inline_script as $script )
			{
				$inlinescript .= $script . "\n";
			}

			$inlinescript .= "</script>";
		}/*$output = $this->apply(
$output,
array(
'page:include_script'	=> $includescript.$remotescript,
'page:inline_script'	=> $inlinescript
)
);*/
		$o = str_ireplace("</body", $remotescript.$includescript.$inlinescript."</body", $output);
		$this->include_script= array();
		$this->remote_script= array();
		$this->inline_script= array();
		return $o;
	}

    /**
     * function out - Return class product as string
     * @param bool $_apply_styles Glue scripts and styles at output
     * @return string
     */
	public function out($_apply_styles = false )
	{
		if ($this->filename && is_file($this->filename) && is_null($this->xml ) )
		{
			$this->log('Parse file: "'. $this->filename .'"');
			$xmldoc= new XMLParser($this->filename , true );
			$this->xml = $xmldoc->Parse();
		}

		if (!$this->xml )
		{
			return 'Content not found.';
		}

		$this->parseOptions();
		$this->parseRecursive($this->xml );
		$output = $this->container;
		$output = $this->apply($output, $this->outdata);

		if ($_apply_styles == true )
		{
			$output = $this->applyStyle($output );
			$output = $this->applyScript($output );
		}

		$output = $this->apply($output, array('lang'=> $this->lang ));
		$output = $this->applyVars($output);
		$output = $this->applyLocale($output);
		$output = $this->applyGlobals($output);
		return $output;
	}
    
    private function makeBrace( $key, array $tags = array() )
    {
        $ob =& $this->defaultTempate[0];
        $cb =& $this->defaultTempate[1];

        if ($tags && is_array($tags) && count($tags) == 2 )
		{
			unset($ob,$cb);
			$ob = &$tags[0];
			$cb = &$tags[1];
		}
        return $ob . $key . $cb;
    }
    
    /*
    * function apply
    * args
    *	$temp(string) - String of template
    *	$data(array) - Assocciated array with data for apply to template
    * return
    *	String - Applied data
    */
	public function apply($output, $data, array $tags = array(), $case = false )
	{
		if (!$data )
		{
			return $output;
		}

		$text = &$output;

		foreach ($data as $key => $value)
		{
			if (is_array($value) )
			{
				continue;
			}

			//$KEY= $tag_begin . $key . $tag_end;
			$text = str_ireplace( $this->makeBrace($key,$tags), $value, $text);//$text	= str_replace( strtoupper($KEY), $value, $text);
		}

		return $text;
	}

	private function applyVars(&$out, $case = false )
	{
		if ( !$out )
            return;

		$tag_begin= &$this->defaultTempate[0];
		$tag_end= &$this->defaultTempate[1];
        
		return $this->apply($out, $this->values, array($tag_begin .'VAR:', $tag_end ), $case );
	}

	private function applyLocale(&$out, $case = false )
	{
		if ( !$out )
            return;		

        $tag_begin= &$this->defaultTempate[0];
		$tag_end= &$this->defaultTempate[1];
		
		$result = $this->apply($out, $this->locale, array($tag_begin .'LOCALE:', $tag_end ), $case );
		$result = $this->apply($result, $this->locale, array($tag_begin .'L:', $tag_end ), $case );
        return $this->apply($result, $this->locale, array($tag_begin, $tag_end ), $case );
	}

	private function applyGlobals(&$out, $case = false )
	{
		if (!$out )
		{
			return "";
		}

		$tag_begin= &$this->defaultTempate[0];
		$tag_end= &$this->defaultTempate[1];

		if (isset($_GET) )
		{
			$output = $this->apply($out, $_GET, array($tag_begin.'GET:', $tag_end ), $case );
		}

		if (isset($_POST) )
		{
			$output = $this->apply($output, $_POST, array($tag_begin.'POST:', $tag_end ), $case );
		}

		if (isset($_REQUEST) )
		{
			$output = $this->apply($output, $_REQUEST, array($tag_begin.'REQUEST:', $tag_end), $case );
		}

		if (isset($_SESSION) )
		{
			$output = $this->apply($output, $_SESSION, array($tag_begin.'SESSION:', $tag_end), $case );
		}

		if (isset($_SERVER) )
		{
			$output = $this->apply($output, $_SERVER, array($tag_begin.'SERVER:', $tag_end), $case );
		}

		if (isset($_COOKIE) )
		{
			$output = $this->apply($output, $_COOKIE, array($tag_begin.'COOKIE:', $tag_end), $case );
			$output = $this->apply($output, $_COOKIE, array($tag_begin.'COOKIES:', $tag_end), $case );
		}

		if( isset($this->values) ) $output = $this->applyVars($output);

		return $output;
	}

    /**
     * function log_print - Print log to string
     * @param string $delimeter Delimener between log lines
     * @return string Output log
     */
	public function log_print($delimeter = '<br />')
	{
		$del = "\n";

		if ($delimeter && is_string($delimeter) && strlen($delimeter) )
		{
			$del = $delimeter;
		}

		$output = "======== Log ====================" . $del;

		foreach ($this->_log as $val )
		{//list($usec, $sec) = explode( " ", microtime() );
			$output .= $val . $del;
		}

		return $output;
	}

	private function pushContent()
	{
		$o = array('container' => $this->container, 'content' => $this->outdata );
		array_push($this->content_stack, $o );
		$this->container = $this->defaultTempate[0] . $this->defaultTempate[2] . $this->defaultTempate[1];
		$this->outdata = array();
		$this->logstack++;
	}

	private function getContent($container = '' )
	{
		if (!$container || !is_string($container ) || strlen($container) < 1 )
		{
			$container = $this->container;
		}
        ////$container = $this->options['templateTagBegin'] . $this->options['templateContainer'] . $this->options['templateTagEnd'];
		return $this->apply($container, $this->outdata );
	}

	private function popContent()
	{
		$o = array_pop($this->content_stack );

		if (!$o )
		{
			throw new Exception('Content is not array');
			return;
		}

		if (isset($o['container'] ) == false )
		{
			throw new Exception('Container not in array');
			return;
		}

		if (isset($o['content'] ) == false )
		{
			throw new Exception('Content not in array');
			return;
		}

		$this->container = $o['container'];
		$this->outdata = $o['content'];
		$this->logstack--;
	}
}
?>
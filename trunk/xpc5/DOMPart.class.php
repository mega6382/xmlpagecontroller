<?php

/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/

defined( 'XPC_CLASSES' ) or die('defined');

/**
 *
 */
class eParser_part_recursive extends DOMElementParser
{
    protected function parse()
    {
        parent::doInside();
    }
}

/**
 *
 */
class eParser_part_container extends DOMElementParser
{
    protected function parse()
    {
        parent::parser()->setContainer( parent::value());
    }
}

/**
 *
 */
class eParser_part_option extends DOMElementParser
{
    protected function parse()
    {
        parent::parser()->setOption( parent::attr('name'), parent::value() );
    }
}

/**
 *
 */
class eParser_part_var extends DOMElementParser
{
    protected function parse()
    {
        parent::parser()->setVar( parent::attr('name'), parent::value() );
    }
}

class eParser_part_template extends DOMElementParser
{
    protected function parse()
    {
        parent::parser()->setTemplate( parent::attr('name'), parent::value() );
    }
}

class eParser_part_templates extends DOMElementParser
{
    protected function parse()
    {
        foreach( $this->child() as $c )
        {
            if( $c->hasAttribute('name') )
            {
                parent::parser()->setOption($c->getAttribute('name'),$c->textContent);
                return;
            }

            if( $c->hasAttribute('name') )
            {
                parent::parser()->setOption($c->getAttribute('name'),$c->textContent);
                return;
            }
        }
    }
}

/**
 * Parser for echo, out, value tags see XPC spec
 */
class eParser_part_echo extends DOMElementParser
{
    protected function parse()
    {
        $templateName = $this->attr('template');
        if( $templateName )
        {
            $template =& parent::parser()->m_templates[ $templateName ];
            
            if( !$template )
                return;

            parent::parser()->push();
            parent::doInside();
            list($container,$echos) = parent::parser()->pop();
            $res = Template::_doAssign($template, $echos, array('${','}'), false );
            parent::parser()->addEcho( parent::attr('name'), $res );
        }
        else
        {
            parent::parser()->addEcho( parent::attr('name'), parent::value() );
        }
    }
}

class eParser_part_if extends DOMElementParser
{
    protected function parse()
    {
        $if_array = array();

        switch( parent::name() )
        {
            case 'if':
            case 'if-get':
                $if_array =& $_GET;
                break;
            case 'if-post':
                $if_array =& $_POST;
                break;
            case 'if-cookie':
            case 'if-cookies':
                $if_array =& $_COOKIE;
                break;
            case 'if-request':
                $if_array =& $_REQUEST;
                break;
            case 'if-server':
                $if_array =& $_SERVER;
                break;
            case 'if-env':
                $if_array =& $_ENV;
                break;
            case 'if-session':
                if( isset( $_SESSION ) ) $if_array =& $_SESSION;
                break;
            case 'if-global':
            case 'if-globals':
                $if_array =& $GLOBALS;
                break;
            case 'if-file':
            case 'if-files':
                $if_array =& $GLOBALS;
                break;
            case 'if-var':
            case 'if-vars':
                $if_array =& parent::parser()->m_vars;
                break;
            case 'if-option':
            case 'if-options':
                $if_array =& parent::parser()->m_options;
                break;
        }


        if( is_array($if_array) == false ) return;

        foreach( parent::attrs() as $key => $value )
        {
            //echo 'Attr key '.$key.': '.$value . '<br />';
            switch( $key )
            {
                case 'has':
                case 'isset':
                case 'is_set':
                    if( isset( $if_array[ $value ] ) )
                        parent::doInside();
                    return;

                case 'noset':
                case 'no_set':
                case 'notset':
                case 'not_set':
                    if( !isset( $if_array[ $value ] ) )
                        parent::doInside();
                    return;

                case 'cond':
                case 'condition':
                case 'param':
                    list( $var1, $cond, $var2 ) = explode(' ', $value);
                    //echo 'Param: ' . $var1 . ' ' . $cond . ' ' . $var2;
                    $if_val =& $if_array[$var1];
                    $if_res = false;

                    switch( $cond )
                    {
                        case '=':
                        case '==':
                            $if_res = ($if_val == $var2 );
                            break;

                        case '!=':
                            $if_res = ($if_val != $var2 );
                            break;

                        case '>':
                            $if_res = ($if_val > $var2 );
                            break;

                        case '>=':
                            $if_res = ($if_val >= $var2 );
                            break;

                        case '<':
                            $if_res = ($if_val < $var2 );
                            break;

                        case '<=':
                            $if_res = ($if_val <= $var2 );
                            break;
                    }

                    if( $if_res )
                        parent::doInside();

                    return;
            }
        }
    }
}

class eParser_part_switch extends DOMElementParser
{
    protected function parse()
    {
        $array = array();

        switch( parent::name() )
        {
            case 'switch':
            case 'switch-get':
                $array =& $_GET;
                break;
            case 'switch-post':
                $array =& $_POST;
                break;
            case 'switch-cookie':
            case 'switch-cookies':
                $array =& $_COOKIE;
                break;
            case 'switch-request':
                $array =& $_REQUEST;
                break;
            case 'switch-server':
                $array =& $_SERVER;
                break;
            case 'switch-env':
                $array =& $_ENV;
                break;
            case 'switch-session':
                if( isset( $_SESSION ) ) $array =& $_SESSION;
                break;
            case 'switch-global':
            case 'switch-globals':
                $array =& $GLOBALS;
                break;
            case 'switch-file':
            case 'switch-files':
                $array =& $GLOBALS;
                break;
            case 'switch-var':
            case 'switch-vars':
                $array =& parent::parser()->m_vars;
                break;
            case 'switch-option':
            case 'switch-options':
                $if_array =& parent::parser()->m_options;
                break;
        }

        if( is_array($array) == false )
            return;

        $array_key = parent::attr('key');

        if( !$array_key )
            $array_key = parent::attr('name');

        if( !$array_key )
            return;

        if( key_exists($array_key, $array) == false )
            return;

        $array_val =& $array[ $array_key ];

        $default    = null;
        $node       = null;
        foreach( parent::child() as $c )
        {
            $node_name = $c->nodeName;

            if( $c->hasAttribute('default') )
            {
                $default =& $c;
            }

            if( $node_name == 'case' )
            {
                $node_val = $c->getAttribute('value');
                
                if( empty($node_val) )
                    $node_val = $c->getAttribute('name');

                if( empty($node_val) )
                    continue;

                if( $node_val == $array_val )
                {
                    $node =& $c;
                    break;
                }
                continue;
            }

            if( $node_name == 'default' )
            {
                $default =& $c;
                continue;
            }

            if( $node_name == $array_val )
            {
                $node =& $c;
                break;
            }
        }

        if( $node )
        {
            foreach( $node->childNodes as $c )
            {
                if( $c->nodeType != XML_ELEMENT_NODE )
                    continue;

                $t_node     = $this->node();
                $t_parser   = $this->parser();

                parent::parser()->parseElement( $c );

                $this->setNode($t_node);
                $this->setParser($t_parser);
                
            }
            return;
        }

        if( $default )
        {
            foreach( $default->childNodes as $c )
            {
                if( $c->nodeType != XML_ELEMENT_NODE )
                    continue;

                $t_node     = $this->node();
                $t_parser   = $this->parser();

                parent::parser()->parseElement( $c );

                $this->setNode($t_node);
                $this->setParser($t_parser);
            }
            return;
        }
    }
}

/**
 * Parser for echo, out, value tags see XPC spec
 */
class eParser_part_default extends DOMElementParser
{
    protected function parse()
    {
        switch( parent::name() )
        {
            default:
           //     echo 'Default parse: ' . parent::name() . '<br />';
                break;
        }
        
    }
}

class dParser_part_default extends DOMElementDataParser
{
    protected function parse()
    {
        return parent::node()->textContent;
    }
}

class dParser_part_file extends DOMElementDataParser
{
    protected function parse()
    {
        $filename = parent::node()->textContent;
        if( empty($filename) )
            return;

        if( !file_exists($filename) )
            return;

        return file_get_contents($filename);
    }
}

class dParser_part_php extends DOMElementDataParser
{
    protected function parse()
    {
        $filename = parent::node()->textContent;
        if( empty($filename) )
            return;

        if( !file_exists($filename) )
            return;

        ob_start();
        $return = include($filename);
        $content = ob_get_contents();
        ob_end_clean();
        
        if( $return != 1 )
            $content = $return . $content;

        return $content;
    }
}

class dParser_part_xml extends DOMElementDataParser
{
    protected function parse()
    {
        $filename = parent::node()->textContent;
        if( empty($filename) )
            return;

        if( !file_exists($filename) )
            return;

        parent::parser()->push();
        parent::parser()->fromFile( $filename );
        $content = parent::parser()->getContent();
        parent::parser()->pop();
        return $content;

        /*$c = get_class( parent::parser() );
        $p = new $c();
        if( $p->fromFile($filename) ) return $p->getContent();*/
    }
}

/**
 * Parse XML document by XPC rules
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */
class DOMPart extends DOMParser
{
    /**
     * Contain container
     *
     * @var string
     */
    public $m_container;

    /**
     * Contain parser options
     *
     * @var array
     */
    public $m_options;

    /**
     * Contain echo values
     *
     * @var array
     */
    public $m_echo;

    /**
     * Temporary contain echo values
     * @var array
     */
    public $m_echo_context;

    /**
     * Contain templates
     *
     * @var array
     */
    public $m_templates;

    /**
     * Contain variables
     * @var array
     */
    public $m_vars;

    /**
     * Flags array
     * @var array
     */
    public $m_flags;

    /**
     * Set container
     *
     * @param strung $a_container
     */
    public function setContainer( $a_container )
    {
        $this->m_container = $a_container;
    }

    
    /**
     * Set option if is exist
     *
     * @param string $a_name
     * @param string $a_value
     * @return null
     */
    public function setOption( $a_name, $a_value )
    {
        if( empty($a_name) )
            return;

        $this->m_options[ $a_name ] = $a_value;
        //echo 'set option ' . $a_name . ': ' . $a_value . '<br />';
        return true;
    }

    public function setVar( $a_name, $a_value )
    {
        if( empty($a_name) )
            return;

        $this->m_vars[ $a_name ] = $a_value;
        //echo 'set var ' . $a_name . ': ' . $a_value . '<br />';
        return true;
    }

    public function setTemplate( $a_name, $a_value )
    {
        if( empty($a_name) )
            return;

        $this->m_templates[ $a_name ] = $a_value;
        //echo 'set template ' . $a_name . ': ' . $a_value . '<br />';
        return true;
    }

    /**
     * Add echo value by $a_name if is empty $a_name set to default
     * @param string $a_name
     * @param string $a_value
     */
    public function addEcho( $a_name, $a_value )
    {
        if( empty($a_name) )
        {
            $a_name = $this->m_options['default_name'];
        }

        if( key_exists($a_name, $this->m_echo) )
        {
            $this->m_echo[ $a_name ] .= (string)$a_value;
        }
        else
        {
            $this->m_echo[ $a_name ] = (string)$a_value;
        }
        //echo 'Add echo ' . $a_name . ': ' . $a_value . '<br >';
    }

    /**
     * Push container and echo
     */
    public function push()
    {
        array_push( $this->m_echo_context, array( $this->m_container, $this->m_echo ) );
        list( $this->m_container, $this->m_echo ) = array( array(),array() );
    }

    /**
     * Pop container and echo
     */
    public function pop()
    {
        $a = array( $this->m_container, $this->m_echo );
        $c = array_pop( $this->m_echo_context );
        if( $c )
        {
            list( $this->m_container, $this->m_echo ) = $c;
            return $a;
        }
        return array( array(), array() );
    }

    /**
     * Construct class and registet default parsers
     */
    public function __construct()
    {
        parent::__construct();
        parent::registerParsers( array(
            'default_parser'    => new eParser_part_default(),
            'root pages'        => new eParser_part_recursive(),
            'option'            => new eParser_part_option(),
            'var variable'      => new eParser_part_var(),
            'template'          => new eParser_part_template(),
            'out echo value'    => new eParser_part_echo(),
            'container'         => new eParser_part_container(),
            'if if-get if-post if-cookie if-cookies if-request if-session if-global if-globals if-file if-files if-server if-env if-var if-vars if-option if-options' => new eParser_part_if(),
            'switch switch-get switch-post switch-cookie switch-cookies switch-request switch-session switch-global switch-globals switch-file switch-files switch-server switch-env switch-var switch-vars switch-option switch-options' => new eParser_part_switch()
        ));

        parent::registerDataTypes( array(
            'default_parser inline'    => new dParser_part_default(),
            'file'              => new dParser_part_file(),
            'php'               => new dParser_part_php(),
            'xml'               => new dParser_part_xml()
        ));

        $this->m_options        = array(
            'default_name' => 'content',
            'default_brace' => array('${','}')
        );
        
        $this->m_echo           = array();
        $this->m_templates      = array();
        $this->m_vars           = array();
        $this->m_echo_context   = array();
        $this->m_flags          = array();
        $this->m_container      = '${CONTENT}';
    }

    public function getContent()
    {
        return Template::_doAssign($this->m_container, $this->m_echo, $this->m_options['default_brace'], false);
    }
}

?>

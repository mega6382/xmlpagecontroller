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
 * Base class for simple object-oriented interface for working with collections
 */
abstract class DataCollection
{
    /**
     * Hold key/value data
     */
    private $m_data = array();

    /**
     * Return true if data with
     * key $a_key is exist in $m_data
     *
     * @param string $a_key
     * @return bool
     */
    public function has( $a_key )
    { return key_exists($a_key, $this->m_data); }

    /**
     * Return data by $a_key
     *
     * @param string $a_key
     * @return mixed
     */
    public function get( $a_key )
    { return $this->has($a_key) ? $this->m_data[$a_key] : null; }

    /**
     * Set $a_val data by key $a_key
     *
     * @param string $a_key
     * @return mixed
     */
    public function set( $a_key, $a_val )
    { $this->m_data[$a_key] = $a_val; }

    /**
     * Delete value by key $a_key
     *
     * @param string $a_key
     */
    public function del( $a_key )
    { unset( $this->m_data[$a_key] ); }
}


class tagRecursiveParser extends DOMElementParser { function parse(){ parent::doInside(); } }
class tagOptionParser extends DOMElementParser
{
    function parse()
    {
        $this->parser()->addOption( $this->attr('name'), $node->data() );
    }
}

class tagOptionsParser extends DOMElementParser
{
    function parse()
    {
        foreach( $this->node()->childNodes as $n )
        {
            if( $n->nodeType != XML_ELEMENT_NODE ) continue;
            $this->parser()->addOption( $n->nodeName, $n->nodeValue );
        }
            
    }
}

class tagMetasParser extends DOMElementParser
{
    function parse()
    {
        foreach( $this->node()->childNodes as $n )
        {
            if( $n->nodeType != XML_ELEMENT_NODE ) continue;
            $this->parser()->addMeta( $n->nodeName, $n->nodeValue );
        }
    }
}

class tagMetaParser extends DOMElementParser
{
    function parse()
    {
        $this->parser()->addMeta( $this->attr('name'), $this->data() );
    }
}

class tagPageParser extends DOMElementParser
{
    var $stack = array();
    function parse()
    {
        $id = $this->attr('id');
        if( !$id )
            return;

        array_push($this->stack, $id );
        $path = implode('/', $this->stack );
        parent::doInside();
        array_pop($this->stack);

        $this->parser()->addPage( $path, $this->node() );
    }
}

class PageElement
{
    public $id;

    public $path;

    public $file;

    public $node;

    public $lang;

    public $role;

    public $link;

    public $type;

    public $alias;

    public $title;

    public $result;

    public $fullpath;
}

/**
 * DOMSite class (php5)
 *
 * This class presente XML tag as php object
 *
 * @author Zombie! <roguevoo@gmail.com>
 * @filesource DOMSimpler.php
 *
 */
class DOMSite extends DOMParser
{
    private $m_options = array(
        'index' => 'index.xml',
        'lang' => 'en',
        'doctype' => 'traditional',
        'base' => '',
        'output' => false,
        'debug' => false,
        'keyname' => 'q',
        'keydelimeter' =>'/',
        'gluescripts' => false,
        'gluestyles' => false,
        'log_space' => '&nbsp;&nbsp;',
        'log_page' => false,
        'log_show' => false,
        'id_page_index'     => 'page_index',
        'id_page_not_found' => 'page_not_found',
        'id_page_redirect'  => 'page_redirect',
        'id_page_login'     => 'page_login',
        'id_page_no_access' => 'page_no_access',
        'page_access'   => 'public'
    );

    private $m_meta = array(
        'generator'	=> 'XML Page Controller'
    );

    private $m_rprovider = null;

    private $m_log = array();

    private $m_pages = array();

    private $m_locales = array();

    private $m_append_body = array();

    private $m_template = null;

    private $m_pagedefaults = array('title'=>'Page title', 'encoding'=>'utf-8' );

    private $m_log_stacksize = 0;

    private $m_scripts = array('inline' => array(), 'include' => array() );

    private $m_styles = array('inline' => array(), 'include' => array() );

    private $m_dom_main = null;
    private $m_dom_body = null;
    private $m_dom_content = null;

    public function __construct(array $options )
    {
        $this->registerParsers( array(
            'root config' => new tagRecursiveParser(),
            'page' => new tagPageParser(),
            'options' => new tagOptionsParser(),
            'option' => new tagOptionParser(),
            'metas' => new tagMetasParser(),
            'meta' => new tagMetaParser()
        ));

        $this->m_rprovider = new RoleProvider();

        $this->m_options = array_merge($this->m_options, $options );//print_r( $this->m_options );
        if ($this->m_options['output'] == true )
        {
            echo $this->out();
        }


    }

    public function addOption( $key, $value )
    {
        if( empty($key) )
            return;

        $this->log('Option "'.$key.'": '.$value);
        $this->m_options[ $key ] = $value;
    }

    public function addMeta( $key, $value )
    {
        if( !$key || !is_string($key) || !strlen($key) )
            return;

        if( !$value || !is_string($value) || !strlen($value) )
            return;

        $this->m_meta[ $key ] = $value;
    }

    public function addPage( $path, DOMNode & $node )
    {
        if( empty($path ) )
            return;

        if( !$node )
            return;

        $this->m_pages[$path] = new PageElement();
        
        $p              =& $this->m_pages[$path];
        $p->id          = $node->getAttribute('id');
        $p->path        = $path;
        $p->lang        = $this->m_options['lang'];
        $p->file        = str_replace('/', '-', $path).'.xml';
        $p->role        = $node->getAttribute('role');
        $p->node        = $node;
        $p->type        = 'page';
        $p->title       = $node->getAttribute('title');
        $p->fullpath    = $this->m_options['base'] . $p->file;
        $p->alias       = $node->getAttribute('alias');

        $custom_path    = $node->getAttribute('path');
        if( !empty($custom_path) )
        {
            $p->fullpath = $custom_path;
        }
        
        $this->log('Register Page "'. $p->path . '"');
    }

    public function delPage( $name )
    {
        if( key_exists($name,$this->m_pages) )
            unset( $this->m_pages[$name] );
    }
    
    private function mf_make_page( $filepath )
    {
        $this->log("Make page" );

        if ( empty( $filepath ) )
        {
            $this->log("Bad page path", 1 );
            return null;
        }

        $this->log("Page path: ". $filepath, 1 );

        $p = new HTMLPart();
        if( $p->fromFile($filepath) ) return $p;
    }

    private function pf_copy_attachments( HTMLPart & $page )
    {
        if ( !$page )
        {
            return;
        }

        foreach ($page->m_style_remote as $s )
        {
            array_push($this->m_styles['include'], $s );
        }

        foreach ($page->m_script_remote as $s )
        {
            array_push($this->m_scripts['include'], $s );
        }

        if ($this->m_options['gluestyles'] )
        {

            foreach ($page->m_style_include as $s )
            {
                $f = file_get_contents($s);

                if ($f && strlen($f) )
                {
                    array_push($this->m_styles['inline'], "\n/***** include  style *****/\n" . $f );
                }
            }
        }
        else
        {

            foreach ($page->m_style_include as $s )
            {
                array_push($this->m_styles['include'], $s );
            }
        }

        if ($this->m_options['gluescripts'] )
        {

            foreach ($page->m_script_include as $s )
            {
                $f = file_get_contents($s);

                if ($f && strlen($f) )
                {
                    array_push($this->m_scripts['inline'], "\n/***** include script *****/\n" . $f );
                }
            }
        }
        else
        {

            foreach ($page->m_script_include as $s )
            {
                array_push($this->m_scripts['include'], $s );
            }
        }

        foreach ($page->m_script_inline as $s )
        {
            array_push($this->m_scripts['inline'], "\n/***** inline script *****/\n" . $s );
        }

        foreach ($page->m_style_inline as $s )
        {
            array_push($this->m_styles['inline'], "\n/***** inline style *****/\n" . $s );
        }
        
/*        if( count($page->locale) )
        {
            $this->m_locales = array_merge( $this->m_locales, $page->locale );
        }
*/
        if( $this->m_options['log_page'] )
        {
            $this->m_log = array_merge( $this->m_log, $page->_log );
        } 
    }
    
    private function _selectPageByFile( $file )
    {
        foreach( $this->m_pages as $p )
        {
            if( $p->file == $file )
                return $this->m_pages[ $p->path ];
        }
    }
    
    private function _selectPageByFullpath( $path )
    {
        foreach( $this->m_pages as $p )
        {
            if( $p->fullpath == $path )
                return $this->m_pages[ $p->path ];
        }
    }

    private function _selectPageByTitle( $title )
    {
        foreach( $this->m_pages as $p )
        {
            if( $p->title == $title )
                return $this->m_pages[ $p->path ];
        }
    }

    private function _selectPageById( $id )
    {
        foreach( $this->m_pages as $p )
        {
            if( $p->id == $id )
                return $this->m_pages[ $p->path ];
        }
    }

    private function _selectPageByPath( $path )
    {
        if( empty ( $path ) )
        {
            if( key_exists($this->m_options['id_page_index'], $this->m_pages) )
                return $this->m_pages[ $this->m_options['id_page_index'] ];
            else
                return $this->m_pages[ $this->m_options['id_page_not_found'] ];
        }
        return ( key_exists($path, $this->m_pages) ) ? $this->m_pages[$path] : null;
    }

    public function appendToBody( $content )
    {
        array_push( $this->m_append_body, $content );
    }

    public function out()
    {
        $log_out = $this->m_options['log_show'];

        /***
         * Parse index
         */
        $indexfile = $this->m_options['index'];

     
        if( !$this->fromFile($indexfile) )
        {
            $this->log('Parse index failed: ' . $indexfile);
            if( $log_out ) echo $this->log_print();
            return;
        }
        $this->log('Parse index: "'.$this->m_options['index'].'"' );

        /*
         * Role class
         */
        if( isset($this->m_options['roleclass']) )
        {
            $rclass =& $this->m_options['roleclass'];
            $this->log('Role class name: "'.$this->m_options['roleclass'].'"' );
            if( class_exists($rclass) )
            {
                $true = false;
                foreach ( class_parents($rclass) as $class )
                {
                    if( $class == 'Role' )
                    {
                        $true = true;
                        break;
                    }
                }

                if ($true)
                {
                    $rc = new $rclass();
                    $this->m_rprovider->registerRole($rc);
                    $this->m_rprovider->doLogin();
                }
                $this->log('Role class not inherits from Role');
                
            }else $this->log('Role class not defined');
        }
        else
        {
            $this->log('Role class not found');
        }

        /**
         * Select page
         */
        $kname = &$this->m_options['keyname'];

        if ( !$kname )
        {
            $this->log('Invalid Keyname');
            if( $log_out ) echo $this->log_print();
            return;
        }
        $req_page = isset( $_GET[$kname] ) ? $_GET[$kname] : null;


        $this->log( 'Request page: ' . $req_page );
        
        $page = $this->_selectPageByPath( $req_page );
        if ( !$page )
        {
            $this->log('Page "' . $req_page . '" not found.',  1);
            $page = $this->_selectPageById( $this->m_options['id_page_not_found'] );
            
            if( !$page )
            {
                $this->log('Page "' . $this->m_options['id_page_not_found'] . '" not found.',  2);
                if( $log_out ) echo $this->log_print();
                return;
            }
        }
        else
        {
            if( $page->alias )
            {
                $a = $page->alias;
                unset( $page );
                $page = $this->_selectPageByPath( $a );
                if( !$page )
                {
                    $this->log('Page alias "' . $page->alias . '" not found.',  2);
                    if( $log_out ) echo $this->log_print();
                    return;
                }
            }

            if( !empty($page->role) )
            {
                $role =& $this->m_rprovider;


                if( $role->isLogged() )
                {
                    echo 'Is logedin';
                    if( $role->hasAccess( $page->role ) )
                    {

                    }
                    else
                    {
                        unset( $page );
                        $page = $this->_selectPageById( $this->m_options['id_page_no_access'] );
                    }
                }
                else
                {
                    $this->m_rprovider->doLogin();
                    if( $role->isLogged() )
                    {
                        echo 'Is success';
                    }
                    else
                        echo 'Is failes';

                    unset( $page );
                    $page = $this->_selectPageById( $this->m_options['id_page_login'] );
                    
                }
            }
        }

        /**
         * Make template
         */
        $body = '${CONTENT}';
        if( key_exists('template', $this->m_options) )
        {
            $cont_ = $this->mf_make_page( $this->m_options['base'] . $this->m_options['template'] );
            if ( $cont_ )
            {
                $this->log( 'Container: ' . $this->m_options['template'] );

                $body = $cont_->getContent();
                $this->pf_copy_attachments($cont_);
            }
            else
            {
                $this->log("Failed to load template page " . $this->m_options['template'] );
                if( $log_out ) echo $this->log_print();
                return;
            }
        }

        //echo $body;

        /**
         * Make page
         */
        $page_ = $this->mf_make_page( $page->fullpath );

        if ($page_ )
        {
            $page_->getContent();
            $this->pf_copy_attachments($page_);

            $arr = array();

            foreach ( $page_->m_echo as $key => $val )
            {
                $k = '${' . $key . '}';
                $this->log( 'Try apply: ' . $k );
                $body = str_ireplace($k, $val, $body);
            }
        }
        else
        {
            $this->log('No make page for output');
            if( $log_out ) echo $this->log_print();
            return;
        }

        $doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
        $headers = "";

        $head = null;

        $this->m_dom_body = new DOMDocumentSimpler('1.0', 'UTF-8');

        $html =& $this->m_dom_body;
        $html->registerNodeClass('DOMElement','DOMElementSimpler');
        $html->registerNodeClass('DOMDocument','DOMDocumentSimpler');

        if( !$html->loadXML( '<html><head /><body><!--[BODY_PLACE]--></body></html>') )
        {
            $this->log("Failed to load case page" );
            if( $log_out ) $this->log_print();
            return;
        }
        
        $html->setTitle('Hello title');

        $head = $html->head();

        $contentType = $html->createElement('meta');
        $contentType->attr('http-equiv','Content-Type');
        $contentType->attr('content','text/html; charset=utf-8');
        $head->appendChild( $contentType );

        foreach ($this->m_meta as $key => $val )
        {
            $html->addMeta($key,$val);
        }
        
        $html->setBase('http://' . $_SERVER['HTTP_HOST'] . dirname( $_SERVER["SCRIPT_NAME"] ) . '/');

        $inlinescript = "";
        $inlinestyle = "";

        foreach ($this->m_scripts['inline'] as $s )
        {
            $inlinescript .= $s . "\n";
        }

        foreach ($this->m_styles['inline'] as $s )
        {
            $inlinestyle .= $s . "\n";
        }

        foreach ($this->m_styles['include'] as $s )
        {
            if( $html->addStyleFile($s) == false )
                $this->log('Failed to append style: ' . $s);
        }

        if( strlen($inlinestyle) )
        {
            if( $html->addStyle($inlinestyle) == false )
                $this->log('Failed to append inline style');
        }

        foreach( $this->m_scripts['include'] as $s )
        {
            $html->addScriptFile($s);
        }

        if ( strlen( $inlinescript ) )
        {
            $html->addScript( $inlinescript );
        }

        foreach( $this->m_append_body as $content )
        {
            $contentNode = $html->createTextNode($content);
            if( $contentNode ) $html->body()->appendChild( $contentNode );
        }

        if( $log_out )
        {
            $body .= $this->log_print();
        }

        //$ra = array( 'body_place' => &$body );

        /*$mutator = new DOMMutator();

        if( !$mutator->fromDOM($html, $ra) )
        {
             $this->log('Mutation failed');
            if( $log_out ) echo $this->log_print();
            return;
        }*/

        $doc = str_replace('<!--[BODY_PLACE]-->', $body, $html->saveHTML() );

        $links_arr = array();
        foreach( $this->m_pages as $k => $v )
        {
            $sname = dirname( $_SERVER["SCRIPT_NAME"] ) . '/';
            $links_arr[ $k ] = 'http://' . $_SERVER['HTTP_HOST'] . $sname . $k;
        }

        $doc = Template::_doAssign( $doc, $links_arr, array('${link:',"}"), false );
        $doc = Template::_doAssign( $doc, $this->m_locales, array('${l:',"}"), false );

        return $doctype . $doc;
    }

    private function log($message, $stacksize = 0 )
    {
        if (!isset($this->m_options['debug']) || $this->m_options['debug'] == false )
        {
            return;
        }

        if (!$message || !is_string($message ) )
        {
            return;
        }

        $stacksize = ( !is_numeric($stacksize ) || $stacksize < 0 ) ? 0 : $stacksize;
        $space = '';

        for ($i=0; $i<$this->m_log_stacksize; $i++)
        {
            $space .= $this->m_options['log_space'];
        }

        for ($i=0; $i<$stacksize; $i++)
        {
            $space .= $this->m_options['log_space'];
        }

        array_push($this->m_log, $space . $message);
    }

    public function log_print($delimeter = "\n" )
    {
        $out = "\n<!-- OUTPUT LOG -->\n<pre>\n";

        foreach ($this->m_log as $l)
        {
            $out .= $l . $delimeter;
        }

        return $out."</pre>";
    }
}

?>
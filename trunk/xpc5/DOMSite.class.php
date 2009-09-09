<?php

/*
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * Base class for parsers and generators
 */
abstract class NodeProcessor
{
    /**
     * Hold DOMSite object instance who call this class
     */
    protected $parent   = null;

    /**
     * Hold current node
     */
    protected $node     = null;

    /**
     * Set instance of DOMSite object.
     * @param DOMSite $parent
     */
    public final function setParent( DOMSite & $parent )
    { $this->parent = $parent; }

    /**
     * Set instance of DOMNode object.
     * @param DOMNode $node
     */
    public final function setNode( DOMNode & $node )
    { $this->node   = $node; }
}

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

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'SiteParserFactory.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'SiteGeneratorFactory.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'DOMSimpler.php';

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
class DOMSite
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

    private $m_log = array();

    private $m_pages = array();

    private $m_locales = array();

    private $m_append_body = array();

    private $m_template = null;

    private $m_pagedefaults = array('title'=>'Page title', 'encoding'=>'utf-8' );

    private $m_log_stacksize = 0;

    private $m_xml = null;

    private $m_scripts = array('inline' => array(), 'include' => array() );

    private $m_styles = array('inline' => array(), 'include' => array() );

    private $m_dom_main = null;
    private $m_dom_body = null;
    private $m_dom_content = null;

    public function __construct(array $options )
    {
        $this->m_options = array_merge($this->m_options, $options );//print_r( $this->m_options );
        if ($this->m_options['output'] == true )
        {
            echo $this->out();
        }
    }

    public function addOption( $key, $value )
    {
        if( !$key || !is_string($key) || !strlen($key) )
            return;

        if( !$value || !is_string($value) || !strlen($value) )
            return;

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
        if( !$path || !is_string($path) || !strlen($path) )
            return;

        if( !$node )
            return;

        $p              = new PageElement();
        $p->id          = $node->attr('id');
        $p->path        = $path;
        $p->lang        = $this->m_options['lang'];
        $p->file        = str_replace('/', '-', $path).'.xml';
        $p->role        = $node->attr('role');
        $p->node        = $node;
        $p->type        = 'page';
        $p->title       = $node->attr('title');
        $p->fullpath    = $this->m_options['base'] . $p->file;
        $p->alias       = $node->attr('alias');

        $this->m_pages[$path] = $p;

        $this->log('Register Page "'. $p->path . '"');
    }

    public function delPage( $name )
    {
        if( key_exists($name,$this->m_pages) )
            unset( $this->m_pages[$name] );
    }

    public function parse_recursive( DOMNode & $node )
    {
        $this->m_log_stacksize += 1;

        foreach ( $node->child() as $item )
            $this->parse_node( $item );

        $this->m_log_stacksize -= 1;
    }
    
    private function _parse_first( DOMNode & $node )
    {
        $this->m_log_stacksize++;

        if( strtolower($node->name()) == 'generator' )
        {
            $genId =  $node->attr('id');

            if( SitemapGeneratorFactory::instance()->has($genId) == false )
            {
                $this->log('Generator not exist: "'. $genId .'"');
                return;
            }

            $generator =& SitemapGeneratorFactory::instance()->get($genId);
            $generator->setParent($this);

            $genresult = $generator->generate( $node );

            switch( gettype($genresult) )
            {
                case 'object':
                {
                    if( $genresult instanceof DOMNode )
                    {
                        //$this->log( 'Insert generator result '  . gettype($genresult) );
                        $node->parent()->replaceChild( $genresult, $node );
                    }
                }
                break;

                case 'array':
                {
                    //$this->log( 'Generator result '  . gettype($genresult) . ', elements count:' . count($genresult) );
                    $parent = $node->parent();
                    $parent->removeChild($node);

                    foreach( $genresult as $i )
                    {
                        //$this->log( 'Walk item ' . gettype($i) );
                        if( $i instanceof DOMNode )
                            $parent->appendChild( $i );
                    }

                }
                break;

                case 'string':
                {
                    $parent = $node->parent();
                    $parent->removeChild( $node );
                    $newnode = $parent->document()->createTextNode( $genresult );
                    $parent->appendChild( $newnode );
                }
                break;
            }
            # dfsdfsd
            // If name == 'generator
            return;
        }

        foreach ( $node->child() as $item )
            $this->_parse_first($item);

        $this->m_log_stacksize--;
    }

    private function mf_make_page( $filepath )
    {
        $this->log("Make page" );

        if (!$filepath || !is_string($filepath) || !strlen($filepath) )
        {
            $this->log("Bad page path", 1 );
            return null;
        }

        $this->log("Page path: ". $filepath, 1 );

        if ( !is_file($filepath) )
        {
            $this->log("Not found", 2);
            return null;
        }

        $p_ = new XMLPage( array(
            'index' => $filepath,
            'lang' => $this->m_options['lang'],
            'templateTag' => array('{', '}', 'CONTENT'),
            'debug' => true
        ));
        return $p_;
    }

    public function parse_node( DOMNode & $node )
    {
        if (!$node )
            return;

        $nodeName = strtolower( $node->name() );
        
        if( !SitemapParserFactory::instance()->has($nodeName) )
        {
            $this->log('Parser not exist: "'. $nodeName .'"');
            return;
        }

        $parser =& SitemapParserFactory::instance()->get( $nodeName );
        $parser->setParent( $this );        
        $parser->parse($node);  

        return;
    }

    private function pf_copy_attachments(XMLPage & $page )
    {
        if ( !$page )
        {
            return;
        }

        foreach ($page->remote_style as $s )
        {
            array_push($this->m_styles['include'], $s );
        }

        foreach ($page->remote_script as $s )
        {
            array_push($this->m_scripts['include'], $s );
        }

        if ($this->m_options['gluestyles'] )
        {

            foreach ($page->include_style as $s )
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

            foreach ($page->include_style as $s )
            {
                array_push($this->m_styles['include'], $s );
            }
        }

        if ($this->m_options['gluescripts'] )
        {

            foreach ($page->include_script as $s )
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

            foreach ($page->include_script as $s )
            {
                array_push($this->m_scripts['include'], $s );
            }
        }

        foreach ($page->inline_script as $s )
        {
            array_push($this->m_scripts['inline'], "\n/***** inline script *****/\n" . $s );
        }

        foreach ($page->inline_style as $s )
        {
            array_push($this->m_styles['inline'], "\n/***** inline style *****/\n" . $s );
        }
        
        if( count($page->locale) )
        {
            $this->m_locales = array_merge( $this->m_locales, $page->locale );
        }

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

        if( file_exists($indexfile) == false )
        {
            $this->log('Index file not exist');
            if( $log_out ) echo $this->print_log();
            return;
        }

        $this->m_dom_main = new DOMDocument('1.0', 'UTF-8');
        $dom =& $this->m_dom_main;

        $dom->registerNodeClass('DOMElement','DOMElementSimpler');
        $dom->registerNodeClass('DOMDocument','DOMDocumentSimpler');

        if( $dom->load( $indexfile, ~LIBXML_NSCLEAN ) == false )
        {
            $this->log('Parse index failed');
            if( $log_out ) echo $this->log_print();
            return;

        } else $this->log('Parse index: "'.$this->m_options['index'].'"' );

        $this->_parse_first($dom->documentElement);
        $this->parse_recursive( $dom->documentElement );

        
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

            if( $page->role != null )
            {
                unset( $page );
                $page = $this->_selectPageById( $this->m_options['id_page_no_access'] );
                
                if( !$page )
                {
                    $this->log('Page "' . $this->m_options['id_page_no_access'] . '" not found.',  2);
                    if( $log_out ) echo $this->log_print();
                    return;
                }
            }
        }



        /**
         * Make template
         */
        $body = " ";
        if( key_exists('template', $this->m_options) )
        {
            $cont_ = $this->mf_make_page( $this->m_options['base'] . $this->m_options['template'] );
            if ( $cont_ )
            {
                $this->log( 'Container: ' . $this->m_options['template'] );

                $body = $cont_->out(false);
                $this->pf_copy_attachments($cont_);
            }
            else
            {
                $this->log("Failed to load template page " . $this->m_options['template'] );
                if( $log_out ) echo $this->log_print();
                return;
            }
        }
        else $body = '{CONTENT}';

        /**
         * Make page
         */
        $page_ = $this->mf_make_page( $page->fullpath );

        if ($page_ )
        {
            $page_->out(false);
            $this->pf_copy_attachments($page_);

            $arr = array();

            foreach ( $page_->outdata as $key => $val )
            {
                $k = $page_->defaultTempate[0] . $key . $page_->defaultTempate[1];
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

        if( !$html->loadXML( '<html><head /><body /></html>') )
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

        if( strlen($body) )
        {
            $textFrag = $html->createComment("[BODY_PLACE]");

            $b = $html->body();
            if( !$b )
            {
                $this->log('Cant find body');
                if( $log_out ) echo $this->log_print();
                return;
            }
            $b->appendChild( $textFrag );
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

        $ra = array( 'body_place' => &$body );

        $mutator = new DOMMutator();

        if( !$mutator->fromDOM($html, $ra) )
        {
             $this->log('Mutation failed');
            if( $log_out ) echo $this->log_print();
            return;
        }

        //$doc = str_replace('<!--[BODY_PLACE]-->', $body, $html->saveHTML() );

        $links_arr = array();
        foreach( $this->m_pages as $k => $v )
        {
            $sname = dirname( $_SERVER["SCRIPT_NAME"] ) . '/';
            $links_arr[ $k ] = 'http://' . $_SERVER['HTTP_HOST'] . $sname . $k;
        }

       // print_r( $this->m_locales );
        $doc = Template::apply( $html->saveHTML(), $links_arr, array("{link:","}") );
        $doc = Template::apply( $doc, $this->m_locales, array("{l:","}") );

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
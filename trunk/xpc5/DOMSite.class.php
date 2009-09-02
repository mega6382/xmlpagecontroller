<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class DOMDocumentSimpler extends DOMDocument
{
    function addStyleFile( $filename )
    {
        if( !$filename || !is_string( $filename ) || !strlen($filename) )
            return false;

        $head = null;
        foreach( $this->xpath('//html/head') as $i) $head = $i;

        if( !$head ) return false;

        return $head->appendStyleFile($filename);
    }

    function addStyle( $style )
    {
        if( !$style || !is_string( $style ) || !strlen($style) )
            return false;

        $body = null;

        foreach( $this->xpath('//html/body') as $i)
            $body = $i;

        if( !$body )
            return false;

        return $body->appendStyle($style);
    }

    function xpath( $query )
    {
        $r = array();

        if( !$query || is_string($query) == false || !strlen( $query ) ) return $r;

        $x = new DOMXPath($this);
        $a = $x->query( $query );

        

        foreach( $a as $i )
        {
            if( $i->nodeType == XML_ELEMENT_NODE )
                array_push($r,$i);
        }

        return $r;
    }
}

class DOMElementSimpler extends DOMElement
{
    function attr( $name = null, $value = null )
    {
        if( is_null($name) )
        {
            $arr = array();
            return $arr;
        }

        if( is_null($value) )
        {
            return ( $this->hasAttribute($name) ) ? $this->getAttribute($name) : null;
        }

        return  $this->setAttribute( $name, $value );
    }

    function child()
    {
        $res = array();

        foreach( $this->childNodes as $item )
        {
            if( $item->nodeType != XML_ELEMENT_NODE ) continue;
            array_push( $res, $item );
        }
        return $res;
    }

    function name( $name = null )
    {
        if( is_null( $name ) )
            return strlen($this->tagName) ? $this->tagName : null;

        if( is_string( $name ) && strlen($name) )
            $this->tagName = $name;
    }

    function data( $data = null )
    {
        if( is_null( $data ) )
            return strlen($this->textContent) ? $this->textContent : null;

        if( is_string( $data ) && strlen($data) )
        {
            $tnode = $this->ownerDocument->createTextNode($data);
            $this->appendChild($tnode);
        }
    }

    function appendStyleFile( $filename )
    {
        if( !$filename || !is_string( $filename ) || !strlen($filename) )
            return false;

        $node = $this->ownerDocument->createElement('link');
        $node->attr('rel', 'stylesheet');
        $node->attr('type', 'text/css');
        $node->attr('href', $filename);

        return $this->appendChild( $node );
    }
    
    function addpendCssFile($filename)
    {
        return $this->appendStyleFile($filename);
    }

    function appendJavaScriptFile( $filename )
    {
        if( !$filename || !is_string( $filename ) || !strlen($filename) )
            return false;

        $node = $this->ownerDocument->createElement('script');
        $node->attr('type', 'text/javascript');
        $node->attr('src', $filename);

        return $this->appendChild( $node );
    }

    function addpendJSFile($filename)
    {
        return $this->appendJavaScriptFile($filename);
    }

    function appendStyle($style)
    {
        if( !$style || !is_string($style) || !strlen($style) )
                return false;

         return $this->appendChild( $this->ownerDocument->createTextNode($style) );
    }

    function appendCss($css)
    {
        return $this->appendStyle($css);
    }

   /* function xpath( $query )
    {
        if( !$query || is_string($query) || !strlen( $query ) ) return;

        $x = new DOMXPath($query);
        $a = $x->query( $query );

        $r = array();

        foreach( $a as $i ) if( $i->nodeType == XML_ELEMENT_NODE ) array_push($a,$i);
        return $r;
    }*/
}

class DOMSite
{
    private $m_options = array('index' => 'index.xml', 'lang' => 'en', 'doctype' => 'traditional', 'base' => '', 'output' => false, 'debug' => false, 'keyname' => 'q', 'keydelimeter' => '/', 'gluescripts' => false, 'gluestyles' => false, 'logspace' => '&nbsp;&nbsp;');

    private $m_meta = array();

    private $m_log = array();

    private $m_pages = array();

    private $m_pagestack = array();

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

    private function mf_parse_recursive( DOMNode & $node )
    {
        $this->m_log_stacksize += 1;
        foreach ( $node->child() as $item ) $this->mf_parse_node( $item );
        $this->m_log_stacksize -= 1;
    }

    private function mf_make_page( $path )
    {
        $this->log("Make page" );

        if (!$path || !is_string($path) || !strlen($path) )
        {
            $this->log("Bad page path", 1 );
            return null;
        }

        $filename = $this->m_options['base'] . $path;
        $this->log("Page path: ". $filename, 1 );

        if (!is_file($filename ) )
        {
            $this->log("Not found", 2);
            return null;
        }

        $p_ = new XMLPage(array('index' => $filename, 'lang' => $this->m_options['lang'], 'templateTag' => array('{', '}', 'CONTENT') ));
        return $p_;
    }

    private function mf_parse_node( DOMNode & $node )
    {
        if (!$node )
        {
            return;
        }

        $nodeName = $node->name();
        $attrName = $node->attr('name');

        switch ($nodeName )
        {
            case 'config':
                {
                    $this->log('Config');

                    foreach( $node->child() as $item )
                    {
                        switch ( $item->nodeName )
                        {
                            case 'option':
                                {
 

                                    $this->m_pagedefaults[ $item->attr('name') ] = $item->data();
                                    $this->log('Option "'.$item->attr('name').'": '.$item->data(), 1);
                                }

                                break;
                            case 'meta':
                                {
                                    $this->m_meta[ $item->attr('name') ] = $item->data();
                                    $this->log('Meta "'.$item->attr('name').'": '.$item->data(), 1);
                                }

                                break;
                            case 'metas':
                                {

                                    foreach ( $item->child() as $i )
                                    {
                                        $this->m_meta[ $i->name() ] = $i->data();
                                        $this->log('Meta "'.$i->name().'": '.$i->data(), 1);
                                    }
                                }

                                break;
                            case 'options':
                                {

                                    foreach ( $item->child() as $i )
                                    {
                                        $this->m_pagedefaults[ $i->name() ] = $i->data();
                                        $this->log('Option "'. $i->name() . '": '.$i->data(), 1);
                                    }
                                }

                                break;
                        }
                    }
                }

                break;
            case 'template':
                {
                    $this->log('Tempate is: ' . $node->data());
                    $this->m_template = $node->data();//$this->log('Template '. ( $this->m_template ) ? 'is' : 'no' .' loaded ', 1);
                }

                break;
            case 'base':
                {
                    $p = $node->data();

                    if (is_string($p) && strlen($p) )
                    {
                        $this->m_options['base'] = $p;
                    }
                }

                break;
            case 'page':
                {
                    $path = "";
                    array_push($this->m_pagestack, $attrName );

                    $path = implode('/', $this->m_pagestack );

                    $this->log('Page: '. $path);
                    $this->mf_parse_recursive($node);
                    $this->m_pages[$path] = $node;
                    
                    array_pop($this->m_pagestack );
                }

                break;
        }
    }

    private function pf_copy_attachments(XMLPage & $page )
    {
        if (!$page )
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
    }

    public function out()
    {
        $this->m_dom_main = new DOMDocument();
        $dom =& $this->m_dom_main;
        $dom->registerNodeClass('DOMElement','DOMElementSimpler');
        $dom->registerNodeClass('DOMDocument','DOMDocumentSimpler');

        if( $dom->load( $this->m_options['index'] ) == false )
        {
            $this->log('Parse index failed');
            return;
        }

        $this->log('Parse index: "'.$this->m_options['index'].'"' );
        
        $this->mf_parse_recursive( $dom->documentElement );
        $kname = &$this->m_options['keyname'];

        if (!$kname )
        {
            $this->log('Invalid Keyname');
            return;
        }

        $page = null;

        if ( array_key_exists($kname, $_GET) )
        {
            $kval = &$_GET[ $kname ];
            $this->log('Request page: ' . $kval );

            if (array_key_exists($kval, $this->m_pages) )
            {
                $this->log('Page found', 1);
                $page = &$this->m_pages[ $kval ];
            }
            else
            {
                $this->log('Page not found', 1);

                if (array_key_exists("page_not_found", $this->m_pages) )
                {
                    $page = &$this->m_pages[ "page_not_found" ];
                }
                else
                {
                    $this->log('page_not_found undefined', 2);
                }
            }
        }
        else
        {
            $this->log('Default page');

            if (array_key_exists("page_index", $this->m_pages) )
            {
                $page = &$this->m_pages[ "page_index" ];
            }
            else
            {
                $this->log('page_index undefined', 2);
            }
        }


        if (!$page )
        {
            $this->log('No page for output');
            return;
        }

        $html = new DOMDocumentSimpler();//DOMDocument();
        $html->registerNodeClass('DOMElement','DOMElementSimpler');
        $html->registerNodeClass('DOMDocument','DOMDocumentSimpler');

        if( $html->loadXML( '<html><head /><body /></html>') )
        {

        }
        else
        {
            $this->log("Failed to load case page" );
            return;
        }

        //$container = "";
        $cont_ = new DOMDocument();

        if ( $cont_->load( $this->m_options['base'].$this->m_template ) )
        {
            //$container = $cont_->saveHTML();
            //$this->pf_copy_attachments($cont_ );
        }
        else
        {
            $this->log("Failed to load template page " . $this->m_options['base'].$this->m_template );
            return;
        }

        $body = "";

        $page_ = $this->mf_make_page( $page->attr('name') . '.xml' );

        if ($page_ )
        {
            $body = $page_->out(false);
            $this->pf_copy_attachments($page_);
        }
        else
        {
            $this->log('No page for output');
        }

        $doctype = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n\"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">";
        $headers = "";

        $head = null;

        foreach( $html->xpath('//html/head') as $i ) $head = $i;
        if( is_null($head) )
        {
            $this->log("Failed to get head");
            return;
        }

        $_body = $head->nextSibling;

        $tag = $html->createElement('title',$this->m_pagedefaults['title']);
        $head->appendChild( $tag );

        foreach ($this->m_meta as $key => $val )
        {
            $tag = $html->createElement('meta');
            $tag->attr('name', $key);
            $tag->attr('content', $val);
            $head->appendChild( $tag );
            unset($tag);
        }

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
            if( $html->addStyleFile($s) == false ) $this->log('Failed to append style: ' . $s);
        }

        if (strlen($inlinestyle) )
        {
            if( $html->addStyle($inlinestyle) == false ) $this->log('Failed to append inline style');
        }
        
        $fr = $html->createDocumentFragment();
        $fr->appendXML( $body );
        $_body->appendChild($fr);

        foreach ($this->m_scripts['include'] as $s )
        {
            $tag = $html->createElement('script');
            $tag->attr('type', 'text/javascript');
            $tag->attr('src', $s);
            $_body->appendChild( $tag );
            unset($tag);
        }


        if (strlen($inlinescript) )
        {
            $tag = $html->createElement('script');
            $tag->attr('type', 'text/javascript');
            $tag->data( $inlinescript );
            $_body->appendChild( $tag );
            unset($tag);
        }


        $html->normalizeDocument();
        return $doctype . $html->saveHTML();
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
            $space .= $this->m_options['logspace'];
        }


        for ($i=0; $i<$stacksize; $i++)
        {
            $space .= $this->m_options['logspace'];
        }

        array_push($this->m_log, $space . $message);
    }

    public function log_print($delimeter = "\n" )
    {
        $out = "";

        foreach ($this->m_log as $l)
        {
            $out .= $l . $delimeter;
        }

        return $out;
    }

    public function apply($output, array $data, array $tags = array(), $case = false )
    {
        if (!$data )
        {
            return $output;
        }

        $ob  = key_exists(0, $tags) ? $tags[0] : "%";
        $cb  = key_exists(1, $tags) ? $tags[1] : "%";
        $ad  = key_exists(2, $tags) ? $tags[2] : ".";
/*
        if ($tags && is_array($tags) && count($tags) == 2 )
        {
            $tag_begin = &$tags[0];
            $tag_end = &$tags[1];
        }
*/
        $text = $output;

        foreach ($data as $key => $value)
        {
            
            if (is_array($value) )
            {
                continue;
            }

            //$KEY= $tag_begin . $key . $tag_end;
            //$text	= str_replace( strtoupper($KEY), $value, $text);
            
            $text = str_ireplace( $ob . $key . $cb, $value, $text);
        }

        return $text;
    }
}

?>
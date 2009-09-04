<?php

interface ISitemapParser
{
    public function parse( DOMNode & $node );
    public function name();
}

interface ISitemapGenerator
{
    public function generate();
    public function name();
}

abstract class NodeProcessor
{
    protected $parent = null;
    protected $node = null;

    public final function setParent( DOMSite & $parent ){ $this->parent = $parent; }
    public final function setNode( DOMNode & $node ){ $this->node = $node; }
}

abstract class SitemapParser extends NodeProcessor implements ISitemapParser{}
abstract class SitemapGenerator extends NodeProcessor implements ISitemapGenerator{}

require_once dirname(__FILE__) . '/SiteParserFactory.php';
require_once dirname(__FILE__) . '/SiteGeneratorFactory.php';
require_once dirname(__FILE__) . '/DOMSimpler.php';

class DOMSite
{
    private $m_parser_instance = array();

    private $m_options = array('index' => 'index.xml', 'lang' => 'en', 'doctype' => 'traditional', 'base' => '', 'output' => false, 'debug' => false, 'keyname' => 'q', 'keydelimeter' => '/', 'gluescripts' => false, 'gluestyles' => false, 'logspace' => '&nbsp;&nbsp;');

    private $m_meta = array(
		'generator'	=> 'XML Page Cpntroller'
	);

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

    public function addOption( $key, $value )
    {
        if( !$key || !is_string($key) || !strlen($key) )
            return;

        if( !$value || !is_string($value) || !strlen($value) )
            return;

        $this->m_options[ $key ] = $value;
        //$this->log( 'Add Option "'.$key.'"' );
    }

    public function addMeta( $key, $value )
    {
        if( !$key || !is_string($key) || !strlen($key) )
            return;

        if( !$value || !is_string($value) || !strlen($value) )
            return;

        $this->m_meta[ $key ] = $value;
        //$this->log( 'Add Meta "'.$key.'"' );
    }

    public function addPage( $name, DOMNode & $node )
    {
        if( !$name || !is_string($name) || !strlen($name) )
		{

            return;
		}

        if( !$node )
            return;

        $this->m_pages[ $name ] = $node;
        $this->log('Add Page "'. $name . '"');
    }

    public function parse_recursive( DOMNode & $node )
    {
        $this->m_log_stacksize += 1;
        foreach ( $node->child() as $item ) $this->parse_node( $item );
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

        $p_ = new XMLPage( array(
	    'index' => $filename,
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
        $nodePrefix = strtolower( $node->prefix );

        switch( $nodePrefix )
        {
            case 'generator':
                {
                    if( SitemapGeneratorFactory::instance()->has( $nodeName ) == false )
                    {
                        $this->log('Generator not exist: "'. $nodeName .'"');
                        return;
                    }

                    $generator =& SitemapGeneratorFactory::instance()->get( $nodeName );
                    $generator->setParent($this);
                    $generator->genetrate($node);
                }
            break;
        }
        
        
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

	if( key_exists('page_log', $this->m_options) && $this->m_options['page_log'] )
	    $this->m_log = array_merge( $this->m_log, $page->_log );
    }

    public function out()
    {
        $this->m_dom_main = new DOMDocument('1.0', 'UTF-8');
        $dom =& $this->m_dom_main;

        //echo $dom->resolveExternals;// = true;

        $dom->registerNodeClass('DOMElement','DOMElementSimpler');
        $dom->registerNodeClass('DOMDocument','DOMDocumentSimpler');

        $indexfile = $this->m_options['index'];

        if( file_exists($indexfile) == false )
        {
            $this->log('Index file not exist');
            return;
        }

        $bm = ~LIBXML_NOWARNING | ~LIBXML_NSCLEAN;
        if( $dom->load( $indexfile, $bm/*~LIBXML_NSCLEAN  LIBXML_NOWARNING*/  ) == false )
        {
            $this->log('Parse index failed');
            return;

        } else $this->log('Parse index: "'.$this->m_options['index'].'"' );
        
        $this->parse_node( $dom->documentElement );
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
                $this->log('Page "'.$kval.' is not exist"', 1);

                if ( array_key_exists("page_not_found", $this->m_pages) )
                {
                    $page = &$this->m_pages[ "page_not_found" ];
                }
                else
                {
                    $this->log('Page "page_not_found" is undefined', 2);
                    return;
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

        $cont_ = $this->mf_make_page( $this->m_options['template'] );
        
        $body = " ";
        if ( $cont_ )
        {
            $this->log( 'Container: ' . $this->m_options['template'] );
            
            $body = $cont_->out(false);
            $this->pf_copy_attachments($cont_);
        }
        else
        {
            $this->log("Failed to load template page " . $containerFile );
            return;
        }

        $page_ = $this->mf_make_page( $page->attr('id') . '.xml' );

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
            $this->log('No page for output');
        }

        $doctype = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n\"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">";
        $headers = "";

        $head = null;

        $this->m_dom_body = new DOMDocumentSimpler('1.0', 'UTF-8');

        $html =& $this->m_dom_body;
        $html->registerNodeClass('DOMElement','DOMElementSimpler');
        $html->registerNodeClass('DOMDocument','DOMDocumentSimpler');


        //echo 'Html encoding: ' . $html->encoding;

        if( $html->loadXML( '<html><head /><body /></html>') )
        {

        }
        else
        {
            $this->log("Failed to load case page" );
            return;
        }

        $head = $html->getElementsByTagName('head')->item(0);
        if( is_null($head) )
        {
            $this->log("Failed to get head");
            return;
        }

        
        

        $html->setTitle('Hello title');

        $contentType = $html->createElement('meta');
        $contentType->attr('http-equiv','Content-Type');
        $contentType->attr('content','text/html; charset=utf-8');
        $head->appendChild( $contentType );

        foreach ($this->m_meta as $key => $val )
        {
            $html->addMeta($key,$val);
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
            if( $html->addStyleFile($s) == false )
                $this->log('Failed to append style: ' . $s);
        }

        if (strlen($inlinestyle) )
        {
            if( $html->addStyle($inlinestyle) == false )
                $this->log('Failed to append inline style');
        }

        $bn = new DOMDocument('1.0', 'UTF-8');

        //echo $body;

        $bn->loadHTML( $body );
       
        //$fr = $html->createTextNode( $body );

        
        //ob_start();
        //$fr->appendXML( utf8_encode("<b>Hello&nbsp;fragment</b>") );
        //ob_end_clean();

        $_body = $head = $html->getElementsByTagName('body')->item(0);
        if( $_body == null )
        {
            $this->log('Cant find body');
            return;
        }

        
        foreach ( $bn->childNodes as $no)
        {
            if( $no->nodeType != XML_ELEMENT_NODE ) continue;
            $n = $html->importNode($no, true);
            if( !$n ) continue;
            $_body->appendChild( $n );
        }



        //echo $body;

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
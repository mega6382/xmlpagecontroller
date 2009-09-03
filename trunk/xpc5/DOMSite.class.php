<?php

require_once dirname(__FILE__).'/DOMSimpler.php';

interface ISitemapParser
{
    public function parse( DOMNode & $node );
    public function name();
}

abstract class SitemapParser implements ISitemapParser
{
    protected $instance = null;
    public final function setInstance( DOMSite & $instance ){ $this->instance = $instance; }
}

class theRecursionParser extends SitemapParser
{
    function name()
    {
        return 'site root config conf metas options';
    }

    function parse( DOMNode & $node )
    {
        $this->instance->parse_recursive($node);
    }
}


class theOptionParser extends SitemapParser
{
    function name()
    {
        return 'option';
    }

    function parse( DOMNode & $node )
    {
        $this->instance->addOption( $node->attr('name'), $node->data() );
    }
}

class theOptionsParser extends SitemapParser
{
    function name()
    {
        return 'options';
    }

    function parse( DOMNode & $node )
    {
        foreach( $node->child() as $n )
            $this->instance->addOption( $n->name(), $n->data() );
    }
}

class theMetasParser extends SitemapParser
{
    function name()
    {
        return 'metas';
    }

    function parse( DOMNode & $node )
    {
        foreach( $node->child() as $n )
            $this->instance->addMeta( $n->name(), $n->data() );
    }
}

class theMetaParser extends SitemapParser
{
    function name()
    {
        return 'meta';
    }

    function parse( DOMNode & $node )
    {
        $this->instance->addMeta( $node->attr('name'), $node->data() );
    }
}

class thePageParser extends SitemapParser
{
    var $stack = array();

    function name(){ return 'page'; }

    function parse( DOMNode & $node )
    {
	$id = $node->find_value('id');
        array_push($this->stack, $id );
        $path = implode('/', $this->stack );
        //$this->log('Page: '. $path);
        $this->instance->parse_recursive($node);
        $this->instance->addPage( $path, $node );
        array_pop($this->stack);
    }
}

class DOMPage
{
    public function __construct()
    {
    }
}

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
        {
            return;
        }

        $nodeName = $node->name();

        //print_r($this->m_parser_instance);

        //echo $nodeName . '<br />';
        if( key_exists( strtolower($nodeName), $this->m_parser_instance ) == false )
        {
            $this->log('Sitemap parser not exist: "'. strtolower($nodeName) .'"');
            return;
        }

        $parser =& $this->m_parser_instance[ strtolower($nodeName) ];
        
        $parser->parse($node);  

        return;

        $attrName = $node->attr('name');

        switch ($nodeName )
        {
            case 'conf':
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
                    array_push($this->m_pagestack, $node->attr('id') );

                    $path = implode('/', $this->m_pagestack );

                    $this->log('Page: '. $path);
                    $this->mf_parse_recursive($node);
                    $this->m_pages[$path] = $node;
                    
                    array_pop($this->m_pagestack);
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

	if( key_exists('page_log', $this->m_options) && $this->m_options['page_log'] )
	    $this->m_log = array_merge( $this->m_log, $page->_log );
    }

    public function out()
    {
        foreach( get_declared_classes() as $decl )
        {
            if( !is_subclass_of($decl, 'SitemapParser') ) continue;
            
            $instance = new $decl();
            $declname = $instance->name();

            if( !$declname || !is_string($declname) || !strlen($declname) || $declname == 'undefined_parser' )
                continue;

            $instance->setInstance($this);
            $keys = explode( ' ', $declname );

            foreach( $keys as $k )
            {
                $k = trim($k);

                if( !$k  ) continue;

                $this->m_parser_instance[ $k ] = $instance;
            }
            //$this->log( 'Register "'. implode(', ',$keys) .'" in '.$decl.' class' );
        }

        $this->m_dom_main = new DOMDocument();
        $dom =& $this->m_dom_main;

        $dom->registerNodeClass('DOMElement','DOMElementSimpler');
        $dom->registerNodeClass('DOMDocument','DOMDocumentSimpler');

        $indexfile = $this->m_options['index'];

        if( file_exists($indexfile) == false )
        {
            $this->log('Index file not exist');
            return;
        }

        if( $dom->load( $indexfile ) == false )
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

	
       /* $containerFile = $this->m_options['base'].$this->m_options['template'];
	if( file_exists($containerFile) == false )
	{
            $this->log( 'Container not exist: ' . $containerFile );
            return;
	}
*/

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

        foreach( $html->xpath('//html/head') as $i ) $head = $i;
        if( is_null($head) )
        {
            $this->log("Failed to get head");
            return;
        }

        $_body = $head->nextSibling;


        $html->setTitle('Hello title');

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
            if( $html->addStyleFile($s) == false )
                $this->log('Failed to append style: ' . $s);
        }

        if (strlen($inlinestyle) )
        {
            if( $html->addStyle($inlinestyle) == false )
                $this->log('Failed to append inline style');
        }
        
        $fr = $html->createDocumentFragment();
        $fr->appendXML( $container.$body );
        $_body->appendChild( $fr );

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
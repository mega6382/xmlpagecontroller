<?php
/*
* To change this template, choose Tools | Templates
* and open the template in the editor.
*/

class XMLSite
{
    private $m_options = array(
        'index'         => 'index.xml',
        'lang'          => 'en',
        'doctype'       => 'traditional',
        'base'          => '',
        'output'        => false,
        'debug'         => false,
        'keyname'       => 'q',
        'keydelimeter'  => '/',
        'gluescripts'   => false,
        'gluestyles'    => false,
        'logspace'      => '&nbsp;&nbsp;'
    );

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

    public function __construct(array $options ) {
        $this->m_options = array_merge($this->m_options, $options );//print_r( $this->m_options );
        if ($this->m_options['output'] == true ) {
            echo $this->out();
        }
    }

    private function mf_parse_recursive(XMLTag & $node ) {
        if (!$node ) {
            return;
        }

        $this->m_log_stacksize += 1;

        foreach ($node->children() as $item ) {//$this->log('<b>Do recursion: </b> "' . $item->name() . '"<br />');
            $this->mf_parse_node($item );
        }

        $this->m_log_stacksize -= 1;
    }

    private function mf_make_page($path ) {
        $this->log("Make page" );

        if (!$path || !is_string($path) || !strlen($path) ) {
            $this->log("Bad page path", 1 );
            return null;
        }

        $filename = $this->m_options['base'] . $path;
        $this->log("Page path: ". $filename, 1 );

        if (!is_file($filename ) ) {
            $this->log("Not found", 2);
            return null;
        }

        $p_ = new XMLPage(array('index' => $filename, 'lang' => $this->m_options['lang'], 'templateTag' => array('{', '}', 'CONTENT') ));
        return $p_;
    }

    private function mf_parse_node(XMLTag & $node ) {
        if (!$node ) { return; }

        $nodeName = $node->name();
        $attrName = $node->attr('name');

        switch ($nodeName )
        {
            case 'config':
                {
                    $this->log('Config');

                    foreach ($node->children() as $item )
                    {

                        switch ($item->name() )
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

                                    foreach ($item->children() as $opt )
                                    {
                                        $this->m_meta[ $opt->name() ] = $opt->data();
                                        $this->log('Meta "'.$opt->name().'": '.$opt->data(), 1);
                                    }
                                }

                                break;
                            case 'options':
                                {

                                    foreach ($item->children() as $opt )
                                    {
                                        $this->m_pagedefaults[ $opt->name() ] = $opt->data();
                                        $this->log('Option "'.$opt->name().'": '.$opt->data(), 1);
                                    }
                                }

                                break;
                        }
                    }
                }

                break;

            case 'template':
                {
                    $this->log('Tempate');
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
                    array_push($this->m_pagestack, $node->attr('name') );
                    $path = implode('/', $this->m_pagestack );
                    $this->log('Page: '. $path);
                    $this->mf_parse_recursive($node );
                    $this->m_pages[$path] = $node;
                    array_pop($this->m_pagestack );
                }
                break;
        }
    }

    private function pf_copy_attachments(XMLPage & $page )
    {
        if (!$page )
            return;
        
        foreach ( $page->remote_style as $s )
        {
            array_push($this->m_styles['include'], $s );
        }

        foreach ( $page->remote_script as $s )
        {
            array_push($this->m_scripts['include'], $s );
        }

        if ( $this->m_options['gluestyles'] )
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
        $l_parser = new XMLParser($this->m_options['index'], true );
        $this->m_xml = $l_parser->Parse();

        if (!$this->m_xml )
        {
            $this->log('Parse index failed');
            return;
        }

        $this->log('Parse index: "'.$this->m_options['index'].'"' );
        $this->mf_parse_recursive($this->m_xml );
        $kname = &$this->m_options['keyname'];

        if (!$kname )
        {
            $this->log('Invalid Keyname');
            return;
        }

        $page = null;

        if (array_key_exists($kname, $_GET) )
        {
            $kval = &$_GET[ $kname ];
            $this->log('Request page: ' . $kval );

            if (array_key_exists($kval, $this->m_pages) ) {
                $this->log('Page found', 1);
                $page = &$this->m_pages[ $kval ];
            }
            else {
                $this->log('Page not found', 1);

                if (array_key_exists("page_not_found", $this->m_pages) ) {
                    $page = &$this->m_pages[ "page_not_found" ];
                }
                else {
                    $this->log('page_not_found undefined', 2);
                }
            }
        }
        else {
            $this->log('Default page');

            if (array_key_exists("page_index", $this->m_pages) ) {
                $page = &$this->m_pages[ "page_index" ];
            }
            else {
                $this->log('page_index undefined', 2);
            }
        }


        if (!$page ) {
            $this->log('No page for output');
            return;
        }

        $container = "";
        $cont_ = $this->mf_make_page($this->m_template );

        if ($cont_ ) {
            $container = $cont_->out(false);
            $this->pf_copy_attachments($cont_ );
        }

        $body = "";
        $page_ = $this->mf_make_page($page->attr('name') . '.xml' );

        if ($page_ ) {

            if (strlen($container) ) {
                $page_->container = $container;
            }

            $body = $page_->out(false);
            $this->pf_copy_attachments($page_ );
        }
        else {
            $this->log('No page for output');
        }

        $doctype = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\"\n\"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">";
        $headers = "";

        foreach ($this->m_meta as $key => $val ) {
            $headers .= $this->apply("<meta name=\"%NAME%\" content=\"%CONTENT%\" />", array("name"=> $key, "content"=> $val ), array("%", "%"), false );
        }

        $inlinescript = "";
        $inlinestyle = "";
        $includescript = "";
        $includestyle = "";

        foreach ($this->m_scripts['inline'] as $s ) {
            $inlinescript .= $s . "\n";
        }


        foreach ($this->m_styles['inline'] as $s ) {
            $inlinestyle .= $s . "\n";
        }


        foreach ($this->m_styles['include'] as $s ) {
            $includescript .= "\n<link rel=\"stylesheet\" type=\"text/css\" href=\"{$s}\" />";
        }


        foreach ($this->m_scripts['include'] as $s ) {
            $includestyle .= "\n<script type=\"text/javascript\" src=\"{$s}\"></script>";
        }


        if (strlen($inlinestyle) ) {
            $inlinestyle = "\n<style type=\"text/css\">\n{$inlinestyle}\n</style>";
        }


        if (strlen($inlinescript) ) {
            $inlinescript = "\n<script type=\"text/javascript\">\n{$inlinescript}\n</script>";
        }

        $body = $inlinestyle . $body . $includescript . $inlinescript;
        $headers .= $includestyle;
        $out = $this->apply($this->m_output, array("doctype" => $doctype, "header" => $headers, "body" => $body, "lang" => $this->m_options['lang'], "title" => $this->m_pagedefaults['title'], "encoding" => $this->m_pagedefaults['encoding'], ), array("%TEMPLATE_FIELD:", "%"), false );
        return $out;
    }

    private function log($message, $stacksize = 0 ) {
        if (!isset($this->m_options['debug']) || $this->m_options['debug'] == false ) {
            return;
        }


        if (!$message || !is_string($message ) ) {
            return;
        }

        $stacksize = ( !is_numeric($stacksize ) || $stacksize < 0 ) ? 0 : $stacksize;
        $space = '';

        for ($i=0; $i<$this->m_log_stacksize; $i++) {
            $space .= $this->m_options['logspace'];
        }


        for ($i=0; $i<$stacksize; $i++) {
            $space .= $this->m_options['logspace'];
        }

        array_push($this->m_log, $space . $message);
    }

    public function log_print($delimeter = "\n" ) {
        $out = "";

        foreach ($this->m_log as $l) {
            $out .= $l . $delimeter;
        }

        return $out;
    }

    public function apply($output, array $data, array $tags = array(), $case = false ) {
        if (!$data ) {
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

        foreach ($data as $key => $value) {

            if (is_array($value) ) {
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
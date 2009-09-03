<?php


class DOMDocumentSimpler extends DOMDocument
{
    function setTitle( $a_title )
    {
        if( !$a_title || !is_string( $a_title ) || !strlen($a_title) )
            return false;

        $html = null;

        foreach( $this->getElementsByTagName('html') as $i) $html = $i;

	//if( is_null($html) ){ echo 'Element is null <br />'; }

        if( !$html ) return;

        $head = null;

        foreach( $html->getElementsByTagName('head') as $c )
        {
	    $head = $c;
	}

	//if( is_null($html) ){ echo 'Element is null <br />'; }

        if( !$head )
        {
            $head = $this->createElement('head');
            $html->appendChildren( $head );
        }

        $title = null;
        foreach( $head->getElementsByTagName('title') as $c )
        {
	    $title = $c;
        }

        if( !$title )
        {
            $title = $this->createElement('title');
            $html->appendChild( $title );
        }

        return $title->data( $a_title );
    }

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

        if( !$query || is_string($query) == false || !strlen( $query ) )
	    return $r;

        $x = new DOMXPath($this);

	
	ob_start();
	$a = $x->query( $query );
	ob_end_clean();

	if( !$a )
	    return $r;

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
	
    function find_value( $a_val_name )
    {
	    if( !$a_val_name || !is_string( $a_val_name ) || !strlen( $a_val_name ) )
		    return null;

	    $val = $this->attr( $a_val_name );
	    if( !is_null($val) ) return $val;

	    foreach( $this->child() as $c )
	    {
		    if( $c->name() == $a_val_name ) return $c->data();
	    }

	    return null;
    }

    function appendStyleFile( $filename )
    {
      //  echo 'Append style file <br />';
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

         $node = $this->ownerDocument->createElement('style');
         $node->attr('type','text/css');
         $node->appendChild( $this->ownerDocument->createTextNode($style) );

         return $this->appendChild( $node );
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

?>

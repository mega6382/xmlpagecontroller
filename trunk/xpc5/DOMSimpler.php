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
  * Extends the class DOMDocument, introducing
  * features for Simplified work with classes
  * for clean the code
  *
  * @author Andrew Saponenko <roguevoo@gmail.com>
  * @filesource DOMSimpler.php
  */
class DOMDocumentSimpler extends DOMDocument
{
    /**
     * Gets the BODY element of the document,
     * if it does not exist returns null
     *
     * @return DOMElement
     */
    function body()
    {
        return $this->getElementsByTagName('body')->item(0);
    }

    /**
     * Gets the HEAD element of the document,
     * if it does not exist returns null
     *
     * @return DOMElement
     */
    function head()
    {
        return $this->getElementsByTagName('head')->item(0);
    }

    /**
     * Gets the HTML element of the document,
     * if it does not exist returns null
     *
     * @return DOMElement
     */
    function html()
    {
        return $this->getElementsByTagName('html')->item(0);
    }

    /**
     * Gets the TITLE element of the document,
     * if it does not exist returns null
     *
     * @return DOMElement
     */
    function title()
    {
        return $this->getElementsByTagName('title')->item(0);
    }

    /**
     * Finds the document TITLE tag and sets it
     * in the specified value, return true if
     * value is setted else false
     *
     * @param string $a_title
     * @return bool
     */
    function setTitle( $a_title )
    {
        if( !$a_title || !is_string( $a_title ) || !strlen($a_title) )
            return false;

        $titleNode = $this->title();
        if( $titleNode == null )
        {
            $headNode = $this->head();
            if( $headNode == null )
                return false;

            $titleNode = $this->createElement('title',$a_title);
            $headNode->appendChild( $titleNode );
            return true;
        }

        $titleNode->data( $a_title );

        return true;
    }

    /**
     * Set base for HTML document
     *
     * @param string $a_base
     * @return bool
     */
    function setBase( $a_base )
    {
        if( !$a_base || !is_string($a_base) || !strlen($a_base) )
            return false;

        $baseNode = $this->getElementsByTagName('base')->item(0);
        if( $baseNode == null )
        {
            $headNode = $this->head();
            if( $headNode == null )
                return false;

            $baseNode = $this->createElement('base');
            $headNode->appendChild( $baseNode );
         }

        $baseNode->attr( 'href', $a_base );

        return true;
    }

    /**
     * Adds a META tag in the document, setting it
     * as the key $a_key as 'name' attribure and value $a_value
     * as 'content' attribute
     *
     * @param string $a_key
     * @param string $a_value
     * @return bool
     */
    function addMeta( $a_key, $a_value )
    {
        $head = $this->head();
        
        if( $head === null )
            return false;

        $tag = $this->createElement('meta', '');
        $tag->attr('name', $a_key);
        $tag->attr('content', $a_value);

        return $head->appendChild( $tag );
    }

    /**
     * Adds a LINK tag in the title of a document
     * which points to the file $a_filename
     *
     * @param string $a_filename
     * @return bool
     */
    function addStyleFile( $a_filename )
    {
        if( !$a_filename || !is_string( $a_filename ) || !strlen($a_filename) )
            return false;

        $head = $this->head();

        if( !$head ) return false;

        return $head->appendStyleFile($a_filename);
    }

    /**
     * Adds a STYLE tag which contains $a_style inside the BODY tag
     *
     * @param string $a_style
     * @return bool
     */
    function addStyle( $a_style )
    {
        if( !$a_style || !is_string( $a_style ) || !strlen($a_style) )
            return false;

        $head = $this->head();

        if( !$head )
            return false;

        return $head->appendStyle($a_style);
    }

    /**
     * Adds a SCRIPT tag which contains $a_script inside the BODY tag
     *
     * @param string $a_script
     * @return bool
     */
    function addScript( $a_script )
    {
        if( !$a_script || !is_string( $a_script ) || !strlen($a_script) )
                return false;

        $body = $this->body();

        if( !$body )
            return false;

        $newnode = $this->createElement('script');
        $newnode->attr( 'type', 'text/javascript' );
        $newnode->data( $a_script );

        return $body->appendChild( $newnode );
    }

    /**
     * Adds a SCRIPT tag that points to a file $a_script inside the BODY tag
     *
     * @param string $a_script
     * @return bool
     */
    function addScriptFile( $a_script )
    {
        if( !$a_script || !is_string($a_script) || !strlen($a_script) )
                return false;

        $body = $this->body();

        if( !$body )
            return;

        $newnode = $this->createElement('script');
        $newnode->attr( 'type', 'text/javascript' );
        $newnode->attr( 'src', $a_script );

        return $body->appendChild( $newnode );
    }

    /**
     * Query XPath formated string $query
     * and return array of DOMNode result
     *
     * @param string $a_query
     * @return array
     */
    function xpath( $a_query )
    {
        $r = array();

        if( !$a_query || is_string($a_query) == false || !strlen($a_query) )
	    return $r;

        $x = new DOMXPath($this);

	
        ob_start();
        $a = $x->query( $a_query );
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

/**
 * Extends the class DOMElement, introducing
 * features for Simplified work with classes
 * for clean the code
 *
 * @author Andrew Saponenko <roguevoo@gmail.com>
 * @filesource DOMSimpler.php
 */
class DOMElementSimpler extends DOMElement
{
    /**
     * Get or set element attribute
     *
     * @param string $name
     * @param string $value
     * @return string
     */
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

    /**
     * Get parent element node
     *
     * @return DOMElement
     */
    function parent()
    {
        return $this->parentNode;
    }

    /**
     * Get parent document node
     *
     * @return DOMDocument
     */
    function document()
    {
        return $this->ownerDocument;
    }

    /**
     * Get element childrens as array of DOMElements
     *
     * @return array
     */
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

    /**
     * Get or set element name
     *
     * @param string $name
     * @return string
     */
    function name( $name = null )
    {
        if( is_null( $name ) )
            return strlen($this->tagName) ? $this->tagName : null;

        if( is_string( $name ) && strlen($name) )
            $this->tagName = $name;
    }

    /**
     * Get or set inner element data
     *
     * @param string $data
     * @return string
     */
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

    /**
     * Find single value in element
     * attribute or from childrens
     *
     * @param string $a_val_name
     * @return string
     */
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

    /**
     * Append STYLE tag as children
     * which points to the file $a_filename
     *
     * @param string $a_filename
     * @return bool
     */
    function appendStyleFile( $a_filename )
    {
        if( !$a_filename || !is_string($a_filename) || !strlen($a_filename) )
            return false;

        $node = $this->document()->createElement('link');
        $node->attr('rel', 'stylesheet');
        $node->attr('type', 'text/css');
        $node->attr('href', $a_filename);

        return $this->appendChild( $node );
    }

    /**
     * Append STYLE tag as children
     * which points to the file $a_filename
     *
     * @param string $a_filename
     * @return bool
     */
    function addpendCssFile( $a_filename )
    {
        return $this->appendStyleFile($a_filename);
    }

    /**
     * Append SCRIPT tag as children
     * which points to the file $a_filename
     *
     * @param string $a_filename
     * @return bool
     */
    function appendJavaScriptFile( $a_filename )
    {
        if( !$a_filename || !is_string( $a_filename ) || !strlen($a_filename) )
            return false;

        $node = $this->ownerDocument->createElement('script');
        $node->attr('type', 'text/javascript');
        $node->attr('src', $a_filename);

        return $this->appendChild( $node );
    }

    /**
     * Append SCRIPT tag as children
     * which points to the file $a_filename
     *
     * @param string $a_filename
     * @return bool
     */
    function addpendJSFile($a_filename)
    {
        return $this->appendJavaScriptFile($a_filename);
    }

    /**
     * Append STYLE tag as children
     * which contains $a_style
     *
     * @param string $a_style
     * @return bool
     */
    function appendStyle($a_style)
    {
        if( !$a_style || !is_string($a_style) || !strlen($a_style) )
                return false;

         $node = $this->document()->createElement('style');
         $node->attr('type','text/css');
         $node->appendChild( $this->document()->createTextNode($a_style) );

         return $this->appendChild( $node );
    }

    /**
     * Append STYLE tag as children
     * which contains $a_css
     *
     * @param string $a_css
     * @return bool
     */
    function appendCss($a_css)
    {
        return $this->appendStyle($a_css);
    }
}

?>

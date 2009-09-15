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
 * Description of HTMLPart
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */
class HTMLPartParser extends DOMElementParser
{
    public function style()
    {
        switch( parent::attr('type') )
        {
            default:
            case 'inline':
                parent::parser()->addStyle( parent::text() );
            break;

            case 'file':
                parent::parser()->addStyleFile( parent::text() );
            break;

            case 'remote':
            case 'url':
                parent::parser()->addStyleRemote( parent::text() );
            break;
        }
    }

    public function script()
    {
        switch( parent::attr('type') )
        {
            default:
            case 'inline':
                parent::parser()->addScript( parent::text() );
            break;

            case 'file':
                parent::parser()->addScriptFile( parent::text() );
            break;

            case 'remote':
            case 'url':
                parent::parser()->addScriptRemote( parent::text() );
            break;
        }
    }
}

class HTMLPart extends DOMPart
{
    public $m_script_inline;
    public $m_script_include;
    public $m_script_remote;

    public $m_style_inline;
    public $m_style_include;
    public $m_style_remote;

    public function addStyle( $style )
    { if( !empty( $style ) ) array_push($this->m_style_inline, $style); }

    public function addStyleFile( $style )
    { if( !empty( $style ) ) array_push($this->m_style_include, $style); }

    public function addStyleRemote( $style )
    { if( !empty( $style ) ) array_push($this->m_style_remote, $style); }

    public function addScript( $script )
    { if( !empty( $script ) ) array_push($this->m_script_inline, $script); }

    public function addScriptFile( $script )
    { if( !empty( $script ) ) array_push($this->m_script_include, $script); }

    public function addScriptRemote( $script )
    { if( !empty( $script ) ) array_push($this->m_script_remote, $script); }

    public function __construct()
    {
        parent::__construct();

        $p = new HTMLPartParser();

        parent::registerParsers( array(
            'css style stylesheet' => array( $p, 'style' ),
            'js script jscript jsscript javascript' => array( $p, 'script' )
        ));

        $this->m_script_inline  = array();
        $this->m_script_include = array();
        $this->m_script_remote  = array();
        $this->m_style_inline   = array();
        $this->m_style_include  = array();
        $this->m_style_remote   = array();
    }
}
?>

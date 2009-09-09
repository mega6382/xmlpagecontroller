<?php

/**
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

/*
* Include XML parser class
*/

    class XMLParser
    {
        /*
        *	XML parser handler
        */
        var $m_parser			= null;
        var $m_allow_php		= false;
        var $m_array			= array();
        var $m_current			= null;
        var $m_result			= null;
        var $m_stacksize		= 0;

        var $m_source_data		= '';
        var $m_source_filename	= '';
        var $m_source_file		= null;

        function XMLParser( $a_file, $a_allow_php = false, $a_parse_now = false )
        {
            $this->m_allow_php = $a_allow_php;

            if( is_string( $a_file ) == false ) return $this;

            if( is_file( $a_file ) == true )
            {
                $this->m_source_filename = $a_file;
                $this->m_source_file = fopen( $this->m_source_filename, 'r' );

                if( $this->m_source_file == FALSE ) return $this;

                $file_size = filesize( $this->m_source_filename );
                $this->m_source_data = fread( $this->m_source_file, $file_size );

                fclose( $this->m_source_file );
            }
            else $this->m_source_data = $a_file;

            if( $a_parse_now )
            {
                return $this->Parse( $this->m_source_data );
            }

            return $this;
        }

        function Parse( &$a_xml_source = null )
        {
            if( !$a_xml_source )
            {
                if( $this->m_source_data == '' ) return null;
                $a_xml_source = &$this->m_source_data;
            }

            if( is_string($a_xml_source) == false || $a_xml_source == '' ) return null;

            $this->m_parser = xml_parser_create();

            xml_set_object($this->m_parser, $this);
            xml_set_element_handler($this->m_parser, 'StartXMLElement', 'EndXMLElement');
            xml_set_character_data_handler($this->m_parser, 'CDATAElement');
            xml_set_processing_instruction_handler($this->m_parser, 'PiElement');

            xml_parser_set_option( $this->m_parser, XML_OPTION_CASE_FOLDING,	0);
            xml_parser_set_option( $this->m_parser, XML_OPTION_SKIP_TAGSTART,	0);
            xml_parser_set_option( $this->m_parser, XML_OPTION_SKIP_WHITE,		0);
            //xml_parser_set_option( $this->m_parser, XML_OPTION_TARGET_ENCODING,	'utf-8');


            if ( !xml_parse($this->m_parser, $a_xml_source ) )
            {
                die(sprintf("XML error: %s at line %d",
                    xml_error_string(xml_get_error_code( $this->m_parser )),
                    xml_get_current_line_number( $this->m_parser )));
            }

            xml_parser_free($this->m_parser);

            return $this->m_result;
        }

        function StartXMLElement( $a_parser, $a_name, $a_attrs )
        {
            $l_node = new XMLTag();
            $l_node->setName( $a_name );
            $l_node->setAttrs( $a_attrs );

            if( is_null($this->m_result) )
                $this->m_result = &$l_node;

            $p = &$this->m_current;
            $l_node->parent( $p );

            if( $p != null )
            {
                $p->addChild($l_node);

                unset( $this->m_current );
                $this->m_current = null;
            }

            unset( $this->m_current );
            $this->m_current = &$l_node;
        }

        function EndXMLElement( $a_parser )
        {
            if( isset($this->m_current) )
            {
                $p = &$this->m_current;
                unset( $this->m_current );

                $parent = $p->parent();
                if( is_null($parent) == false ) $this->m_current = $parent;
            }
        }

        function CDATAElement( $a_parser, $a_data )
        {
            if( isset($this->m_current) == false ) return;

            $this->m_current->data( $this->m_current->data() . $a_data );

            //echo '<hr />';
        }

        function PiElement( $a_parser, $a_target, $a_data )
        {
            switch( $a_target )
            {
                default:
                    //echo 'Hello is unknownn PI as'. $a_target .'<br />';
                break;

                case 'php':
                /*	echo 'PI is php: ' . $a_data . '<br />';*/

                    $this->m_current->tagData = eval( $a_data );
                break;
            }

        }
    }
?>

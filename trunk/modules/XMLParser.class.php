<?php

/*
    XML Page Controller
    Copyright (C) 2008 Saponenko Andrew<roguevoo@gmail.com>

    XML Page Controller is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    XML Page Controller is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with XML Page Controller.  If not, see <http://www.gnu.org/licenses/>.
*/

/*
* Include XML parser class
*/
class XMLParser
{
    /**
     * XML parser handler
     */
    private $m_parser			= null;
    private $m_current			= null;
    private $m_result			= null;

    private $m_source_data		= '';
    private $m_source_filename	= '';
    private $m_source_file		= null;

    /**
     * Constructor
     * @param string $a_file File name to parse
     * @param bool $a_parse_now Start parse after construction
     */
    public function  __construct( $a_file, $a_parse_now = false )
    {
        if( is_string( $a_file ) == false )
            return;

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

    /**
     * Parse XML source
     * @param string $a_xml_source XML string
     * @param string $a_encoding Encoding
     * @return XMLTag Return parsed result as XMLTag
     */
    public final function Parse( &$a_xml_source = null, $a_encoding = 'utf-8' )
    {
        if( !$a_xml_source )
        {
            if( $this->m_source_data == '' )
                return;

            $a_xml_source = &$this->m_source_data;
        }

        if( is_string($a_xml_source) == false || $a_xml_source == '' )
            return;

        $this->m_parser = xml_parser_create();

        xml_set_object($this->m_parser, $this);
        xml_set_element_handler($this->m_parser, 'StartXMLElement', 'EndXMLElement');
        xml_set_character_data_handler($this->m_parser, 'CDATAElement');
        xml_set_processing_instruction_handler($this->m_parser, 'PiElement');

        xml_parser_set_option( $this->m_parser, XML_OPTION_CASE_FOLDING,	0);
        xml_parser_set_option( $this->m_parser, XML_OPTION_SKIP_TAGSTART,	0);
        xml_parser_set_option( $this->m_parser, XML_OPTION_SKIP_WHITE,		0);
        xml_parser_set_option( $this->m_parser, XML_OPTION_TARGET_ENCODING,	$a_encoding);


        if ( !xml_parse($this->m_parser, $a_xml_source ) )
        {
            die(sprintf("XML error: %s at line %d",
                xml_error_string(xml_get_error_code( $this->m_parser )),
                xml_get_current_line_number( $this->m_parser )));
        }

        xml_parser_free($this->m_parser);

        return $this->m_result;
    }

    /* THIS FUNCTION FOR INNER USAGE ONLY */
    public function StartXMLElement( $a_parser, $a_name, $a_attrs )
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

    /* THIS FUNCTION FOR INNER USAGE ONLY */
    public function EndXMLElement( $a_parser )
    {
        if( isset($this->m_current) )
        {
            $p = &$this->m_current;
            unset( $this->m_current );

            $parent = $p->parent();
            if( is_null($parent) == false ) $this->m_current = $parent;
        }
    }

    /* THIS FUNCTION FOR INNER USAGE ONLY */
    public function CDATAElement( $a_parser, $a_data )
    {
        if( isset($this->m_current) == false ) return;

        $this->m_current->data( $this->m_current->data() . $a_data );
    }

    /* THIS FUNCTION FOR INNER USAGE ONLY */
    public function PiElement( $a_parser, $a_target, $a_data )
    {
        switch( $a_target )
        {
            default:
                //TODO Insert PI functions
            break;
        }

    }
}
?>

<?php


/**
 * Description of XMLComposer
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */

class MutateField
{
    var $name = '';
    var $node = null;
    var $data = null;
}

class DOMMutator
{
    var $m_document;
    var $m_fields;
    var $m_data;

    public function __construct()
    {
        $this->m_document = null;
        $this->m_fields = array();
        $this->m_data = array();

    }

    private function _searchFields( DOMNode & $a_node )
    {
        switch( $a_node->nodeType )
        {
            case XML_COMMENT_NODE:

                if( preg_match('/[\[](\w+)[\]]/im', $a_node->textContent) )
                {
                    $key = preg_replace('/[\[](\w+)[\]]/im', '${1}', $a_node->textContent);
                    if( $key && strlen($key) )
                    {
                        $key = strtoupper($key);
                        if( isset( $this->m_data[ $key ] ) )
                        {
                            $this->m_fields[ $key ] = new MutateField();
                            $f =& $this->m_fields[ $key ];

                            $f->data = $this->m_data[ $key ];
                            $f->node = $a_node;
                            $f->name = $key;
                        }
                    }
                }
                break;


            case XML_DOCUMENT_NODE:
            case XML_ELEMENT_NODE:
                foreach( $a_node->childNodes as $child )
                {
                    $this->_searchFields( $child );
                }
                break;
        }
    }

    private function _mutate( array & $a_data )
    {
        if( !$this->m_document )
            return false;

        //echo 'Do mutation';
        if( !count($a_data) )
            return true;
           
        $this->m_data = array();
        foreach( $a_data as $k => $val )
            $this->m_data[ strtoupper($k) ] = $val;

        $this->m_fields = array();
        $this->_searchFields( $this->m_document );

        foreach( $this->m_fields as $v )
        {
            $newnode = null;

            switch( gettype($v->data) )
            {
                case "boolean":
                case "integer":
                case "double":
                case "float":
                case "string":
                {
                    $newnode = $this->m_document->createTextNode( $v->data );
                }
                break;

                default:
                {
                    $newnode = $this->m_document->createTextNode( '[Bad data]' );
                }
                break;
            }

            if( !$newnode ) continue;

            if( !$v->node->parentNode ) continue;

            $v->node->parentNode->replaceChild( $newnode, $v->node );
        }
        //echo 'Do mutation';
        return true;
    }

    public function fromFile( $a_file, array & $a_data )
    {
        if( $a_file && is_string($a_file) && file_exists($a_file) )
            return $this->fromString( file_get_contents($a_file), $a_data);
    }

    public function fromString( $a_str, array & $a_data )
    {
        if( !$a_str || !is_string($a_str) || !strlen($a_str) )
            return false;

        $d = new DOMDocument();
        if( !$d->loadXML( $a_str ) )
            return false;

        return $this->fromDOM($d,$a_data);
    }

    public function fromDOM( DOMNode & $a_dom, array & $a_data )
    {
        if( !$a_dom || !count( $a_data ) )
            return false;

        unset( $this->m_document);

        if( $a_dom->nodeType != XML_DOCUMENT_NODE )
            $this->m_document = $a_dom->ownerDocument;
        else
            $this->m_document = $a_dom;

        
        return $this->_mutate( $a_data );
    }
}
?>

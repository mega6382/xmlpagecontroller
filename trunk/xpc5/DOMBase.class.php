<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DOMBase
 *
 * @author Zombie!
 */

class DOMBase
{
    private $m_dom = null;

    public function __construct() {

    }

    public function fromString( $a_string )
    {
        if( empty($a_string) )
            return;

        $d = new DOMDocument();

        if( $d->loadXML( $a_string ) )
            return $this->fromDOM( $d );
    }

    public function fromFile( $a_filename )
    {
        if( empty($a_filename) )
            return;

        if ( !file_exists($a_filename) )
            return;

        $d = new DOMDocument();

        if( $d->load( $a_filename ) )
            return $this->fromDOM( $d );
    }

    public function fromDOM( DOMDocument & $a_dom )
    {
        if( !$a_dom )
            return false;

        return true;
    }
}
?>

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
 * Description of DOMPart.class
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */

 defined( 'XPC_CLASSES' ) or die();

class DOMDataDraft_tagRoot extends DOMElementParser
{
    public function parse()
    {
        return true;
    }
}

class DOMDataDraft_tagEcho extends DOMElementParser
{
    public function parse()
    {
        $id = $this->attr('id');
        echo 'Found ID: ' . $id . '<br />';
        return $id;
    }
}

class DOMDataBase extends DOMParser
{
    protected $m_data;
    public function __construct()
    {
        $walkers = array(
            'echo hello worl' => new DOMDataDraft_tagEcho($this),
            'root' => 'DOMDataDraft_tagRoot'
        );

        $this->registerWalkers($walkers);
    }
}

?>

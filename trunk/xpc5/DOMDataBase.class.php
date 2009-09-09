<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DOMPart.class
 *
 * @author Andrew Saponenko (roguevoo@gmail.com)
 */

class DOMDataDraft_tagRoot extends DOMElementWalker
{
    public function transform()
    {
        return true;
    }
}

class DOMDataDraft_tagEcho extends DOMElementWalker
{
    public function transform()
    {
        $id = $this->attr('id');
        echo 'Found ID: ' . $id . '<br />';
        return $id;
    }
}



class DOMDataBase extends DOMWalker
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

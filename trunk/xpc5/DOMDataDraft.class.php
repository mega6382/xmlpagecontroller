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

class DOMDataDraft_tagRoot extends DOMElementTransformer
{
    public function transform()
    {
        return true;
    }
}

class DOMDataDraft_tagEcho extends DOMElementTransformer
{
    public function transform()
    {
        $id = $this->attr('id');
        echo 'Found ID: ' . $id . '<br />';
        return $id;
    }
}

class DOMDataDraft extends DOMTransformer
{
    protected $m_data;
    public function __construct()
    {
        $this->registerTransformInstance('echo', new DOMDataDraft_tagEcho($this) );
        $this->registerTransformer('root', 'DOMDataDraft_tagRoot');
    }
}

?>

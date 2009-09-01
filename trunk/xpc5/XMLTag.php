<?php

/*
 * class XMLTag
 */
class XMLTag
{
	private $tagName;
	private $tagAttrs;
	private $tagChildren;
	private $tagData;
	private $tagParent;

	public function  __construct()
    {
		$this->tagName		= '';
		$this->tagAttrs		= array();
		$this->tagChildren	= array();
		$this->tagData		= null;
		$this->tagParent	= null;
	}

	public function setAttr( $a_name, $a_value )
	{
		if( is_string( $a_name ) == false ) return;
		if( $a_name == '' ) return;

		$this->tagAttrs[ $a_name ] = $a_value;
	}

	public function setAttrs( $a_array )
	{
		if( is_array( $a_array ) == false ) return;
		$this->tagAttrs = $a_array;
	}

	public function setName( $a_name )
	{
		if( is_string( $a_name ) == false ) return;
		if( $a_name == '' ) return;

		$this->tagName = $a_name;
	}

	public function setData( $a_value )
	{
		$this->tagData = $a_value;
	}

	public function setChildren( $a_arr )
	{
		if( is_array( $a_arr ) == false ) return;
		$this->tagChildren = $a_arr;
	}

	public function setParent( XMLTag & $a_node )
	{
		if( !$a_node ) return;
		$this->tagParent = $a_node;
	}

	public function addChild( XMLTag & $a_node )
	{
		if( !$a_node ) return;
		array_push( $this->tagChildren, $a_node );
	}

	public function delChild( $a_element )
	{
		if( is_string( $a_element ) || is_numeric( $a_element ) )
		{
			if( isset( $this->tagChildren[ $a_element ] ) ) unset( $this->tagChildren[ $a_element ] );
		}

		if( is_a( $a_element, 'XMLTag' ) )
		{
			foreach( $this->tagChildren as $k => $v )
			{
				if( $v == $a_element )
				{
					unset( $this->tagChildren[ $k ] );
					return;
				}
			}
		}
	}

	public function name( $a_name = null )
	{
		if( $a_name )
		{
			$this->setName( $a_name );
			return;
		}
		return $this->tagName;
	}

	public function attr( $a_name = null, $a_value = null )
	{
		if( is_null($a_name) ) {
			return $this->tagAttrs;
		}

		if( is_null($a_value) ) {
			return ( isset($this->tagAttrs[$a_name]) ) ? $this->tagAttrs[ $a_name ] : null;
		} else {
			$this->setAttr( $a_name, $a_value );
		}
	}

	public function data( $a_data = null )
	{
		if( is_null( $a_data ) == true ) return $this->tagData;

		$this->tagData = $a_data;
	}

	public function children( $a_child = null )
	{
		if( is_null($a_child) ) return $this->tagChildren;

		if( is_a( $a_child, 'XMLTag') ) $this->addChild( $a_child );
		if( is_array( $a_child ) )
		{

		}
		$this->setParent($a_child);
	}

	public function child( $a_child = null )
	{
		return $this->children( $a_child );
	}

	public function parent( XMLTag & $a_parent = null )
	{
		if( is_null($a_parent) == true ) return $this->tagParent;
		$this->setParent($a_parent);
	}
}

?>

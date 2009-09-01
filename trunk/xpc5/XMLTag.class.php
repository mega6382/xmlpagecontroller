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

/**
 * XMLTag class (php5)
 * 
 * This class presente XML tag as php object
 *
 * @author Zombie! <roguevoo@gmail.com>
 * @filesource XMLTag.class.php
 *
 */
class XMLTag
{
	/**
	 * Tag name
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $m_name;

	/**
	 * Array of tag attributes.
	 * One element must look at array( 'attrname' => 'attrvalue' )
	 *
	 * @access private
	 *
	 * @var array $m_attrs
	 */
	private $m_attrs;

	/**
	 * Store childrens of tag
	 * Array of XMLTag's
	 *
	 * @access private
	 *
	 * @var array
	 */
	private $m_children;

	/**
	 * Store data between tag
	 *
	 * @access private
	 *
	 * @var string
	 */
	private $m_data;

	/**
	 * Store XMLTag of parent tag
	 *
	 * @access private
	 *
	 * @var XMLTag
	 */
	private $m_parent;

	/**
	 * Class constructor
	 *
	 * @access public
	 */
	public function  __construct()
    {
		$this->m_parent		= '';
		$this->m_attrs		= array();
		$this->m_children	= array();
		$this->m_data		= null;
		$this->m_parent		= null;
	}

	/**
	 * Set tag attribute value
	 *
	 * @access public
	 *
	 * @param string $a_name A tag attribute name
	 * @param string $a_value A tag attribute value
	 *
	 * @return null
	 */
	public function setAttr( $a_name, $a_value )
	{
		if( is_string( $a_name ) == false || empty( $a_name ) ) return;
		$this->m_attrs[ $a_name ] = $a_value;
	}

	/**
	 * Set tag attributes
	 *
	 * @access public
	 *
	 * @param array $a_array Array of values
	 *
	 * @return null
	 */
	public function setAttrs( array $a_array )
	{
		if( is_array( $a_array ) == false ) return;
		$this->m_attrs = $a_array;
	}

	/**
	 * Set tag name
	 *
	 * @access public
	 *
	 * @param string $a_name A tag name
	 * @return null
	 */
	public function setName( $a_name )
	{
        if( is_string($a_name) && strlen($a_name) ) $this->m_name = $a_name;
	}

	/**
	 * Set tag data
	 *
	 * @access public
	 *
	 * @param mixed $a_data A tag data
	 */
	public function setData( $a_data )
	{
		$this->m_data = $a_value;
	}

	/**
	 * Set tag childrens
	 *
	 * @access public
	 *
	 * @param array $a_childs Array of tag childrens
	 *
	 * @return null
	 */
	public function setChildrens( $a_childs )
	{
		if( is_array( $a_childs ) == false )
            return;

		$this->m_children = $a_childs;
	}

	/**
	 * Set parent of tag
	 *
	 * @access public
	 *
	 * @param XMLTag $a_parent
	 *
	 * @return null
	 */
	public function setParent( XMLTag & $a_parent )
	{
		if( !$a_parent )
            return;

        unset( $this->m_parent );
		$this->m_parent = $a_parent;
	}

	/**
	 * Add tag children
	 *
	 * @access public
	 *
	 * @param XMLTag $a_child
	 *
	 * @return null
	 */
	public function addChild( XMLTag & $a_child )
	{
		if( !$a_child ) return;

		$a_child->setParent( $this );

		array_push( $this->m_children, $a_child );
	}

    /**
	 * Add tag children
	 *
	 * @access public
	 *
	 * @param XMLTag $a_child
	 *
	 * @return null
	 */
	public function addChildren( XMLTag & $a_child )
	{
		$this->addChild( $a_child );
	}

	/**
	 * Remove tag children
	 *
	 * @access public
	 *
	 * @param mixed $a_element Can't be string, int, XMLTag
	 *
	 * @return null
	 */
	public function delChild( $a_element )
	{
		if( is_string( $a_element ) || is_numeric( $a_element ) )
		{
			if( isset( $this->m_children[ $a_element ] ) ) unset( $this->m_children[ $a_element ] );
		}

		if( is_a( $a_element, 'XMLTag' ) )
		{
			foreach( $this->m_children as $k => $v )
			{
				if( $v == $a_element )
				{
					unset( $this->m_children[ $k ] );
					return;
				}
			}
		}
	}

    /**
	 * Remove tag children
	 *
	 * @access public
	 *
	 * @param mixed $a_element Can't be string, int, XMLTag
	 *
	 * @return null
	 */
	public function delChildren( $a_element )
	{
        $this->delChild( $a_element );
	}

	/**
	 *  Get or set tag name
	 *
	 * @access public
	 *
	 * @param string $a_name Name to set
	 *
	 * @return mixed Return string if $a_name != null, else null
	 */
	public function name( $a_name = null )
	{
		if( is_null( $a_name ) )
			return $this->m_name;

		$this->setName( $a_name );
	}

	/**
	 * Get or set tag attribute
	 *
	 * @access public
	 *
	 * @param string $a_name A attribute name
	 * @param mixed $a_value A atribute value
	 *
	 * @return mixed Return string if $a_name != null and element is found, else null
	 */
	public function attr( $a_name = null, $a_value = null )
	{
		if( is_null($a_name) )
			return $this->m_attrs;

		if( is_null($a_value) )
            return key_exists($a_name, $this->m_attrs) ? $this->m_attrs[ $a_name ] : null;
		else
			$this->setAttr( $a_name, $a_value );
	}

	/**
	 * Get or set tag data
	 *
	 * @access public
	 *
	 * @param mixed $a_data Tag data
	 *
	 * @return mixed Return string if $a_data != null, else null
	 */
	public function data( $a_data = null )
	{
		if( is_null( $a_data ) )
            return $this->m_data;

		$this->m_data = $a_data;
	}

	/**
	 * Get or set tag childrens
	 *
	 * @access public
	 *
	 * @param mixed $a_child A children/s to add. Can't be XMLTag of array of XMLTag's
	 * @return mixed Return array of XMLTag's if $a_child != null, else null
	 */
	public function children( $a_child = null )
	{
		if( is_null($a_child) )
			return $this->m_children;

		if( is_a( $a_child, 'XMLTag') )
			$this->addChild( $a_child );

		if( is_array( $a_child ) )
			$this->setChildrens($a_child);
	}

	/**
	 * Get or set tag childrens
	 *
	 * @access public
	 *
	 * @param mixed $a_child A children/s to add. Can't be XMLTag of array of XMLTag's
	 * @return mixed Return array of XMLTag's if $a_child != null, else null
	 */
	public function child( $a_child = null )
	{
		return $this->children( $a_child );
	}

	/**
	 * Get or set tag parent
	 *
	 * @access public
	 *
	 * @param mixed $a_parent Tag parent as XMLTag
	 *
	 * @return mixed Return XMLTag parent of tag if is have it, else null
	 */
	public function parent( XMLTag & $a_parent = null )
	{
		if( is_null($a_parent) )
            return $this->m_parent;
            
		$this->setParent($a_parent);
	}
}
?>

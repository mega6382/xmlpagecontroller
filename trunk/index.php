<?php

/*
    This file is part of XML Page Controller.

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

    /* Get current PHP version*/
    $php_ver = phpversion();

    /* Get major number of PHP version */
    $php_ver_major = substr($php_ver, 0, 1 );

    /* Switch between PHP version*/
    switch( $php_ver_major )
    {
        case '4':
        {
            // Include (XML Page controller) class for PHP4 version
            include "modules/xpc4.php";
        }
        break;

        case '5':
        {
            // Include (XML Page controller) class for PHP5 version
            include "modules/xpc5.php";
        }
        break;
    }

    $options = array(
        'index'     =>  'example.xml',                                      // Where to locate document with the content of pages.
        'lang'      =>  isset( $_GET['lang'] ) ? $_GET['lang'] : 'en',      // Language which will be displayed on the pages.
        'output'    =>  true                                                // Output result to browser when class has created ( if false, use echo $page->out() )
    );

    $page = new XMLPage( $options ); // Create a new instance of class XMLPage with given arguments.
?>
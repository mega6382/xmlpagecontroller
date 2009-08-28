<?php

	include "xmlpagecontroller.php";    // Include "XPC" library
	$page = new xmlpage(                // Create a new instance of class xmlpage with given arguments.
		'example.xml',                  // Where to locate document with the content of pages.
		"en",                           // Language which will be displayed on the pages.
		true                            // Turn debugging information generation.
	);

	echo $page->out();                  // Output result to browser

?>
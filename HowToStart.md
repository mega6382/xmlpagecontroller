# How to start #

Before we start. The author believes that you have configured web server to work. Also, the author believes that you have to know the basics of Web server and what is XML document.

As is well known that "XML page controller" further XPC, written in PHP. First, it is essential to create an index file which should include the necessary library which will start the entire procedure.

#### Example file "index.php". ####
```
<?php
	include "xmlpagecontroller.php"; // Include "XPC" library
	$page = new xmlpage( array(		// Create a new instance of class xmlpage with given arguments.
		'index' => 'example.xml',		// Where to locate document with the content of pages.
		'lang' => 'ru',			// Language which will be displayed on the pages. Default is ("en")
		'debug' => true			// Turn debugging information generation. Default is (false)
	) );
	echo $page->out();		// Output result to browser
?>
```


At the end programming, because the more you want to set XML document in our case example.xml


#### Example file "example.xml". ####
```
<?xml version="1.0" encoding="utf-8"?>
<root>
	<container type="inline">
	<![CDATA[
	    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="[LANG]" lang="[LANG]">
	        <head>
	            <title>[HEADER_TITLE]</title>
            </head>
	    <body>[CONTENT]</body>
	    </html>
	]]>
	</container>
	<pages>
		<out name="header_title">Hello World title</out>
		<out name="content">Hello World!!!</out>
	</pages>
</root>
```

As result of execution you can see on browser "Hello world!!!"

## Step by step ##
Now step by step we will consider how to set up XML document. I tell on each line.

---

```
<?xml version="1.0" encoding="utf-8"?>
```
This tag, you will probably already familiar because he must apply for each XML file, it said parser rules on what must be read the entire document.

---

```
<root>
...
</root>	
```
This is a required root tag, XPC is beginning to generate a whole page on the basis of its contents.

---

```
<container type="inline">
...
</container>
```
Tag "container" mustbe follows in any XML configuration file, one tag per file. This is primary container what XPC output first.
```
<![CDATA[
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="[LANG]" lang="[LANG]">
       <head>
          <title>[HEADER_TITLE]</title>
       </head>
       <body>[CONTENT]</body>
    </html>
]]>
```
In our case it simple HTML documen with fields for templatinng. e.g. [[HEADER\_TITLE](HEADER_TITLE.md)], [[CONTENT](CONTENT.md)]. If this tag not exist in configfile or he is empty XPC fill this "[[CONTENT](CONTENT.md)]" by default.

---

```
<pages>...</pages>
```
Now all content of configfile start beetween this tag.

---

```
<out name="header_title">Hello World title</out>
```
The "out" tag mean, what XPC replace [[HEADER\_TITLE](HEADER_TITLE.md)] in "container" tag by "Hello World title" text.
```
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="[LANG]" lang="[LANG]">
   <head>
      <title>Hello World title</title>
   </head>
   <body>[CONTENT]</body>
</html>
```

---

```
<out name="content">Hello World!!!</out>
```
And same way XPC replace [[CONTENT](CONTENT.md)] in "container" tag by "Hello World!!!" text, and result of his work is.
```
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="[LANG]" lang="[LANG]">
   <head>
      <title>Hello World title</title>
   </head>
   <body>Hello World!!!</body>
</html>
```
### Download example. ###
http://xmlpagecontroller.googlecode.com/files/xpc_example.zip
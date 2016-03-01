# Tags #
Here I will tell tags used by XPC in the XML file.
**Important**: All next tags must be located between `<root>` tag or his be ignored.
## How to read ##
> ### `<Tag name>`, `<Tag synonym>`, ... ###
> _Description of tag_
    * **Accepted attributes name** - _Description of attribute_
      * **_Posible value_** - _Description of value_
    * ...

```
 <!-- Code description -->
 <root>
 ...
 <!-- Markup examples -->
 ...
 </root>
```

---

### `<option>` ###
_Option to configure XPC in XML file (his can be changed), must be contained between `<options>`..`</options>` tags else ignored._
  * **name** - _Name of option (Option ignored if name is not set)_
    * **_searchdir_** - _Directory where XPC must fearch files_
    * **_language_** - _Using this language to output_
    * **_lang_** - _Same..._


```
<!-- Directory search option -->
<options>
   <option name="searchdir">/var/home/www/mysite/</option>
   <option name="searchdir">/var/home/www/xml/</option>
   <option name="searchdir">/var/home/www/php/</option>
   <option name="searchdir">/var/home/www/js/</option>
   <option name="searchdir">/var/home/www/css/</option>
</options>
```

---

### `<container>` ###
_Primary template, all fields contained in this tag will be filled by custom values, if container not set or is empty XPC assign this by default:_
`<container>[CONTENT]</container>`
  * **type** - _Type of container_
    * **inline<sup>(default)</sup>** - _Value locate (between this tag)_
    * **file** - _Value locate in file (file name must be between this tag)_
    * **template** - _Value generated from another XML document (file name must be between this tag)_

```
<!-- Simple container -->
<container>...</container>

<!-- The same -->
<container type="inline">...</container>

<!-- Container get data from file -->
<container type="file">templates/template1.htm</container>

<!-- Container get data as result of another XPC work -->
<container type="template">xml/left_frame.xml</container>
```

---

### `<template>` ###
_Named template, used by `<out>` tag, he help repeat multiple values, must be contained between `<templates>...</templates>` tags else ignored._
  * **name** - _Name of template_
  * **type** - _Type of template_
    * **inline (default)** - _Template locate between this tag_
    * **file** - _Template locate in file (file name must be between this tag)_
    * **template** - _Template generated from another XML document (file name must be between this tag)_

```
<templates>
   <!-- Simple template -->
   <template name="header1"><![CDATA[<h1>[CAPTION]</h1>]]></template>

   <!-- The same template -->
   <template name="header2" type="inline"><![CDATA[<h1>[CAPTION]</h1>]]></template>

   <!-- Advanced template -->
   <template name="header3" type="inline">
   <![CDATA[
      <div onClick="[CLICK]">
         <div class="Header">[HEADER]</div>
         <div class="Body"><h2>[CAPTION]</h2><br />[BODY]</div>
         <div class="Footer">[FOOTER]</div>
         <hr />
      </div>
   ]]>
   </template>

   <!-- Template get data from file -->
   <template name="header4" type="file">templates/template1.htm</template>

   <!-- Template get data as result of another XPC work -->
   <template name="header5" type="template">xml/header.xml</template>
</templates>
```

---

## `<out>`, `<value>`, `<frame>` ##
_Values containing in this tags fill fields in "container" or "template" tags._
  * **name** - Name of field located in Primary template to fill, if is not set default name will be [[CONTENT](CONTENT.md)].
  * **type** - _Type of field_
    * **inline<sup>(default)</sup>** - _Value locate between this tag_
    * **file** - _Value locate in file (file name must be between this tag)_
    * **template** - _Value generated from another XML document (file name must be between this tag)_
  * **template** - _Use named templates (must contain template name)_

```
 <!-- Imagine that we have such a container -->
 <container>
 <![CDATA[
    <b>[HEADER]</b>
    <br />
    [BODY]
    <br />
    <i>[FOOTER]</i>
 ]]>
 </container>
 <!-- Imagine that we had such patterns -->
 <templates>
    <template name="bodyRed">
    <![CDATA[
       <div style="color:#FF0000">[CAPTION]</div>
    ]]>
    </template>
    <template name="bodyGreen">
    <![CDATA[
       <div style="color:#00FF00">[CAPTION]</div>
    ]]>
    </template>
 </templates>

 <!-- Simple field filling  -->
 <out name="header">My header</out>

 <!-- The same (same type using) -->
 <out name="header" type="inline">My header</out>

 <!-- The same (synonym 'value' using) -->
 <value name="header">My header</value>

 <!-- The same (synonym 'frame' using) -->
 <frame name="header">My header</frame>

 <!-- Use file -->
 <out name="header" type="file">templates/template1.htm</out>
 
 <!-- Use result of another XPC work -->
 <out name="header" type="template">xml/mainHeader.xml</out>

 <!-- Use named template (Header will be red) -->
 <out name="header" template="bodyRed">
    <out name="caption">Header is red</out>
 </out>

 <!-- Use named template (Header will be green) -->
 <out name="header" template="bodyGreen">
    <out name="caption">Header is green</out>
 </out>

 <!-- Load body from file and fill his green -->
 <out name="body" template="bodyGreen">
    <out name="caption" type="file">templates/thebody.htm</out>
 </out>

 <!-- Get footer as result of another XPC and fill his red -->
 <out name="footer" template="bodyGreen">
    <out name="caption" type="template">xml/thefooter.xml</out>
 </out>
```

---

## `<if>` ##
_The "if" tag is condition, if condition in this tag is true, XPC include data beetween "if" tag, else XPC start parse next tag._

  * **isset** - _if value is set in variable XPC pass the childrens of this tag to parse next, else skip it_
  * **noset** - _if value is not set in variable XPC pass the childrens of this tag to parse next, else skip it_
  * **param** - _parameters like (id == 1) or (id > 10), if is true XPC pass the childrens of this tag to parse next._

By "if" tag get data to comparison from global variables of PHP runtime like $_GET, $_POST, $_REQUEST, by default is $_GET.If you want what "if" tag test condition for another variable like $_POST you must point it like this:_
```
<!-- Test $_POST array -->
<if:post ...>...</if:post>

<!-- Test $_PUT array -->
<if:put ...>...</if:put>

<!-- Test $_REQUEST array -->
<if:request ...>...</if:request>

<!-- Test $_FILE array -->
<if:file ...>...</if:file>

<!-- Test $_SERVER array -->
<if:server ...>...</if:server>

<!-- Test $_SESSION array -->
<if:session ...>...</if:session>
 
<!-- Test $_ENV array -->
<if:env ...><if:env>

<!-- Test $_SESSION array -->
<if:session ...>...</if:session>

<!-- Test is set condition -->
<if isset="id">
    <!-- Pass this childrent tag if $_GET["id"] value is set. -->
    <out name="content">ID is set</out>
</if>

<!-- Test no set condition -->
<if noset="id">
    <!-- Pass this childrent tag if $_GET["id"] value is not set. -->
    <out name="content">ID is set</out>
</if>
```
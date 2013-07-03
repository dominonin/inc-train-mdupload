<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<?php 

function special_formatting($input) {
    $output = htmlspecialchars($input, ENT_QUOTES);
    $output = str_replace(array('  ', "\n"), array('&nbsp;&nbsp;', '<br>'), $output);
    return str_replace('&nbsp; ', '&nbsp;&nbsp;', $output);
}

function isUniqueFileName($filename) {
        if($filename == '') {
            return false;
        }

        if($filename == 'Metadata') {  // Prevent use of common default
            return false;
        }

        if($filename == 'idp-metadata.xml') { // Prevent use of common default
            return false;
        }

        if($filename == 'metadata.xml') { // Prevent use of common default
            return false;
        }
       return true;
   }


function isValidMetadataFile($filename) {

        $xmllintCall = "xmllint --noout --schema /opt/xmlschema/saml-schema-metadata-2.0.xsd {$filename}";

        exec($xmllintCall, $xmllintOutput, $xmllintFeedback);

        if($xmllintFeedback == '0') {

            return true;

        } else {

             //echo $xmllintOutput;
             return false; 
        }

}


if(isset($_POST['submit'])){
   //process
    //   $allowed_filetypes = array('.xml','.txt');           These will be the types of file that will pass the validation.
   $max_filesize = 40960;                              // Maximum filesize in BYTES (currently 40KB).
   $upload_path = '/opt/testshib-user-metadata/';                  // The place the files will be uploaded to, don't forget trailing slash '/'

   $filename = $_FILES['userfile']['name'];             // Get the name of the file (including file extension).
   $ext = substr($filename, strrpos($filename,'.'));      // Get the extension from the filename.
 
   $errors = "";
   
// We don't want to bother checking file types
//   if(!in_array($ext,$allowed_filetypes))
//      die('<font color=\'red\'>The file type you attempted to upload is not allowed.  You must upload a .xml file.</font>');
 
   if(filesize($_FILES['userfile']['tmp_name']) > $max_filesize)
      $errors .= '<font color=\'red\'>The file you attempted to upload is too large.  Please ensure the file is less than 40k and try again.</font><br/>';

   if(!isUniqueFileName($filename))
       //die("{file:\"$filename\",type:\"xml\",width:\"\",height:\"\",error:\"you are an idiot\"}");
      $errors .= '<font color=\'red\'>The filename you chose, "' . $filename . '", is a common default file name for metadata.  Please rename the file to something unique and then reattempt this upload.</font><br/>';

   if(!isValidMetadataFile($_FILES['userfile']['tmp_name']))
      $errors .= '<font color=\'red\'>The file you are attempting to upload is not valid metadata.  Please correct any errors and try again.</font><br/>';

   if(!is_writable($upload_path))
      $errors .= '<font color=\'red\'>Something horrible happened.  Please contact the Shibboleth Users list.</font><br/>';

   if(move_uploaded_file($_FILES['userfile']['tmp_name'],$upload_path . $filename))
      {
         echo 'Your metadata was uploaded successfully.  Please proceed to <a href="configure.html">configuration</a> and <a href="test.html">testing</a>. <br /> <br />';                        // It worked
         echo 'Your metadata filename is <b>' . $filename . '</b>.  Please keep this filename so you can overwrite your metadata file in the event you need to update your entry. <br /><br />';
         echo 'Your complete metadata is below.  You don\'t need to understand the entire file, but it\'s helpful to recognize your entityID in the first element below, as well as your provider\'s certificate.  <a href="https://wiki.shibboleth.net/confluence/display/SHIB2/Home" target="_new">The Shibboleth wiki</a> can help you <a href="https://wiki.shibboleth.net/confluence/display/SHIB2/Metadata" target="_new">learn about metadata</a>.<br /> <br /> <br /> <p><tt>'; 

         // Build display array of metadata
         exec('xmlstarlet fo -o ' . $upload_path . $filename, $displayMetadata);
	 foreach ($displayMetadata as $value) {        
         echo (special_formatting($value) . '<br />');
         }
         echo '</tt></p> <br />';
         shell_exec('sleep 3; sh /opt/cpm.sh'); // Build metadata file after giving a moment to ensure upload completed
      } else {
          die('<font color=\'red\'>There was an error during the file upload.  Please try again.</font>');     // It failed
      }



} else{
?>
<html lang="en-US">
<head>
        <title>TestShib Two</title>
        <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/dojo/1.9.0/dijit/themes/tundra/tundra.css">
        <link rel="stylesheet" type="text/css"
                   href="styles.css"
                   title="styles">
</head>


<body style="margin: 0px; font-family: sans-serif" class='tundra'>
    <script data-dojo-config="async: 1, parseOnLoad: true, dojoBlankHtmlUrl: '/arcit/blank.html'"
    src="//ajax.googleapis.com/ajax/libs/dojo/1.9.0/dojo/dojo.js"></script>
<center>
    
<!-- header bar -->

<div id="header"><a href="http://www.shibboleth.net/"><img border="0" align="left" src="testshibtwo.jpg"></a></div>
<div id="main">

<!--- start content --->

<br />
<br />
<form method="post" action="procUpload.php" id="myForm"
enctype="multipart/form-data">
    <fieldset>
        <legend>Metadata Upload</legend>
        <input name="uploadedfile" type="file" id="uploader"/>
        <input type="submit" label="Submit" data-dojo-type="dijit/form/Button">
    </fieldset>
</form>

</div>
<br>
<?php

}
?>
<font style="font-size:80%; color:#AAAAAA">&copy; Copyright 2006-2013 Internet2.</font>
</center>

</body>
</html>

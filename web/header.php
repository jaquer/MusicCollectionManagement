<?php

/* mcm_html_header - prints an html header (yes, duh) */

function mcm_html_header($params) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Music Collection Management</title>

    <script type="text/javascript" src="ui.js"></script>
    <script type="text/javascript" src="swfobject.js"></script>
    <script type="text/javascript" src="player.js"></script>

    <link rel="stylesheet" type="text/css" href="ui.css">
    <link rel="icon" type="image/png" href="images/favicon.png">
</head>
<body onload="positionToolbar()" class="center">
<h1 id="header" class="center">Music Collection Management</h1>
<?php

}

?>

<?php 
/**
 * functions that may be called from multiple files
 *
 * Computer Science E-75
 * Philip Durbin
 */
//error_reporting(E_ALL | E_STRICT);
require_once('config.php');

function logger($message) {
    $to_log = date(c) . " $message";
    file_put_contents(LOG_FILE, "$to_log\n", FILE_APPEND);
}

//print errors, if any
function print_errors($errors) { 
    if (isset($errors)) {
        foreach ($errors as $error) {
            print "<p class='errors'>$error</p>\n";
        }
    }
}

// From http://us2.php.net/manual/en/function.print-r.php#87418
function print_r_pre($array) {
    print "<pre>\n";
    print_r($array);
    print "</pre>\n";
}

// print out XHTML at top of page
// page_header is used in "Learning PHP 5" (2004 O'Reilly) page 70
function page_header($title_end, $focus = 'title', $world_readable = false, $title_start = 'Last Minute Plans') {
    // don't redirect user who are already authenticated
    if ((!empty($_SESSION)) && $_SESSION["authenticated"]) {
    }
    // don't redirect if page wants to be world readable
    // (i.e. home page, login page, register page, etc.)
    elseif ($world_readable == true) {
    }
    else {
      // redirect user to home page, using absolute path, per
      // http://us2.php.net/manual/en/function.header.php
      $host = $_SERVER["HTTP_HOST"];
      $path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
      header("Location: http://$host$path/index.php");
      exit;
    }

    $complete_title = "$title_start: $title_end";
    $navigation = '';
    if ((!empty($_SESSION)) && !empty($_SESSION["authenticated"])) { 
        $navigation = "
            <p>
            <a href='index.php'>home</a>
            <a href='locations.php'>locations</a>
            <a href='subscriptions.php'>subscriptions</a>
            <a href='subscribers.php'>subscribers</a>
            <a href='subscribe.php'>subscribe</a>
            <a href='logout.php' class='smaller'>logout $_SESSION[username] </a>
            </p>\n";
    }

    //javascript focus from http://www.w3schools.com/JS/tryit.asp?filename=tryjs_focus
    //idea to use CDATA from http://www.cs75.net/sections/6/src/validation/validation6.phps
    print <<< END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> $complete_title</title>
<link rel="stylesheet" type="text/css" href="styles.css" />
<script type="text/javascript">
//<![CDATA[
function setFocus() {
    document.getElementById("$focus").focus();
    // special processing for Login page
    if ("$title_end" == "Login") {
        if (document.forms.login.user.value == '') {
            document.forms.login.user.focus();
        }
        else {
            document.forms.login.pass.focus();
        }
    }
}
//]]>
</script>
</head>
<body onload="setFocus()">
<img src="sivvus_analog_clock_mini.png" id="title_image" alt="Last Minute Plans logo" />
<h2 id='title'>$complete_title</h2>
$navigation

END;
}

// print out XHTML at bottom of page
function page_footer() {
    print "</body>\n";
    print "</html>\n";
}
?>

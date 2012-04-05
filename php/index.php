<?php 
/**
 * Home page
 *
 * Philip Durbin
 * Computer Science E-75
 * Harvard Extension School
 */

// enable sessions
session_start();
require_once('common.php');
page_header('Home', 'title', true);
if ((!empty($_SESSION)) && $_SESSION["authenticated"]) {
    print "<p>You are logged in.</p>\n";
} else {
    print "<p>Welcome!</p>\n";
    print "<p>Please <a href='login.php'>login</a> or <a href='register.php'>register</a></p>";
}
page_footer();

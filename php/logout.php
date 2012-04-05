<?php 
/**
 * logout.php
 *
 * A simple logout module for all of our login modules.
 *
 * Philip Durbin
 * Computer Science E-75
 * Harvard Extension School
 */

// enable sessions
session_start();

// from http://www.cs75.net/lectures/3/src/login/logout.phps
// delete cookies, if any
setcookie("user", "", time() - 3600);
setcookie("pass", "", time() - 3600);

// log user out
setcookie(session_name(), "", time() - 3600);
session_destroy();
require_once('common.php');
page_header('Logout');
print "<p>You are logged out.</p>\n";
page_footer();

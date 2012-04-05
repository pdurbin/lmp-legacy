<?php 
/**
 * subscribers.php
 *
 * Philip Durbin
 * Computer Science E-75
 * Harvard Extension School
 */

// enable sessions
session_start();
require_once('common.php');
page_header('My Subscribers');
//print_r_pre($_SESSION);
try {
    $dbh = new PDO("sqlite:lmp.db");
    $sql = "SELECT owner,locationid,subscriber,locations.name,locations.description FROM subscriptions LEFT JOIN locations ON locationid = locations.id WHERE owner = '$_SESSION[username]'";
    print "<p>\n";
    foreach ($dbh->query($sql) as $row) {
        //print_r_pre($row);
        print $row['subscriber'] . " is subscribed to " .  $row['owner'] . "'s location: " . $row['name'] . "<br />\n";
    }
    print "</p>\n";
}
catch(PDOException $e)
{
    echo $e->getMessage();
}
page_footer();

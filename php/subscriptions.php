<?php 
/**
 * subscriptions.php
 *
 * Show user's portfolio
 *
 * Philip Durbin
 * Computer Science E-75
 * Harvard Extension School
 */

// enable sessions
session_start();
require_once('common.php');
page_header('My Subscriptions');
//print_r_pre($_SESSION);
//print_r_pre($_POST);
try {
    $dbh = new PDO("sqlite:lmp.db");
    if (!empty($_POST)){
        $sql = "DELETE FROM subscriptions WHERE subscriber = '$_SESSION[username]' AND locationid = '$_POST[locidremove]'";
        $dbh->exec($sql);
    }
    $sql = "SELECT owner,locationid,subscriber,locations.name,locations.description FROM subscriptions LEFT JOIN locations ON locationid = locations.id WHERE subscriber = '$_SESSION[username]'";
    print "<p>\n";
    foreach ($dbh->query($sql) as $row) {
        //print_r_pre($row);
        $locid = $row['locationid'];
        print "<form action='$_SERVER[PHP_SELF]' method='post'>\n";
        print "<input type='hidden' name='locidremove' value='$locid'>\n";
        print "<input type='submit' value='Remove'>\n";
        print $row['subscriber'] . " is subscribed to " .  $row['owner'] . "'s location: " . $row['name'] . "<br />\n";
        print "</form>\n";
    }
    print "</p>\n";
}
catch(PDOException $e)
{
    echo $e->getMessage();
}
page_footer();

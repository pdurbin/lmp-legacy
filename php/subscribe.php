<?php 
/**
 * subscribe.php
 *
 * subscribe to locations
 *
 * Philip Durbin
 * Computer Science E-75
 * Harvard Extension School
 */

// enable sessions
session_start();
require_once('common.php');
page_header('Subscribe');
//print_r_pre($_SESSION);
//print_r_pre($_POST);
try {
    $dbh = new PDO("sqlite:lmp.db");
    if (!empty($_POST)) {
        if (isset($_POST['locidsubscribe'])) {
            $sql = "INSERT INTO subscriptions (locationid, subscriber, enabled) VALUES('$_POST[locidsubscribe]','$_SESSION[username]', 'true');";
            $dbh->exec($sql);
        }
    }
    $sql = "SELECT owner,locationid,subscriber,locations.name,locations.description FROM subscriptions LEFT JOIN locations ON locationid = locations.id WHERE subscriber = '$_SESSION[username]'";
    $locations_subscribed = array();
    foreach ($dbh->query($sql) as $row) {
        //print_r_pre($row);
        $locations_subscribed[] = $row['locationid'];
    }
    //print_r_pre($locations_subscribed);
    $sql = "SELECT * FROM locations WHERE owner != '$_SESSION[username]' ORDER BY owner";
    foreach ($dbh->query($sql) as $row) {
        //print_r_pre($row);
        //$subscribed = in_array($row['id'], $locations_subscribed) ? "(already subscribed)" : "";
        $locid = $row['id'];
        if (in_array($row['id'], $locations_subscribed)) {
            // already subscribed
        }
        else {
            print "<form action='$_SERVER[PHP_SELF]' method='post'>\n";
            print "<input type='hidden' name='locidsubscribe' value='$locid' />";
            print "<input type='submit' name='subscribe' value='Subscribe' />";
            //print $row['owner'] . "'s location: " . $row['name'] . ": " . $row['description'] . " $subscribed<br />\n";
            print $row['owner'] . "'s location: " . $row['name'] . ": " . $row['description'] . "<br />\n";
            print "</form>\n";
        }
    }
}
catch(PDOException $e)
{
    echo $e->getMessage();
}
page_footer();

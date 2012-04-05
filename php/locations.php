<?php 
/**
 * portfolio.php
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
page_header('My Locations', 'location_add');
//print_r_pre($_SESSION);
$dbh = new PDO("sqlite:lmp.db");
//$sql = "SELECT owner,locationid,subscriber,locations.name,locations.description FROM subscriptions LEFT JOIN locations ON locationid = locations.id WHERE owner = '$_SESSION[username]'";

if (!empty($_POST)) {
    //print_r_pre($_POST);
    if (isset($_POST['locnameadd'])) {
        $sql = "INSERT INTO locations (owner, name, description) VALUES('$_SESSION[username]', '$_POST[locnameadd]', '$_POST[locdesc]')";
        $dbh->exec($sql);
    }
    elseif (isset($_POST['locidremove'])) {
        $sql = "DELETE FROM locations WHERE owner = '$_SESSION[username]' AND id = '$_POST[locidremove]'";
        $dbh->exec($sql);
        $sql = "DELETE FROM subscriptions WHERE locationid = '$_POST[locidremove]'";
        //print $sql;
        $dbh->exec($sql);
    }
    //print $sql;
    //try {
        //$dbh->exec($sql);
        //print $dbh->errorCode();
        //$arr = $dbh->errorInfo();
        //print_r_pre($arr);
    //}
    //catch(PDOException $e) {
    //    echo $e->getMessage();
    //}
}

$sql = "SELECT owner,locationid,subscriber,locations.name,locations.description FROM locations LEFT JOIN subscriptions ON locationid = locations.id WHERE owner = '$_SESSION[username]'";
foreach ($dbh->query($sql) as $row) {
    //print_r_pre($row);
    //print $row['name'] .': '. $row['description'] . " is subscribed to by " . $row['subscriber'] . "<br />\n";
    //$locations = array(); 
    $name = $row['name'];
    $description = $row['description'];
    $locationid = $row['locationid'];
    //print "$name: $description<br />\n";
    $locations[$name]['name'] = $name;
    $locations[$name]['description'] = $description;
    $locations[$name]['locationid'] = $locationid;
    if ($row['subscriber'] ) {
        $locations[$name]['subscribers'][] = $row['subscriber'];
    }
}
if (isset($locations)) {
    //print_r_pre($locations);
    foreach ($locations as $location) {
        $locname = $location['name'];
        $locid = $location['locationid'];
        print "<form action='$_SERVER[PHP_SELF]' method='post'>\n";
        //print "<input type='hidden' name='locnameremove' value='$locname'>";
        print "<input type='hidden' name='locidremove' value='$locid'>";
        print "<input type='submit' name='remove' value='Remove'>";
        print "{$location['name']} ({$location['description']})";
        if (count($location['subscribers']) > 0) {
            //print count($location['subscribers']) . "<br /> \n";
            print " is subscribed to by ";
            foreach ($location['subscribers'] as $subscriber) {
                print "$subscriber ";
            }
        }
        print "<br />\n";
        print "</form>\n";
    }
}
else {
    print "<p>You have not set up any locations yet.</p>\n";
}

print "<hr />\n";
print "<form method='post'>\n";
print "<p>Add a new location<p>\n";
print "Location name <input type='text' name='locnameadd' id='location_add'><br />\n";
print "Location description <input type='text' name='locdesc'><br />\n";
print "<input type='submit' name='submit' value='Add location'>\n";
print "</form>\n";
page_footer();

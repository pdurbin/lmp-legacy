#!/usr/bin/php
<?php
require_once('config.php');
$login = TW_USER . ":" . TW_PASS;
$url = "http://twitter.com/account/rate_limit_status.json";
$tw = curl_init();
// CURL_POST* idea from http://twitter.slawcup.com/twitter.class.phps
curl_setopt($tw, CURLOPT_URL, $url);
curl_setopt($tw, CURLOPT_USERPWD, $login);
curl_setopt($tw, CURLOPT_RETURNTRANSFER, TRUE);
$result = curl_exec($tw); 
//print_r($result);
$decoded = json_decode($result);
print_r($decoded);
if ($decoded->error ) {
        print $decoded->error . "\n";
}
else {
        print "no errors\n";
}

/*
output:

stdClass Object
(
    [reset_time_in_seconds] => 1261178016
    [remaining_hits] => 150
    [hourly_limit] => 150
    [reset_time] => Fri Dec 18 23:13:36 +0000 2009
)
no errors

*/

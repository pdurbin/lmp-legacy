#!/usr/bin/php
<?php
require_once('common.php');
logger("Processing tweets...");
$login = TW_USER . ":" . TW_PASS;
$tweets = "http://twitter.com/direct_messages.json";
#http://papermashup.com/using-the-twitter-api/
$tw = curl_init();
curl_setopt($tw, CURLOPT_URL, $tweets);
curl_setopt($tw, CURLOPT_USERPWD, $login);
curl_setopt($tw, CURLOPT_RETURNTRANSFER, TRUE);
// cast to stdClass Object: http://www.tierra-innovation.com/blog/2009/06/26/php-tip-inline-stdclass-object-creation/
// INSERT INTO "tweets" VALUES(2,'philipdurbin',630662366,'heading to the zoo when it opens',0);
//
/*
$tweets_to_insert = array(

    0 => (object) array(
        'sender_screen_name' => 'philipdurbin',
        'id' => '630662366',
        'text' => 'heading to the zoo when it opens',
    ),

    1 => (object) array(
        'sender_screen_name' => 'philipdurbin',
        'id' => '987654321',
        'text' => 'no valid location here',
    ),

    2 => (object) array(
        'sender_screen_name' => 'tobias7777',
        'id' => '987654322',
        'text' => 'leaving for griggs park',
    ),

    3 => (object) array(
        'sender_screen_name' => 'chris9999',
        'id' => '987654323',
        'text' => 'going to skyline park',
    ),

);
print_r($tweets_to_insert);
*/
//exit;

$twi = curl_exec($tw);
$tweets_to_insert = array();
//var_dump($twi);
$tweets_to_insert = json_decode($twi);
//var_dump($tweets_to_insert);

//Array
// 16 (
// 17     [0] => stdClass Object
// 18         (
// 19             [recipient_screen_name] => lastminuteplans
// 20             [recipient_id] => 88579156
//print_r($tweets_to_insert);
//exit;
//print_r_pre($tweets_to_insert);
// http://mark.biek.org/blog/2009/02/writing-a-simple-twitter-bot-in-php/
$dbh = new PDO("sqlite:lmp.db");
foreach($tweets_to_insert as $message) {
    //echo 'Message from ' . $message->sender->screen_name . '';
    $sender = $message->sender_screen_name;
    $tweet = $message->text;
    // from Re: [sqlite] Equivalent of mysql_real_escape_string() ?
    // http://www.mail-archive.com/sqlite-users@sqlite.org/msg34146.html
    $tweet_quoted = $dbh->quote($tweet);
    $tweetid = $message->id;
    //echo 'Message from ' . $message->sender_screen_name. "\n";
    //echo 'Message: ' . $message->text. "\n";
    //echo 'Tweet id: ' . $message->id. "\n\n";
    // "OR IGNORE" is sqlite specific! http://www.sqlite.org/lang_conflict.html
    // purposefully fail to insert same tweetid (primary key)
    $sql = "INSERT INTO tweets (tweetid, sender, tweet, sent) VALUES($tweetid, '$sender', $tweet_quoted, 0)";
//            INSERT INTO "tweets" VALUES(1,'pdurbin',1234567,'heading to the rose garden',0);

    $dbh->exec($sql);
    //print $sql . "\n";
    //print_r($dbh->errorInfo());
/*


INSERT INTO tweets (tweetid, sender, tweet, sent) VALUES(659300038, 'philipdurbin', 'maybe i''ll bring the kids to the zoo tomorrow', 0)
Array
(
    [0] => 00000
)
INSERT INTO tweets (tweetid, sender, tweet, sent) VALUES(659251826, 'philipdurbin', 'going for a run up corey hill in an hour', 0)
Array
(
    [0] => 23000
    [1] => 19
    [2] => PRIMARY KEY must be unique
)


ignore this error on purpose
Array
(
    [0] => 23000
    [1] => 19
    [2] => PRIMARY KEY must be unique
)
    print_r($dbh->errorInfo());
    print "\n";
*/
}

$sql = "SELECT * FROM tweets WHERE sent = 0";
foreach ($dbh->query($sql) as $row) {
    //print_r_pre($row);
    $tweetid = $row['tweetid'];
    $sender = $row['sender'];
    $tweet = $row['tweet'];
    logger("$sender: tweetid  $tweetid: tweet: $tweet");
/*
    [tweetid] => 630662366
    [sender] => philipdurbin
    [tweet] => heading to the zoo when it opens
    [sent] => 0
*/
    // get location names and ids
    $sql = "SELECT id,owner,name FROM locations WHERE owner = '$sender'";
    //$count = 0;
    $location_names = array();
    foreach ($dbh->query($sql) as $locrow) {
        //print_r_pre($locrow);
        //print $count++;
        $owner = $locrow['owner'];
        $locname = $locrow['name'];
        $locid = $locrow['id'];
        //print " $owner: $locname: $locid\n";
        $location_names[$locid] = $locname;
    }
    //print_r_pre($location_names);
    //$any_location_found_in_tweet = false;
    $id_of_location_found_in_tweet = 0;
    foreach (explode(" ", $tweet) as $word) {
        //print "$word\n";
        if ($key = array_search($word, $location_names)) {
            logger(" found $word (locid $key) in \"$tweet\"");
            //$any_location_found_in_tweet = true;
            //$id_of_location_found_in_tweet = $locid;
            $id_of_location_found_in_tweet = $key;
        }
    }

    $mark_tweet_sent = true;
    if (empty($id_of_location_found_in_tweet)) {
        logger("WARNING no location found in tweet: $tweet");
    }
    else {
        // send tweet to subscribers
        //print "  locid $id_of_location_found_in_tweet found in tweet: $tweet\n";
        $sql = "SELECT * from subscriptions WHERE locationid = $id_of_location_found_in_tweet";
        foreach ($dbh->query($sql) as $subrow) {
            //print_r_pre($subrow);
            $subscriber = $subrow['subscriber'];
            //$subscriber = 'philipdurbin';
            $text = "@$sender said $tweet";
            $postargs = 'user='.urlencode($subscriber).'&text='.urlencode($text); 
            logger("   DM to $subscriber: $text");
            //print "     sending dm: $text\n";
            //print "     \$postargs = $postargs\n";
            $tw_url = "http://twitter.com/direct_messages/new.json";
            // CURL_POST* idea from http://twitter.slawcup.com/twitter.class.phps
            curl_setopt($tw, CURLOPT_URL, $tw_url);
            curl_setopt($tw, CURLOPT_POST, true);
            curl_setopt($tw, CURLOPT_POSTFIELDS, $postargs); 
            $to_log = "";
            //print "------$text\n";
            sleep(1); //respect Twitter API limits 
            $twi = curl_exec($tw);
            //$twi = 0;
            //print_r_pre($twi);
            if ($twi === false) {
                //maybe twitter was down, so we'll try again
                $mark_tweet_sent = false;
                $to_log .= 'maybe twitter is down';
            }
            else {

                $decoded = json_decode($twi);
                //print_r($decoded);
                if ($decoded->error) {
                    //print $decoded->error . "\n";
                    $to_log .= "ERROR sending to $subscriber: ";
                    if ($decoded->error == 'You cannot send messages to users who are not following you.' ) {
                        $to_log .= $decoded->error;
                    }
                    elseif ($decoded->error = 'Not found') {
                        $to_log .= $decoded->error;
                    }
                }
                else {
                    $to_log .= $decoded->id . " sent to " . $decoded->recipient_screen_name . ": " . $decoded->text;
                }

            }
            // logging idea from http://blog.taragana.com/index.php/archive/simple-logging-in-php-file-based-one-liner/
            logger("$to_log");
        }
    }
    if ($mark_tweet_sent === true) {
        $sql = "UPDATE tweets SET sent = 1 WHERE tweetid = $tweetid";
        $dbh->exec($sql);
    }
}

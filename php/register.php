<?php
/**
 * register.php
 *
 * Philip Durbin
 * Computer Science E-75
 * Harvard Extension School
 */

// enable sessions
session_start();
require_once('common.php');
require_once('config.php');

$username_submitted = '';
//print_r_pre($_POST);
$errors = array();
// if username and password were submitted, check them
if (isset($_POST["user"]) && isset($_POST["pass"]) && isset($_POST["pass2"]) ) {
    $username_submitted = $_POST["user"];
    // A user’s username must be a syntactically valid email address
    //if (! filter_var($_POST["user"], FILTER_VALIDATE_EMAIL)) {
    //    $errors[] = "invalid email address: {$_POST["user"]}";
    //}
    // A user’s password must be at least six characters, and it cannot be entirely alphabetic or entirely numeric.
    //if (strlen($_POST["pass"]) < 6) {
    //    $errors[] = "password is fewer than 6 characters";
    //}
    // inspiration from http://www.eukhost.com/forums/f18/php-advanced-password-validation-using-regular-expressions-229/
    //if (ctype_alpha($_POST["pass"])) {
    //    $errors[] = "password is all letters";
    //}
    //if (ctype_digit($_POST["pass"])) {
    //    $errors[] = "password is all numbers";
    //}
    if ($_POST["pass"] != $_POST["pass2"]) {
        $errors[] = "Please enter the same password twice";
    }
    // only INSERT valid data
    // check if new user is following @lastminute plans
    if (empty($errors)) {
        $to_find = $_POST["user"];
        $result = file_get_contents("http://twitter.com/followers/ids.json?screen_name=lastminuteplans");
        //print_r($result);
        //print "\n\n";
        $followers = json_decode($result);
        $follower_usernames = array();
        $is_following = false;
        foreach ($followers as $follower_id) {
            //$result = @file_get_contents("http://twitter.com/users/show/$follower_id.jsoaan");
            //$result = @file_get_contents("http://twittdsaafdsdafser.com/users/show/$follower_id.json");
            $result = @file_get_contents("http://twitter.com/users/show/$follower_id.json");
            if ($result === false) {
                $errors[] = "Sorry! We couldn't verify @$to_find is following @lastminuteplans on Twitter. Please try again in a few minutes.";
                // exit foreach loop
                break;
            }
            else {
                $follower_info = json_decode($result);
                $follower_usernames[] = $follower_info->screen_name;
                //print "examining " . $follower_info->screen_name . "\n";
                if (in_array($to_find, $follower_usernames)) {
                    $is_following = true;
                    //print "found $to_find in followers list!\n";
                    // we found the user!  stop processing
                    break;
                }
            }
        }
        if ($is_following === false) {
            $errors[] = "$to_find is not following @lastminuteplans on Twitter.  Please correct this.";
        }
    }
    // attempt to follow the user who is trying to register
    if (empty($errors)) {
        $login = TW_USER . ":" . TW_PASS;
        $to_follow = $username_submitted;
        $url = "http://twitter.com/friendships/create/$to_follow.json";
        $tw = curl_init();
        // CURL_POST* idea from http://twitter.slawcup.com/twitter.class.phps
        curl_setopt($tw, CURLOPT_URL, $url);
        curl_setopt($tw, CURLOPT_USERPWD, $login);
        curl_setopt($tw, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($tw, CURLOPT_POST, true);
        $result = curl_exec($tw);
        $decoded = json_decode($result);
        //print_r_pre($decoded);
        if ($decoded->error ) {
            $follow_error = $decoded->error;
            $type = "ERROR";
            if (preg_match("/is already on your list/", $follow_error)) {
                // already following. not a problem.
                $type = "WARNING";
            }
            else {
                $errors[] = "Sorry! We couldn't start following $to_follow on Twitter! Please try re-registering in a few minutes.";
            }
            logger("$type following $username_submitted: $follow_error");
        }
        else {
            logger("Now following $username_submitted");
        }

    }
    if (empty($errors)) {
        // prepare SQL
        $dbh = new PDO("sqlite:lmp.db");
        // ERRMODE idea from http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html#9
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //sha1 recommended at http://phpsec.org/articles/2005/password-hashing.html
        $password = sha1($_POST['pass']);
        $sql = "INSERT INTO 'users' (username, pass) VALUES('$_POST[user]', '$password')";
        // execute query
        try {
            $dbh->exec($sql);
            //$_SESSION["uid"] = mysql_insert_id();
            $_SESSION["authenticated"] = TRUE;
            $_SESSION["username"] = $_POST["user"];
            $host = $_SERVER["HTTP_HOST"];
            $path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
            header("Location: http://$host$path/index.php");
            exit;
        }
        catch(PDOException $e) {
            //echo $e->getMessage();
            //print "<p>Couldn't register<p>\n";
            //$arr = $dbh->errorInfo();
            print_r_pre($arr);
            if ($dbh->errorCode() == 23000) {
                $errors[] = "Username $_POST[username] is in use!  Please pick a new one.";
            }
            else {
                $errors[] = "Unknown error $return_code.  Please try again.";
            }
        }
}
}
page_header('Register', 'user', true);
print_errors($errors);
?>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post">
      <table>
        <tr>
          <td>Twitter username:</td>
          <td>
          <input name="user" type="text" value="<?php print $username_submitted ?>" id="user" /></td>
        </tr>
        <tr>
          <td>LastMinutePlans password:</td>
          <td><input name="pass" type="password" /></td>
        </tr>
        <tr>
          <td>LastMinutePlans password (again):</td>
          <td><input name="pass2" type="password" /></td>
        </tr>
        <tr>
          <td></td>
          <td><input type="submit" value="Register" /></td>
        </tr>
      </table>      
    </form>
    <p>Please note:</p>
    <ul>
    <li>Your LastMinutePlans username must be your Twitter username. Please go <a href="https://twitter.com/signup">register for Twitter first</a>, if necessary.</li>
    <li>Your Twitter account must be following <a href="http://twitter.com/lastminuteplans">@lastminuteplans</a> on Twitter.</li>
    <li>Your LastMinutePlans password is completely separate from your Twitter password. There is no need to give us your Twitter password!</li>
    </ul>
<?php
page_footer();

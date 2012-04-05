<?php
/**
 * Philip Durbin
 * Computer Science E-75
 * Harvard Extension School
 */

// enable sessions
session_start();
require_once('common.php');
require_once('config.php');

//print_r_pre($_POST);

$errors = array();
$username_submitted = '';
// if username and password were submitted, check them
if (isset($_POST["user"]) && isset($_POST["pass"])) {
    $username_submitted = $_POST["user"];
    $dbh = new PDO("sqlite:lmp.db");
    //$sql = sprintf("SELECT * FROM users WHERE username='%s'", $username_submitted);
    // idea to use prepared statements from http://stackoverflow.com/questions/134099/are-pdo-prepared-statements-sufficient-to-prevent-sql-injection and http://www.php.net/manual/en/pdo.prepare.php#90209
    $sql = sprintf("SELECT * FROM users WHERE username = :uservar");
    $sth = $dbh->prepare($sql);
    $sth->execute(array(':uservar' => $username_submitted));
    $rows = $sth->fetchAll();
    //$result = $dbh->query($sql);
    // idea to use fetchAll from http://stackoverflow.com/questions/769767/pdos-rowcount-not-working-on-php-5-2-6/1314865#1314865
    //$rows = $result->fetchAll();
    $count = count($rows);
    //print_r_pre($count);
    if ($count == 1) {
        //print "result was 1\n";
        //foreach ($dbh->query($sql) as $row) {
        foreach ($rows as $row) {
            //print $row['username'] .' - '. $row['pass'] . "\n";

            //store userid
            //$_SESSION["uid"] = $row["uid"];

            // check password
            // sha1 recommended at http://phpsec.org/articles/2005/password-hashing.html
            if ($row["pass"] == sha1($_POST["pass"])) {
                // remember that user's logged in
                $_SESSION["authenticated"] = TRUE;
                $_SESSION["username"] = $_POST["user"];

                //print_r_pre($_SESSION);

                // redirect user to home page, using absolute path, per
                // http://us2.php.net/manual/en/function.header.php
                $host = $_SERVER["HTTP_HOST"];
                $path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
                header("Location: http://$host$path/index.php");
                exit;
            }
            else {
                $errors[] = 'invalid username or password';
            }
        }
    }
    else  {
        $errors[] = 'invalid username or password';
    }

    $dbh = null;
}

page_header('Login', 'user', true);
print_errors($errors);
?>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post" id="login">
      <table>
        <tr>
          <td>Username:</td>
          <td>
            <input name="user" type="text" value="<?php print $username_submitted ?>" id="user" /></td>
        </tr>
        <tr>
          <td>Password:</td>
          <td><input name="pass" type="password" /></td>
        </tr>
        <tr>
          <td></td>
          <td><input type="submit" value="Log In" /></td>
        </tr>
      </table>      
    </form>
    <p>Don't have an account? Go ahead and <a href="register.php">register</a>!</p>
<?php
page_footer();

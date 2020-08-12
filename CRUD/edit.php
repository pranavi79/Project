<?php

session_start();

if ( ! isset($_SESSION['name']) ) {
    die("Not logged in");
}

// If the user requested logout go back to index.php
if ( isset($_POST['cancel']) ) {
    header('Location: index.php');
    return;
}

$status = false;

if ( isset($_SESSION['status']) ) {
    $status = htmlentities($_SESSION['status']);
    $status_color = htmlentities($_SESSION['color']);

    unset($_SESSION['status']);
    unset($_SESSION['color']);
}

try 
{
    $pdo = new PDO('mysql:host=localhost;port=3306;dbname=misc', 
   'fred', 'zap');
// See the "errors" folder for details...
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e)
{
    echo "Connection failed: " . $e->getMessage();
    die();
}

$name = htmlentities($_SESSION['name']);

$_SESSION['color'] = 'red';

if (isset($_REQUEST['profile_id']))
{

    $profile_id = htmlentities($_REQUEST['profile_id']);

    // Check to see if we have some POST data, if we do process it
    if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['mobile']) && isset($_POST['dob'])&& isset($_POST['pin'])) 
    {
        if (strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['mobile']) < 1 || strlen($_POST['dob']) < 1||strlen($_POST['pin'])<1)
        {
            $_SESSION['status'] = "All fields are required";
            header("Location: edit.php?profile_id=" . htmlentities($_REQUEST['profile_id']));
            return;
        }

        if (strpos($_POST['email'], '@') === false)
        {
            $_SESSION['status'] = "Email address must contain @";
            header("Location: edit.php?profile_id=" . htmlentities($_REQUEST['profile_id']));
            return;
        }

        $first_name = htmlentities($_POST['first_name']);
        $last_name = htmlentities($_POST['last_name']);
        $email = htmlentities($_POST['email']);
        $mobile = htmlentities($_POST['mobile']);
        $dob = htmlentities($_POST['dob']);
        $pin=htmlentities($_POST['pin']);

        $stmt = $pdo->prepare("
            UPDATE profile
            SET first_name = :first_name, last_name = :last_name, email = :email, mobile = :mobile, dob = :dob, pin=:pin
            WHERE profile_id = :profile_id
        ");

        $stmt->execute([
            ':first_name' => $first_name, 
            ':last_name' => $last_name, 
            ':email' => $email,
            ':mobile' => $mobile,
            ':dob' => $dob,
            ':pin' => $pin,
            ':profile_id' => $profile_id,
        ]);

        $_SESSION['status'] = 'Record edited';
        $_SESSION['color'] = 'green';

        header('Location: index.php');
        return;
    }

    $stmt = $pdo->prepare("
        SELECT * FROM profile 
        WHERE profile_id = :profile_id
    ");

    $stmt->execute([
        ':profile_id' => $profile_id, 
    ]);

    $profile = $stmt->fetch(PDO::FETCH_OBJ);

}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Pranavi Autos</title>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    </head>
    <body>
        <div class="container">
            <h1>Editing Profile for <?php echo $name; ?></h1>
            <?php
                if ( $status !== false ) 
                {
                    echo(
                        '<p style="color: ' .$status_color. ';" class="col-sm-10 col-sm-offset-2">'.
                            htmlentities($status).
                        "</p>\n"
                    );
                }
            ?>
            <form method="post" class="form-horizontal">
                <div class="form-group">
                    <label class="control-label col-sm-2" for="first_name">First Name:</label>
                    <div class="col-sm-5">
                        <input class="form-control" type="text" name="first_name" id="first_name" value="<?php echo $profile->first_name; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="last_name">Last Name:</label>
                    <div class="col-sm-5">
                        <input class="form-control" type="text" name="last_name" id="last_name" value="<?php echo $profile->last_name; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="email">Email:</label>
                    <div class="col-sm-5">
                        <input class="form-control" type="text" name="email" id="email" value="<?php echo $profile->email; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="mobile">Mobile No:</label>
                    <div class="col-sm-5">
                        <input pattern="[0-9]{10}"class="form-control" type="text" name="mobile" id="mobile" value="<?php echo $profile->mobile; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="dob">Date of Birth:</label>
                    <div class="col-sm-5">
                        <input type="date" class="form-control" name="dob" id="dob" value="<?php echo $profile->dob; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="pin">Pin No:</label>
                    <div class="col-sm-5">
                        <input pattern="[0-9]{6}" class="form-control" name="pin" id="pin" value="<?php echo $profile->pin; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-2 col-sm-offset-2">
                        <input class="btn btn-primary" type="submit" value="Save">
                        <input class="btn" type="submit" name="cancel" value="Cancel">
                    </div>
                </div>
            </form>

        </div>
    </body>
</html>
<?php
/**
 * Folio Register User File
 * Connell Reffo 2019
 */

include_once "app_main.php";

// Init PHPMailer
use PHPMailer\PHPMailer\PHPMailer;

require_once("../PHPMailer/PHPMailer.php");
require_once("../PHPMailer/SMTP.php");
require_once("../PHPMailer/Exception.php");

// Authenticate Input
$email = escapeString($_REQUEST["email"]);
$location = escapeString($_REQUEST["location"]);
$username = escapeString($_REQUEST["username"]);
$password = escapeString($_REQUEST["password"]);
$confPass = escapeString($_REQUEST["confPass"]);
$votesJSON = '{"upvotes": [], "downvotes": []}';

$maxChars = 20;
$minPass = 6;

$illegalChars = "'&*()^%$#@!+:-";

// Insertion Query
if (empty($location) || !validLocation($location)) {
    $location = "Unknown";
}

$code = generateVerificationCode(); // Generate Verification Code
$passHash = password_hash($password, PASSWORD_BCRYPT, array("cost" => 11));
$profImg = randomProfileImage();
$date = date("j-n-Y");
$query = "INSERT INTO
    users (username, email, accountLocation, password, verificationCode, verified, profileBio, voteCount, date, allowComments, profileImagePath, votes, joinedForums) 
    VALUES('$username', '$email', '$location', '$passHash', '$code', '0', 'Sample Bio', '0', '$date', '1', '$profImg', '$votesJSON', '[]')
";

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid Email"
    ));
}
else if (empty($username) || $username != strip_tags($username) || filter_var($username, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid Username"
    ));
}
else if (strpbrk($username, $illegalChars)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Username Cannot Contain $illegalChars"
    ));
}
else if (strpos($username, " ") !== false) {
    echo json_encode(array(
        "success" => false,
        "message" => "Username cannot contain Spaces"
    ));
}
else if (strlen($username) > $maxChars) {
    echo json_encode(array(
        "success" => false,
        "message" => "Username is too Long (Maximum $maxChars Characters)"
    ));
}
else if (empty($password) || empty($confPass) || $password != strip_tags($password) || $confPass != strip_tags($confPass)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid Password(s)"
    ));
}
else if ($password != $confPass) {
    echo json_encode(array(
        "success" => false,
        "message" => "Passwords don't Match"
    ));
}
else if (strlen($password) > $maxChars) {
    echo json_encode(array(
        "success" => false,
        "message" => "Password is too Long\n (Maximum $maxChars Characters)"
    ));
}
else if (strlen($password) < $minPass) {
    echo json_encode(array(
        "success" => false,
        "message" => "Password is too Short\n (Minimum $minPass Characters)"
    ));
}
else if (!empty(getUserData("username", "username='$username'"))) {
    // Check for duplicate usernames
    echo json_encode(array(
        "success" => false,
        "message" => "An Account with that Username already exists"
    ));
}
else if (!empty(getUserData("email", "email='$email'"))) {
    // Check for duplicate emails
    echo json_encode(array(
        "success" => false,
        "message" => "An Account with that Email already exists"
    ));
}
else {
    // Create Account and Send Auth info
    $mail = new PHPMailer();

    // Send Email
    initPHPMailer($mail, $email);

    $mail->Subject = "Folio Verification Code";
    $mail->Body = "
    <body style='background-color: #252529; padding: 20px; border: 7px solid #252529; border-radius: 7px' >
        <h2 style='color: white; position: absolute; margin: auto' >Hello $username, your verification code is: </h2>
        <h1 style='color: #f53643; font-size: 40px; margin-top: 5px; position: absolute' >$code</h1>
    </body>
    ";

    // Send Code
    if ($mail->send()) {

        // Create User
        if (!insertUser($query)) { // Execute Query
            echo json_encode(array(
                "success" => false,
                "message" => $db->error
            ));
        }
        else {
            // Send Successful Response
            echo json_encode(array(
                "success" => true,
                "verify" => true,
                "message" => "Sent Email Verifaction to " . substr($email, 0, 23)
            ));
        }
    }
    else {
        echo json_encode(array(
            "success" => false,
            "message" => substr($mail->ErrorInfo, 0, 40) . "..."
        ));
    }
}

function insertUser($query) {
    $db = $GLOBALS["db"];

    $result = $db->query($query);
    return $result;
}

?>
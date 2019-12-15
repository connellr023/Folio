<?php
/**
 * Folio Main PHP File
 * Connell Reffo 2019
 */

// Generate <option> tags for Account Location input field
function fetchLocationsHtml() {
    $countries = [
        "Canada",
        "Costa Rica",
        "Cuba",
        "Mexico",
        "United States"
    ];

    $final = "";

    foreach ($countries as $country) {
        $final .= "<option value='" . str_replace(" ", "-", $country) . "' >" . $country . "</option>\n";
    }

    return $final;
}

function initPHPMailer($mail, $sendTo) {
    // SMTP Settings
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "foliowebapp@gmail.com";
    $mail->Password = "phpapp2328";
    $mail->Port = 465;
    $mail->SMTPSecure = "ssl";

    // Email Settings
    $mail->isHTML(true);
    $mail->setFrom($sendTo, "Folio");
    $mail->addAddress($sendTo);
}

// Verification Code Algorithm
function generateVerificationCode() {
    return strtoupper(substr(md5(strval(rand(0, 100))), 0, 8));
}

// Returns a user Object
function getUserData($db, $column, $condition) {
    $query = $db->query("SELECT $column FROM users WHERE $condition");
    $array = $query->fetchArray();

    return $array[$column];
}

// Change Users in DB
function updateUser($db, $column, $value, $condition) {
    $query = $db->query("UPDATE users SET $column = '$value' WHERE $condition");
    
    return $query;
}

?>
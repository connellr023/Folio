<?php
/**
 * Folio Forum Grabber
 * Connell Reffo 2019
 */

include_once "app_main.php";
session_start();

// Init DB
$db = new SQLite3("../db/folio.db");

// Null Check Query
if (isset($_REQUEST["fquery"]) && !empty($_REQUEST["fquery"])) {

    // Grab Forum Data
    $user = $_SESSION["user"];
    $fquery = $_REQUEST["fquery"];
    $forumId = getForumIdByName($db, $fquery);
    $forum = getForumDataById($db, $forumId);

    if (!empty($forum)) {

        // Check if Current User is a Member of the Forum
        $joinedForum = $forum->hasMember($user);

        // Check if Moderator of Forum
        $moderator = $forum->isModerator($user);

        // Send Response to Client
        echo json_encode([
            "success" => true,
            "forum" => [
                "joined" => $joinedForum,
                "owner" => getUserData($db, "username", "uid='$forum->ownerUID'"),
                "name" => $forum->name,
                "description" => $forum->description,
                "icon" => $forum->iconURL,
                "date" => $forum->date,
                "moderator" => $moderator
            ]
        ]);
    }
    else {
        echo json_encode([
            "success" => false,
            "message" => "There are no Forums with that Name"
        ]);
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid Forum Query"
    ]);
}

?>
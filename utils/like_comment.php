<?php
/**
 * Folio Comment Liking
 * @author Connell Reffo
 */

include_once "app_main.php";
session_start();

// Init DB
$db = db();

// Check Session
if (validateSession($_SESSION["user"])) {
    $user = $_SESSION["user"];

    $CID = escapeString($_REQUEST["cid"]);
    $commentQuery = $db->query("SELECT usersLiked, uid, likes FROM comments WHERE cid='$CID'");

    // Fetch Comment Data
    if ($commentQuery) {
        $commentData = $commentQuery->fetch_array(MYSQLI_ASSOC);
        
        // Validate Permissions
        $profileId = $commentData["uid"];
        if (getUserData("allowComments", "uid='$profileId'") == 1) {
            
            // Check if User has Already Liked Comment
            $likesArray = json_decode($commentData["usersLiked"]);
            $likesArrayEncoded = $commentData["usersLiked"];
            if (in_array($user, $likesArray)) {

                // Has Liked
                $likeIndex = array_search($user, $likesArray);
                $likes = $commentData["likes"] - 1; // Remove Like
                $liked = false;
                $likeQuery = "UPDATE comments SET likes=$likes, usersLiked=JSON_REMOVE('$likesArrayEncoded', '$[$likeIndex]') WHERE cid='$CID'";
            }
            else {

                // Has Not Liked
                $likes = $commentData["likes"] + 1; // Add Like
                $liked = true;
                $likeQuery = "UPDATE comments SET likes=$likes, usersLiked=JSON_ARRAY_INSERT('$likesArrayEncoded', '$[0]', $user) WHERE cid='$CID'";
            }

            // Update DB
            $updateQuery = $db->query($likeQuery);
            if ($updateQuery) {

                // Send Successful Response to Client
                echo json_encode([
                    "success" => true,
                    "likes" => $likes,
                    "liked" => $liked
                ]);
            }
            else {
                echo json_encode([
                    "success" => false,
                    "message" => $db->error
                ]);
            }
        }
        else {
            echo json_encode([
                "success" => false,
                "message" => "You do not have Permission to Perform this Action"
            ]);
        }
    }
    else {
        echo json_encode([
            "success" => false,
            "message" => $db->error
        ]);
    }
}
else {
    echo json_encode([
        "success" => false,
        "message" => "You Must be Logged in to Like Comments"
    ]);
}

?>
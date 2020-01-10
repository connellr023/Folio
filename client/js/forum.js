/**
 * Folio Forum Javascript File
 * Connell Reffo 2019
 */

// Global Variables
let joined = false;

// On Load
function triggerOnLoad() {
    loadForum(forum);

    $(document).on("click", "#view-member", function (e) {
        let profile = $(this).parent().parent().attr("data-profile");
        let URL = "/profile.php?uquery=" + profile;

        location.replace(URL);
    });
}

function loadForum(fquery) {
    if (fquery !== null && fquery !== "") {
        
        // Send Request
        $.ajax({
            type: "POST",
            url: "../../utils/view_forum.php",
            dataType: "json",
            data: {
                fquery: fquery
            },
            success: function(res) {
                if (res.redirect) {
                    location.replace("/index.php");
                }
                else if (res.success) {

                    // Load JSON Response into Webpage
                    let forum = res.forum;

                    joined = forum.joined;
                    if (joined) {
                        displayLeaveForumBtn();
                    }
                    else {
                        displayJoinForumBtn();
                    }

                    if (!res.forum.moderator) {
                        $(".edit-forum-btn").remove();
                    }
                    else {
                        $(".edit-forum-btn").css("display", "block");
                    }

                    $("#profile-img").attr("src", forum.icon);
                    $("#bio").val(forum.description);
                    $("#profile-name").text(forum.name);
                    $("#forum-members").text(forum.members);
                    $("#creation-date").text(forum.date);
                }
                else {
                    popUp("clientm-fail", res.message, null);
                }
            },
            error: function(err) {
                popUp("clientm-fail", "Failed to Contact Server", null);
            }
        })
    }
    else {
        popUp("clientm-fail", "Invalid Forum Query", null);
    }
}

// Show Members of Forum in Modal
let hasShowed = false;
function showMembers() {
    $("#members-modal").css("display", "block");
    if (!hasShowed) {
        $.ajax({
            type: "POST",
            url: "../../utils/forum_members.php",
            dataType: "json",
            data: {
                forum: forum
            },
            success: function(res) {
                if (res.success) {
                    hasShowed = true;

                    // Load List of Users
                    $("#members-container").empty();
                    loadMembers(res.members);
                }
                else {
                    popUp("clientm-fail", res.message, null);
                }
            },
            error: function(err) {
                popUp("clientm-fail", "Failed to Contact Server", null);
            }
        })
    }
}

function closeMembers() {
    $("#members-modal").css("display", "none");
}

function closeLeaveForum() {
    $("#leave-forum-modal").css("display", "none");
}

// Render Forum Members JSON as HTML
function loadMembers(members) {
    let container = $("#members-container");

    for (let member in members) {
        let html = '<div class="profile-forum" data-profile="'+members[member].username+'" ><img class="member-img" src="'+members[member].image+'" ><div class="forum-member-name" >'+members[member].username+'</div><br /><div class="member-options" ><button id="view-member" class="member-default-option" >View</button></div></div>';
        $(container).prepend(html);

        let element = $(container).children().first();
        let memberData = members[member];

        // Show Change DOM based on Permission Level
        let title = $(element).find(".forum-member-name")

        // Coloured Names
        if (memberData.owner) {
            $(title).css("color", "violet");
            $(title).text(memberData.username + " (Owner)");
        }
        else if (memberData.moderator) {
            $(title).css("color", "orange");
            $(title).text(memberData.username + " (Mod)");
        }

        // Options (Kick, Ban, Promote)
        if (memberData.removable) {
            let options = $(element).find(".member-options");
            
            $(options).append('<button id="kick-member" class="member-default-option member-option-red" >Kick</button>');
            $(options).append('<button id="ban-member" class="member-default-option member-option-red" >Ban</button>');
            
            if (memberData.promotable) {
                $(options).append('<button id="promote-member" class="member-default-option member-option-green" >Promote</button>');
            }

            if (memberData.demotable) {
                $(options).append('<button id="demote-member" class="member-default-option member-option-red" >Demote</button>');
            }
        }
    }
}

// Allow Users to Join Forums
function joinForum() {

    // Close Modal
    closeLeaveForum();

    // Send Request
    $.ajax({
        type: "POST",
        url: "../../utils/join_forum.php",
        dataType: "json",
        data: {
            forum: forum
        },
        success: function(res) {
            if (res.success) {
                joined = res.joined;

                if (joined) {
                    $("#forum-members").text(parseInt($("#forum-members").text()) + 1);
                    displayLeaveForumBtn();
                }
                else {
                    $("#forum-members").text(parseInt($("#forum-members").text()) - 1);
                    displayJoinForumBtn();
                }

                if (res.reload) {
                    location.reload();
                }

                hasShowed = false;
            }
            else {
                popUp("clientm-fail", res.message, null);
            }
        },
        error: function(err) {
            popUp("clientm-fail", "Failed to Contact Server", null);
        }
    })
}

function displayLeaveForumBtn() {
    $(".join-forum-btn").addClass("joined-forum-btn");
    $(".join-forum-btn").text("Leave Forum");
    $(".join-forum-btn").attr("onclick", "confirmLeave()");
}

function displayJoinForumBtn() {
    $(".join-forum-btn").removeClass("joined-forum-btn");
    $(".join-forum-btn").text("Join Forum");
    $(".join-forum-btn").attr("onclick", "joinForum()");
}

function confirmLeave() {
    $("#leave-forum-modal").css("display", "block");
}
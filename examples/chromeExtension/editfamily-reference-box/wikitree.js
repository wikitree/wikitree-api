// This Chrome extension activates on WikiTree "EditFamily" pages.
// It loads data for the edited profile, displaying names with links to existing parents and children.

// Parse the URL query parameters (to look for the edited user id).
// Store/set the name/id of the user after logging into the API.
var urlParams = new URLSearchParams(window.location.search);
var userName = '';
var userId = '';
var editId = '';

// Only do anything if we have an edited-user "u" parameter.
// The extension only activates on EditFamily per the manifest.
if (urlParams.has('u')) {
	$('#content').append("<div id=\"wtRefBox\"></div>");
	editId = urlParams.get('u');
	checkLogin();
}

// The first thing the extension does is check if the user is logged into the WikiTree API.
function checkLogin() {
	// Load our username/id from the cookie, and check the URL for an authcode returned by clientLogin.
	userName = $.cookie('userName');
	userId = $.cookie('userId');
	var authcode = urlParams.get('authcode');

	// If we have an authcode, try to confirm it.
	if ((typeof authcode != 'undefined') && (authcode != null) && (authcode != '')) {
		postToAPI({'action':'clientLogin', 'authcode': authcode})
		.then(function(data) {
			if (data.clientLogin.result == 'Success') {
				// If the login authcode validates, then save the connected username/id.
				// Redirect back to our page with the authcode stripped from the URL.
				userName = data.clientLogin.username;
				userId = data.clientLogin.userid;
				$.cookie('userName', userName);
				$.cookie('userId', userId);
				u = window.location.href.replace(/&authcode=.*/, "");
				window.location = u;
			} else {
				console.log("Authcode confirmation failed.");
				//alert("Login failure");
			}	
		})
		.catch(function(error) {
			console.log(error);
		});	
	}
	else if ((typeof(userName) != 'undefined') && (userName != '')) {
		// If we don't have an authcode, but we have already set out username,
		// then we're ready to display our reference box.
		doReferenceBox();
	}
	else {
		// The user is not logged in. Create a button to send them to the API clientLogin
		// function, returning here.
		var html = '<form action="https://api.wikitree.com/api.php" method="POST">';
		html += '<input type="hidden" name="action" value="clientLogin">';
		html += '<input type="hidden" name="returnURL" value="'+window.location.href+'">';
		html += '<input type="submit" class="small" value="API Login">';
		html += '</form>';
		$('#wtRefBox').html(html).show();
	}
}

// Query the API for information on the edited profile, then render it into our reference box.
function doReferenceBox() {
	postToAPI({'action': 'getPerson', 'key': editId, 'fields': 'Id,Name,FirstName,LastNameCurrent,BirthDate,DeathDate,Mother,Father,Parents,Children' })
	.then(function(result) { renderReferenceBox(result); })
	.catch(function(error) {
		console.log(error);
	});	
}

// Given the getProfile results, put data about the edited profile and any parents/children found into the reference box.
function renderReferenceBox(result) {
	var data = result[0];
	var html = "";

	// The edited person.
	html += "<b>"+renderPerson(data.person)+"</b><br>";

	// Parents
	if (data.person.Parents) {
		if (data.person.Parents) {
			for (var idx in data.person.Parents) {
				html += "<img class=\"fauxBullet\" src=\"https://www.wikitree.com/images/icons/pedigree.gif\">";
				html += renderPerson(data.person.Parents[idx]) + "<br>";
			}
		}
	}

	// Children
	if (data.person.Children) {
		if (data.person.Children) {
			for (var idx in data.person.Children) {
				html += "<img class=\"fauxBullet\" src=\"https://www.wikitree.com/images/icons/descendant-link.gif\">";
				html += renderPerson(data.person.Children[idx]) + "<br>";
			}
		}
	}

	// Put our built HTML content into the div of our reference box.
	$('#wtRefBox').html(html).show();
}

// For every displayed reference person, show some basic information, including a link
// to the profile page itself.
function renderPerson(p) {
	var html = "";

	html += "<a href=\"https://www.wikitree.com/wiki/"+p.Name+"\" target=\"_new\">"+p.FirstName+" "+p.Name+"</a>";
	if ((p.BirthDate && p.BirthDate != '0000-00-00') || (p.DeathDate && p.DeathDate != '0000-00-00')) {
		html += " (";
		if (p.BirthDate && p.BirthDate != '0000-00-00') { html += p.BirthDate.substring(0,4); } else { html += "?"; }
		html += " - ";
		if (p.DeathDate && p.DeathDate != '0000-00-00') { html += p.DeathDate.substring(0,4); } else { html += "?"; }
		html += ") ";
	}
	html = "<span style='white-space:nowrap;'>"+html+"</span>";

	return html;
}

// Use jQuery's .ajax method to query the WikiTree API for some content.
function postToAPI(postData) {
    var ajax = $.ajax({
        // The WikiTree API endpoint
        'url': 'https://api.wikitree.com/api.php',
    
        // We tell the browser to send any cookie credentials we might have (in case we authenticated).
        'xhrFields': { withCredentials: true },

        // Doesn't help. Not required from (dev|apps).wikitree.com and api.wikitree.com disallows cross-origin:*
        //'crossDomain': true,

        // We're POSTing the data so we don't worry about URL size limits and want JSON back.
        type: 'POST',
        dataType: 'json',
        data: postData
    });

    return ajax;
}

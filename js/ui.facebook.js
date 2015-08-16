window.fbAppId = '835001473193147';

$(document).ready(function() {
	$.ajaxSetup({ cache: true });
	$.getScript('//connect.facebook.net/en_UK/all.js', function(){
		FB.init({
			appId: window.fbAppId,
		});
		FB.getLoginStatus(facebook.init);
	});
});

var facebook = {
	/*
	 *  void facebook.init()
	 *
	 *  initial after the SDK had the login status
	 */
	init: function(response) {
		if (response.status === 'connected') {
			// update the navbar profile pic and name
			FB.api('/me', function(response) {
				$(".fb-name span").text(response.name).click(function(event) {
					event.preventDefault();
    				window.open(response.link);
				});
			});

			// set fb-logout event
			$(".fb-logout").click(function() {
				facebook.logout();
			});
		
			// toggle elements status
			$(".fb-not-logined").hide();
			$(".fb-logined").show();

		} else {
			// toggle elements status
			$(".fb-not-logined").show();
			$(".fb-logined").hide();
		}
	},

	/*
	 *  void facebook.logout()
	 *
	 *  logout the currecnt session of facebook
	 */
	logout: function() {
		FB.logout(function() {
			// clear the access token
			$.ajax({ async: false, dataType: "json",
				url: "action.php?do=auth.logout&rand=" + Math.random(),
				success: function(response){
					window.location.href = response.next;
				}
			});
		});
	},

	/*
	 *  void facebook.request()
	 *
	 *  call FB.ui for user requesting or inviting
	 */
	 request: function(message, to) {
	 	if( to == false ) {
			FB.ui({method: 'apprequests',
				app_id: window.fbAppId,
				message: message,
			}, facebook.requestCallback);
	 	} else {
			FB.ui({method: 'apprequests',
				app_id: window.fbAppId,
				message: message,
				to: to,
			}, facebook.requestCallback);
	 	}
	 },

	/*
	 *  void facebook.requestCallback()
	 *
	 *  callback interface for "apprequests"
	 */
	 requestCallback: function(response) {},
};

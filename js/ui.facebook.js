window.fbAppId = '835001473193147';

window.fbAsyncInit = function() {
  FB.init({
    appId:   window.fbAppId,
    xfbml:   true,
    version: 'v2.4',
    status:  true,
    cookie:  true
  });
  FB.getLoginStatus(facebook.init);
};

(function(d, s, id){
   var js, fjs = d.getElementsByTagName(s)[0];
   if (d.getElementById(id)) {return;}
   js = d.createElement(s); js.id = id;
   js.src = "//connect.facebook.net/en_US/sdk.js";
   fjs.parentNode.insertBefore(js, fjs);
 }(document, 'script', 'facebook-jssdk'));

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

window.fbAppId = '835001473193147';

window.fbAsyncInit = function() {
  FB.init({
    appId:   window.fbAppId,
    xfbml:   true,
    version: 'v2.4',
    status:  true,
    cookie:  true
  });
};

var oauth = {
	/*
	 *  void oauth.init()
	 *
	 *  initial the page with a login button
	 */
	init: function(response) {
		// set click event for buttons
		$(".proceed-button").click(function() {
			FB.login(function(response) {
				// user accepted the login request
				if (response.authResponse) {
					oauth.proceed(response);
				}
			}, {scope: 'user_friends'});
		});
	},

	/*
	 *  void home.proceed()
	 *
	 *  after FB.login or already logined, post uid and token to server
	 */
	proceed: function(response) {
		if( response.authResponse ) {
			// show animation
			$(".proceed-login").html('<br /><div class="progress progress-striped active"><div class="progress-bar" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width: 70%"></div></div>');

			$.ajax({ async: false, dataType: "json", type: "POST",
				url: "action.php?do=auth.login&rand=" + Math.random(),
				data: "signed=" + response.authResponse.signedRequest,
				success: function(response){
					window.location.href = window.origin;
				}
			});
		}
	},
};

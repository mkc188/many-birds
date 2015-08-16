var home = {
	/*
	 *  void home.init()
	 *
	 *  initial the home page
	 */
	init: function() {
		// set elements on correct size
		this.resize();

		// set click event for buttons
		$(".proceed-login").click(function() {
			FB.login(function(response) {
				// user accepted the login request
				if (response.authResponse) {
					$(".proceed-login").html('<br /><div class="progress progress-striped active"><div class="progress-bar" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width: 70%"></div></div>');
					home.proceed(response);
				}
			});
		});
		$(".proceed-mode").click(function() {
			$(this).button("loading");

			// obtain auth object for proceed
			FB.getLoginStatus(home.proceed);
		});
	},

	/*
	 *  void home.proceed()
	 *
	 *  after FB.login or already logined, post uid and token to server
	 */
	proceed: function(response) {
		if( response.authResponse ) {
			$.ajax({ async: false, dataType: "json", type: "POST",
				url: "action.php?do=auth.login&rand=" + Math.random(),
				data: "signed=" + response.authResponse.signedRequest,
				success: function(response){
					window.location.href = response.next;
				}
			});
		}
	},	

	/*
	 *  void home.resize()
	 *
	 *  activate after reized the page
	 */
	resize: function() {
		// setup the real-time characters srction to fill the screen,
		// minus 46px fb button with 2x20px margin
		// $("div#box-realtime").height($(window).height() - $("div#box-realtime").position()["top"] - (46+20));

		$("div#index-logo").css({'max-width': $(window).width() + 'px'});
	},
};

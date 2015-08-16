var mode_select = {
	/*
	 *  void mode_select.init()
	 *
	 *  initial the mode selection page
	 */
	init: function() {
		// set elements on correct size
		this.resize();

		// set click event for buttons
		$("div#mode-online").click(function() {
			window.location.href = "online.php";
		});
		$("div#mode-battle").click(function() {
			window.location.href = "battle.php";
		});
	},

	/*
	 *  void mode_select.resize()
	 *
	 *  activate after reized the page
	 */
	resize: function() {
		if( $(document).width() < 768 ) {
		    // small device
		    var shared_height = Math.round($(window).height() - $("div#mode-online").position()["top"] - 50) / 2;
		} else {
			// others, max height of the box is 500
		    var shared_height = ( $(window).height() < 550 ) ? $(window).height() - 100 : 500;
		}
		$("div#mode-online").height(shared_height);
		$("div#mode-battle").height(shared_height);
	},
};

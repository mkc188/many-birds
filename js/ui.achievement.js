var achievement = {
	/*
	 *  void achievement.init()
	 *
	 *  initial features in achievement page
	 */
	init: function() {
		// event for botton
		$('.achievement-refresh').click(achievement.update);
	},

	/*
	 *  void achievement.update()
	 *
	 *  on page update
	 */
	update: function() {
		$("#achievement-content").html('<br /><div class="progress progress-striped active" style="width: 50%; margin-left: auto; margin-right: auto;"><div class="progress-bar" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width: 70%"></div></div>');

		$.ajax({ dataType: "html", type: "GET",
			url: "achievement.php?rand=" + Math.random(),
			success: function(response){
				var update = $('#achievement-content', $(response)).html();
				$("#achievement-content").html(update);
			}
		});
	},
};

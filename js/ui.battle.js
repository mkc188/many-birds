var battle = {
	/*
	 *  void battle.init()
	 *
	 *  initial features in battle page
	 */
	init: function() {
		$('.battle-new').click(battle.recreate);
		$('.battle-join').click(battle.join);
	},

	/*
	 *  void battle.recreate()
	 *
	 *  recreate a new room for user
	 */
	recreate: function() {
		$(this).button("loading");

		$.ajax({ dataType: "json", type: "POST",
			url: "battleajax.php?do=recreate&rand=" + Math.random(),
			success: function(response){
				window.location.href = window.location.href;
			}
		});
	},

	/*
	 *  void battle.join()
	 *
	 *  join an existing room
	 */
	join: function() {
		var btn = $(this);
		var hostid = btn.attr("data-path");
		btn.button("loading");

		$.ajax({ dataType: "json", type: "POST",
			url: "battleajax.php?do=join&rand=" + Math.random(),
			data: "hostid=" + hostid,
			success: function(response){
				if( response.success ) {
					window.location.href = "battleplay.php?game=" + hostid;
				} else {
					btn.button("reset");
					$('#danger-message').text(response.message);
					$('#danger-modal').modal('show');
				}
			}
		});
	},
};

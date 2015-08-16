var online = {
	start: function() {},
	end: function() {},
};

var battleplay = {
	hostid: 0,
	time: 120,
	counter: null,

	/*
	 *  void battleplay.init()
	 *
	 *  initial features in battle play page
	 */
	init: function() {
		this.hostid = window.hostid;

		$('.battle-start').click(function() {
			$.ajax({ async: false, dataType: "json", type: "GET",
				url: "battleajax.php?do=hostStart&hostid=" + battleplay.hostid + "&rand=" + Math.random(),
				success: function(response){
				}
			});
		});

		$('.battle-leave').click(function() {
			battleplay.leave();
		});

		// from ui.online.js
		// obtain user information on the beginning
		$.ajax({ async: false, dataType: "json", type: "GET",
			url: "action.php?do=tournament.map&rand=" + Math.random(),
			success: function(response){
				if( response.success ) {
					game_state.map = response.map;
				}
			}
		});

		$.ajax({ async: false, dataType: "json", type: "GET",
			url: "action.php?do=item.active&rand=" + Math.random(),
			success: function(response){
				if( response.auth ) {
					game_state.bird_img = response.items.bird.content_path;
					game_state.bird_height = parseInt(response.items.bird.height);
					game_state.bird_width = parseInt(response.items.bird.width);
					game_state.bg_img = response.items.background.content_path;
				}
			}
		});

		// resize the DIVs 
		if( $(window).height() < 570 ) {
			var shared_height = $(window).height() - 56;
		} else {
			var shared_height = 512;
		}
		$("div#battle-gameplay-container").height(shared_height);
		$("div#online-tournament").css({'max-height': shared_height + 'px'});

		// set the size variables
		this.height = shared_height;
		this.width = $("div#battle-gameplay-container").width();

		// initialize Phaser
		game = new Phaser.Game(this.width, this.height, Phaser.AUTO, 'battle-gameplay-container');

		// add game states
		game.state.add('boot', game_state.boot);
		game.state.add('preloader', game_state.preloader);
		game.state.add('main', game_state.main);

		// start the game
		this.pullStatus();
	},

	/*
	 *  void battleplay.pullStatus()
	 *
	 *  start phaser
	 */
	pullStatus: function() {
		$.ajax({ dataType: "json",
			url: "battleajax.php?do=pull&hostid=" + battleplay.hostid + "&rand=" + Math.random(),
			success: function(data){
				// to ensure the user can re-activate the ajax in some case
				if( data.started ) {
					battleplay.startGame();
				} else {
					setTimeout(function() { battleplay.pullStatus(); }, 250);
				}
			}
		});
	},

	/*
	 *  void battleplay.startGame()
	 *
	 *  start phaser
	 */
	startGame: function() {
		game.state.start('boot');
		battleplay.startCounter();
	},

	/*
	 *  void battleplay.startCounter()
	 *
	 *  start count 2 minutes
	 */
	startCounter: function() {
		this.counter = setInterval(battleplay.timer, 1000);
	},

	/*
	 *  void battleplay.timer()
	 *
	 *  timer
	 */
	timer: function() {
		battleplay.time = battleplay.time - 1;
		$("#time-count").text(battleplay.time + " seconds left");
		if( battleplay.time <= 0 ) {
			clearInterval(battleplay.counter);
			battleplay.end(100);
		}
	},


	/*
	 *  void battleplay.end()
	 *
	 *  submit score and wait 5 sec, then call battleplay.rank()
	 */
	end: function(score) {
		$('#info-modal').modal('show');

		$.ajax({ async: false, dataType: "json", type: "POST",
			url: "battleajax.php?do=end&hostid=" + battleplay.hostid + "&rand=" + Math.random(),
			data: "hostid=" + battleplay.hostid + "&score=" + parseInt(game_state.highScore),
			success: function(response){
				window.setTimeout(function() { battleplay.rank(); }, 4000);
			}
		});
	},

	/*
	 *  void battleplay.rank()
	 *
	 *  show the ranking page of gameroom, allow user click "continue" or "leave"
	 */
	rank: function() {
		$.ajax({ async: false, dataType: "json", type: "POST",
			url: "battleajax.php?do=rank&rand=" + Math.random(),
			data: "hostid=" + battleplay.hostid,
			success: function(response){
				if( response.success ) {
					var tbody = '';
					var ranking = 1;

					for( var i = 0; i < response['rank'].length; i++ ) {
						tpl_fill = {'id':    response['rank'][i]['id'],
									'name':  response['rank'][i]['name'],
									'score': response['rank'][i]['score'],
									'rank':  ( ranking <= 3 ) ? '<img src="img/rank_' + ranking + '.gif">': ranking,
							};
						tbody = tbody + tpl.getFormatted('battleRank', tpl_fill);
						ranking++;
					}
					$($(".battle-ranking-table tbody")[0]).html(tbody);

					$('.battle-continue').click(function() {
						window.location.href = window.location.href;
					});

					$('#info-modal').modal('hide');
					$('#success-modal').modal('show');
				}
			}
		});
	},

	/*
	 *  void battleplay.leave()
	 *
	 *  release seat for current game room
	 */
	leave: function() {
		$.ajax({ async: false, dataType: "json", type: "POST",
			url: "battleajax.php?do=leave&rand=" + Math.random(),
			data: "hostid=" + battleplay.hostid,
			success: function(response){
				window.location.href = "battle.php";
			}
		});
	},
};

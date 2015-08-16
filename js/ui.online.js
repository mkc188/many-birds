var game = {};

var online = {
	game: null,
	height: 0,
	width: 0,

	/*
	 *  void online.init()
	 *
	 *  initial the mode selection page
	 */
	init: function() {
		// resize the DIVs 
		if( $(window).height() < 570 ) {
			var shared_height = $(window).height() - 56;
		} else {
			var shared_height = 512;
		}
		$("div#online-gameplay").height(shared_height);
		$("div#online-tournament").css({'max-height': shared_height + 'px'});

		// do the first update of tournament
		this.tournamentUpdate(false);

		// set click event for buttons
		$(".tournament-refresh").click(function() {
			online.tournamentUpdate(false);
		});
		$(".tournament-invite").click(function() {
			online.inviteSelector();
		});
		$(".view-achievement").click(function() {
			window.location.href = "achievement.php";
		});

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

		// set the size variables
		this.height = shared_height;
		this.width = $("div#online-gameplay").width();

		// initialize Phaser
		game = new Phaser.Game(this.width, this.height, Phaser.AUTO, 'online-gameplay');

		// add game states
		game.state.add('boot', game_state.boot);
		game.state.add('preloader', game_state.preloader);
		game.state.add('main', game_state.main);

		// start the game
		game.state.start('boot');
	},

	/*
	 *  void online.tournamentUpdate()
	 *
	 *  update the ranking list in page
	 */
	tournamentUpdate: function(highlightScore) {
		$.ajax({ dataType: "json",
			url: "action.php?do=tournament.rank&rand=" + Math.random(),
			success: function(data){
				if( data.success ) {
					var myUid = data.id;
					var tbody = '';
					var time = data.time;
					var previousScore = -1;
					var ranking = 0;

					// translate list into html rows
					for( var i = 0; i < data['list'].length; i++ ) {
						if( typeof data['list'][i]['username'] === "undefined" ) {
							// friend not installed
							tpl_fill = {'id':    data['list'][i]['id'],
										'name':  data['list'][i]['name'],
								};
							tbody = tbody + tpl.getFormatted('wtOnlinePageInvite', tpl_fill);

						} else {
							// mark my score
							if( myUid == data['list'][i]['id'] ) {
								var myScore = data['list'][i]['score'];
							}

							// friend installed
							if( parseInt(data['list'][i]['score']) > 0 ) {
								// have record
								var recordscore = data['list'][i]['score'];

								if( recordscore != previousScore ) {
									ranking++;
								}

								if( typeof data['list'][i]['unrank'] === "undefined" ) {
									var rank = ( i < 3 ) ? '<img src="img/rank_' + ranking + '.gif">': ranking;
								} else {
									var rank = '<h4>?</h4>';
								}
								previousScore = data['list'][i]['score'];

								var recordtime = tpl.secondsToStr(time - parseInt(data['list'][i]['timestamp'])) + ' ago';
							} else {
								// no record
								var rank = '<h4>-</h4>';
								var recordscore = '';
								var recordtime = 'no record';
							}

							tpl_fill = {'rank':        rank,
										'id':          data['list'][i]['id'],
										'name':        data['list'][i]['name'],
										'score':       recordscore,
										'time':        recordtime,
										'username':    data['list'][i]['username'],
								};
							tbody = tbody + tpl.getFormatted('wtOnlinePage', tpl_fill);
						}
					}
					$($(".online-tournament-ranking tbody")[0]).html(tbody);

					if( highlightScore ) {
						$('.owt-row-myself').highlight();
					}

					// set event for individual challenge or invite button
					$(".tournament-invite-fd").click(function() {
						online.sendInvitation($(this).attr('data-path'));
					});
					$(".tournament-challenge").click(function() {
						online.sendChallenge($(this).attr('data-path'), myScore);
					});
					$(".owt-elem").each(function(index, e) {
						if( myUid == $($(e).find(".tournament-challenge")).attr('data-path') ) {
							// my record, no challenge can be sent
							$(e).addClass("owt-row-myself");

						} else {
							// set different color on mouse over
							$(e).mouseover(function() {
								$($(e).find(".owt-score-value")[0]).hide();
								$($(e).find(".owt-score-challenge")[0]).show();
								$(e).addClass("owt-row-selected");
							});
							$(e).mouseout(function() {
								$($(e).find(".owt-score-challenge")[0]).hide();
								$($(e).find(".owt-score-value")[0]).show();
								$(e).removeClass("owt-row-selected");
							});
						}
					});
				}
			}
		});
	},

	/*
	 *  void online.inviteSelector()
	 *
	 *  show user friend selector and invite this game
	 */
	inviteSelector: function() {
		facebook.request(tpl.getFormatted('fbInviteMessage', {}), false);
	},

	/*
	 *  void online.sendInvitation()
	 *
	 *  call Facebook API to send invitation message
	 */
	sendInvitation: function(id) {
		facebook.request(tpl.getFormatted('fbInviteMessage', {}), id);
	},

	/*
	 *  void online.inviteSelector()
	 *
	 *  call Facebook API to send challenge message
	 */
	sendChallenge: function(id, score) {
		facebook.request(tpl.getFormatted('fbChallengeMessage', {'score': parseInt(score)}), id);
	},

	/*
	 *  void online.start()
	 *
	 *  game start
	 */
	start: function() {
		$.ajax({ dataType: "json", type: "GET",
			url: "action.php?do=tournament.start&rand=" + Math.random(),
			success: function(response){
				if( response.success ) {
					game_state.gameid = response.gameid;
				}
			}
		});
	},

	/*
	 *  void online.end()
	 *
	 *  game end
	 */
	end: function(score) {
		$.ajax({ dataType: "json", type: "POST",
			url: "action.php?do=tournament.end&rand=" + Math.random(),
			data: "gameid=" + game_state.gameid + "&score=" + score, // <--- here
			success: function(response){
				if( response.success ) {
					// find if new high score,
					// then update weekly tournament list on the right
					if( typeof response.message.isHighScore !== "undefined" ) {
						if( response.message.isHighScore ) {
							online.notifyHighScore();
						}
					}

					// find if new achievement obtained,
					// show a modal to notify user
					if( typeof response.message.newAchievement !== "undefined" ) {
						if( response.message.newAchievement.length > 0 ) {
							online.notifyAchievement(response.message.newAchievement);
						}
					}
				}
			}
		});
	},

	/*
	 *  void online.notifyHighScore()
	 *
	 *  game end
	 */
	notifyHighScore: function() {
		online.tournamentUpdate(true);
	},

	/*
	 *  void online.notifyHighScore()
	 *
	 *  game end
	 */
	notifyAchievement: function(list) {
		var listHtml = '';

		for( var i = 0; i < list.length; i++ ) {
			listHtml = listHtml + '<h4 class="text-warning"><img src="' + list[i]['icon'] + '" /> ' + list[i]['name'] + '</h4>';
		}

		$('#achievement-list').html(listHtml);
		$('#success-modal').modal();
	},
};

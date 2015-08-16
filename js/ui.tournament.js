var tournament = {
	/*
	 *  void tournament.init()
	 *
	 *  initial the week tournament page
	 */
	init: function() {
		// set click event for buttons
		$(".tournament-refresh").click(function() {
			tournament.update();
		});
		$(".tournament-invite").click(function() {
			tournament.inviteSelector();
		});

		// update the ranking list on page load
		this.update();
	},

	/*
	 *  void tournament.update()
	 *
	 *  update the ranking list in page
	 */
	update: function() {
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
							tbody = tbody + tpl.getFormatted('wtFriendInvite', tpl_fill);

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

							var recordAchievement = '';
							if( data['list'][i]['achievement'].length > 0 ) {
								$(data['list'][i]['achievement']).each(function(index, e) {
									recordAchievement = recordAchievement +
										tpl.getFormatted('wtMedal', {'name': e['achievement'], 'path': e['achievement_icon']});
								});
							}

							tpl_fill = {'rank':        rank,
										'id':          data['list'][i]['id'],
										'name':        data['list'][i]['name'],
										'score':       recordscore,
										'time':        recordtime,
										'username':    data['list'][i]['username'],
										'bird-path':   data['list'][i]['bird-icon'],
										'bird-width':  data['list'][i]['bird-width'],
										'bird-height': data['list'][i]['bird-height'],
										'bird-name':   data['list'][i]['bird-name'],
										'medal':       recordAchievement,
								};
							tbody = tbody + tpl.getFormatted('wtFriendInstalled', tpl_fill);
						}
					}
					$($(".tournament-ranking tbody")[0]).html(tbody);

					// set tooltip for medals and birds shown
					$(".wt-medal-img").tooltip();
					$(".wt-bird-img").tooltip();

					// set event for individual challenge or invite button
					$(".tournament-invite-fd").click(function() {
						tournament.sendInvitation($(this).attr('data-path'));
					});
					$(".tournament-challenge").click(function() {
						tournament.sendChallenge($(this).attr('data-path'), myScore);
					});
					$(".wt-elem").each(function(index, e) {
						if( myUid == $($(e).find(".tournament-challenge")).attr('data-path') ) {
							// my record, no challenge can be sent
							$(e).addClass("wt-row-myself");

						} else {
							// set different color on mouse over
							$(e).mouseover(function() {
								$($(e).find(".wt-score-value")[0]).hide();
								$($(e).find(".wt-score-challenge")[0]).show();
								$(e).addClass("wt-row-selected");
							});
							$(e).mouseout(function() {
								$($(e).find(".wt-score-challenge")[0]).hide();
								$($(e).find(".wt-score-value")[0]).show();
								$(e).removeClass("wt-row-selected");
							});
						}
					});
				}
			}
		});

	},

	/*
	 *  void tournament.inviteSelector()
	 *
	 *  show user friend selector and invite this game
	 */
	inviteSelector: function() {
		facebook.request(tpl.getFormatted('fbInviteMessage', {}), false);
	},

	/*
	 *  void tournament.sendInvitation()
	 *
	 *  call Facebook API to send invitation message
	 */
	sendInvitation: function(id) {
		facebook.request(tpl.getFormatted('fbInviteMessage', {}), id);
	},

	/*
	 *  void tournament.inviteSelector()
	 *
	 *  call Facebook API to send challenge message
	 */
	sendChallenge: function(id, score) {
		facebook.request(tpl.getFormatted('fbChallengeMessage', {'score': parseInt(score)}), id);
	},
};

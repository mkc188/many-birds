var tpl = {
	/* weekly tournament list */
	// no var
	wtFriendInstalled: '<tr class="wt-elem">' +
					   '<td class="wt-rank">{{rank}}</td>' +
					   '<td class="wt-name"><h4><img class="img-circle wt-pic" src="https://graph.facebook.com/{{id}}/picture"> <a href="https://facebook.com/{{username}}" target="_blank">{{name}}</a></h4></td>' +
					   '<td class="wt-score"><span class="wt-score-value"><h4>{{score}} <br class="visible-xs"><small>{{time}}</small></h4></span>' +
					   '<span class="wt-score-challenge"><button type="button" class="btn btn-warning tournament-challenge" data-path="{{id}}">send challenge</button></span></td>' +
					   '<td class="wt-bird hidden-xs"><div class="wt-bird-img" style="background-image: url({{bird-path}}); height: {{bird-height}}px; width: {{bird-width}}px;" data-toggle="tooltip" data-placement="top" title="{{bird-name}}"></div></td>' +
					   '<td class="wt-medal hidden-xs">{{medal}}</td>' +
					   '</tr>',
	wtFriendInvite: '<tr class="wt-elem">' +
					'<td class="wt-rank"><h4>-</h4></td>' +
					'<td class="wt-name"><h4><img class="img-circle wt-pic" src="https://graph.facebook.com/{{id}}/picture"> <a href="https://facebook.com/profile.php?id={{id}}" target="_blank">{{name}}</a></h4></td>' +
					'<td class="wt-score"><button type="button" class="btn btn-info tournament-invite-fd" data-path="{{id}}">invite me</button></td>' +
					'<td class="wt-bird hidden-xs"></td><td class="wt-medal hidden-xs"></td></tr>',

	wtOnlinePage: '<tr class="owt-elem">' +
				  '<td class="owt-rank">{{rank}}</td>' +
				  '<td class="owt-name"><h4><img class="img-circle owt-pic" src="https://graph.facebook.com/{{id}}/picture"> <a href="https://facebook.com/{{username}}" target="_blank">{{name}}</a></h4></td>' +
				  '<td class="owt-score"><span class="owt-score-value"><h4>{{score}} <br class="visible-xs"><small>{{time}}</small></h4></span>' +
				  '<span class="owt-score-challenge"><button type="button" class="btn btn-warning tournament-challenge" data-path="{{id}}">challenge</button></span></td>' +
				  '</tr>',
	wtOnlinePageInvite: '<tr class="owt-elem">' +
						'<td class="owt-rank"><h4>-</h4></td>' +
						'<td class="owt-name"><h4><img class="img-circle owt-pic" src="https://graph.facebook.com/{{id}}/picture"> <a href="https://facebook.com/profile.php?id={{id}}" target="_blank">{{name}}</a></h4></td>' +
						'<td class="owt-score"><button type="button" class="btn btn-info tournament-invite-fd" data-path="{{id}}">invite me</button></td>' +
						'</tr>',

	/* medal image in weekly tournament */
	wtMedal: '<img alt="{{name}}" class="wt-medal-img" src="{{path}}" data-toggle="tooltip" data-placement="top" title="{{name}}" /> ',

	/* battle ranking */
	battleRank: '<tr><td>{{rank}}</td>' +
                '<td><img class="img-circle fb-pic" src="https://graph.facebook.com/{{id}}/picture"> {{name}}</td>' +
                '<td><h4 class="text-primary">{{score}}</h4></td></tr>',

	/* facebook message */
	fbInviteMessage: 'Come check out the Many Birds game. It\'s awesome.',
	fbChallengeMessage: 'I got {{score}} score in Many Birds. Can you get a higher score?',

	/*
	 *  string tpl.getFormatted( secondslate's name, array )
	 */
	getFormatted: function(id, param) {
		var content = tpl[id];

		for(var index in param) {
			content = content.replace(new RegExp("{{"+index+"}}","g"), param[index]);
		}

		return content;
	},

	secondsToStr: function(seconds) {
		function numberEnding (number) {
			return (number > 1) ? 's' : '';
		}

		var days = Math.floor((seconds %= 31536000) / 86400);
		if (days) {
			return days + ' day' + numberEnding(days);
		}
		var hours = Math.floor((seconds %= 86400) / 3600);
		if (hours) {
			return hours + ' hour' + numberEnding(hours);
		}
		var minutes = Math.floor((seconds %= 3600) / 60);
		if (minutes) {
			return minutes + ' min' + numberEnding(minutes);
		}
		var seconds = seconds % 60;
		if (seconds) {
			return seconds + ' sec' + numberEnding(seconds);
		}
		return 'just now';
	},
}

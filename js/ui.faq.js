var faq = {
	/*
	 *  void faq.init()
	 *
	 *  initial features in faq page
	 */
	init: function() {
		// set all panels' chevron
		$('#accordion').on('hidden.bs.collapse', faq.toggleChevron);
		$('#accordion').on('shown.bs.collapse', faq.toggleChevron);

		// set vote icons animation
		$('.voting-section').each(function(index, e) {
			$(e).mouseover(function () {
				$(e).find(".upvote-text").fadeIn();
				$(e).find(".downvote-text").fadeIn();
			});
			$(e).find(".upvote").hover(function() {
				$(this).addClass("text-success");
			}, function() {
				$(this).removeClass("text-success");
			});
			$(e).find(".downvote").hover(function() {
				$(this).addClass("text-danger");
			}, function() {
				$(this).removeClass("text-danger");
			});
		});

		// set upvote and downvote event
		$(".upvote").click(function(e) {
			faq.vote($(this).attr('data-target'), 'up');
		});
		$(".downvote").click(function(e) {
			faq.vote($(this).attr('data-target'), 'down');
		});
	},

	/*
	 *  void faq.toggleChevron()
	 *
	 *  show panel collapse state as an icon
	 *  credits: http://stackoverflow.com/questions/18325779/bootstrap-3-collapse-show-state-with-chevron-icon
	 */
	toggleChevron: function (e) {
	    $(e.target)
	        .prev('.panel-heading')
	        .find('i.indicator')
	        .toggleClass('glyphicon-chevron-down glyphicon-chevron-up');
	},

	/*
	 *  void faq.vote()
	 *
	 *  send ajax request for voting an QA pair
	 */
	vote: function(id, vote) {
		$.ajax({ dataType: "json", type: "POST",
			url: "action.php?do=faq.vote&rand=" + Math.random(),
			data: "id=" + id + "&vote=" + vote,
			success: function(response){
				if( response.success ) {
					// on page update voting information (i.e. set class for div)
					$('.voting-section').each(function(index, e) {
						if( $(e).attr('data-path') == response.id ) {
							$(e).find("span").removeClass("upvoted").removeClass("downvoted");
							$(e).find("." + response.value + "vote").addClass(response.value + "voted");
						}
					});

					if( response.value == "down" ) {
						// show contact us dialog
						faq.showContact();
					}
				}
			}
		});
	},

	/*
	 *  void faq.showContact()
	 *
	 *  show contact us dalog for user think the faq answer unhelpful
	 */
	showContact: function() {
		$('#faq-contact').modal('show');
	},
};

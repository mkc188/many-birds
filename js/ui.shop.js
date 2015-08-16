var shop = {
	/*
	 *  void shop.init()
	 *
	 *  initial the item shop page
	 */
	init: function() {
		// set popover for buttons
		$('#shop-available').popover({
			trigger: "hover",
			html: true,
			placement: 'left',
			content: 'available score for purchase: <strong class="text-primary">'+window.available_score+'</strong>',
		});

		// event for refresh botton
		$('.shop-refresh').click(function() {
			$("#shop-content").html('<br /><div class="progress progress-striped active" style="width: 50%; margin-left: auto; margin-right: auto;"><div class="progress-bar" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width: 70%"></div></div>');
			window.location.href = window.location.href;
		});

		// event for all "enable" botton
		$('.bird-enable').click(shop.enable);

		// event for all "purchase" botton
		$('.bird-purchase').click(shop.purchase);
	},

	/*
	 *  void shop.enable()
	 *
	 *  enable specific item for user
	 */
	enable: function() {
		$(this).button('loading');
		var itemid = $(this).attr('data-path');

		$.ajax({ async: false, dataType: "json", type: "POST",
			url: "action.php?do=item.enable&rand=" + Math.random(),
			data: "itemid=" + itemid,
			success: function(response){
				if( response.auth && response.success ) {
					window.location.href = window.location.href;
				} else {
					$('#danger-message').text(response.message);
					$('#danger-modal').modal('show');
				}
			}
		});
	},

	/*
	 *  void shop.purchaseCancel()
	 *
	 *  purchase cancelled, close modal and reset botton
	 */
	purchaseCancel: function(btn) {
		btn.button('reset');
		$('#warning-modal').modal('hide');
	},

	/*
	 *  void shop.purchaseConfirm()
	 *
	 *  do ajax requset for purchasing
	 */
	purchaseConfirm: function(itemid, btn) {
		$.ajax({ async: false, dataType: "json", type: "POST",
			url: "action.php?do=item.buy&rand=" + Math.random(),
			data: "itemid=" + itemid,
			success: function(response){
				if( response.auth && response.success ) {
					window.location.href = window.location.href;
				} else {
					$('#purchase-confirm').button('reset');
					shop.purchaseCancel(btn);

					$('#danger-message').text(response.message);
					$('#danger-modal').modal('show');
				}
			}
		});
	},

	/*
	 *  void shop.purchase()
	 *
	 *  show confirm modal when user first click purchase botton
	 */
	purchase: function() {
		$(this).button('loading');
		var btn = $(this);
		var itemid = $(this).attr('data-path');
		var name = $(this).attr('data-name');
		var price = $(this).attr('data-price');

		if( parseInt(price) == 0 ) {
			this.purchaseConfirm(itemid);

		} else {
			$('#warning-price').text(price + ' score');
			$('#warning-name').text(name);

			// event for botton in modal
			$('#purchase-cancel').unbind('click').click(function() {
				shop.purchaseCancel(btn);
			});
			$('#purchase-confirm').unbind('click').click(function() {
				$('#purchase-confirm').button('loading');
				shop.purchaseConfirm(itemid, btn);
			});

			$('#warning-modal').modal();
		}
	},
};

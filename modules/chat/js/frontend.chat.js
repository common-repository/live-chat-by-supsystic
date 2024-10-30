jQuery(document).ready(function(){
	if(typeof(lcsChatsFromFooter) !== 'undefined' && lcsChatsFromFooter && lcsChatsFromFooter.length) {
		lcsEngines = typeof(lcsEngines) === 'undefined' ? [] : lcsEngines;
		lcsEngines = lcsEngines.concat( lcsChatsFromFooter );
	}
	if(typeof(lcsEngines) !== 'undefined' && lcsEngines && lcsEngines.length) {
		jQuery(document).trigger('lcsBeforeChatsInit', lcsEngines);
		for(var i = 0; i < lcsEngines.length; i++) {
			jQuery('body').append( lcsEngines[ i ].tpl.rendered_html );
			g_lcsChats.add({
				engine: lcsEngines[ i ]
			});
		}
		_lcsBindOnElementClickChats();
		jQuery(document).trigger('lcsAfterChatsInit', lcsEngines);
	}
});
function _lcsBindOnElementClickChats() {
	var clickOnLinks = jQuery('[href*="#lcsShowChat_"]');
	if(clickOnLinks && clickOnLinks.size()) {
		clickOnLinks.each(function(){
			jQuery(this).click(function(){
				var chatId = jQuery(this).attr('href');
				if(chatId && chatId != '') {
					chatId = chatId.split('_');
					chatId = chatId[1] ? parseInt(chatId[1]) : 0;
					if(chatId) {
						lcsShowChat( chatId );
					}
				}
				return false;
			});
		});
	}
	var clickOnMenuItems = jQuery('[title*="#lcsShowChat_"]');	// You can also set this in title - for menu items for example
	if(clickOnMenuItems && clickOnMenuItems.size()) {
		clickOnMenuItems.each(function(){
			var title = jQuery(this).attr('title')
			,	matched = title.match(/#lcsShowChat_(\d+)/);
			if(matched && matched.length == 2) {
				var chatId = parseInt(matched[1]);
				if(chatId) {
					jQuery(this)
					.data('chat-id', chatId)
					.attr('title', str_replace(title, matched[0], ''))
					.click(function(){
						lcsShowChat( jQuery(this).data('chat-id') );
						return false;
					});
				}
			}
		});
	}
}
function lcsShowChat( engineId ) {
	var engine = g_lcsChats.getByIngineId( engineId );
	if(engine) {
		engine.showChat();
	} else
		console.log('CAN NOT FIND ENGINE FOR CHAT '+ engineId+ ' !!!');
}

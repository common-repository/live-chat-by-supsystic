function lcsChat(params) {
	params = params || {};
	this._idleDelay = 7 * 1000;
	this._timeInterval = null;
	this._isAdmin = params.is_admin ? true : false;
	this._session = params.session ? params.session : {};
	this._user = null;
	this._agent = null;
	this._engine = params.engine;
	this._stage = null;
	this._messagesList = {};
	this._$ = null;
	this._$style = null;
	this._opened = false;
	this._$eyeCatch = null;
	this._soundObj = null;
	this._enbSound = false;
	this._$soundBtn = null;
	this._addOptsOpened = false;
	this._$optsShell = null;
	this._$optsBtn = null;
	this._visible = false;
	this._lastOpenedStage = false;
	this.init();
}
lcsChat.prototype.init = function() {
	this.getShell();
	this.afterGetShell();
};
lcsChat.prototype.afterGetShell = function() {
	if(typeof(this._$) != 'undefined' && this._$.length) {
		this.applyShellFunctions();
	} else {
		setTimeout(this.afterGetShell, 500);
	}
};
lcsChat.prototype.applyShellFunctions = function() {
	this._initIdleDelay();
	if(!this._isAdmin) {
		this._initSession();
		this._moveStyles();
		this._bindLove();
		this._bindShow();
		this._bindClose();
		this._bindOpen();
		this._bindRegistration();
		this._checkPositionClass();
		this._bindDraggable();
		this._bindAdditionalOptsBtns();
		// Make height - responsive
		this._adaptHeight();
		var self = this;
		jQuery(window).resize(function(){
			self._adaptHeight();
		});
		this._bindTriggers();
	}
	this._bindSendMsg();
	this._initSound();
	if(this._session.status_code == 'in_progress') {
		this._bindIdle();
	}
};
lcsChat.prototype._initIdleDelay = function() {
	if(this._engine.params.idle_delay && parseInt(this._engine.params.idle_delay)) {
		this._idleDelay = parseInt(this._engine.params.idle_delay) * 1000;
	}
};
lcsChat.prototype._initSession = function() {
	var sessionId = parseInt(this._$.data('session_id'));
	if(sessionId) {
		this._session.id = sessionId;
	}
};
lcsChat.prototype._moveStyles = function() {
	var $style = jQuery('<style type="text/css" id="'+ this._engine.tpl.view_html_id+ '_style" />')
	,	$replacerTag = jQuery('#lcsChatStylesHidden_'+ this._engine.tpl.view_id);
	$style.appendTo('body');
	$style.html( $replacerTag.html() );
	$replacerTag.remove();
};
lcsChat.prototype._bindLove = function() {
	if(parseInt(toeOptionLcs('add_love_link'))) {
		this._$.append( toeOptionLcs('love_link_html') );
	}
};
lcsChat.prototype._bindShow = function() {
	var self = this;
	switch(self._engine.params.main.show_on) {
		case 'page_load':
			var delay = 0;
			if(self._engine.params.main.show_on_page_load_enb_delay && parseInt(self._engine.params.main.show_on_page_load_enb_delay)) {
				self._engine.params.main.show_on_page_load_delay = parseInt( self._engine.params.main.show_on_page_load_delay );
				if(self._engine.params.main.show_on_page_load_delay) {
					delay = self._engine.params.main.show_on_page_load_delay * 1000;
				}
			}
			if(delay) {
				setTimeout(function(){
					self.checkChatShow( self._engine );
				}, delay);
			} else {
				self.checkChatShow( self._engine );
			}
			break;
		case 'click_on_page':
			jQuery(document).click(function(){
				if(!self._engine.click_on_page_displayed) {
					self.checkChatShow( self._engine );
					self._engine.click_on_page_displayed = true;
				}
			});
			break;
		case 'click_on_element':
			// @see _lcsBindOnElementClickChats()
			break;
		case 'scroll_window':
			jQuery(window).scroll(function(){
				if(parseInt(self._engine.params.main.show_on_scroll_window_enb_perc_scroll)) {
					var percScroll = parseInt( self._engine.params.main.show_on_scroll_window_perc_scroll );
					if(percScroll) {
						var docHt = jQuery(document).height()
						,	wndHt = jQuery(window).height()
						,	wndScrollPos = jQuery(window).scrollTop()
						,	wndScrollHt = docHt - wndHt
						,	currScrollPerc = wndScrollPos * 100 / wndScrollHt;
						if(wndScrollHt > 0 && currScrollPerc < percScroll) {
							return;
						}
					}
				}
				if(!self._engine.scroll_window_displayed) {
					var delay = 0;
					if(self._engine.params.main.show_on_scroll_window_enb_delay && parseInt(self._engine.params.main.show_on_scroll_window_enb_delay)) {
						self._engine.params.main.show_on_scroll_window_delay = parseInt( self._engine.params.main.show_on_scroll_window_delay );
						if(self._engine.params.main.show_on_scroll_window_delay) {
							delay = self._engine.params.main.show_on_scroll_window_delay * 1000;
						}
					}
					if(delay) {
						setTimeout(function(){
							self.checkChatShow( self._engine );
						}, delay);
					} else {
						self.checkChatShow( self._engine );
					}
					self._engine.scroll_window_displayed = true;
				}
			});
			break;
	}
};
lcsChat.prototype.checkChatShow = function() {
	var showKey = 'lcs_show_'+ this._engine.id
	,	prevShow = getCookieLcs( showKey );

	if(this._engine.params.main.show_to == 'first_time_visit' && prevShow)
		return;

	if(!prevShow) {
		var saveCookieTime = parseInt(this._engine.params.main.show_to_first_time_visit_days);
		saveCookieTime = isNaN(saveCookieTime) ? 30 : saveCookieTime;
		if(!saveCookieTime)
			saveCookieTime = null;	// Save for current session only
		setCookieLcs('lcs_show_'+ this._engine.id, 1, saveCookieTime);
	}

	if(this._checkDisplayTime())
		return;
	this.showChat({
		isUnique: prevShow ? 0 : 1
	});
};
/**
 * Check chat show time - "Time display settings" in admin area
 */
lcsChat.prototype._checkDisplayTime = function() {
	var timeToNum = function(timeStr) {
		var add = strpos(timeStr, 'pm') !== false ? 12 : 0;
		var time = parseFloat(str_replace(str_replace(str_replace(timeStr, 'am', ''), 'pm', ''), ':', '.'));
		if(toeInArray(time, [12, 12.3]) === -1) {
			time += add;
		} else if(!add) {
			time -= 12;
		}
		return time;
	};
	var currDate = new Date();

	if(this._engine.params.main.enb_show_time
		&& this._engine.params.main.show_time_from
		&& this._engine.params.main.show_time_to
		&& this._engine.params.main.show_time_from != this._engine.params.main.show_time_to
	) {
		var timeFrom = timeToNum(this._engine.params.main.show_time_from)
		,	timeTo = timeToNum(this._engine.params.main.show_time_to)
		,	currTime = currDate.getHours() + (currDate.getMinutes() / 100);

		if(currTime < timeFrom || currTime > timeTo) {
			return true;
		}
	}else if( this._engine.params.main.show_to === 'until_make_action'){

		var actionsKey = 'lcs_actions_'+ this._engine.id
		,	actions = getCookieLcs( actionsKey );

		var skipDays = parseInt(this._engine.params.main.show_to_until_make_action_days) ;
		var timeTo = actions[Object.keys(actions)[0]] ;
		if(timeTo.length > 0){
			var timeToSkip = new Date(timeTo);
			timeToSkip.setDate(timeToSkip.getDate() + skipDays);

			if(timeToSkip > currDate) {
				return true;
			}
		}

	}

	return false;
}
lcsChat.prototype.showChat = function(params) {
	if(!lcsCorrectJqueryUsed()) {
		lcsReloadCoreJs(this.showChat, [params]);
		return;
	}
	params = params || {};
	
	this._$.show();
	this._addStat( 'show', params.isUnique );	// Save show chat statistics
	this._showBlock( 'closed' );
	this._positionChat();
	
	/*if(this._engine.params.anim && !this._engine.resized_for_wnd) {
		this._handleChatAnimationShow();
	} else {
		this._$.show();
	}*/
	
	this._engine.is_visible = true;
	this._engine.is_rendered = true;	// Rendered at least one time
	this._visible = true;
	jQuery(document).trigger('lcsAfterChatsActionShow', this);
};
lcsChat.prototype._handleChatAnimationShow = function() {
	var self = this;
	var preAnimClass = this._engine.params.anim.old ? 'magictime' : 'animated';
	this._$.animationDuration( this._engine.params.anim_duration, true );
	this._$.removeClass(this._engine.params.anim.hide_class);
	this._$.addClass(preAnimClass+ ' '+ this._engine.params.anim.show_class).show();
	// This need to make properly work responsivness
	setTimeout(function(){
		this._$.removeClass(preAnimClass+ ' '+ self._engine.params.anim.show_class);
	}, parseInt(this._engine.params.anim_duration));
};
lcsChat.prototype._addStat = function( action, isUnique ) {
	jQuery.sendFormLcs({
		msgElID: 'noMessages'
	,	data: {mod: 'statistics', action: 'add', id: this._engine.id, type: action, is_unique: isUnique, 'connect_hash': this._engine.connect_hash}
	});
};
lcsChat.prototype._showBlock = function( stage ) {
	var $blocks = this._$.find('.lcsChatBlock')
	,	notList = [];
	$blocks.show();
	switch(stage) {
		case 'closed':
			notList = ['.lcsBar'];
			break;
		case 'registration':
			notList = ['.lcsBar', '.lcsRegistration'];
			break;
		case 'wait':
			notList = ['.lcsBar', '.lcsWait'];
			break;
		case 'chat':
			notList = ['.lcsBar', '.lcsChat'];
			break;
		case 'complete':
			notList = ['.lcsBar', '.lcsComplete'];
			break;
	}
	$blocks.not( notList.join(',') ).hide();
};
lcsChat.prototype._openChatStage = function ( stage ) {
	if(stage == 'closed') {
		this._opened = false;
		this._$.removeClass('lcsOpened');
	} else {
		this._opened = true;
		this._$.addClass('lcsOpened');
	}
	// Eye catcher don't need to be visible when we start chat
	if(this._$eyeCatch) {
		this._$eyeCatch.remove();
		this._$eyeCatch = null;
	}
	this._stage = stage;
	this._showBlock( stage );
	switch(stage) {
		case 'wait':
			this._bindIdle();
			break;
		case 'chat':
			this._checkAgentJoinedHtml();
			this._bindIdle();
			break;
		case 'complete':
			this._unBindIdle();
			break;
	}
	if(stage != 'closed') {
		this._lastOpenedStage = stage;
	}
};
lcsChat.prototype._positionChat = function() {
	jQuery(document).trigger('lcsResize', this);
	if(!this._$.positioned_outside) {	// Make available - re-position chat from outside modules
		var cssPos = {}
		,	sideD = parseInt(this._engine.params.chat_padding);
		if(isNaN(sideD)) {
			sideD = 40;	// 40px by default
		}
		switch(this._engine.params.chat_position) {
			case 'top_left':
				cssPos = {'top': '0px', 'left': sideD+ 'px', 'border-bottom-right-radius': '5px', 'border-bottom-left-radius': '5px', 'border-top-right-radius': '0', 'border-top-left-radius': '0'};
				break;
			case 'top_center':
				cssPos = {'top': '0px', 'left': '50%', 'transform': 'translate(-50%, 0)', 'border-bottom-right-radius': '5px', 'border-bottom-left-radius': '5px', 'border-top-right-radius': '0', 'border-top-left-radius': '0'};
				break;
			case 'top_right':
				cssPos = {'top': '0px', 'right': sideD+ 'px', 'border-bottom-right-radius': '5px', 'border-bottom-left-radius': '5px', 'border-top-right-radius': '0', 'border-top-left-radius': '0'};
				break;
			case 'bottom_left':
				cssPos = {'bottom': '0px', 'left': sideD+ 'px'};
				break;
			case 'bottom_center':
				cssPos = {'bottom': '0px', 'left': '50%', 'transform': 'translate(-50%, 0)'};
				break;
			case 'bottom_right':
				cssPos = {'bottom': '0px', 'right': sideD+ 'px'};
				break;
		}
		this._$.css( cssPos );
	}
};
lcsChat.prototype._checkPositionClass = function() {
	this._$.get(0).className = this._$.get(0).className.replace(/\lcsPos_.*?\b/g, '');
	this._$.addClass('lcsPos_'+ this._engine.params.chat_position);
	this._$.attr('data-position', this._engine.params.chat_position);
};
lcsChat.prototype._reBindIdle = function() {
	this._unBindIdle();
	this._bindIdle();
};
lcsChat.prototype._bindIdle = function() {
	if(this._timeInterval) {
		return;
	}
	var self = this;
	this._timeInterval = setInterval(function(){
		self._idle();
	}, this._idleDelay);
};
lcsChat.prototype._idle = function( params ) {
	params = params || {};
	var self = this
	,	idleData = {mod: 'chat', action: 'getMsgList'};
	if(this._session && this._session.id) {
		idleData.session_id = this._session.id;
	}
	jQuery.sendFormLcs({
		msgElID: 'noMessages'
	,	data: idleData
	,	onSuccess: function( res ) {
			if(!res.error) {
				if(res.data.messages) {
					if(self._stage == 'wait') {
						self._openChatStage('chat');
					}
					self.buildMsgList({
						messages: res.data.messages
					,	notNewMsgs: params.firstTime ? true : false
					});
				}
			}
		}
	});
};
lcsChat.prototype._unBindIdle = function() {
	clearInterval( this._timeInterval );
	this._timeInterval = null;
}; 
lcsChat.prototype.setStatus = function(status) {
	if(this._session.status_code != status) {
		this._session.status_code = status;
	}
};
lcsChat.prototype.getShell = function() {
	if(!this._$) {
		this._$ = this._isAdmin
			? jQuery('#lcsAgentChatShell')
			: jQuery('#'+ this._engine.tpl.view_html_id);
	}
	return this._$;
};
lcsChat.prototype.getStyle = function() {
	if(!this._$style) {
		this._$style = jQuery('#'+ this._engine.tpl.view_html_id+ '_style');
	}
	return this._$style;
};
lcsChat.prototype._bindClose = function() {
	var self = this;
	this._$.find('.lcsChatClose').click(function(){
		self.closeChat();
		return false;
	});
};
lcsChat.prototype.closeChat = function() {
	if(this._engine.params.anim) {
		this._handleChatAnimationHide();
	} else {
		this._$.hide();
	}
	this._engine.is_visible = false;
};
lcsChat.prototype._handleChatAnimationHide = function() {
	var self = this;
	var preAnimClass = this._engine.params.anim.old ? 'magictime' : 'animated';
	self._$.removeClass( this._engine.params.anim.show_class ).addClass( this._engine.params.anim.hide_class );
	setTimeout(function(){
		self._$.removeClass( preAnimClass ).hide();
	}, parseInt(this._engine.params.anim_duration) );
};
lcsChat.prototype._bindOpen = function() {
	var self = this;
	self._$.find('.lcsBar').click(function(){
		self.open();
		return false;
	});
	self._$.find('.lcsBarCloseBtn').click(function(){
		self.close();
		return false;
	});
};
lcsChat.prototype.open = function() {
	if(!this._opened) {
		this._opened = true;
		var stage = this._lastOpenedStage ? this._lastOpenedStage : false;
		if(!stage) {
			this._addStat( 'open' );
			if(this._engine.params.reg_type == 'none' || this._session.id) {
				this._startSession();
				if(this._engine.params.main.wait_agent_response) {
					stage = 'wait';
				} else {
					stage = 'chat';
					if(this._session.id) {	// If user return from prev. session - let's show all prev. messages right now
						this._idle({
							firstTime: true
						});
					}
				}
			} else {
				stage = 'registration';
			}
		}
		this._openChatStage( stage );
	}
};
lcsChat.prototype.close = function() {
	if(this._opened) {
		var stage = 'closed';
		this._openChatStage( stage );
	}
};
lcsChat.prototype._startSession = function( data, clb ) {
	data = data || {};
	if(!this._session.id || data.autostart) {
		var self = this;
		var sendData = {mod: 'chat', action: 'startSession', id: this._engine.id, url: window.location.href, _supnonce: this._engine.connect_hash};
		if(data) {
			sendData = jQuery.extend(sendData, data);
		}
		jQuery.sendFormLcs({
			msgElID: 'noMessages'
		,	data: sendData
		,	onSuccess: function(res) {
				if(!res.error) {
					self._session.id = res.data.session_id;
					if(res.data.messages) {
						self.buildMsgList({
							messages: res.data.messages
						});
					}
					if(typeof(clb) == 'function') {
						clb();
					}
				}
			}
		});
	}
};
lcsChat.prototype._bindRegistration = function() {
	if(this._engine.params.reg_type != 'none') {
		var self = this;
		self._$.find('.lcsRegForm').submit(function(){
			var submitBtn = jQuery(this).find('input[type=submit]')
			,	form = this
			,	msgEl = jQuery(this).find('.lcsChatRegMsg');
			submitBtn.attr('disabled', 'disabled');
			jQuery(this).sendFormLcs({
				msgElID: msgEl
			,	onSuccess: function(res){
					jQuery(form).find('input[type=submit]').removeAttr('disabled');
					if(!res.error) {
						var parentShell = jQuery(form).parents('.lcsRegistration');

						msgEl.appendTo( parentShell );
						jQuery(form).animateRemoveLcs( 300 );
						self.chatRegSuccess();
					}
				}
			});
			return false;
		});
	}
};
lcsChat.prototype.chatRegSuccess = function () {
	this._openChatStage( 'wait' );
	this._setActionDone( 'registration' );
};
lcsChat.prototype._setActionDone = function( action ) {
	var actionsKey = 'lcs_actions_'+ this._engine.id
	,	actions = getCookieLcs( actionsKey );
	if(!actions)
		actions = {};
	actions[ action ] = (new Date()).toString();
	var saveCookieTime = 30;
	saveCookieTime = isNaN(saveCookieTime) ? 30 : saveCookieTime;
	if(!saveCookieTime)
		saveCookieTime = null;	// Save for current session only
	setCookieLcs(actionsKey, actions, saveCookieTime);
	this._addStat( action );
	jQuery(document).trigger('lcsAfterChatsActionDone', this);
};
lcsChat.prototype.buildMsgList = function( params ) {
	params = params || {};
	var self = this;
	var _build = function( messages ) {
		if(messages && messages.session) {
			self._session = messages.session;
		}
		if(!self._isAdmin) {
			self._checkSessionUserChanged();
		}
		// Check if messages was changed
		if(messages && messages.list) {
			if(self._messagesList && JSON.stringify(self._messagesList) == JSON.stringify(messages.list))
				return;
			self._messagesList = messages.list;
		} else
			return;
		
		var $msgShell = self._$.find('.lcsMessages')
		,	$exampleMsgShell = self._$.find('.lcsMessagesExShell')  
		,	$agentMsgShell = $exampleMsgShell.find('.lcsAgentMsgShell')
		,	$userMsgShell = $exampleMsgShell.find('.lcsUserMsgShell')
		,	$msgEnd = $exampleMsgShell.find('.lcsMsgEnd')
		,	users = {};
		
		$msgShell.html('');
		if(messages && messages.users) {
			for(var i = 0; i < messages.users.length; i++) {
				users[ parseInt(messages.users[i].id) ] = messages.users[i];
			}
		}
		for(var id in users) {
			if(users[ id ].position_code != 'user') {
				self._checkAgentJoinedHtml( users[ id ] );
				break;
			}
		}
		if(messages && messages.list && messages.list.length) {
			for(var i = 0; i < messages.list.length; i++) {
				var userId = parseInt(messages.list[i].user_id)
				,	user = (userId && users[ userId ]) ? users[ userId ] : false
				,	isAgent = (user && user.position_code != 'user')
				,	$newMsgShell = isAgent
						? $agentMsgShell.clone()
						: $userMsgShell.clone()
				,	$name = isAgent 
						? $newMsgShell.find('.lcsAgentNameShell') 
						: $newMsgShell.find('.lcsUserNameShell')
				,	$time = $newMsgShell.find('.lcsMsgTime');
				if($name && $name.size()) {
					if(user) {
						$name.html( user.name );
					} else {
						$name.html( toeLangLcs('You') );
					}
				}
				if($time && $time.size()) {
					var hideTime = true;
					if(messages.list[i].date_created) {
						var msgDate = new Date( messages.list[i].date_created_format );
						if(msgDate) {
							hideTime = false;
							$time.html(msgDate.getHours()+ ':'+ msgDate.getMinutes());
						}
					}
					if(hideTime) {
						$time.hide();
					}
				}
				$newMsgShell.find('.lcsMsg').html( messages.list[i].msg );
				$msgShell.append( $newMsgShell );
				if(user) {
					isAgent ? self._agent = user : self._user = user;
				}
			}
			if($msgEnd && $msgEnd.size()) {
				$msgShell.append( $msgEnd.clone() );
			}
			var $scrollElement = null;
			if(self._isAdmin) {
				$scrollElement = self._$.find('.lcsMessagesShell');
			} else {
				$scrollElement = parseInt(self._engine['tpl']['params']['msg_height']) 
					? self._$.find('.lcsMessagesShell') 
					: self._$;
			}
			$scrollElement.animate({
				scrollTop: $scrollElement.prop('scrollHeight')
			}, 500);
			if(messages.session) {
				self._session = messages.session;
			}
			if(self._isAdmin) {
				self._checkSessionAdminStatusChanged();
			} else {
				self._checkAgentAvatar();
			}
			if(!params.notNewMsgs)	// New messages can counting just sent user message, or first time loaded messages
				self._playSound();
		}
	};
	if(params.messages) {
		_build( params.messages );
	} else {
		var dataToGet = {
			mod: 'chat', action: 'getMsgList'
		};
		if(params.session_id) {
			dataToGet.session_id = params.session_id;
		}
		jQuery.sendFormLcs({
			msgElID: 'noMessages'
		,	data: dataToGet
		,	onSuccess: function(res) {
				if(!res.error) {
					_build( res.data.messages );
				}
			}
		});
	}
};
lcsChat.prototype._checkAgentAvatar = function() {
	if(this._agent && this._agent.avatar) {
		var $avatarImgs = this._$.find('.lcsAgentAvatartShell')
		,	self = this;
		if($avatarImgs && $avatarImgs.size()) {
			$avatarImgs.each(function(){
				jQuery(this).find('img').attr('src', self._agent.avatar);
			});
		}
	}
};
lcsChat.prototype._checkSessionAdminStatusChanged = function() {
	jQuery('#lcsChatStatusSel').val( this._session.status_id );
	jQuery('#lcsChatStatusSel').trigger('chosen:updated');
};
lcsChat.prototype._checkSessionUserChanged = function() {
	if(this._session.status_code == 'complete') {
		this._openChatStage('complete');
	}
};
lcsChat.prototype._checkAgentJoinedHtml = function( agent ) {
	var $agentJoined = this._$.find('.lcsChatOperJoinedMsg');
	if($agentJoined && $agentJoined.size()) {
		if(agent) {
			$agentJoined.html( lcsCodeReplace($agentJoined.html(), agent) ).show();
		} else
			$agentJoined.hide();
	}
	if(agent) {
		var $agentName = this._$.find('.lcsAgentName');
		if($agentName && $agentName.size()) {
			$agentName.html( agent.name );
		}
	}
};
lcsChat.prototype._bindSendMsg = function() {
	var self = this
	,	$form = this._$.find('.lcsInputForm');
	$form.submit(function(){
		var $btn = jQuery(this).find('button:first');
		if(!$btn || !$btn.size()) {
			$btn = jQuery(this).find('input[type=submit]:first');
		}
		var $form = jQuery(this);
		jQuery(this).sendFormLcs({
			btn: $btn
		,	onSuccess: function(res) {
				if(!res.error) {
					$form.find('.lcsChatMsg').val('');
					if(res.data.messages) {
						self._reBindIdle();
						self.buildMsgList({
							messages: res.data.messages
						,	notNewMsgs: true
						});
					}
				}
			}
		});
		return false;
	});
	$form.find('textarea').keydown(function (e) {
		if (e.ctrlKey && e.keyCode == 13) {	// Ctrl + Enter
			$form.submit();
		}
	});
};
lcsChat.prototype._adaptHeight = function() {
	var wndHeight = jQuery(window).height();
	this._$.css({
		'max-height': wndHeight
	});
};
lcsChat.prototype.getEngineId = function() {
	return this._engine ? this._engine.id : false;
};
lcsChat.prototype._bindTriggers = function() {
	if(this._engine.triggers && this._engine.triggers.length) {
		for(var i = 0; i < this._engine.triggers.length; i++) {
			if(!parseInt(this._engine.triggers[ i ].active)) continue;
			var make = false
			,	makeConditions = []
			,	delay = 0;
			if(this._engine.triggers[ i ].conditions && this._engine.triggers[ i ].conditions.length) {
				for(var j = 0; j < this._engine.triggers[ i ].conditions.length; j++) {
					var c = this._engine.triggers[ i ].conditions[ j ];
					makeConditions[ j ] = false;
					switch(c.type_code) {
						case 'agent_online':
							if((c.equal_code == 'is_true' && this._engine.current_state.agent_online) 
								|| (c.equal_code == 'is_false' && !this._engine.current_state.agent_online)
							) {
								makeConditions[ j ] = true;
							}
							break;
						case 'pages_posts':
							var onPage = toeInArrayLcs(this._engine.current_state.page_id, c.value);
							if((c.equal_code == 'equal' && onPage) 
								|| (c.equal_code == 'not_equal' && !onPage)
							) {
								makeConditions[ j ] = true;
							}
							break;
						case 'country':
							var onCountry = toeInArrayLcs(this._engine.current_state.country_code, c.value);
							if((c.equal_code == 'equal' && onCountry) 
								|| (c.equal_code == 'not_equal' && !onCountry)
							) {
								makeConditions[ j ] = true;
							}
							break;
						case 'day_hour':
							var serverDate = new Date( this._engine.current_state.date )
							,	serverHour = serverDate.getHours()
							,	hour = c.value;
							if((c.equal_code == 'equal' && serverHour == hour) 
								|| (c.equal_code == 'not_equal' && serverHour != hour)
								|| (c.equal_code == 'more_then' && serverHour > hour)
								|| (c.equal_code == 'less_then' && serverHour < hour)
							) {
								makeConditions[ j ] = true;
							}
							break;
						case 'week_day':
							var serverDate = new Date( this._engine.current_state.date )
							,	onDay = toeInArrayLcs(serverDate.getDay(), c.value);
							if((c.equal_code == 'equal' && onDay) 
								|| (c.equal_code == 'not_equal' && !onDay)
							) {
								makeConditions[ j ] = true;
							}
							break;
						case 'page_url':
							var currUrl = window.location.href
							,	checkUrl = c.value
							,	urlRegExp = RegExp(checkUrl, 'i');
							if((c.equal_code == 'equal' && currUrl == checkUrl) 
								|| (c.equal_code == 'not_equal' && currUrl != checkUrl)
								|| (c.equal_code == 'like' && urlRegExp.test(currUrl)) 
								|| (c.equal_code == 'not_like' && !urlRegExp.test(currUrl)) 
							) {
								makeConditions[ j ] = true;
							}
							break;
						case 'time_on_page':
							makeConditions[ j ] = true;
							var min = parseInt(c.value.min)
							,	sec = parseInt(c.value.sec)
							,	totalMs = 0;
							if(min) {
								totalMs += min * 60 * 1000;
							}
							if(sec) {
								totalMs += sec * 1000;
							}
							if(totalMs) {
								delay = totalMs;
							}
							break;
					}
				}
			}
			if(makeConditions.length) {
				make = true;
				for(var j = 0; j < makeConditions.length; j++) {
					if(!makeConditions[ j ]) {
						make = false;
						break;
					}
				}
			}
			// If conditions allow us to do something - let's do selected actions
			if(make) {
				this._runTrigger( this._engine.triggers[ i ], delay );
			}
		}
	}
};
lcsChat.prototype._getTriggersByConditionCode = function(conditionCode) {
	var res = [];
	for(var i = 0; i < this._engine.triggers.length; i++) {
		if(!parseInt(this._engine.triggers[ i ].active)) continue;
		if(this._engine.triggers[ i ].conditions && this._engine.triggers[ i ].conditions.length) {
			for(var j = 0; j < this._engine.triggers[ i ].conditions.length; j++) {
				if(this._engine.triggers[ i ].conditions[ j ].type_code == conditionCode) {
					res.push( this._engine.triggers[ i ] );
				}
			}
		}
	}
	return res.length ? res : false;
};
/**
 * Run trigger actions
 * @param {object|array} trigger Trigger object
 * @param {int} delay Trigger action delay if required
 */
lcsChat.prototype._runTrigger = function( trigger, delay ) {
	// It can be list of triggers to run
	var triggers = jQuery.isArray(trigger) ? trigger : [ trigger ];
	for(var i = 0; i < triggers.length; i++) {
		var currTrigger = triggers[ i ];
		for(var actId in currTrigger.actions) {
			if(parseInt(currTrigger.actions[ actId ].enb)) {
				switch(currTrigger.actions[ actId ].code) {
					case 'show_eye_cach':
						this.checkShowEyeCatch( currTrigger.actions[ actId ], currTrigger, delay );
						break;
					case 'auto_start':
						this.checkShowAutoStart( currTrigger.actions[ actId ], currTrigger, delay );
						break;
					case 'auto_open':
						this.checkShowAutoOpen( currTrigger.actions[ actId ], currTrigger, delay );
						break;
				}
			}
		}
	}
};
lcsChat.prototype.checkShowEyeCatch = function( params, trigger, delay ) {
	if(!this._opened) {
		if(delay) {
			var self = this;
			setTimeout(function(){
				self.showEyeCatch( params );
			}, delay);
		} else
			this.showEyeCatch( params );
	}
};
lcsChat.prototype.showEyeCatch = function( params ) {
	if(!this._opened) {
		var	self = this;
		this._$eyeCatch = jQuery('<img src="'+ params.eye_img+ '" class="lcsEyeCatchImg" />')
		.appendTo('body')
		.load(function(){
			var pos = self._$.offset()
			,	chatWidth = self._$.width()
			,	chatHeight = self._$.outerHeight()
			,	width = jQuery(this).width()
			,	height = jQuery(this).height()
			,	preAnimClass = 'animated'
			,	animDuration = 1000;	//TODO: Move this to opts

			if(parseInt(params.anim_speed)) {
				animDuration = parseInt(params.anim_speed);
			}

			var $attr = self._$.attr('data-position');

			if ($attr.indexOf('top') !== -1){
				//top
				pos.top = self._$.outerHeight();
				pos.left = pos.left + (chatWidth / 2) - (width / 2);

				jQuery(this).css({
						'top': pos.top
					,	'left': pos.left
				});
			}else{
				//bottom
				pos.bottom = self._$.outerHeight();
				pos.left = pos.left + (chatWidth / 2) - (width / 2);

				jQuery(this).css({
						'bottom': pos.bottom
					,	'left': pos.left
				});
			}
			
			self._$eyeCatch.animationDuration( animDuration, true );
			self._$eyeCatch.addClass(preAnimClass+ ' '+ params.anim.show_class).show();
			// This need to make properly work responsivness
			setTimeout(function(){
				if(self._$eyeCatch) {	// Chat can be opened during animation process - so there will be no eye catch at this particular moment at all
					self._$eyeCatch.removeClass(preAnimClass+ ' '+ params.anim.show_class);
				}
			}, parseInt(animDuration));
		}).click(function(){
			self.open();
			return false;
		});
	}
};
lcsChat.prototype.checkShowAutoStart = function( params, trigger, delay ) {
	if(!this._opened && this._visible) {
		if(delay) {
			var self = this;
			setTimeout(function(){
				self.showAutoStart( params, trigger );
			}, delay);
		} else
			this.showAutoStart( params, trigger );
	}
};
lcsChat.prototype.showAutoStart = function( params, trigger ) {
	var self = this;
	this._startSession({
		autostart: 1
	,	trigger_id: trigger.id
	}, function(){
		self._openChatStage('chat');
	});
};
lcsChat.prototype.checkShowAutoOpen = function( params, trigger, delay ) {
	if(!this._opened && this._visible) {
		if(delay) {
			var self = this;
			setTimeout(function(){
				self.showAutoOpen( params, trigger );
			}, delay);
		} else
			this.showAutoOpen( params, trigger );
	}
};
lcsChat.prototype.showAutoOpen = function( params, trigger ) {
	if(!this._opened && this._visible) {
		this.open();
	}
};
lcsChat.prototype._initSound = function() {
	if(parseInt(this._engine.params['enb_sound'])
		&& this._engine.params['sound'] 
		&& this._engine.params['sound'] != ''
		&& typeof(Audio) !== 'undefined'
	) {
		this._soundObj = new Audio( this._engine.params['sound'] );
		this._enbSound = true;
	}
};
lcsChat.prototype._playSound = function() {
	if(this._soundObj && this._enbSound) {
		// Stop audio in case it didn't finish prev. play
		if(this._soundObj.currentTime) {
			this._soundObj.pause();
			this._soundObj.currentTime = 0;
		}
		// Play sound again
		this._soundObj.play();
	}
};
lcsChat.prototype._bindDraggable = function() {
	if(this._engine.params['enb_draggable'] && parseInt(this._engine.params['enb_draggable'])) {
		var $handle = this._$.find('.lcsBar');
		this._$.draggable({ 
			handle: $handle
		,	containment: 'window'
		,	stop: function( event, ui ) {
				ui.helper.css({
					'height': ''
				,	'max-height': ''
				,	'width': ''
				,	'max-width': ''
				});
			}
		});
		$handle.disableSelection();
	}
};
lcsChat.prototype._bindAdditionalOptsBtns = function() {
	if(this._engine.params['enb_opts'] && parseInt(this._engine.params['enb_opts'])) {
		var self = this;
		this._$optsBtn = this._$.find('.lcsOptBtn');
		this._$optsShell = this._$.find('.lcsOptsList');
		if(this._$optsBtn 
			&& this._$optsBtn.size() 
			&& this._$optsShell 
			&& this._$optsShell.size()
		) {
			this._$optsBtn .click(function(){
				self.switchOptsBtns();
				return false;
			});
		}
		var btnsToClb = {
			'.lcsPrintBtn': 'printChat'
		,	'.lcsSendToEmailBtn': 'checkSendChatToEmail'
		,	'.lcsSwitchSoundBtn': 'switchSound'
		};
		for(var selector in btnsToClb) {
			var $btn = this._$.find( selector );
			if($btn && $btn.size()) {
				$btn.data('_lcsClb', btnsToClb[ selector ]).click(function(){
					self[ jQuery(this).data('_lcsClb') ]();
					self.closeOptsBtns();
					return false;
				});
			}
		}
	}
};
lcsChat.prototype.printChat = function() {
	var title = 'Chat Content';
	var printWnd = window.open('', title, 'height=400,width=600');
	printWnd.document.write('<html><head><title>'+ title+ '</title>');
	printWnd.document.write('</head><body >');
	printWnd.document.write( this.extractChatData() );
	printWnd.document.write('</body></html>');

	printWnd.document.close(); // necessary for IE >= 10
	printWnd.focus(); // necessary for IE >= 10

	printWnd.print();
	printWnd.close();
};
lcsChat.prototype.checkSendChatToEmail = function() {
	var self = this;
	if(!this._user || !this._user.email) {
		var $emailInputOver = jQuery('#'+ this._engine.tpl.view_html_id+ '_emailInputOverlay')
		,	$emailInputForm = $emailInputOver.find('.lcsEmailInputShell').find('form');
		if(!$emailInputForm.data('_lcsSubmitBinded')) {
			$emailInputForm.submit(function(){
				self.sendChatToEmail ( jQuery(this).find('[name="email"]').val() );
				$emailInputOver.slideUp( g_lcsAnimationSpeed );
				return false;
			});
			$emailInputForm.data('_lcsSubmitBinded', 1);
		}
		$emailInputOver.slideDown( g_lcsAnimationSpeed );
	} else {
		this.sendChatToEmail( this._user.email );
	}
};
lcsChat.prototype.sendChatToEmail = function ( email ) {
	var $btn = this._$.find('.lcsSendToEmailBtn');
	jQuery.sendFormLcs({
		btn: $btn
	,	data: {mod: 'chat', action: 'sendChatToEmail', email: email, chat_content: this.extractChatData()}
	});
};
lcsChat.prototype.switchSound = function() {
	this._enbSound
		? this.turnOffSound()
		: this.turnOnSound();
};
lcsChat.prototype.turnOnSound = function() {
	this._enbSound = true;
	var $soundBtn = this.getSoundBtn();
	if($soundBtn) {
		var $switchClassElem = $soundBtn.find('[data-switch-class]')
		,	switchClass = ($switchClassElem && $switchClassElem.size()) ? $switchClassElem.data('switch-class') : false;
		if(switchClass) {
			switchClass = switchClass.split('/');
			$switchClassElem.removeClass(switchClass[ 0 ]).addClass(switchClass[ 1 ]);
		}
	}
};
lcsChat.prototype.turnOffSound = function() {
	this._enbSound = false;
	var $soundBtn = this.getSoundBtn();
	if($soundBtn) {
		var $switchClassElem = $soundBtn.find('[data-switch-class]')
		,	switchClass = ($switchClassElem && $switchClassElem.size()) ? $switchClassElem.data('switch-class') : false;
		if(switchClass) {
			switchClass = switchClass.split('/');
			$switchClassElem.removeClass(switchClass[ 1 ]).addClass(switchClass[ 0 ]);
		}
	}
};
lcsChat.prototype.getSoundBtn = function() {
	if(!this._$soundBtn) {
		this._$soundBtn = this._$.find('.lcsSwitchSoundBtn');
		if(!this._$soundBtn || !this._$soundBtn.size()) {
			this._$soundBtn = null;
		}
	}
	return this._$soundBtn;
};
lcsChat.prototype.extractChatData = function() {
	var $chatBlock = this._$.find('.lcsChat').clone()
	,	$style = this.getStyle().clone()
	,	remove = ['.lcsInputShell', '.lcsChatFooter', '.lcsMessagesExShell', '.lcsOptBtnsShell'];
	for(var i = 0; i < remove.length; i++) {
		$chatBlock.find( remove[ i ] ).remove();
	}
	return jQuery('<div />').append( jQuery('<div id="'+ this._engine.tpl.view_html_id+ '" />').append( $chatBlock ).append( $style ) ).html();
};
lcsChat.prototype.openOptsBtns = function() {
	if(this._$optsShell) {
		this._$optsShell.slideDown( g_lcsAnimationSpeed );
	}
	this._addOptsOpened = true;
};
lcsChat.prototype.closeOptsBtns = function() {
	if(this._$optsShell) {
		this._$optsShell.slideUp( g_lcsAnimationSpeed );
	}
	this._addOptsOpened = false;
};
lcsChat.prototype.switchOptsBtns = function() {
	this._addOptsOpened
		? this.closeOptsBtns()
		: this.openOptsBtns();
};
var g_lcsChats = {
	_list: []
,	add: function(params) {
		this._list.push( new lcsChat(params) );
	}
,	getByIngineId: function( engineId ) {
		if(this._list && this._list.length) {
			for(var i = 0; i < this._list.length; i++) {
				if(this._list[ i ].getEngineId() == engineId) {
					return this._list[ i ];
				}
			}
		}
		return false;
	}
};
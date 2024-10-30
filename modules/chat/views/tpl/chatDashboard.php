<div class="wrap">
    <div class="supsystic-plugin">
        <section class="supsystic-content">
            <div class="supsystic-container" style="margin-left: 0;">
				<section>
					<div class="supsystic-item supsystic-panel" style="padding-left: 10px;">
						<div id="containerWrapper">
							<ul class="supsystic-bar-controls">
								<?php if($this->isAdmin) {?>
								<li title="<?php _e('Delete selected', LCS_LANG_CODE)?>">
									<button class="button" id="lcsChatRemoveGroupBtn" disabled data-toolbar-button>
										<i class="fa fa-fw fa-trash-o"></i>
										<?php _e('Delete selected', LCS_LANG_CODE)?>
									</button>
								</li>
								<?php }?>
								<li title="<?php _e('Search', LCS_LANG_CODE)?>">
									<input id="lcsChatsTblSearchTxt" type="text" name="tbl_search" placeholder="<?php _e('Search', LCS_LANG_CODE)?>">
								</li>
								<li title="<?php _e('Status', LCS_LANG_CODE)?>">
									<?php echo htmlLcs::selectbox('session_status', array(
										'options' => $this->availableAgentStatusesSel,
										'value' => $this->getModel('chat_sessions')->getStatusId('pending'),
										'attrs' => 'class="chosen" id="lcsChatsTblSearchStatus"'
									))?>
								</li>
								<li>
									<a href="#" class="button lcsChatsTblRefreshBtn">
										<i class="fa fa-fw fa-refresh"></i>
										<?php _e('Refresh', LCS_LANG_CODE)?>
									</a>
								</li>
							</ul>
							<div id="lcsChatsTblNavShell" class="supsystic-tbl-pagination-shell"></div>
							<div style="clear: both;"></div>
							<hr />
							<table id="lcsChatsTbl"></table>
							<div id="lcsChatsTblNav"></div>
							<div id="lcsChatsTblEmptyMsg" style="display: none;">
								<h3><?php _e('You have no active Chats for now.', LCS_LANG_CODE)?></h3>
							</div>
						</div>
					</div>
				</section>
                <div class="clear"></div>
            </div>
        </section>
    </div>
</div>

<?php
	$screenId = 'lcs-agent-chat';
	$pageTitle = __('Agents Support', LCS_LANG_CODE);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<title><?php echo $pageTitle;?></title>
	<?php
		require_once(ABSPATH . 'wp-admin/includes/admin.php');

		wp_enqueue_script( 'common' );

		do_action( 'admin_init' );
		set_current_screen( $screenId );

		global $hook_suffix;
		do_action( 'admin_enqueue_scripts', $hook_suffix );

		do_action( "admin_print_styles-$hook_suffix" );

		do_action( 'admin_print_styles' );

		do_action( "admin_print_scripts-$hook_suffix" );

		do_action( 'admin_print_scripts' );

		do_action( "admin_head-$hook_suffix" );

		do_action( 'admin_head' );

		wp_user_settings();
	?>
</head>
<body class="wp-admin wp-core-ui js auto-fold admin-bar branch-4-4 version-4-4 admin-color-fresh locale-en-us customize-support svg">
	<div class="wrap">
		<div class="supsystic-plugin">
			<section class="supsystic-content">
				<div class="supsystic-container" style="margin-left: 0;">
					<section>
						<div class="supsystic-item supsystic-panel" style="padding-left: 10px;">
							<div id="containerWrapper">
								<div class="row">
									<div id="lcsAgentChatShell" class="col-sm-6">
										<h2>
											<?php _e('Chat', LCS_LANG_CODE)?>
											<?php /*if(in_array($this->session['status_code'], array('pending'))) { ?>
												<a href="#" class="button"><?php _e('Start Chat', LCS_LANG_CODE)?></a>
											<?php }*/?>
										</h2>
										<div class="form-group">
											<div class="lcsMessagesShell form-control">
												<div class="lcsMessagesExShell">
													<div class="lcsAgentMsgShell">
														<div class="lcsAgentNameShell">Consultant</div>
														<div class="lcsAgentMsg lcsMsg">Hello Client! Please tell me - how can I help You today?</div>
													</div>
													<div class="lcsUserMsgShell">
														<div class="lcsUserNameShell">User</div>
														<div class="lcsUserMsg lcsMsg">Hello! Really nice support, thank you!</div>
													</div>
												</div>
												<div id="lcsMessages" class="lcsMessages">

												</div>
											</div>
										</div>
										<div class="lcsInputShell">
											<form id="lcsAgentForm" action="" method="post" class="lcsInputForm">
												<div class="lcsMainAreaShell">
													<div class="lcsMainTxtShell form-group">
														<textarea name="msg" class="lcsChatMsg form-control"></textarea>
													</div>
													<div class="lcsMainBtnShell form-group" style="">
														<button name="send" class="lcsChatSendBtn button button-primary form-control">
															<i class="fa fa-paper-plane"></i>
															<?php _e('Send', LCS_LANG_CODE)?>
														</button>
													</div>
												</div>
												<?php echo htmlLcs::hidden('mod', array('value' => 'chat'))?>
												<?php echo htmlLcs::hidden('action', array('value' => 'agentSend'))?>
												<?php echo htmlLcs::hidden('session_id', array('value' => $this->session['id']))?>
											</form>
										</div>
									</div>
									<div class="col-sm-6">
										<?php if($this->session['user']) {?>
										<h2><?php _e('User Data', LCS_LANG_CODE)?></h2>
										<table class="table">
											<?php if(!empty($this->session['user']['name'])) { ?>
											<tr>
												<th scope="row"><?php _e('Name', LCS_LANG_CODE)?></th>
												<td><?php echo $this->session['user']['name'];?></td>
											</tr>
											<?php }?>
											<tr>
												<th scope="row"><?php _e('Email', LCS_LANG_CODE)?></th>
												<td><?php echo $this->session['user']['email'];?></td>
											</tr>
										</table>
										<?php }?>
										<h2><?php _e('Chat Session Data', LCS_LANG_CODE)?></h2>
										<table class="table">
											<tr>
												<th scope="row"><?php _e('Created', LCS_LANG_CODE)?></th>
												<td><?php echo esc_html($this->session['date_created']);?></td>
											</tr>
											<?php if(!empty($this->session['url'])) { ?>
											<tr>
												<th scope="row"><?php _e('From', LCS_LANG_CODE)?></th>
												<td><?php echo esc_html($this->session['url']);?></td>
											</tr>
											<?php }?>
											<tr>
												<th scope="row"><?php _e('IP', LCS_LANG_CODE)?></th>
												<td><?php echo $this->session['ip'];?></td>
											</tr>
											<tr>
												<th scope="row"><?php _e('Status', LCS_LANG_CODE)?></th>
												<td>
													<?php echo htmlLcs::selectbox('session_status', array(
														'options' => $this->availableAgentStatusesSel,
														'attrs' => 'class="chosen" id="lcsChatStatusSel"',
														'value' => $this->session['status_id'],
													))?>
													<span id="lcsChatStatusMsg"></span>
												</td>
											</tr>
										</table>
									</div>
								</div>
							</div>
						</div>
					</section>
					<div class="clear"></div>
				</div>
			</section>
		</div>
	</div>
	<script type="text/javascript">
		ajaxurl = typeof(ajaxurl) != 'undefined' ? ajaxurl : LCS_DATA.ajaxurl;
	</script>
	<?php
		do_action( 'admin_footer', '' );
		do_action( 'admin_print_footer_scripts' );
	?>
</body>
</html>

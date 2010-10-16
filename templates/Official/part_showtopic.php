
<div class="bigmenu">
	<div class="pagemenu">
		<?php if (!empty($t_pagemenu)):  echo lang('pages'); ?> <?php echo $t_pagemenu; ?> <br /> <?php endif; ?>
		<?php if ($g_islogged): ?>
		<?php if ($t_bookmarked): ?>
		<a href="<?php echo manage_url('index.php?showtopic='.$t_topicid.((isset($_GET['page']))?'&page='.(int)$_GET['page']:'').'&amp;bookmark=0', 'forum-t'.$t_topicid.((isset($_GET['page']))?'-p'.(int)$_GET['page']:'').','.rewrite_words($t_topicname).'.html?bookmark=0'); ?>" class="tt_nobookmark" onclick="javascript:quickBookmark(<?php echo $t_topicid; ?>,'bookmark',this);"><?php echo lang('t_nobookmark'); ?></a>
		<?php else: ?>
		<a href="<?php echo manage_url('index.php?showtopic='.$t_topicid.((isset($_GET['page']))?'&page='.(int)$_GET['page']:'').'&amp;bookmark=1', 'forum-t'.$t_topicid.((isset($_GET['page']))?'-p'.(int)$_GET['page']:'').','.rewrite_words($t_topicname).'.html?bookmark=1'); ?>" class="tt_bookmark" onclick="javascript:quickBookmark(<?php echo $t_topicid; ?>,'bookmark',this);"><?php echo lang('t_bookmark'); ?></a>
		<?php endif; ?>
		<?php if ($t_topictrack): if ($t_topictracked): ?>
		 - <a href="<?php echo manage_url('index.php?showtopic='.$t_topicid.((isset($_GET['page']))?'&page='.(int)$_GET['page']:'').'&amp;track=0', 'forum-t'.$t_topicid.((isset($_GET['page']))?'-p'.(int)$_GET['page']:'').','.rewrite_words($t_topicname).'.html?track=0'); ?>" class="tt_notrack" onclick="javascript:quickBookmark(<?php echo $t_topicid; ?>,'track',this);"><?php echo lang('t_notrack'); ?></a>
		<?php else: ?>
		 - <a href="<?php echo manage_url('index.php?showtopic='.$t_topicid.((isset($_GET['page']))?'&page='.(int)$_GET['page']:'').'&amp;track=1', 'forum-t'.$t_topicid.((isset($_GET['page']))?'-p'.(int)$_GET['page']:'').','.rewrite_words($t_topicname).'.html?track=1'); ?>" class="tt_track" onclick="javascript:quickBookmark(<?php echo $t_topicid; ?>,'track',this);"><?php echo lang('t_track'); ?></a>
		<?php endif; endif; endif; ?>
	</div>
<?php if (!empty($t_optionbuttons)): ?>
	<ul class="optionmenu">
		<?php if ($t_ismod): ?>
		<li><a href="javascript:void(0);" onclick="hideAndShow('modmenu'); hideAndShow('modmenu_msgs'); hideAndShowC('messmoderation');" class="bb_t_mod"><span><?php echo lang('bb_t_mod'); ?></span></a></li>
		<?php endif; ?>
		<?php if (in_array('topic',$t_optionbuttons)): ?>
		<li><a href="<?php echo manage_url('index.php?act=wm&amp;newtopic='.$t_parent,'forum-wtopic-tg'.$t_parent.'.html'); ?>" class="bb_tg_topic"><span><?php echo lang('bb_tg_topic'); ?></span></a></li>
		<?php endif; ?>
		<?php if (in_array('poll',$t_optionbuttons)): ?>
		<li><a href="<?php echo manage_url('index.php?act=wm&amp;newpoll='.$t_parent,'forum-wpoll-tg'.$t_parent.'.html'); ?>" class="bb_tg_poll"><span><?php echo lang('bb_tg_poll'); ?></span></a></li>
		<?php endif; ?>
		<?php if (in_array('reply',$t_optionbuttons)): ?>
		<li><a href="<?php echo manage_url('index.php?act=wm&amp;addreply='.$t_topicid,'forum-wmsg-t'.$t_topicid.'.html'); ?>" class="bb_t_reply"><span><?php echo lang('bb_t_reply'); ?></span></a></li>
		<?php endif; ?>
		<?php if (in_array('closed',$t_optionbuttons)): ?>
		<li><span class="bb_t_clsd"><span><?php echo lang('bb_t_clsd'); ?></span></span></li>
		<?php endif; ?>
	</ul>
<?php endif; ?>
</div>

<?php if (isset($t_poll_results)): ?>
<form action="" method="post">
	<div class="table" id="table_poll">
	<table>
		<caption><?php echo $title_pre;  echo lang('t_poll_title'); ?> : <?php echo $t_poll_title;  if (!empty($t_poll_info)): ?> ( <?php echo lang(array('item' => $t_poll_info)); ?> )<?php endif;  if ($t_poll_canedit): ?> ( <?php if (!$t_poll_editing): ?><a href="<?php echo $t_poll_editlink; ?>"><?php echo lang('t_poll_modifymode'); ?></a><?php else: ?><a href="<?php echo $t_poll_normallink; ?>"><?php echo lang('t_poll_normalmode'); ?></a><?php endif; ?> )<?php endif; ?></caption>

		<tfoot>
			<tr>
				<th></th>
				<th></th>
			</tr>
		</tfoot>

		<?php foreach ($t_poll_results as $poll): ?>
		<tr class="pollpossibility">
			<td class="pollleft">
				<?php if ($t_poll_editing): ?><input type="text" name="poll_poss_<?php echo $poll['poss_id']; ?>" value="<?php echo $poll['poss_name']; ?>" size="40" /><?php else:  echo $poll['poss_name'];  endif; ?>
			</td>
			<td class="pollright">
				<?php if ($t_poll_editing): ?>
				<a href="<?php echo $t_poll_normallink.manage_url('&amp;','?'); ?>deleteposs=<?php echo $poll['poss_id']; ?>"><?php echo lang('delete'); ?></a>
				<?php else: ?>
				<?php if ($g_islogged):  if ($t_poll_alreadyvoted): ?>
				<div class="pollbar" style="width:<?php echo $poll['poss_barwidth']; ?>px;"></div>&nbsp;&nbsp; <?php echo $poll['poss_votes']; ?> <?php echo lang('t_poll_votes'); ?> ( <?php echo $poll['poss_percentage']; ?> % )<?php else: ?><input type="radio" name="choice" value="<?php echo $poll['poss_id']; ?>" /><?php endif;  else: ?>---<?php endif; ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
		<?php if ($g_islogged && !$t_poll_alreadyvoted && !$t_poll_editing): ?>
		<tr class="pollpossibility">
			<td class="pollleft">
				<input type="submit" name="vote" value="<?php echo lang('t_poll_vote'); ?>" />
			</td>
			<td class="pollright">
				<input type="submit" name="white" value="<?php echo lang('t_poll_white'); ?>" />
			</td>
		</tr>
		<?php endif; ?>
		<?php if (!$t_poll_editing): ?>
		<tr class="pollpossibility">
			<td class="pollleft">
				<?php echo lang('t_poll_white_voted'); ?> : <?php echo $t_poll_white; ?>
			</td>
			<td class="pollright">
				<?php echo lang('t_poll_votes_voted'); ?> : <?php echo $t_poll_totalvotes; ?>
			</td>
		</tr>
		<?php else: ?>
		<tr class="pollpossibility">
			<td class="pollleft">
				<?php echo lang('t_poll_confirmedit'); ?>
			</td>
			<td class="pollright">
				<input type="submit" name="poll_edit" value="<?php echo lang('confirm'); ?>">
			</td>
		</tr>
		<tr class="pollpossibility">
			<td class="pollleft">
				<?php echo lang('t_poll_addposs'); ?>
			</td>
			<td class="pollright">
				<input type="text" name="poll_newposs" value="" size="40" /> <input type="submit" name="poll_addposs" value="<?php echo lang('confirm'); ?>">
			</td>
		</tr>
		<?php endif; ?>
	</table>
	</div>
</form>
<?php endif; ?>

<?php if ($t_ismod): ?>
<div id="modmenu">
	<form action="<?php echo $t_modaction; ?>" method="post">
		<?php echo $t_modmenu; ?>
	</form>
</div>

<form action="<?php echo $t_modaction; ?>" method="post">
<div id="modmenu_msgs">
	<div class="moditem">
		<?php echo lang('mod_t_displacemessages'); ?>
	</div>
	<div class="moditem">
		<p><label><input type="radio" name="select_displace" value="new" /> <?php echo lang('mod_t_displacetonew'); ?></label></p>
		<p><?php echo lang('mod_t_displacetonew_form'); ?> : <input type="text" name="mod_newtopic" value="" /></p>
		<p><?php echo lang('mod_t_displacetonew_topicgroup'); ?> : <?php echo showForumMenu('mod_newtopic_tg','mod_t_displacetonew_topicgroup_select',0,0,0,0,true); ?></p>
	</div>
	<div class="moditem">
		<p><label><input type="radio" name="select_displace" value="existing"/> <?php echo lang('mod_t_displacetoexisting'); ?></label></p>
		<p><?php echo lang('mod_t_displacetoexisting_form'); ?> : <input type="text" name="mod_existingtopic" value="" /></p>
	</div>
	<div class="moditem">
		<input type="submit" name="mod_msgs_submit" value="<?php echo lang('confirm'); ?>" />
		<input type="button" value="<?php echo lang('mod_t_displace_invertselection'); ?>" onclick="invertselection('selectmsg[]')" />
	</div>
</div>
<script type="text/javascript">hideAndShow('modmenu'); hideAndShow('modmenu_msgs');</script>
<?php endif; ?>

<div class="table" id="table_topic">
<table>
	<caption>
		<?php echo $rss_tag;  echo $title_pre; ?><a href="<?php echo manage_url('index.php?showtopic='.$t_topicid, 'forum-t'.$t_topicid.'-p1,'.rewrite_words($t_topicname).'.html'); ?>"><?php echo $t_topicname;  if (!empty($t_topiccomment)): ?>, <?php echo $t_topiccomment;  endif; ?></a>
	</caption>

	<tfoot>
		<tr>
			<th></th>
			<th></th>
		</tr>
	</tfoot>

	<?php foreach ($t_messages as $message): ?>
	<tr class="field1">
		<td class="messageuser">
			<?php echo $message['mess_userlink']; ?>
		</td>
		<td class="messageheader">
			<?php echo $message['mess_inlink']; ?>
			<?php if ($t_ismod): ?>
			<span class="messmoderation"><input type="checkbox" name="selectmsg[]" value="<?php echo $message['mess_id']; ?>" /></span>
			<?php endif; ?>
			<span class="messhead<?php echo ($message['mess_read']?'read':'unread'); ?>" title="<?php echo lang('t_msg_'.($message['mess_read']?'read':'unread')); ?>"></span>
			<span class="messheadlocalid">#<?php echo $message['mess_localid']; ?></span>
			<span class="messheaddate">
				<a href="#<?php echo $message['mess_id']; ?>" onmouseup="cbAlert('<?php echo lang('t_directlink'); ?>','<?php echo $message['mess_link']; ?>');"><?php echo $message['mess_time']; ?></a> 
				<?php if ($message['u_showip'] && $message['user_ip'] != '0.0.0.0'): ?>| IP: <a href="admin.php?act=ip&amp;sub=3&amp;analyze=<?php echo $message['user_ip']; ?>"><?php echo $message['user_ip']; ?></a><?php endif; ?>
			</span>
			<ul class="messheadoptions">
				<li><a href="#template" class="sb_scroll" title="<?php echo lang('sb_scroll'); ?>"><span><?php echo lang('sb_scroll'); ?></span></a></li>
				<?php if (in_array('report',$message['mess_buttonoptions'])): ?>
				<li><a href="<?php echo manage_url('index.php?act=report&amp;mess='.$message['mess_id'],'forum-report.html?mess='.$message['mess_id']); ?>" class="sb_m_report" title="<?php echo lang('sb_m_report'); ?>"><span><?php echo lang('sb_m_report'); ?></span></a></li>
				<?php endif; ?>
				<?php if (in_array('delete',$message['mess_buttonoptions'])): ?>
				<li><a href="<?php echo manage_url('index.php?showtopic='.$t_topicid.'&amp;deletemessage='.$message['mess_id'],'forum-t'.$t_topicid.'.html?deletemessage='.$message['mess_id']); ?>" class="sb_m_delete" title="<?php echo lang('sb_m_delete'); ?>"><span><?php echo lang('sb_m_delete'); ?></span></a></li>
				<?php endif; ?>
				<?php if (in_array('edit',$message['mess_buttonoptions'])): ?>
				<li><a href="<?php echo manage_url('index.php?act=wm&amp;editmessage='.$message['mess_id'].'&amp;intopic='.$t_topicid,'forum-editmsg-m'.$message['mess_id'].'-t'.$t_topicid.'.html'); ?>" class="sb_m_edit" onclick="quickEdit('<?php echo $message['mess_id']; ?>',this);" title="<?php echo lang('sb_m_edit'); ?>"><span><?php echo lang('sb_m_edit');?></span></a></li>
				<?php endif; ?>
				<?php if (in_array('quote',$message['mess_buttonoptions'])): ?>
				<li><a href="<?php echo manage_url('index.php?act=wm&amp;quotemessage='.$message['mess_id'].'&amp;addreply='.$t_topicid,'forum-wmsg-t'.$t_topicid.'-quote'.$message['mess_id'].'.html');?>" class="sb_m_quote" title="<?php echo lang('sb_m_quote');?>"><span><?php echo lang('sb_m_quote'); ?></span></a></li>
				<?php endif; ?>
			</ul>
		</td>
	</tr>
	<tr class="field1 navitem">
		<td class="messageuser2">
			<?php if ($message['user_id']): ?>
			<p class="avatar">
				<?php echo $message['mess_userinfo_avatar']; ?>
			</p>
			<?php if ($message['u_canpunish']): ?>
			<p class="t_reputation">
				<?php echo $message['mess_userinfo_reputation']; ?>
			</p>
			<?php endif; ?>
			<p class="userinfos" id="usr_info_<?php echo $message['mess_id']; ?>">
				<?php if (!empty($message['mess_userinfo_group_img'])): ?>
				<?php echo $message['mess_userinfo_group_img']; ?><br />
				<?php endif; ?>
				<?php echo lang('class'); ?> : <?php echo $message['mess_userinfo_group']; ?> <br />
				<?php echo lang('t_rank'); ?> : <?php echo $message['mess_userinfo_rank']; ?> <br />
				<?php echo lang('posts'); ?> : <?php echo $message['mess_userinfo_posts']; ?> <br />
				<?php echo lang('registered'); ?> : <?php echo $message['mess_userinfo_registered']; ?>
			</p>
			<p class="communication">
				<?php echo $message['mess_userinfo_connected']; ?>
				<?php if ($g_islogged): ?>
				<?php echo $message['mess_mpicon']; ?>
				<?php endif; ?>
				<?php echo $message['mess_mailicon']; ?>
				<?php echo $message['mess_wwwicon']; ?>
			</p>
			<?php if ($message['u_canpunish']): ?>
			<p class="punish">
				<a href="<?php echo manage_url('index.php?act=user&amp;editprofile='.$message['user_id'], 'forum-profile'.$message['user_id'].'.html'); ?>" class="mod_editpr" title="<?php echo lang('mod_editprofile'); ?>"><span><?php echo lang('mod_editprofile'); ?></span></a>
			</p>
			<?php endif; ?>
			<?php else: ?>
			<p class="communication">
				<?php echo lang('t_postbyguest'); ?>
			</p>
			<?php endif; ?>
		</td>
		<td class="message">
			<div id="message_<?php echo $message['mess_id']; ?>" class="messagecontent">
				<?php echo $message['mess_messcontent']; ?>
			</div>
			<?php if ($message['mess_edited']): ?>
			<div class="messageedit" id="msg_edit_<?php echo $message['mess_id']; ?>">
				<?php echo lang(array('item' => "t_edited", 'user' => $message['mess_edit_userlink'], 'date1' => $message['mess_edit_date1'], 'date2' => $message['mess_edit_date2'])); ?>
			</div>
			<?php endif; ?>
			<?php if (!empty($message['mess_signature'])): ?>
			<div class="messagesignature">
				<?php echo $message['mess_signature']; ?>
			</div>
			<?php endif; ?>
		</td>
	</tr>
	<?php endforeach; ?>
</table>
</div>

<?php if ($t_ismod): ?>
</form>
<script type="text/javascript">hideAndShowC('messmoderation');</script>
<?php endif; ?>

<div class="bigmenu">
	<div class="pagemenu">
		<?php if (!empty($t_pagemenu)):  echo lang('pages'); ?> <?php echo $t_pagemenu;  endif; ?>
		<?php if ($g_islogged): ?>
		<br /><?php if ($t_bookmarked): ?>
		<a href="<?php echo manage_url('index.php?showtopic='.$t_topicid.((isset($_GET['page']))?'&page='.(int)$_GET['page']:'').'&amp;bookmark=0', 'forum-t'.$t_topicid.((isset($_GET['page']))?'-p'.(int)$_GET['page']:'').','.rewrite_words($t_topicname).'.html?bookmark=0'); ?>" class="tt_nobookmark" onclick="javascript:quickBookmark(<?php echo $t_topicid; ?>,'bookmark',this);"><?php echo lang('t_nobookmark'); ?></a>
		<?php else: ?>
		<a href="<?php echo manage_url('index.php?showtopic='.$t_topicid.((isset($_GET['page']))?'&page='.(int)$_GET['page']:'').'&amp;bookmark=1', 'forum-t'.$t_topicid.((isset($_GET['page']))?'-p'.(int)$_GET['page']:'').','.rewrite_words($t_topicname).'.html?bookmark=1'); ?>" class="tt_bookmark" onclick="javascript:quickBookmark(<?php echo $t_topicid; ?>,'bookmark',this);"><?php echo lang('t_bookmark'); ?></a>
		<?php endif; ?>
		<?php if ($t_topictrack): if ($t_topictracked): ?>
		 - <a href="<?php echo manage_url('index.php?showtopic='.$t_topicid.((isset($_GET['page']))?'&page='.(int)$_GET['page']:'').'&amp;track=0', 'forum-t'.$t_topicid.((isset($_GET['page']))?'-p'.(int)$_GET['page']:'').','.rewrite_words($t_topicname).'.html?track=0'); ?>" class="tt_notrack" onclick="javascript:quickBookmark(<?php echo $t_topicid; ?>,'track',this);"><?php echo lang('t_notrack'); ?></a>
		<?php else: ?>
		 - <a href="<?php echo manage_url('index.php?showtopic='.$t_topicid.((isset($_GET['page']))?'&page='.(int)$_GET['page']:'').'&amp;track=1', 'forum-t'.$t_topicid.((isset($_GET['page']))?'-p'.(int)$_GET['page']:'').','.rewrite_words($t_topicname).'.html?track=1'); ?>" class="tt_track" onclick="javascript:quickBookmark(<?php echo $t_topicid; ?>,'track',this);"><?php echo lang('t_track'); ?></a>
		<?php endif; endif; endif; ?>
	</div>
<?php if (!empty($t_optionbuttons)): ?>
	<ul class="optionmenu">
		<?php if (in_array('topic',$t_optionbuttons)): ?>
		<li><a href="<?php echo manage_url('index.php?act=wm&amp;newtopic='.$t_parent,'forum-wtopic-tg'.$t_parent.'.html'); ?>" class="bb_tg_topic"><span><?php echo lang('bb_tg_topic'); ?></span></a></li>
		<?php endif; ?>
		<?php if (in_array('poll',$t_optionbuttons)): ?>
		<li><a href="<?php echo manage_url('index.php?act=wm&amp;newpoll='.$t_parent,'forum-wpoll-tg'.$t_parent.'.html'); ?>" class="bb_tg_poll"><span><?php echo lang('bb_tg_poll'); ?></span></a></li>
		<?php endif; ?>
		<?php if (in_array('reply',$t_optionbuttons)): ?>
		<li><a href="javascript:void(0);" onclick="hideAndShow('flashreply');" class="bb_t_fl_rep"><span><?php echo lang('bb_t_fl_rep'); ?></span></a></li>
		<li><a href="<?php echo manage_url('index.php?act=wm&amp;addreply='.$t_topicid,'forum-wmsg-t'.$t_topicid.'.html'); ?>" class="bb_t_reply"><span><?php echo lang('bb_t_reply'); ?></span></a></li>
		<?php endif; ?>
		<?php if (in_array('closed',$t_optionbuttons)): ?>
		<li><span class="bb_t_clsd"><span><?php echo lang('bb_t_clsd'); ?></span></span></li>
		<?php endif; ?>
	</ul>
<?php endif; ?>
</div>

<?php if ($t_flashreply): ?>
<form action="<?php echo $t_fr_action; ?>" method="post" id="form_flashreply">
	<div id="flashreply">
		<div class="container">
			<h2><?php echo $title_pre;  echo lang('t_fastreply'); ?></h2>
			<div class="subcontainer">
				<fieldset>
					<legend><?php echo lang('t_fr_message'); ?></legend>
					<p>
						<?php $ta_opt = array('name' => 'message', 'id' =>  'message_fast', 'nomenu' => true,'value' => '') ?>
						<?php include($template_path.'menu_writemsg.php'); ?>
						<input type="hidden" name="prev" value="ok" />
					</p>
				</fieldset>
				<div class="confirm">
					<input type="submit" name="fastreply" value="<?php echo lang('wm_postmessage'); ?>" tabindex="2" /> 
					<input type="submit" name="prev" value="<?php echo lang('wm_previsualization'); ?>" tabindex="3" /> 
					<input type="submit" name="moreoptions" value="<?php echo lang('wm_moreoptions'); ?>" tabindex="4" />
				</div>
			</div>
		</div>
	</div>
</form>
<script type="text/javascript">document.getElementById('flashreply').style.display='none';</script>

<?php endif; ?>

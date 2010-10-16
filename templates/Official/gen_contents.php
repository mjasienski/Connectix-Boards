<?php if (!defined('CB_TEMPLATE')) exit('Incorrect access attempt !!'); ?>

<?php $title_pre = '<span class="title_pre"><span>&nbsp;>&nbsp;&nbsp;</span></span>'; ?>

<p id="skiplinks"><a href="#contents"><?php echo lang('skiplinks'); ?></a></p>

<div id="menu">
	<div id="navmenu_cnt" style="display:none;">
		<a id="navmenu_cmd" onclick="hideAndShow('navmenu');"><?php echo lang('nav_menu'); ?></a>
		<div id="navmenu" style="display:none;">
			<ul id="navmenu_items">
				<li><a id="nav_forward" onclick="navigate('forward');" title="<?php echo lang('nav_item_select'); ?> (f)"><span><?php echo lang('nav_item_select'); ?> (f)</span></a></li>
				<li><a id="nav_back" onclick="navigate('back');" title="<?php echo lang('nav_page_parent'); ?> (s)"><span><?php echo lang('nav_page_parent'); ?> (s)</span></a></li>
				<li><a id="nav_top" onclick="navigate('top');" title="<?php echo lang('nav_item_first'); ?> (z)"><span><?php echo lang('nav_item_first'); ?> (z)</span></a></li>
				<li><a id="nav_up" onclick="navigate('up');" title="<?php echo lang('nav_item_prev'); ?> (e)"><span><?php echo lang('nav_item_prev'); ?> (e)</span></a></li>
				<li><a id="nav_down" onclick="navigate('down');" title="<?php echo lang('nav_item_next'); ?> (d)"><span><?php echo lang('nav_item_next'); ?> (d)</span></a></li>
				<li><a id="nav_bottom" onclick="navigate('bottom');" title="<?php echo lang('nav_item_last'); ?> (x)"><span><?php echo lang('nav_item_last'); ?> (x)</span></a></li>
			</ul>
			<ul id="navmenu_pages">
				<li><a id="nav_nextpage" onclick="navigate('nextpage');" title="<?php echo lang('nav_page_next'); ?> (c)"><span><?php echo lang('nav_page_next'); ?> (c)</span></a></li>
				<li><a id="nav_prevpage" onclick="navigate('prevpage');" title="<?php echo lang('nav_page_prev'); ?> (w)"><span><?php echo lang('nav_page_prev'); ?> (w)</span></a></li>
			</ul>
		</div>
	</div>
	<script type="text/javascript">hideAndShow('navmenu_cnt');</script>

	<?php if ( !$g_islogged ): ?><form id="fast_connect_form" action="" method="post"><?php endif; ?>
	<div id="headmenu">
		<ul>
		<?php if ( $g_islogged ): ?>
			<?php if (!empty($g_backtowebsite)): ?>
			<li id="hm_backtosite"><a href="<?php echo $g_backtowebsite; ?>"><span><?php echo lang('backtowebsite'); ?></span></a></li>
			<?php endif; ?>
			<li id="hm_members"><a href="<?php echo manage_url('index.php?act=members', 'forum-members.html'); ?>"><span><?php echo lang('members'); ?></span></a></li>
			<li id="hm_connected"><a href="<?php echo manage_url('index.php?act=cu', 'forum-connected.html'); ?>"><span><?php echo lang('connected_people'); ?></span></a></li>
			<li id="hm_search"><a href="<?php echo manage_url('index.php?act=src', 'forum-search.html'); ?>"><span><?php echo lang('search'); ?></span></a></li>
			<li id="hm_rules"><a href="<?php echo manage_url('index.php?act=rules', 'forum-rules.html'); ?>"><span><?php echo lang('rules'); ?></span></a></li>
			<?php if ( $g_user_admin ): ?>
			<li id="hm_admin"><a href="<?php echo manage_url('admin.php', 'forum-admin.html'); ?>"><span><?php echo lang('paneladmin'); ?></span></a></li>
			<?php endif; ?>
			<?php if ( $g_user_mod ): ?>
			<li id="hm_mods"><a href="<?php echo manage_url('index.php?act=mods', 'forum-moderators.html'); ?>"><span><?php echo lang('modpanel'); ?><?php if ( isset($g_nbreports) && $g_nbreports > 0 ): ?> (<?php echo lang(array('item' => 'modpanel_nbreports','nb' => $g_nbreports)); ?>)<?php endif; ?></span></a></li>
			<?php endif; ?>
		<?php else: ?>
			<?php if ( !empty($g_backtowebsite) ): ?>
			<li><a href="<?php echo $g_backtowebsite; ?>" id="hm_backtosite"><span><?php echo lang('backtowebsite'); ?></span></a></li>
			<?php endif; ?>
			<li id="fast_connect">
				<input type="text" name="fast_login" id="fcf_login" size="18" value="<?php echo lang('username'); ?>" onfocus="javascript:fc_username();" />
				<input type="password" name="fast_password" id="fcf_password" size="18" value="password" onfocus="javascript:fc_password();" />
				<input type="checkbox" name="fast_remember" id="fcf_remember" title="<?php echo lang('remember'); ?>" checked="checked" />
				<input type="submit" name="fast_connect" id="fcf_connect" value="<?php echo lang('login_confirm'); ?>" />
			</li>
		<?php endif; ?>
		</ul>
	</div>
	<?php if ( !$g_islogged ): ?></form><?php endif; ?>

	<div id="connectpanel">
		<?php if ( count($g_langs) > 1 ): $langcounter = 1; ?>
		<ul id="lang_choice">
			<?php foreach ($g_langs as $lang): ?>
			<li id="con_lang_choice_<?php echo $langcounter++; ?>" class="con_lang_choice"><a class="con_lang_link" style="background:url(lang/<?php echo $lang.'/'.$lang; ?>.gif) left no-repeat;" href="<?php echo manage_url('index.php', 'forum.html').'?lang='.$lang; ?>"><span><?php echo $lang; ?></span></a></li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>
		
		<ul id="connectlist">
		<?php if ( $g_islogged ): ?>
			<li id="con_ulink"><?php echo lang(array('item' => 'connected_as','name' => $g_user_link)); ?></li>
			<li id="con_profile"><a href="<?php echo manage_url('index.php?act=user&amp;editprofile='.$g_user_id, 'forum-profile'.$g_user_id.'.html'); ?>" id="cp_profile"><?php echo lang('pers_settings'); ?></a></li>
			<li id="con_mp"><a href="<?php echo manage_url('index.php?act=mp', 'forum-mp.html'); ?>" id="cp_mp"><?php if ( $g_newmessages==0 ): ?><?php echo lang('mps'); ?><?php else: ?><?php echo lang(array('item' => 'mp_newmessage','n' => $g_newmessages)); ?><?php endif; ?></a></li>
			<li id="con_logout"><a href="<?php echo manage_url('logout.php', 'forum-logout.html'); ?>" id="cp_logout"><?php echo lang('logout'); ?></a></li>
		<?php else: ?>
			<li id="con_welcome"><?php echo lang('welcome'); ?></li>
			<li id="con_login"><a href="<?php echo manage_url('index.php?act=login', 'forum-login.html'); ?>" id="cp_login"><?php echo lang('login'); ?></a></li>
			<?php if ( !$g_suspendregister ): ?>
			<li id="con_register"><a href="<?php echo manage_url('index.php?act=register', 'forum-register.html'); ?>" id="cp_register"><?php echo lang('register'); ?></a></li>
			<?php endif; ?>
		<?php endif; ?>
		</ul>
	</div>
	
	<?php if ( $g_islogged ): ?>
	<div id="shortcuts">
		<ul>
			<li id="sc_lm">
				<a href="<?php echo manage_url('index.php?act=tlist', 'forum-tlist.html'); ?>" id="sc_lastmessages" title="<?php echo lang('sc_lastmessages'); ?>"><span><?php echo lang('sc_lastmessages'); ?></span></a>
			</li>
			<li id="sc_lm_p">
				<a href="<?php echo manage_url('index.php?act=tlist&amp;', 'forum-tlist.html?').'posted=1'; ?>" id="sc_lastmessages_posted" title="<?php echo lang('sc_lastmessages_posted'); ?>"><span><?php echo lang('sc_lastmessages_posted'); ?></span></a>
			</li>
			<li id="sc_nr">
				<a href="<?php echo manage_url('index.php?act=tlist&amp;', 'forum-tlist.html?').'noreply=1'; ?>" id="sc_noreply" title="<?php echo lang('sc_noreply'); ?>"><span><?php echo lang('sc_noreply'); ?></span></a>
			</li>
			<li id="sc_ur">
				<a href="<?php echo manage_url('index.php?act=tlist&amp;', 'forum-tlist.html?').'unread=1'; ?>" id="sc_unread" title="<?php echo lang('sc_newmessages'); ?>"><span><?php echo lang('sc_newmessages'); ?></span></a>
			</li>
			<li id="sc_ur_p">
				<a href="<?php echo manage_url('index.php?act=tlist&amp;', 'forum-tlist.html?').'unread=1&amp;posted=1'; ?>" id="sc_unread_posted" title="<?php echo lang('sc_newmessages_posted'); ?>"><span><?php echo lang('sc_newmessages_posted'); ?></span></a>
			</li>
			<li id="sc_bm">
				<a href="<?php echo manage_url('index.php?act=tlist&amp;', 'forum-tlist.html?').'bookmark=1'; ?>" id="sc_newmessages" title="<?php echo lang('sc_bookmarks'); ?>"><span><?php echo lang('sc_bookmarks'); ?></span></a>
			</li>
			<li id="sc_tt">
				<a href="<?php echo manage_url('index.php?act=tlist&amp;', 'forum-tlist.html?').'tracked=1'; ?>" id="sc_tracked" title="<?php echo lang('sc_tracked'); ?>"><span><?php echo lang('sc_tracked'); ?></span></a>
			</li>
		</ul>
	</div>
	<?php endif; ?>
</div>
	
<div id="contents">

	<?php if ( $g_paused ): ?>
	<div class="biginfo">
		<p>
			<?php echo lang('warning'); ?> : <?php echo lang('paused'); ?>
		</p>
	</div>
	<?php endif; ?>

	<?php if ( $g_mpadv ): ?>
	<div id="mpadv_div">
		<div class="mpadv">
			<p>
				<?php echo lang('g_mpadv'); ?>
			</p>
		</div>
	</div>
	<script type="text/javascript">
		hideAndShow('mpadv_div');
		cbAlert("<?php echo lang('g_mpadv_javascript'); ?>");
	</script>
	<?php endif; ?>

	<?php if ( !empty($g_foruminfotop) ): ?>
	<div class="foruminfo">
		<p>
			<?php echo $g_foruminfotop; ?>
		</p>
	</div>
	<?php endif; ?>

	<?php if ( !empty($g_foruminfotop_dyn) ): ?>
	<div class="foruminfo_dyn">
		<?php echo $g_foruminfotop_dyn; ?>
	</div>
	<?php endif; ?>

	<?php if ( !empty($warning) ): ?>
	<div class="warning">
		<?php foreach ($warning as $war): ?>
		<p>
			<?php echo $war['str']; ?> <?php echo $war['pos']; ?>
		</p>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<?php if ( !empty($notice) ): ?>
	<div class="notice">
		<?php foreach ($notice as $not): ?>
		<p>
			<?php echo $not['str']; ?> <?php echo $not['pos']; ?>
		</p>
		<?php endforeach; ?>
	</div>

	<?php endif; ?>

	<?php if (count($g_addressbar) > 1): ?>
	<div class="addressbar">
		<p>
			<?php echo implode(' '.CB_ADDR_SEP.' ',$g_addressbar); ?>
		</p>
	</div>
	<?php endif; ?>

	<?php if ( !empty($g_part) ): include ($template_path.$g_part); endif; ?>

	<?php if (count($g_addressbar) > 1 && $g_addressbar_double): ?>
	<div class="addressbar">
		<p>
			<?php echo implode(' '.CB_ADDR_SEP.' ',$g_addressbar); ?>
		</p>
	</div>
	<?php endif; ?>

	<?php if ($g_displayfastredirect): ?>
	<div id="general_options">
		<form action="<?php echo manage_url('index.php','forum.html'); ?>" method="get" id="quick_redirect_form">
			<p id="quick_redirection">
				<label><strong><?php echo lang('fastredirect'); ?></strong><br />
				<?php echo $g_fastredirect_menu; ?></label> 
				<input id="quick_redir_submit" type="submit" value="<?php echo lang('confirm'); ?>" />
			</p>
		</form>
		<?php if ($g_selectskin_nbskins > 1): ?>
		<form action="" method="get" id="skin_select_form">
			<p id="skin_select">
				<label><strong><?php echo lang('skinselect'); ?></strong><br />
				<?php echo $g_selectskin_menu; ?></label> 
				<input id="skin_select_submit" type="submit" value="<?php echo lang('confirm'); ?>" />
			</p>
		</form>
		<?php endif; ?>
	</div>
	
	<script type="text/javascript">hideAndShow('quick_redir_submit'); hideAndShow('skin_select_submit');</script>
	<?php endif; ?>

	<?php if ($g_displayconnected || $g_showstats): ?>
	<div id="stats">
		<h2><?php echo $title_pre; ?><?php echo lang('forum_infos'); ?></h2>
		<div class="subcontainer">
			<?php if ( $g_displayconnected ): ?>
			<p class="inforow" id="stats_users">
				<?php echo lang(array('item' => 'guys_connected','members'=>$g_connected_nummembers,'guests'=>$g_connected_numguests,'total'=>$g_connected_total,'time'=>$g_connected_minutes)); ?>
				<?php if ( !empty($g_membersconnected) ): ?><br /><?php echo lang('members_connected'); ?> (<?php echo lang(array('item' => $g_membersconnectedtype)); ?>) : <?php echo $g_membersconnected; ?><?php endif; ?>
			</p>
			<?php endif; ?>
			<?php if ( $g_showstats ): ?>
			<?php if ( !empty($g_classes_legend) ): ?>
			<p class="inforow" id="stats_legend">
				<?php echo lang('keys'); ?> : <?php echo $g_classes_legend; ?>
			</p>
			<?php endif; ?>
			<p class="inforow" id="stats_stats">
				<?php echo lang(array('item' => 'members_registered','n' => $g_membersregistered)); ?><br />
				<?php echo lang(array('item' => 'total_messages','m' => $g_totalmessages,'s' => $g_totaltopics)); ?>
			</p>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<?php if ( !empty($g_foruminfobottom) ): ?>
	<div class="foruminfo">
		<p>
			<?php echo $g_foruminfobottom; ?>
		</p>
	</div>
	<?php endif; ?>

	<?php if ( !empty($g_foruminfobottom_dyn) ): ?>
	<div class="foruminfo_dyn">
		<?php echo $g_foruminfobottom_dyn; ?>
	</div>
	<?php endif; ?>

	<?php if ( $g_debugging ): include($template_path.'gen_debug.php'); endif; ?>
</div>
	
<div id="footer">
	<p id="copyright">
		Powered by <a href="http://www.connectix-boards.org">Connectix Boards</a> <?php echo $g_version; ?> &copy; 2005-<?php echo date('Y'); ?>
	</p>
</div>

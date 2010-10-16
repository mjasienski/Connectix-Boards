<?php if (!defined('CB_TEMPLATE')) exit('Incorrect access attempt !!'); ?>

<div class="table" id="tbl_gen_stats">
  <table>
    <caption><?php echo $title_pre; ?><?php echo lang('stats'); ?></caption>
    <tr class="field1">
	  <td><strong><?php echo lang('st_founded'); ?>:</strong> <?php echo $st_opendate; ?></td>
	  <td><strong><?php echo lang('st_board_age'); ?>:</strong> <?php echo $st_boardage; ?> <?php echo lang('days'); ?></td>
    </tr>
    <tr class="field2">
      <td><strong><?php echo lang('st_total_topics'); ?>:</strong> <?php echo $st_totaltopics; ?></td>
      <td><strong><?php echo lang('st_topicperday'); ?>:</strong> <?php echo $st_topicperday; ?></td>
    </tr>
    <tr class="field1">
      <td><strong><?php echo lang('st_total_msgs'); ?>:</strong> <?php echo $st_totalmsgs; ?></td>
      <td><strong><?php echo lang('st_msgperday'); ?>:</strong> <?php echo $st_msgperday; ?></td>
    </tr>
    <tr class="field2">
      <td><strong><?php echo lang('st_total_users'); ?>:</strong> <?php echo $st_totalusers; ?></td>
      <td><strong><?php echo lang('st_userperday'); ?>:</strong> <?php echo $st_userperday; ?></td>
    </tr>
    <tr class="field1">
      <td><strong><?php echo lang('st_lastregdate'); ?>:</strong> <?php echo $st_lastregdate; ?></td>
      <td><strong><?php echo lang('st_lastreguser'); ?>:</strong> <?php echo $st_lastregistered; ?></td>
    </tr>
	<tr class="field2">
	  <td><strong><?php echo lang('st_lastgentime'); ?>:</strong> <?php echo $st_lastgentime; ?></td>
	  <td><strong><?php echo lang('st_nextgentime'); ?>:</strong> <?php echo $st_nextgentime; ?></td>
	</tr>
    <tr class="field2">
      <td colspan="2" style="text-align:center;"><?php echo lang('st_gendelay'); ?></td>
    </tr>
  </table>
</div>

<div class="table" id="tbl_active_topics">
  <table>
	<caption><?php echo $title_pre; ?><?php echo lang('st_active_topics'); ?></caption>
	<tr>
      <th class="rank"><?php echo lang('st_rank'); ?></th>
      <th class="number"><?php echo lang('st_nbreply'); ?></th>
      <th class="topic"><?php echo lang('st_topic'); ?></th>
    </tr>
	<?php for($at_t_rank=1; $at_t_rank <= 10; $at_t_rank++): ?>
	<?php if(!empty($at_t_topicname[$at_t_rank])): ?>
	<tr>
	  <td class="rank"><?php echo $at_t_rank; ?></td>
	  <td class="number"><?php echo $at_t_replies[$at_t_rank]; ?></td>
	  <td class="topic"><?php echo '<a href="'.manage_url('index.php?showtopic='.$at_t_topicid[$at_t_rank],'forum-t'.$at_t_topicid[$at_t_rank].','.$at_t_topicname[$at_t_rank].'.html').'">'.$at_t_topicname[$at_t_rank].'</a>'; ?></td>
	</tr>
	<?php endif; ?>
	<?php endfor; ?>
  </table>
</div>

<div class="table" id="tbl_viewed_topics">
  <table>
	<caption><?php echo $title_pre; ?><?php echo lang('st_viewed_topics'); ?></caption>
	<tr>
	  <th class="rank"><?php echo lang('st_rank'); ?></th>
	  <th class="number"><?php echo lang('st_nbviews'); ?></th>
	  <th class="topic"><?php echo lang('st_topic'); ?></th>
	</tr>
	<?php for($vt_t_rank=1; $vt_t_rank <= 10; $vt_t_rank++): ?>
	<?php if(!empty($vt_t_topicname[$vt_t_rank])): ?>
	<tr>
	  <td class="rank"><?php echo $vt_t_rank; ?></td>
	  <td class="number"><?php echo $vt_t_views[$vt_t_rank]; ?></td>
	  <td class="topic"><?php echo '<a href="'.manage_url('index.php?showtopic='.$vt_t_topicid[$vt_t_rank],'forum-t'.$vt_t_topicid[$vt_t_rank].','.$vt_t_topicname[$vt_t_rank].'.html').'">'.$vt_t_topicname[$vt_t_rank].'</a>'; ?></td>
	</tr>
	<?php endif; ?>
	<?php endfor; ?>
  </table>
</div>

<div class="table" id="tbl_most_starters">
  <table>
    <caption><?php echo $title_pre; ?><?php echo lang('st_most_starters'); ?></caption>
    <tr>
      <th class="rank"><?php echo lang('st_rank'); ?></th>
      <th class="username"><?php echo lang('st_username'); ?></th>
	  <th class="nbtopics"><?php echo lang('st_topics'); ?></th>
	  <th class="percent"><?php echo lang('st_percent'); ?></th>
	  <th class="bar"><?php echo lang('st_graph'); ?></th>
    </tr>
	<?php for($stu_u_rank=1; $stu_u_rank <= 10; $stu_u_rank++): ?>
	<?php if(!empty($stu_u_percent[$stu_u_rank])): ?>
	<tr>
	  <td class="rank"><?php echo $stu_u_rank; ?></td>
	  <td class="username"><?php echo getUserLink($stu_u_userid[$stu_u_rank],$stu_u_username[$stu_u_rank],''); ?></td>
	  <td class="nbtopics"><?php echo $stu_u_nbstart[$stu_u_rank]; ?></td>
	  <td class="percent"><?php echo $stu_u_percent[$stu_u_rank]; ?>%</td>
	  <td class="bar"><div class="pollbar" style="width:<?php echo $stu_u_bar[$stu_u_rank]; ?>px;"></div></td>
	</tr>
	<?php endif; ?>
	<?php endfor; ?>
  </table>
</div>

<div class="table" id="tbl_week_posters">
  <table>
    <caption><?php echo $title_pre; ?><?php echo lang('st_weekly_posters'); ?> [<?php echo $wku_u_firstday; ?> - <?php echo $wku_u_lastday; ?>]</caption>
	<tr>
      <th class="rank"><?php echo lang('st_rank'); ?></th>
	  <th class="username"><?php echo lang('st_username'); ?></th>
	  <th class="nbmess"><?php echo lang('st_messages'); ?></th>
	  <th class="percent"><?php echo lang('st_percent'); ?></th>
	  <th class="bar"><?php echo lang('st_graph'); ?></th>
    </tr>
	<?php for($wku_u_rank=1; $wku_u_rank <= 10; $wku_u_rank++): ?>
	<?php if(!empty($wku_u_percent[$wku_u_rank])): ?>
	<tr>
	  <td class="rank"><?php echo $wku_u_rank; ?></td>
	  <td class="username"><?php echo getUserLink($wku_u_userid[$wku_u_rank],$wku_u_username[$wku_u_rank],''); ?></td>
	  <td class="nbmess"><?php echo $wku_u_nbpost[$wku_u_rank]; ?></td>
	  <td class="percent"><?php echo $wku_u_percent[$wku_u_rank]; ?>%</td>
	  <td class="bar"><div class="pollbar" style="width:<?php echo $wku_u_bar[$wku_u_rank]; ?>px;"></div></td>
    </tr>
	<?php endif; ?>
	<?php endfor; ?>
  </table>
</div>

<div class="table" id="tbl_month_posters">
  <table>
	<caption><?php echo $title_pre; ?><?php echo lang('st_monthly_posters'); ?> [<?php echo $mtu_u_month; ?>]</caption>
	<tr>
	  <th class="rank"><?php echo lang('st_rank'); ?></th>
	  <th class="username"><?php echo lang('st_username'); ?></th>
	  <th class="nbmess"><?php echo lang('st_messages'); ?></th>
	  <th class="percent"><?php echo lang('st_percent'); ?></th>
	  <th class="bar"><?php echo lang('st_graph'); ?></th>
	</tr>
	<?php for($mtu_u_rank=1; $mtu_u_rank <= 10; $mtu_u_rank++): ?>
	<?php if(!empty($mtu_u_percent[$mtu_u_rank])): ?>
	<tr>
	  <td class="rank"><?php echo $mtu_u_rank; ?></td>
	  <td class="username"><?php echo getUserLink($mtu_u_userid[$mtu_u_rank],$mtu_u_username[$mtu_u_rank],''); ?></td>
	  <td class="nbmess"><?php echo $mtu_u_nbpost[$mtu_u_rank]; ?></td>
	  <td class="percent"><?php echo $mtu_u_percent[$mtu_u_rank]; ?>%</td>
	  <td class="bar"><div class="pollbar" style="width:<?php echo $mtu_u_bar[$mtu_u_rank]; ?>px;"></div></td>
	</tr>
	<?php endif; ?>
	<?php endfor; ?>
  </table>
</div>

<div class="table" id="tbl_best_posters">
  <table>
	<caption><?php echo $title_pre; ?><?php echo lang('st_best_posters'); ?></caption>
	<tr>
	  <th class="rank"><?php echo lang('st_rank'); ?></th>
	  <th class="username"><?php echo lang('st_username'); ?></th>
	  <th class="nbmess"><?php echo lang('st_messages'); ?></th>
	  <th class="percent"><?php echo lang('st_percnet'); ?></th>
	  <th class="bar"><?php echo lang('st_graph'); ?></th>
	</tr>
	<?php for($bpu_u_rank=1; $bpu_u_rank <= 10; $bpu_u_rank++): ?>
	<?php if(!empty($bpu_u_percent[$bpu_u_rank])): ?>
	<tr>
	  <td class="rank"><?php echo $bpu_u_rank; ?></td>
	  <td class="username"><?php echo getUserLink($bpu_u_userid[$bpu_u_rank],$bpu_u_username[$bpu_u_rank],''); ?></td>
	  <td class="nbmess"><?php echo $bpu_u_nbpost[$bpu_u_rank]; ?></td>
	  <td class="percent"><?php echo $bpu_u_percent[$bpu_u_rank]; ?>%</td>
	  <td class="bar"><div class="pollbar" style="width:<?php echo $bpu_u_bar[$bpu_u_rank]; ?>px;"></div></td>
	</tr>
	<?php endif; ?>
	<?php endfor; ?>
  </table>
</div>

<div class="table" id="tbl_topics_twelve_months">
  <table>
	<caption><?php echo $title_pre; ?><?php echo lang('st_twelve_months_topics'); ?></caption>
	<tr>
	  <th class="month"><?php echo lang('st_month'); ?></th>
	  <th class="nbtopics"><?php echo lang('st_topics'); ?></th>
	  <th class="percent"><?php echo lang('st_percent'); ?></th>
	  <th class="bar"><?php echo lang('st_graph'); ?></th>
	</tr>
	<?php for($twt_t_rank=0; $twt_t_rank <= 11; $twt_t_rank++): ?>
	<tr>
	  <td class="month"><?php echo $twt_t_months[$twt_t_rank]; ?></td>
	  <td class="nbtopics"><?php echo $twt_t_number[$twt_t_rank]; ?></td>
	  <td class="percent"><?php echo $twt_t_percent[$twt_t_rank]; ?>%</td>
	  <td class="bar"><div class="pollbar" style="width:<?php echo $twt_t_bar[$twt_t_rank]; ?>px;"></div></td>
	</tr>
	<?php endfor; ?>
  </table>
</div>


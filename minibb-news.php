<?php
/*
Plugin Name: MiniBB News
Plugin URI: http://deuced.net/wpress/minibb-news/
Description: Displays last miniBB news at your sidebar
Author: ..::DeUCeD::..
Version: 1.7
Author URI: http://deuced.net
*/
/*

A widget that displays latest discussions from miniBB forums.

*/
/*	Copyright 2008 ..::DeUCeD::..

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
### MiniBB News plugin
if(function_exists('load_plugin_textdomain'))
  load_plugin_textdomain('minibb news','wp-content/plugins/minibb-news');
function widget_minibb_news_init() {
// Check for the required API functions
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
  return;	
//  main widget function
	function widget_minibb_news($args) {
//  get my options
		extract($args);
		$options = get_option('widget_minibb_news');
		$title = $options['title'];
//	$bb_forumsID = $options['bb_forumsID'];
    $bb_topiclimit = $options['bb_topiclimit'];
    $bb_sort = $options['bb_sort']; 
    $bb_maxlength = $options['bb_maxlength'];
    $bb_timediff = $options['bb_timediff']; 
    $bb_path = $options['bb_path']; 
    if ($title=="") {
			$title = "Forum Latest Topics";
    }
//  if ($bb_forumsID=="") {
//			$bb_forumsID = array();
//  }
if ($bb_topiclimit=="") {
			$bb_topiclimit = 5;
      }
if ($bb_sort=="") {
			$bb_sort = 1;
      }
if ($bb_maxlength=="") {
			$bb_maxlength = 50;
      }
if ($bb_timediff=="") {
			$bb_timediff = 1;
      }
if ($bb_path=="") {
			$bb_path = 'forums/';
      }
// start the widget	& Display Title
		echo $before_widget . $before_title . $title . $after_title;
// ********************************************************** //
// HACK STARTS: check if this a forum page
$getcurrentblog_url = ($_SERVER['PHP_SELF']);
$thecurrent_url = strpos($getcurrentblog_url, $bb_path);
if ($thecurrent_url === false) 
{
// $displayForums = explode(',', $bb_forumsID);
$displayForums = array();
$limit = $bb_topiclimit;
  if ($bb_sort=="0")  { 
    $sort='topic_id DESC';
    $post_sort=0;
  }
  else  { 
    $sort='topic_last_post_id DESC';
    $post_sort=1;
  }
$maxTxtLength = $bb_maxlength; 
$path = (ABSPATH . $bb_path);
/* HACK: needed values comes now from WP widget control */
define ('INCLUDED776',1);
require_once ($path . 'setup_options.php');
require_once ('./wp-content/plugins/minibb-news/minibb-func.php');
if (count($displayForums)>0) {
$firstForum=$displayForums[0];
$xtr=getClForums($displayForums,'where','','forum_id','or','=');
}
$topics=array();
$topics2=array();
$topics3=array();
if($res=db_simpleSelect(0,$Tt,'topic_id, topic_title, topic_poster_name, topic_time, topic_last_post_id, forum_id, posts_count, topic_time','','','',$sort,$limit)){
do{
$tid=$res[0];
$topics2[]=$res[4];
$topics3[]=$res[0];
$topics[$tid]['topic_title']=$res[1];
$topics[$tid]['topic_last_post_id']=$res[4];
$topics[$tid]['forum_id']=$res[5];
$topics[$tid]['posts_count']=$res[6]-1;
$topics[$tid]['topic_poster_name']=$res[2];
// HACK: Don't convert any time, later!
$topics[$tid]['topic_time']=($res[7]);
}
while($res=db_simpleSelect(1));
}
if($maxTxtLength!=0){
if($post_sort==0) $xtr=getClForums($topics3,'where','','topic_id','or','=');
else $xtr=getClForums($topics2,'where','','post_id','or','=');
/* ---------------> IMPORTANT: if you wanna display the FIRST POST of the LATEST TOPIC which has activity instead of the LATEST REPLY of the LATEST TOPIC which has activity change 'topic_id DESC, post_id DESC' to 'topic_id ASC, post_id ASC' <--------------- */
if($res=db_simpleSelect(0,$Tp,'topic_id, post_text, post_time, poster_name','','','','topic_id DESC, post_id DESC')){
$keep=0;
do{
$tid=$res[0];
$keepNext=$tid;
if($keepNext!=$keep){
$topics[$tid]['post_text']=substr(strip_tags(str_replace('<br />', "\n", $res[1])), 0, $maxTxtLength);
// HACK: Don't convert any time, later!
$topics[$tid]['topic_time']=($res[2]);
$topics[$tid]['topic_poster_name']=$res[3];
if (strlen(strip_tags($res[1]))>$maxTxtLength) $topics[$tid]['post_text'].='...';
} 
$keep=$tid;
}
while($res=db_simpleSelect(1));
}
}
foreach($topics as $key=>$val){
$topic_id=$key;
foreach($val as $k=>$v) $$k=$v;
// HACK: if postsort by latest reply link to latest message else link the topic
$mini_pagelink = intval($posts_count / $viewmaxreplys);
if ($post_sort==1)  {
if(isset($mod_rewrite) and $mod_rewrite) $linkToTopic="{$main_url}/{$forum_id}_{$topic_id}_{$mini_pagelink}.html#msg{$topic_last_post_id}";
else $linkToTopic="{$main_url}/{$indexphp}action=vthread&amp;forum={$forum_id}&amp;topic={$topic_id}&amp;page={$mini_pagelink}#msg{$topic_last_post_id}";
}
else  {
if(isset($mod_rewrite) and $mod_rewrite) $linkToTopic="{$main_url}/{$forum_id}_{$topic_id}_0.html";
else $linkToTopic="{$main_url}/{$indexphp}action=vthread&amp;forum={$forum_id}&amp;topic={$topic_id}";
}
// HACK: There was a problem with the $limit and i had to put a condition here
if ($limit>0) {
// it's time to convert each topics time difference
$bb_topic_time = strtotime($topic_time);
$bb_topicnewtime = date('d/m/y @ H:i', ($bb_topic_time + ($bb_timediff)));
// HACK: display latest discussions on sidebar
echo '<ul><li>';
echo '<a href="' . $linkToTopic . '"><strong>' . $topic_title . '</strong>:</a> ';
echo $post_text;
echo ' &raquo; <em><a href="' . $linkToTopic . '">' . $topic_poster_name . '</a></em>';
echo '<br /><small>' . $bb_topicnewtime;
echo ' </small><big>&rarr;</big><small> Replies: ' . $posts_count .  '</small>';
echo '</li></ul>';
//HACK: $limit condition: decreases by 1, then ending ::: A way to come around it
$limit = ($limit - 1); }
}
}
// HACK ENDS: script was executed only if NOT IN a forum directory
else { echo '<ul><li><strong>Enjoy the Forums!</strong></li></ul>'; }
		echo $after_widget;
    }
// ********************************************************** //
// control panel
	function widget_minibb_news_control() {
		$options = $newoptions = get_option('widget_minibb_news');
		if ( $_POST["minibb_news-submit"] ) {
			$newoptions['title'] = trim(strip_tags(stripslashes($_POST["minibb_news-title"])));
//			$newoptions['bb_forumsID'] = trim(strip_tags(stripslashes($_POST["minibb_news-bb_forumsID"])));
			$newoptions['bb_topiclimit'] = trim(strip_tags(stripslashes($_POST["minibb_news-bb_topiclimit"])));
			$newoptions['bb_sort'] = trim(strip_tags(stripslashes($_POST["minibb_news-bb_sort"])));
			$newoptions['bb_maxlength'] = trim(strip_tags(stripslashes($_POST["minibb_news-bb_maxlength"])));
			$newoptions['bb_path'] = trim(strip_tags(stripslashes($_POST["minibb_news-bb_path"])));
			$newoptions['bb_timediff'] = trim(strip_tags(stripslashes($_POST["minibb_news-bb_timediff"])));
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_minibb_news', $options);
		}
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
//		$bb_forumsID = htmlspecialchars($options['bb_forumsID'], ENT_QUOTES);
		$bb_topiclimit = htmlspecialchars($options['bb_topiclimit'], ENT_QUOTES);
		$bb_sort = htmlspecialchars($options['bb_sort'], ENT_QUOTES);
		$bb_maxlength = htmlspecialchars($options['bb_maxlength'], ENT_QUOTES);
		$bb_path = htmlspecialchars($options['bb_path'], ENT_QUOTES);
		$bb_timediff = htmlspecialchars($options['bb_timediff'], ENT_QUOTES);
	?>
		<div style="text-align: left;"><label for="minibb_news-title">Give the widget a title (<em>optional</em>):</label>
		<input style="width: 290px;" id="minibb_news-title" name="minibb_news-title" type="text" value="<?php echo $title; ?>" /></div>
		<p></p>
		<div style="text-align: left;"><label for="minibb_news-bb_path" >Write the directory where miniBB is installed (<em><strong>required</strong>, must be <strong>INSIDE</strong> your Wordpress installation <strong>WITH</strong> ending slash</em>), <br />example: <font color="#CC0000"><strong>forums/</strong></font></label>
		<input style="width: 290px;" id="minibb_news-bb_path" name="minibb_news-bb_path" type="text" value="<?php echo $bb_path; ?>" /></div>
		<p></p>
<!--
		<div style="text-align: left;"><label for="minibb_news-bb_forumsID">Display Forums ID separated with commas,<br /> example: <font color="#CC0000"><strong>3,8,9</strong></font> (<em>optional, blank means ALL</em>):</label>
		<input style="width: 290px;" id="minibb_news-bb_forumsID" name="minibb_news-bb_forumsID" type="text" value="<?php // echo $bb_forumsID; ?>" /></div>
		<p></p>
-->		
		<table border="0"><tr><td width="230"><div style="text-align: left;"><label for="minibb_news-bb_topiclimit">Number of topics to display in the sidebar (<em>optional</em>):</label></td><td><input style="width: 50px; text-align: right;" id="minibb_news-bb_topiclimit" name="minibb_news-bb_topiclimit" type="text" value="<?php echo $bb_topiclimit; ?>" /></div></td></tr></table>
		<br />
		<table border="0"><tr><td width="230"><div style="text-align: left;"><label for="minibb_news-bb_maxlength">Number of maximum characters for each displayed topic (<em>optional</em>):</label></td><td><input style="width: 50px; text-align: right;" id="minibb_news-bb_maxlength" name="minibb_news-bb_maxlength" type="text" value="<?php echo $bb_maxlength; ?>" /></div></td></tr></table>
		<br />
		<table border="0"><tr><td width="230"><div style="text-align: left;"><label for="minibb_news-bb_sort">Set <font color="#CC0000"><strong>0</strong></font> to sort by latest topics OR set <font color="#CC0000"><strong>1</strong></font> to sort by latest replies (<em>optional</em>):</label></td><td><input style="width: 50px; text-align: right;" id="minibb_news-bb_sort" name="minibb_news-bb_sort" type="text" value="<?php echo $bb_sort; ?>" /></div></td></tr>
		</table>
		<br />
		<table border="0"><tr><td width="230"><div style="text-align: left;"><label for="minibb_news-bb_timediff">Time difference from your server in seconds, example: <font color="#CC0000"><strong>3600</strong></font> (<em>optional</em>):</label></td><td><input style="width: 50px; text-align: right;" id="minibb_news-bb_timediff" name="minibb_news-bb_timediff" type="text" value="<?php echo $bb_timediff; ?>" /></div></td></tr>
		</table>
		<input type="hidden" id="minibb_news-submit" name="minibb_news-submit" value="1" />
	<?php
	}
// Register Widgets
	register_sidebar_widget('MiniBB News', 'widget_minibb_news');
	register_widget_control('MiniBB News', 'widget_minibb_news_control', 300, 420);
}
// Load The minibb_news Widget
add_action('plugins_loaded', 'widget_minibb_news_init');
?>
<?php
/*
Plugin Name: TrustedOnes
Plugin URI: http://blog.trustedones.com/wordpress
Description: Promote products and services that you (and your friends) trust, and increase the SEO relevance of your blog pages.
Author: Matt Hart, Arno Grbac
Version: 1.42
Author URI: http://arnox.trustedones.com/
*/

if (TrustedOnes::Javascript()) {
	header("Content-type: application/x-javascript");
	if (TrustedOnes::getGet('css') != 'no') {
		TrustedOnes::echojs("
<style>
#TrustedOnes {
	width:100%;
	padding:10px 0 10px;
	font-family:Verdana,Arial,Sans-Serif;
	font-size-adjust:none;
	font-stretch:normal;
	font-style:normal;
	font-variant:normal;
	font-weight:normal;
	line-height:normal;
	text-align:left;
	font-size:10pt;
}

#TrustedOnes a, h2 a:hover, h3 a:hover {
	color:#0066CC;
	text-decoration:none;
	font-size:10pt;
}

#TrustedOnes a:hover {
	text-decoration:underline;
	font-size:10pt;
}

#TrustedOnes h2 {
	margin:5px 0pt 0pt;
	padding:0pt;
	font-weight:bold;
	font-size:12pt;
}

#TrustedOnes a img {
	border:none;
}

#TrustedOnes td {
	font-size:8pt;
}

.TrustedOnes-logo {
  text-align:center;
  padding:20px;
}
</style>");
	}
	TrustedOnes::echojs("
<div id='TrustedOnes'>
<a target='TrustedOnes' href='http://www.trustedones.com'><img src='http://www.trustedones.com/images/trustedones.gif' /></a>
");
	TrustedOnes::widget();
} else {
	add_action("plugins_loaded", "TrustedOnes_init");
}



function TrustedOnes_init() {
	register_sidebar_widget(__('TrustedOnes.com'), 'TrustedOnes_widget');
	register_widget_control(__('TrustedOnes.com'), 'TrustedOnes_control');
}

function TrustedOnes_widget($args) {
	if (stripos($_SERVER['REQUEST_URI'],"wp-admin") !== false) return;

	extract($args);
	TrustedOnes::echojs($before_widget);

	TrustedOnes::echojs("
<style>
#TrustedOnes {
	width:100%;
}

#TrustedOnes td {
	font-size:8pt;
}

.TrustedOnes-topic, .TrustedOnes-topic a {
	font-size:10pt;
	font-weight:bold;
  text-align:center;
	padding:2px;
  background-color:#fab040;
	color:#fff;
    -moz-border-radius: 0.3em;
    -webkit-border-radius: 0.3em;
}

.TrustedOnes-logo {
  text-align:center;
}
.TrustedOnes-middle {
padding:5px 0 5px 0;
border-bottom:1px dashed #fab040;
}
.TrustedOnes-bottom {
  /*border-top:2px solid #fab040;*/
}
</style>");

	$username = TrustedOnes::GetOption('Username');
	if (TrustedOnes::Translate(TrustedOnes::GetOption('ShowFriends'),true,false)) {
		$wisdom = 2; // include friends
	} else {
		$wisdom = 0; // your own recommendations
	}

	TrustedOnes::echojs("<div class='TrustedOnes-logo'><a target='TrustedOnes' href='http://www.trustedones.com/tones/$username?wisdom=$wisdom'><img style='border:none;' src='http://trustedones.com/images/trustedones.gif' /></a></div>");

	TrustedOnes::widget();

	TrustedOnes::echojs("<div class='TrustedOnes-bottom'></div>");

	TrustedOnes::echojs($after_widget);
}

function TrustedOnes_control() {
	TrustedOnes::control();
}

class TrustedOnes {

	public static function Unable() {
		if (TrustedOnes::Javascript()) {
			if (TrustedOnes::getGet('css') != 'no') {
				echo "
el = document.getElementById('TrustedOnes');
if (el) el.style.width = '100%';";
			}
			TrustedOnes::echojs("
Unable to retrieve TrustedOnes recommendations. Usage:<br />
<b>&lt;script src='http://www.trustedones.com/php/trustedones-script.php?username=</b><i>username</i><b>&wisdom=</b><i>0,1,or 2</i><b>&numlines=</b><i># of lines, e.g. 4</i><b>&css=</b><i>no</i><b>&topics=</b><i>topic list, e.g.: kids,computers,gadgets,home-gardenprofessional-services-local</i><b>'&gt;&lt;/script&gt;</b>
<br />Parameters:<ul>
<li>username: your TrustedOnes username</li>
<li>apikey: your TrustedOnes API key (click Generate key under Account setup, Advanced features)</li>
<li>wisdom: 0 = you, 1 = you and your friends, 2 = you and your friends and your friend's friends</li>
<li>numlines: number of recommendations to display at once (defaults to 4)</li>
<li>css: no = don't use the default CSS. You can define your own CSS, just use the id <b>TrustedOnes</b>, e.g.:<br />
&lt;style&gt;<br />
#TrustedOnes {<br />
&nbsp;&nbsp;&nbsp;&nbsp;color:#77634;<br />
&nbsp;&nbsp;&nbsp;&nbsp;/* etc... */<br />
}<br />
&lt;/style&gt;</li>
<li>topics: comma-delimited list of topics to display, e.g.:<br />");
			$permalinks = TrustedOnes::XML_ToArray(TrustedOnes::Topics_XML(),"permalink",true);
			$topics = "";
			$count = 0;
			foreach ($permalinks as $key => $value) {
				if ($topics != "") {
					$topics .= ",";
					if ($count % 5 == 0) $topics .= "<br />";
				}
				$topics .= $key;
				$count++;
			}
			TrustedOnes::echojs("<b>&topics=</b>$topics</li></ul>");
		} else {
			TrustedOnes::echojs("Unable to retrieve TrustedOnes recommendations. Please <a href='wp-admin/widgets.php'>check your settings.</a>");
		}
	}

	public static function Translate($option, $iftrue, $iffalse = "") {
		if ($option) {
			return $iftrue;
		} else if ($option == "on") {
				return $iftrue;
			} else {
				return $iffalse;
			}
	}

	public static function widget() {
		$display_error = ini_get('display_errors');
		//		ini_set('display_errors','off');

		$username = TrustedOnes::GetOption('Username');
		$showfriends = TrustedOnes::Translate(TrustedOnes::GetOption('ShowFriends'),true,false);
		$numlines = TrustedOnes::GetOption('Numlines');
		if ($numlines == "" || $numlines == 0 || !is_numeric($numlines)) $numlines = 4;

		$topics = TrustedOnes::GetOption('topics');
		$topics_LastRetrieved = TrustedOnes::GetOption('topics_LastRetrieved');
		$topics_LastShown = TrustedOnes::GetOption('topics_LastShown');

		if ($topics == "" || count($topics) == 0) {
			TrustedOnes::Unable();
			ini_set('display_errors',$display_error);
			return ;
		}

		$result = "";

		if (TrustedOnes::Javascript()) {
			$topics_LastRetrieved = array();
			foreach ($topics as $key => $value) {
				if (!isset($topics_LastShown[$key])) {
					$topics_LastShown[$key] = 0;
				}
				if (!isset($topics_LastRetrieved[$key])) {
					$topics_LastRetrieved[$key] = 0;
				}
			}
		}

		asort($topics_LastShown,SORT_NUMERIC);
		foreach ($topics_LastShown as $key => $value) {
			if (!isset($topics[$key])) {
				$topics[$key] = false;	// this handles any cookie problems
			}
			if ($topics[$key]) {
				if (($topics_LastRetrieved[$key] + 60*60*24) < date("U")) {
				// More than a day since it was last retrieved
					$link = TrustedOnes::GetUserFeedLink($key);
					$xml_String = "";
					$xml_String = file_get_contents($link);
					if ($xml_String != "") {
						$topics_LastRetrieved[$key] = date("U");
						TrustedOnes::SetOption('topics_LastRetrieved',$topics_LastRetrieved);
						TrustedOnes::SetOption("topics_XML_$key", $xml_String);
						break;
					}
				} else {
					$xml_String = TrustedOnes::GetOption("topics_XML_$key");
					break;
				}
			}
		}

		$topic_count = 0;
		foreach ($topics_LastShown as $count_key => $value) {
			if ($topics[$count_key]) $topic_count++;
		}

		if (strpos($xml_String,"<?xml") !== false) {
			$username = TrustedOnes::GetOption('Username');
			if (TrustedOnes::Translate(TrustedOnes::GetOption('ShowFriends'),true,false)) {
				$wisdom = 2; // include friends
			} else {
				$wisdom = 0; // your own recommendations
			}

			$topics_LastShown[$key] = date("U");
			TrustedOnes::SetOption('topics_LastShown',$topics_LastShown);

			$xml = simplexml_load_string($xml_String);
			//$link = str_replace(".com/",".com",$xml->children()->link);
			$link = $xml->children()->root;
			$count = 0;
			$visible = "block";

			// The below version should be used if/when the link is fixed
			$top = "<div class='TrustedOnes-topic''><a target='TrustedOnes' title='Topic' href='" . TrustedOnes::GetTopicLink($key) . "?user=" . $username . "&wisdom=" . $wisdom . "'>$key</a></div>";

			$table = "<table style='width:100%'>";
			foreach($xml->children()->recommendations->children() as $rmd) {
				$attr = $rmd->attributes();
				$title = $attr['title'];
				$rec = $attr['rating'];
				$by_attr = $rmd->children()->by->attributes();
				$blurb = $rmd->children()->post->children()->blurb;
				if ($blurb != "..." && $blurb != "") {
					$count++;
					if ($count > $numlines) $visible = "none";
					$post_attr = $rmd->children()->post->attributes();
					$table .= "
<tr style='display:$visible'><td id='TrustedOnes_$count'><a target='TrustedOnes' href='$link" . $by_attr['link'] . "'><img style='padding-right:5px;border:none;' align='left' src='$link" . $by_attr['image_link'] . "' alt='" . $by_attr['user'] . "' /></a>
<a target='TrustedOnes' style='font-size:8pt;' href='$link" . $post_attr['link'] . "'><b>$title</b><a><div style='font-size:7pt;font-style:italic;'><b>$rec</b></div><a target='TrustedOnes' href='$link" . $post_attr['link'] . "'>
<div class='TrustedOnes-middle'>" .	substr($blurb,0,128) . "...</span></a></td></tr>";
				}
			}
			$table .= "</table>";

			$top .= "<div  class='TrustedOnes-middle' id='TrustedOnes' style='text-align:center;'>";

			if ($topic_count > 1) {
				$top .= "<a href='JavaScript:location.reload(true);'>next topic</a>";
			}

			if ($count > $numlines) {
				if ($topic_count > 1) $top .= " | ";
				$top .= "<a href='#' onClick='return TrustedOnes_ScrollPage()'>next page</a><br />
<script>
	var TrustedOnes_Top = 1;
	for (var i=0; i<" . rand(0,$count) . "; i++) {
		TrustedOnes_Scroller();
	}

function TrustedOnes_ScrollPage() {
	for (var i=0; i<$numlines; i++) {
		TrustedOnes_Scroller();
	}
	return false;
}
function TrustedOnes_Scroller() {
	el = document.getElementById('TrustedOnes_' + TrustedOnes_Top);
	if (el) {
		savedHTML = el.innerHTML;
		for (var i=1; i<$count; i++) {
			el_To = document.getElementById('TrustedOnes_' + i);
			iTo = i+1;
			el_From = document.getElementById('TrustedOnes_' + iTo);
			el_To.innerHTML = el_From.innerHTML;
		}
		el_To = document.getElementById('TrustedOnes_$count');
		el_To.innerHTML = savedHTML;
	}
}
</script>";
			}

			$top .= "</div>";

			$result = $top . $table;
		}

		if ($result == "") {
			TrustedOnes::Unable();
		} else {
			TrustedOnes::echojs($result);
		}
		ini_set('display_errors',$display_error);
	}

	public static function GetUserFeedLink($topic) {
		$xml = simplexml_load_string(TrustedOnes::Topics_XML());

		$username = TrustedOnes::GetOption('Username');
		if (TrustedOnes::Translate(TrustedOnes::GetOption('ShowFriends'),true,false)) {
			$wisdom = 1;
		} else {
			$wisdom = 0;
		}

		$apikey = TrustedOnes::GetOption('apikey');

		$link = $xml->children()->root . $xml->children()->link_users . "$username?key=$apikey&wisdom=$wisdom&topic=" . TrustedOnes::GetPermaLink($topic,$xml);

		return $link;
	}

	public static function GetTopicLink($topic) {
		$xml = simplexml_load_string(TrustedOnes::Topics_XML());

		return $xml->children()->root . $xml->children()->link_topic . TrustedOnes::GetPermaLink($topic, $xml);
	}

	public static function GetPermaLink($topic, $xml = "") {
		if ($xml == "") {
			$xml = simplexml_load_string(TrustedOnes::Topics_XML());
		}

		$permalink = "";
		foreach($xml->children()->topics->children() as $child) {
			$attr = $child->attributes();
			if ($attr["title"] == $topic) {
				$permalink = $attr['permalink'];
				break;
			}
		}
		return $permalink;
	}

	public static function ResetCache() {
		TrustedOnes::SetOption('topics_LastRetrieved',array());
		TrustedOnes::SetOption("topics_DateLastRetrieved",0);
	}

	public static function HandleForm() {
		TrustedOnes::SetOption('posted',$_POST);
		if (isset($_POST['username'])) TrustedOnes::SetOption('Username', $_POST['username']);
		if (isset($_POST['showfriends'])) TrustedOnes::SetOption('ShowFriends', $_POST['showfriends']);
		if (isset($_POST['numlines'])) TrustedOnes::SetOption('Numlines', $_POST['numlines']);
		if (isset($_POST['apikey'])) TrustedOnes::SetOption('apikey', $_POST['apikey']);

		if (isset($_POST['reset_cache'])) {
			if (TrustedOnes::Translate($_POST['reset_cache'],true,false)) {
				TrustedOnes::ResetCache();
			}
		}

		$topics = TrustedOnes::XML_ToArray(TrustedOnes::Topics_XML(),"title",true);
		$topics_LastRetrieved = TrustedOnes::GetOption('topics_LastRetrieved');
		$topics_LastShown = TrustedOnes::GetOption('topics_LastShown');
		if ($topics_LastRetrieved == "") $topics_LastRetrieved = array();
		if ($topics_LastShown == "") $topics_LastShown = array();

		foreach ($topics as $key => $value) {
			$name = TrustedOnes::parseName($key);
			if (TrustedOnes::getPost("to_topics_$name") == "on") {
				$topics[$key] = true;
			} else {
				$topics[$key] = false;
			}
			if (!isset($topics_LastRetrieved[$key])) $topics_LastRetrieved[$key] = 0;
			if (!isset($topics_LastShown[$key])) $topics_LastShown[$key] = 0;
		}
		TrustedOnes::SetOption('topics',$topics);
		TrustedOnes::SetOption('topics_LastRetrieved',$topics_LastRetrieved);
		TrustedOnes::SetOption('topics_LastShown',$topics_LastShown);
	}

	public static function control() {
		if (isset($_POST['trustedones_submit'])) TrustedOnes::HandleForm();

		$posted = TrustedOnes::GetOption('posted');

		$numlines = TrustedOnes::GetOption('Numlines');
		if ($numlines == "" || $numlines == 0 || !is_numeric($numlines)) $numlines = 4;

		TrustedOnes::echojs("
<input type='hidden' name='trustedones_submit' value='2' />
Enter your <a target='TrustedOnes' href='http://www.trustedones.com'>TrustedOnes</a> username: <input type='text' name='username' value='" . TrustedOnes::GetOption('Username') . "' /> <hr />
Enter your <a target='TrustedOnes' href='http://www.trustedones.com'>TrustedOnes</a> API key: <input type='text' name='apikey' value='" . TrustedOnes::GetOption('apikey') . "' /> <hr />
Number of recommendation lines to show at once?<br /> <input type='text' name='numlines' value='$numlines' size='4' /> <hr />
Do you want to show your friend's feeds? <br /> <input type='checkbox' name='showfriends' " . TrustedOnes::Translate(TrustedOnes::GetOption('ShowFriends'), "checked") . " /> Yes<hr />
Reset the local data cache? Use this if links or recommendations appear goofed up in the widget. It won't break anything. <br /> <input type='checkbox' name='reset_cache' /> Reset<hr />
Select the topics to show<br />
");
		$topics = array_merge(TrustedOnes::XML_ToArray(TrustedOnes::Topics_XML(),"title",true), TrustedOnes::GetOption('topics'));
		ksort($topics);

		TrustedOnes::echojs("<input type='checkbox' name='to_topicsNone' id='to_topicsNone' onClick='to_topicsFix(this)' /> None<br />");
		foreach ($topics as $key => $value) {
			$checked = "";
			if ($value) $checked = "checked";
			$name = TrustedOnes::parseName($key);
			TrustedOnes::echojs("<input type='checkbox' name='to_topics_$name' id='to_topics_$name' " . TrustedOnes::Translate($value,"checked") . " onClick='to_topicsFix(this)' /> $key<br />");
		}
		TrustedOnes::echojs("
<script>
function to_topicsFix(cb) {
	if (cb.id == 'to_topics_All') {
		els = document.getElementsByTagName('input');
		for (var i=0; i<els.length; i++) {
			if (els[i].id.substr(0,10) == 'to_topics_') {
				els[i].checked = true;
			}
		}
	} else if (cb.id == 'to_topicsNone') {
		els = document.getElementsByTagName('input');
		for (var i=0; i<els.length; i++) {
			if (els[i].id.substr(0,10) == 'to_topics_') {
				els[i].checked = false;
			}
		}
		cb.checked = false;
	} else {
		el = document.getElementById('to_topics_All');
		el.checked = false;
	}
}
</script>
");
	}

	public static function GetTopicTitleFromPermalink($permalink, $xml = "") {
		if ($xml == "") {
			$xmlText = TrustedOnes::GetOption('topics_XML');
			$xml = simplexml_load_string($xmlText);
		}
		foreach ($xml->children()->topics->children() as $topic) {
			$attr = $topic->attributes();
			if ($attr['permalink'] == $permalink) {
				return (string) $attr['title'];
			}
		}
		return $permalink;
	}


	public static function GetOption($option) {
		$result = "";
		if (TrustedOnes::Javascript()) {
			switch ($option) {
				case 'Username':
					$result = TrustedOnes::getGet('username');
					break;

				case 'ShowFriends':
					if (TrustedOnes::getGet('wisdom') == 1) {
						$result = true;
					} else {
						$result = false;
					}
					break;

				case 'apikey':
					$result = TrustedOnes::getGet('apikey');
					break;

				case 'Numlines':
					$result = TrustedOnes::getGet('numlines');
					break;

				case 'topics':
					$topics_Get = TrustedOnes::getGet('topics');
					if ($topics_Get != "") {
						$topics_Base = split(",",$topics_Get);
						$topics = array();
						$xmlText = TrustedOnes::GetOption('topics_XML');
						$xml = simplexml_load_string($xmlText);
						foreach ($topics_Base as $key) {
							if (trim($key) != "") {
							//$topics[] = trim($key);
								$topics_key = TrustedOnes::GetTopicTitleFromPermalink($key, $xml);
								$topics[$topics_key] = true;
							}
						}
						$result = $topics;
						if (count($topics) > 0) {
						// Pick a random one to show...
							$key = $topics[rand(0,count($topics)-1)];
							$result = array($key => true);
						}
					}
					if ($result == "") {
						TrustedOnes::Unable();
						exit;
					}
					break;

				case 'posted':
				// unused by Javascript
					break;

				//				default:
				//					$result = TrustedOnes::GetCookieOption($option);
				//					break;

				case 'topics_LastShown':
					$result = TrustedOnes::GetCookieOption($option);
					break;

				case 'topics_LastRetrieved':
				case 'topics_DateLastRetrieved':
					$result = 0;
					break;

				case 'topics_XML':
					$result = TrustedOnes::Topics_XML();
					break;

				case 'topics_DefaultXML':
					$result = TrustedOnes::DefaultOption("topics_DefaultXML");
					break;

				case 'topics_XML_$key':
			// never actually hits this - unused by Javascript anyway
			}
		} else {
			$result = get_option("TrustedOnes_$option");
		}
		if ($result == "") $result = TrustedOnes::DefaultOption($option);
		return $result;
	}

	public static function GetCookieOption($option) {
		$result = "";
		if (isset($_COOKIE["TrustedOnes_$option"])) {
			$result = unserialize($_COOKIE["TrustedOnes_$option"]);
		}
		return $result;
	}

	public static function SetCookieOption($option, $value, $timeplus = 0) {
		setcookie("TrustedOnes_$option", serialize($value), time()+$timeplus);
	}

	public static function SetOption($option, $value) {
		if (TrustedOnes::Javascript()) {
			switch ($option) {
				case 'topics_LastShown':
					$timeplus = 60*60*24*30;
					TrustedOnes::SetCookieOption($option, $value, $timeplus);
					break;
			}
		} else {
			update_option("TrustedOnes_$option", $value);
		}
	}

	public static function XML_ToArray($xmlText, $node, $default) {
		$xml = simplexml_load_string($xmlText);
		switch ($node) {
			case "title":
				$result = array("All" => true);
				break;
			default:
				$result = array();
				break;
		}
		foreach($xml->children()->topics->children() as $child) {
			$attr = $child->attributes();
			$value = $attr[$node];
			$result[(string) $value] = $default;
		}
		return $result;
	}

	public static function DefaultOption($option) {
		switch ($option) {
			case "Username":
				return "username";

			case "ShowFriends":
				return true;

			case "apikey":
				return "apikey";

			case "topics_DateLastRetrieved":
				return 0;

			case "topics":
				return TrustedOnes::XML_ToArray(TrustedOnes::GetOption('topics_XML'),"title",true);

			case "topics_XML":
				return TrustedOnes::Topics_XML();

			case "topics_DefaultXML":
				return '<?xml version="1.0" encoding="UTF-8"?>
<data version="0.9">
  <root>http://www.trustedones.com</root>
  <link_topic>/topics/</link_topic>
  <link_users>/feeds/users/</link_users>
  <description>Topics feed.</description>
  <topics>
		<topic title="art" permalink="art"/>
		<topic title="beer" permalink="beer"/>
		<topic title="books" permalink="books-local"/>
		<topic title="cars" permalink="cars-local"/>
		<topic title="computers" permalink="computers"/>
		<topic title="cool websites" permalink="cool-websites"/>
		<topic title="cycling" permalink="cycling-local"/>
		<topic title="entertainment" permalink="entertainment"/>
		<topic title="fashion &amp; clothing" permalink="fashion-n-clothing"/>
		<topic title="fine foods shopping" permalink="fine-foods"/>
		<topic title="gadgets &amp; electronics" permalink="gadgets"/>
		<topic title="guns" permalink="guns"/>
		<topic title="health &amp; beauty" permalink="health-n-beauty-local"/>
		<topic title="home &amp; garden" permalink="home-garden"/>
		<topic title="hotels" permalink="hotels-local"/>
		<topic title="kids" permalink="kids"/>
		<topic title="local events" permalink="events-local"/>
		<topic title="movies" permalink="movies"/>
		<topic title="music" permalink="music"/>
		<topic title="other services" permalink="other-services-local"/>
		<topic title="photography" permalink="photography"/>
		<topic title="podcasts" permalink="podcasts"/>
		<topic title="professional services" permalink="professional-services-local"/>
		<topic title="restaurants" permalink="restaurant-local"/>
		<topic title="shopping" permalink="shopping-local"/>
		<topic title="soapbox" permalink="soapbox"/>
		<topic title="special occasions" permalink="special-occasions"/>
		<topic title="sports" permalink="sports"/>
		<topic title="what to see" permalink="what-to-see-local"/>
		<topic title="wine" permalink="wine"/>
  </topics>
</data>
';
		}
	}

	public static function Topics_XML() {
		$last_retrieved = TrustedOnes::GetOption('topics_DateLastRetrieved');
		if ($last_retrieved >= (date("U") - 60*60*24)) {
		// Ignore last retrieved for now
			$result = TrustedOnes::GetOption('topics_DefaultXML');
		}

		if (!isset($result)) {
			try {
				$result = file_get_contents("http://www.trustedones.com/feeds/topics");
			} catch (Exception $ex) {
				$result = TrustedOnes::DefaultOption("topics_DefaultXML");
			}
		}

		TrustedOnes::SetOption("topics_DateLastRetrieved",date("U"));
		TrustedOnes::SetOption("topics_DefaultXML", $result);

		return $result;
	}

	public static function implode($assoc, $inglue = " = ", $outglue = "<br />\n") {
		$return = '';
		foreach ($assoc as $tk => $tv) {
			$return .= $outglue . $tk . $inglue . $tv;
		}
		return substr($return,strlen($outglue));
	}

	public static function parseName($name) {
		$result = $name;
		for ($i = 0; $i < strlen($result); $i++) {
			$char = substr($result,$i,1);
			if (($char >= 'A' && $char <= 'Z') ||
					($char >= 'a' && $char <= 'z')) {
			// valid characters
			} else {
				$result = str_replace($char,"_",$result);
			}
		}
		return $result;
	}

	public static function getPost($var) {
		if (isset($_POST[$var])) {
			return $_POST[$var];
		} else {
			return "";
		}
	}

	public static function getGet($var) {
		if (isset($_GET[$var])) {
			return $_GET[$var];
		} else {
			return "";
		}
	}

	public static function echojs($towrite) {
		if (TrustedOnes::Javascript()) {
			$lines = split("\n",$towrite);
			foreach ($lines as $line) {
				if (trim($line) != "") {
					echo "document.write(\"" . str_replace("\r","",str_replace("\"","\\\"",$line)) . "\");\n";
				}
			}
		// echo "document.write(\"" . str_replace("\r","",str_replace("\n","",$towrite)) . "\");\n";
		} else {
			echo $towrite;
		}
	}

	public static function Javascript() {
		if (stripos($_SERVER['PHP_SELF'],"script") > 0) {
			return true;
		} elseif (TrustedOnes::getGet('script') == 1) {
			return true;
		} else {
			return false;
		}
	}

}
?>

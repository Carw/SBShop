<?php

/**
 * 3b [build by burik] FileOrganizer tree
 * Создает дерево - аналог дерева документов MODx.
 * 
 * @author 		Буров Александр [burik] burikella@mail.ru
 * @cateatedOn	13.11.2009
 * @version 	1.0
 * 
 */

$_3b_tree_debug = false;

if($_3b_tree_debug) $fDbg = fopen("tree.debug.txt", "a");

function debugMess($mess) {
	global $fDbg, $_3b_tree_debug;
	if($_3b_tree_debug) {
		fwrite($fDbg, "{$mess}\r\n");
	}
}

$confname = $_REQUEST['conf'];

$ctime = time();
debugMess(str_repeat("=", 40));
debugMess("Session start at " . strftime("%d.%m.%Y %H:%M:%S"));
debugMess("Config name: {$confname}");

$siteURL = 'http://' . $_SERVER['HTTP_HOST'] . '/';
$sitePath = eregi_replace('.assets.libs.treebuilder', '', dirname(__file__));

debugMess("\$siteURL = {$siteURL}");
debugMess("\$sitePath = {$sitePath}");

define('MODX_SITE_URL', $siteURL);
define('MODX_BASE_URL', '/');
define('MODX_API_MODE', true);

@include($sitePath . '/index.php');

$modx->db->connect();
$modx->getSettings();

if(!isset ($_SESSION['mgrValidated'])) exit();

$confname = str_replace('../', '', $confname);

if(!preg_match("~^/?((([-\.a-z0-9]+)/)*[-\.a-z0-9]+\.php)$~i", $confname, $mathes)) {
	exit('Security error!');
}

$module = MODX_BASE_PATH . 'assets/modules/';

if(file_exists($module . $confname)) {
	@include_once($module . $confname);
} else {
	exit("<script type=\"text/javascript\">alert('Configuration file not found!');document.location = '{$siteURL}manager/index.php?a=1&f=tree';</script>");
}

$keySessionArray = preg_replace("~[^-.a-z0-9]*~i", "", $_3b_treebuilder['treeName']);
$modid = isset($_GET['modid']) ? intval($_GET['modid']) : 'top.main.modid';

$manager_theme = $modx->config['manager_theme'];
$theme = $manager_theme ? $manager_theme . '/' : '';
$site_name = "File Organizer v{$version}";

$folderIcon = MODX_SITE_URL . 'assets/modules/3b-modules/fileorganizer/images/icons/folder.gif';
$folderIconOpn = MODX_SITE_URL . 'assets/modules/3b-modules/fileorganizer/images/icons/folder_opened.gif';
	
if(file_exists(MODX_BASE_PATH . "manager/media/style/{$theme}style.php") && !isset($_style)) {
    $_style = array();
    include_once MODX_BASE_PATH . "manager/media/style/{$theme}style.php";
}

if(isset($modx->config['manager_language'])) $manager_language = $modx->config['manager_language'];
if(!isset($manager_language)) {
    $manager_language = "english"; // if not set, get the english language file.
}
$_lang = array();
@include_once $sitePath . "/manager/includes/lang/english.inc.php";
$length_eng_lang = count($_lang);
if($manager_language!="english" && file_exists(MODX_MANAGER_PATH."includes/lang/".$manager_language.".inc.php")) {
    @include_once $sitePath . "/manager/includes/lang/".$manager_language.".inc.php";
}

if (isset($_GET['opened'])) $_SESSION[$keySessionArray] = $_GET['opened'];
if (isset($_GET['savestateonly'])) {
    exit;
}

$indent    = intval($_GET['indent']);
$parent    = intval($_GET['parent']);
$expandAll = $_GET['expandAll'];

debugMess("\$indent = {$indent}");
debugMess("\$parent = {$parent}");
debugMess("\$expandAll = {$expandAll}");

if (isset($_SESSION[$keySessionArray])) {
    $opened = explode("|", $_SESSION[$keySessionArray]);
} else {
    $opened = array();
}
$opened2 = array();
$closed2 = array();
debugMess("\$opened2 = " . print_r($opened2, true));
debugMess("\$closed2 = " . print_r($closed2, true));

$output = makeHTML($indent,$parent,$expandAll,$theme);

$_SESSION[$keySessionArray] = implode('|', $opened2);
//@header("Content-Type: text/plain");
//@header("Content-Lenght: " . strlen($output));
debugMess("\$output = $output");

if($_3b_tree_debug) fclose($fDbg);
echo $output;
return;

if ($expandAll==2) {
	if($_3b_treebuilder['issetDeleted']()) {
		debugMess("exists deleted nodes");
		echo "<span id=\"binFull\"></span>";
	}
}

function makeHTML($indent, $parent, $expandAll, $theme) {
	global $modx, $siteURL, $_3b_treebuilder;
	global $theme, $_style, $modxDBConn, $output, $dbase, $table_prefix, $_lang, $opened, $opened2, $closed2;
	
	debugMess("function makeHTML starts({$indent}, {$parent}, {$expandAll}, {$theme})");
	
	$padding = "<img src=\"{$siteURL}assets/libs/treebuilder/images/empty.gif\" align=\"absmiddle\" />";
	
	$spacer = "";
	for ($i = 1; $i <= $indent; $i++){
		$spacer .= $padding;
	}
	
	$return = "";
	$returnJS = "";
	
	$cats = $_3b_treebuilder['getCats']($parent);
	debugMess("\$cats = " . print_r($cats, true));
	if(count($cats) > 0) {
		foreach($cats as $id => $cat) {
			
			$cat['title'] = htmlspecialchars($cat['title']);
			$title = (!$cat['published']) ? "<span class=\"unpublishedNode\">{$cat['title']}</span>" : $cat['title'];
			$title = ($cat['deleted']) ?   "<span class=\"deletedNode\">{$title}</span>" : $title;
			
			$idDisplay = "<small>({$id})</small>";
			
			$deleted = ($cat['deleted']) ? '1' : '0';
			
			$alt = addslashes($cat['tooltip']);
			
			debugMess("\$title = {$title}");
			debugMess("\$idDisplay = $idDisplay");
			debugMess("\$alt = $alt");

			if($expandAll == 1) {
				$opened2[] = $id;
			}
			
			$indentInc = $indent + 1;
			if ($expandAll ==1 || ($expandAll == 2 && in_array($id, $opened))) {
				
				$image = $_style["tree_folderopen"];
				if(isset($_3b_treebuilder['folderImageOpn']))
					if(!empty($_3b_treebuilder['folderImageOpn'])) $image = $siteURL.$_3b_treebuilder['folderImageOpn'];
				
				$return .= "<div id=\"node{$id}\" p=\"$parent\" style=\"white-space: nowrap;\">{$spacer}";
				$return .= "<img id=\"s{$id}\" align=\"absmiddle\" style=\"cursor: pointer\" src=\"{$_style["tree_minusnode"]}\" onclick=\"toggleNode(this, {$indentInc}, {$id}, 0, 0); return false;\" oncontextmenu=\"this.onclick(event); return false;\" />&nbsp;";
				$return .= "&nbsp;<img id=\"f{$id}\" align=\"absmiddle\" title=\"{$_lang['click_to_context']}\" style=\"cursor: pointer;\" src=\"{$image}\" onclick=\"showPopup({$id}, '" . addslashes($cat['title']) . "', event); return false;\" oncontextmenu=\"this.onclick(event); return false;\" onmouseover=\"setCNS(this, 1);\" onmouseout=\"setCNS(this, 0);\" onmousedown=\"itemToChange={$id}; selectedObjectName='" . addslashes($cat['title']) . "'; selectedObjectDeleted={$deleted}; selectedObjectFolder=1\" />&nbsp;";
				$return .= "<span onclick=\"treeAction({$id}, '" . addslashes($cat['title']) . "'); setSelected(this);\" onmouseover=\"setHoverClass(this, 1);\" onmouseout=\"setHoverClass(this, 0);\" class=\"treeNode\" onmousedown=\"itemToChange={$id}; selectedObjectName='" . addslashes($cat['title']) . "'; selectedObjectDeleted={$deleted}; selectedObjectFolder=1;\" oncontextmenu=\"document.getElementById('f{$id}').onclick(event); return false;\" title=\"{$alt}\">{$title}</span> {$idDisplay}<div style=\"display: block;\">";
				$return .= makeHTML($indentInc, $id, $expandAll, $theme);
				$return .= "</div></div>";
				
			}
			else {
				
				$image = $_style["tree_folder"];
				if(isset($_3b_treebuilder['folderImage']))
					if(!empty($_3b_treebuilder['folderImage'])) $image = $siteURL.$_3b_treebuilder['folderImage'];
				
				$return .= "<div id=\"node{$id}\" p=\"{$parent}\" style=\"white-space: nowrap;\">{$spacer}";
				$return .= "<img id=\"s{$id}\" align=\"absmiddle\" style=\"cursor: pointer\" src=\"{$_style["tree_plusnode"]}\" onclick=\"toggleNode(this, {$indentInc}, {$id}, 0, 0); return false;\" oncontextmenu=\"this.onclick(event); return false;\" />&nbsp;";
				$return .= "&nbsp;<img id=\"f{$id}\" title=\"{$_lang['click_to_context']}\" align=\"absmiddle\" style=\"cursor: pointer;\" src=\"{$image}\" onclick=\"showPopup({$id}, '" . addslashes($cat['title']) . "', event); return false;\" oncontextmenu=\"this.onclick(event); return false;\" onmouseover=\"setCNS(this, 1);\" onmouseout=\"setCNS(this, 0);\" onmousedown=\"itemToChange={$id}; selectedObjectName='" . addslashes($cat['title']) . "'; selectedObjectDeleted={$deleted}; selectedObjectFolder=1;\" />&nbsp;";
				$return .= "<span onclick=\"treeAction({$id}, '" . addslashes($cat['title']) . "'); setSelected(this);\" onmouseover=\"setHoverClass(this, 1);\" onmouseout=\"setHoverClass(this, 0);\" class=\"treeNode\" onmousedown=\"itemToChange={$id}; selectedObjectName='" . addslashes($cat['title']) . "'; selectedObjectDeleted={$deleted}; selectedObjectFolder=1;\" oncontextmenu=\"document.getElementById('f{$id}').onclick(event); return false;\" title=\"{$alt}\">{$title}</span> {$idDisplay}<div style=\"display:none;\"></div></div>";
				array_push($closed2, $id);
			}
			
			if ($expandAll == 1) {
				$returnJS .= '<script type="text/javascript"> ';
				foreach ($opened2 as $item) {
					$returnJS .= "top.tree.openedArray[" . intval($item) . "] = 1; ";
				}
				$returnJS .= '</script> ';
			} elseif ($expandAll == 0) {
				$returnJS .= '<script type="text/javascript"> ';
				foreach ($closed2 as $item) {
					$returnJS .= "top.tree.openedArray[" . intval($item) . "] = 0; ";
				}
				$returnJS .= '</script> ';
			}
			
		}
	}
	reset($cats);
	
	debugMess("making of cats html code is finished");
	
	$items = $_3b_treebuilder['getItems']($parent);
	debugMess("\$items = " . print_r($items, true));
	if(count($items) > 0) {
		foreach($items as $id => $item) {
			
			$item['title'] = htmlspecialchars($item['title']);
			$title = (!$item['published']) ? "<span class=\"unpublishedNode\">{$item['title']}</span>" : $item['title'];
			$title = ($item['deleted']) ?   "<span class=\"deletedNode\">{$title}</span>" : $title;
			
			$idDisplay = "<small>({$id})</small>";
			
			$deleted = ($item['deleted']) ? '1' : '0';
			
			$alt = addslashes($item['tooltip']);
			
			$image = $_style["tree_page"];
			if(isset($item['image']))
				if(!empty($item['image'])) $image = $siteURL.$item['image'];
				
			$return .= "<div id=\"node{$id}\" p=\"{$parent}\" style=\"white-space: nowrap;\">{$spacer}&nbsp;{$padding}&nbsp;";
			$return .= "<img id=\"p{$id}\" align=\"absmiddle\" title=\"{$_lang['click_to_context']}\" srtyle=\"cursor: pointer;\" src=\"{$image}\" onclick=\"showPopup({$id}, '" . addslashes($cat['title']) . "', event); return false;\" oncontextmenu=\"this.onclick(event); return false;\" onmouseover=\"setCNS(this, 1);\" onmouseout=\"setCNS(this, 0);\" onmousedown=\"itemToChange={$id}; selectedObjectName='" . addslashes($cat['title']) . "'; selectedObjectDeleted={$deleted}; selectedObjectFolder=0;\" />&nbsp;";
			$return .= "<span p=\"{$parent}\" onclick=\"treeAction({$id}, '" . addslashes($cat['title']) . "'); setSelected(this);\" onmouseover=\"setHoverClass(this, 1);\" onmouseout=\"setHoverClass(this, 0);\" class=\"treeNode\" onmousedown=\"itemToChange={$id}; selectedObjectName='" . addslashes($cat['title']) . "'; selectedObjectDeleted={$deleted}; selectedObjectFolder=0;\" oncontextmenu=\"document.getElementById('p{$id}').onclick(event); return false;\" title=\"{$alt}\">{$title}</span> {$idDisplay}</div>";
						
		}
	}
	reset($items);
	
	debugMess("making of items html code is finished");
	
	if(count($cats) + count($items) == 0) {
		$return .= "<div style=\"white-space: nowrap;\">{$spacer}&nbsp;{$padding}&nbsp;<img align=\"absmiddle\" src=\"{$_style["tree_deletedpage"]}\">&nbsp;<span class=\"emptyNode\">{$_lang['empty_folder']}</span></div>";
	}
	
	return $returnJS.$return;
	
}

?>
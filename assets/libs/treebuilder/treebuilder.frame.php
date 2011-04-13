<?php

/**
 * 3b [build by burik] FileOrganizer tree
 *
 * ��������� ��������� ������, �������� ������ ���������� MODx
 * �� ������������ ���������� �������
 * 
 * @author 		Burov Alexander [burik] burikella@mail.ru
 * @cateatedOn	13.11.2009
 * @version 	1.0 (rc1)
 * 
 */
$confname = $_REQUEST['conf'];

$siteURL = 'http://' . $_SERVER['HTTP_HOST'] . '/';
$sitePath = eregi_replace('.assets.libs.treebuilder', '', dirname(__file__));

define('MODX_SITE_URL', $siteURL);
define('MODX_BASE_URL', '/');
define('MODX_API_MODE', true);
include($sitePath . '/index.php');

$modx->db->connect();
$modx->getSettings();

// ����� ���������� ����� ������ ����� modules
// �� ��������� �� ���� ���� ��� ����� ������� :))
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

/*if(!IN_MANAGER_MODE) {
	die("<strong>INCLUDE_ORDERING_ERROR</strong><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
}*/

$manager_theme = $modx->config['manager_theme'];
$theme = $manager_theme ? $manager_theme . '/' : '';
$site_name = "File Organizer v{$version}";
	
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

$folderImage = $_style["tree_folder"];
if(isset($_3b_treebuilder['folderImage']))
	if(!empty($_3b_treebuilder['folderImage'])) $folderImage = $siteURL.$_3b_treebuilder['folderImage'];
		
$folderImageOpen = $_style["tree_folderopen"];
if(isset($_3b_treebuilder['folderImageOpn']))
	if(!empty($_3b_treebuilder['folderImageOpn'])) $folderImageOpen = $siteURL.$_3b_treebuilder['folderImageOpn'];

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html <?php echo $modx->config['manager_direction'] == 'rtl' ? 'dir="rtl"' : '';?> lang="<?php echo $modx->config['manager_lang_attribute'];?>" xml:lang="<?php echo $modx->config['manager_lang_attribute'];?>">
<head>
	
	<title>3b Tree Builder v1.0</title>
	
	<base href="<? echo $siteURL; ?>manager/" />
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $modx->config['modx_charset']; ?>" />
	
	<link rel="stylesheet" type="text/css" href="<? echo $siteURL;?>manager/media/style/<?php echo $theme; ?>style.css" />
	
	<script src="<? echo $siteURL;?>assets/libs/javascript/jquery-1.3.2.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		
		var modid = <?=$modid?>; // ID модуля для обратной связи
		
		$(document).ready(function(){
			treeResize();
			treeRebuild();
			treeControl();
			$(window).resize(function(){treeResize()});
		});
		
		function treeControl() {
			var params = top.main.location.search;
			if((params.indexOf('a=112') == -1)||(params.indexOf('id='+modid) == -1)) {
				top.tree.location = '<?php echo $siteURL; ?>manager/index.php?a=1&f=tree';
			} else {
				setTimeout("treeControl();", 2000);
			}
		}
		
		$(document).click(function() {
			if(_rc) return false;
			$('#mx_contextmenu').css('visibility', 'hidden');
		});
		
		var i = new Image(18,18);
		i.src="<?php echo $_style["tree_page"]?>";
		i = new Image(18,18);
		i.src="<?php echo $_style["tree_globe"]?>";
		i = new Image(18,18);
		i.src="<?php echo $_style["tree_minusnode"]?>";
		i = new Image(18,18);
		i.src="<?php echo $_style["tree_plusnode"]?>";
		i = new Image(18,18);
		i.src="<?php echo $_style["tree_folderopen"]?>";
		i = new Image(18,18);
		i.src="<?php echo $_style["tree_folder"]?>";
		
		var rpcNode = null;
		var ca = "open";
		var selectedObject = 0;
		var selectedObjectDeleted = 0;
		var selectedObjectName = "";
		var selectedObjectFolder = false;
		var _rc = 0;
		
<?php
		//
		// Jeroen adds an array
		//
		echo  "var openedArray = new Array();\n";
		if (isset($_SESSION[$keySessionArray])) {
		        $opened = explode("|", $_SESSION[$keySessionArray]);
		
		        foreach ($opened as $item) {
		             printf("openedArray[%d] = 1;\n", $item);
		        }
		}
		//
		// Jeroen end
		//
	
?>
		
		function treeResize() {
			$('#treeHolder').width($(window).width() - 20);
			$('#treeHolder').height($(window).height() - $('#treeHolder').offset().top - 6);
			$('#treeHolder').css('overflow', 'auto');
		}
		
		function rpcLoadData(source) {
			if(rpcNode != null) {
				$(rpcNode).html(source);
				$(rpcNode).css('display', 'block');
				$(rpcNode).loaded = true;
				var elm = top.mainMenu.document.getElementById("buildText");
	            if (elm) {
	                $(elm).html('');
	                $(elm).css('display', 'none');
	            }
				if($(rpcNode).attr('id') == 'treeRoot') {
					if($('#binFull').size())
						showBinFull();
					else
						showBinEmpty();
				}
	            if($('#mx_loginbox').size()) {
	                $(rpcNode).html('');
	                top.location = 'index.php';
	            }
			}			
		}
		
		function treeRebuild() {
			//saveFolderState();
			rpcNode = $('#treeRoot');
			$.ajax({
				type:		"GET",
				url: 		"<?php echo $siteURL?>assets/libs/treebuilder/treebuilder.nodes.php",
				data: 		"conf=<?=$confname?>&indent=1&parent=0&expandAll=2"+getFolderState(),
				dataType:	"html",
				success: 	function(source){
					rpcLoadData(source);
				}
			});
		}
		
		function showBinFull() {
	        var a = $('#Button10');
	        var title = '<?php echo $_lang['empty_recycle_bin']; ?>';
	        $(a).attr('title', title);
	        $(a).html('<?php echo $_style['empty_recycle_bin']; ?>');
			$(a).className = 'treeButton';
			$(a).click(function() {
				emptyTrash();
			});
	    }
	
	    function showBinEmpty() {
	        var a = $('#Button10');
	        var title = '<?php echo $_lang['empty_recycle_bin_empty']; ?>';
	        $(a).attr('title', title);
	        $(a).html('<?php echo $_style['empty_recycle_bin_empty']; ?>');
	        $(a).className = 'treeButtonDisabled';
	        $(a).unbind("click");
	    }
		
		function emptyTrash() {
			<?php echo $_3b_treebuilder['onBinClear']; ?>
		}
		
		function setActiveFromContextMenu(id) {
			$('.treeNodeSelected').removeClass('treeNodeSelected');
			$('#node' + id + ' span')[0].addClass('treeNodeSelected');
		}
		
		function expandTree() {
			rpcNode = $('#treeRoot');
			$.ajax({
				type:		"GET",
				url: 		"<?php echo $siteURL?>assets/libs/treebuilder/treebuilder.nodes.php",
				data: 		"conf=<?=$confname?>&indent=1&parent=0&expandAll=1",
				dataType:	"html",
				success: 	function(source){
					rpcLoadData(source);
				}
			});
		}
		
		function collapseTree() {
			rpcNode = $('#treeRoot');
			$.ajax({
				type:		"GET",
				url: 		"<?php echo $siteURL?>assets/libs/treebuilder/treebuilder.nodes.php",
				data: 		"conf=<?=$confname?>&indent=1&parent=0&expandAll=0",
				dataType:	"html",
				success: 	function(source){
					rpcLoadData(source);
				}
			});
		}
		
		function setSelected(obj) {
			$('.treeNodeSelected').removeClass('treeNodeSelected');
			$(obj).addClass('treeNodeSelected');
		}
		
		function setHoverClass(obj, val) {
			if (!$(obj).hasClass('treeNodeSelected')) {
				if (val) {
					$('.treeNodeHover').removeClass('treeNodeHover');
					$(obj).addClass('treeNodeHover');
				}
			}
		}
		
		function setCNS(obj, val) {
	        if(val == 1)
				$(obj).css('background', 'beige');
	        else
	            $(obj).css('background', '');
	    }
		
		function updateTree() {
	        treeRebuild();
	    }
		
		function treeAction(id, name) {
	        if(ca == 'move') {
	            try {
	                parent.main.setMoveValue(id, name);
	            } catch(oException) {
	                alert('<?php echo $_lang['unable_set_parent']; ?>');
	            }
	        }
	        if(ca == 'open' || ca == '') {
	        	top.main.gototree = false;
	            if(id == 0) {
	                // на главную модуля
	                parent.main.location.href="index.php?a=112&id=" + modid;
	            } else {
					if(selectedObjectFolder) {
						<?php echo $_3b_treebuilder['onFolderClick']; ?>;
					} else {
						<?php echo $_3b_treebuilder['onItemClick']; ?>;
					}
	            }
	        }
	        if(ca == 'parent') {
	        	if(!selectedObjectFolder && id != 0) {
	        		alert('В качестве родителя можно выбрать только категорию!');
	        		return;
	        	}
	            try {
	                parent.main.setParent(id, name);
	            } catch(oException) {
	                alert('<?php echo $_lang['unable_set_parent']; ?>');
	            }
	        }
	    }
		
		function showPopup(id, title, e) {
			
			var x, y;
	        var mnu = $('#mx_contextmenu');
			
			$('#nameHolder').html(title);
	        
	        var bodyHeight = parseInt(document.body.offsetHeight);
	        x = e.clientX > 0 ? e.clientX : e.pageX;
	        y = e.clientY > 0 ? e.clientY : e.pageY;
	        y = $(document).scrollTop() + (y/2);
			
	        if (y + mnu.offsetHeight > bodyHeight) {
	            y = y - ((y + mnu.offsetHeight) - bodyHeight + 5);
	        }
	        
	        itemToChange = id;
	        selectedObjectName = title;
	        //dopopup(x + 5, y);
			x += 5;
			// --------------------------------
			if(selectedObjectName.length > 20) {
	            selectedObjectName = selectedObjectName.substr(0, 20) + "...";
	        }
	        var h,context = $('#mx_contextmenu');
	        if(selectedObjectFolder) {
	            $('#mnuItem').css('display', 'none');
	            $('#mnuFolder').css('display', '');
	        } else {
	            $('#mnuItem').css('display', '');
	            $('#mnuFolder').css('display', 'none');
	        }
	        context.css('left', x<?php echo $modx->config['manager_direction']=='rtl' ? '-190' : '';?>+"px");
	        context.css('top', y + "px");
	
	        context.css('visibility', 'visible');
	        _rc = 1;
	        setTimeout("_rc = 0;", 100);
			// --------------------------------
	        e.cancelBubble = true;
			
	        return false;
			
		}
		
		function toggleNode(node,indent,parent,expandAll,privatenode) {
			
	        privatenode = (!privatenode || privatenode == '0') ? privatenode = '0' : privatenode = '1';
	        rpcNode = $(node.parentNode.lastChild);
	
	        var rpcNodeText;
	        var loadText = "<?php echo $_lang['loading_doc_tree'];?>";
	
	        var signImg = $("#s" + parent);
	        var folderImg = $("#f" + parent);
	
	        if ($(rpcNode).css('display') != 'block') {
				// открываем папку
	            if(signImg && signImg.attr('src').indexOf('<?php echo $manager_theme ? "$manager_theme/":""; ?>images/tree/plusnode.gif') > -1) {
	                signImg.attr('src', '<?php echo $_style["tree_minusnode"]; ?>');
	                folderImg.attr('src', '<?php echo $folderImageOpen; ?>');
	            }
	
	            rpcNodeText = $(rpcNode).html();
	
	            if (rpcNodeText == "" || rpcNodeText.indexOf(loadText) > 0) {
					
	                var i, spacer = '';
	                for(i=0;i<=indent+1;i++)
						spacer += '<img src="<?php echo $siteURL; ?>assets/libs/treebuilder/images/empty.gif" />';
						
	                $(rpcNode).css('display', 'block');
					
	                openedArray[parent] = 1 ;
	                
	                var folderState = getFolderState();
	                $(rpcNode).html("<span class='emptyNode' style='white-space:nowrap;'>"+spacer+"&nbsp;&nbsp;&nbsp;"+loadText+"...<\/span>");
					
					$.ajax({
						type:		"GET",
						url: 		"<?php echo $siteURL?>assets/libs/treebuilder/treebuilder.nodes.php",
						data: 		'conf=<?=$confname?>&indent=' + indent + '&parent=' + parent + '&expandAll=' + expandAll + folderState,
						dataType:	"html",
						success: 	function(source){
							rpcLoadData(source);
						}
					});
	            } else {
					
	                $(rpcNode).css('display', 'block');
	                openedArray[parent] = 1 ;
					
	            }
	        } else {
	            // закрываем папку
	            if(signImg && signImg.attr('src').indexOf('media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/tree/minusnode.gif')>-1) {
	                signImg.attr('src', '<?php echo $_style["tree_plusnode"]; ?>');
	                folderImg.attr('src', '<?php echo $folderImage; ?>');
	            }
	            //rpcNode.innerHTML = '';
	            $(rpcNode).css('display', 'none');
	            openedArray[parent] = 0 ;
	        }
			
	    }
		
		function getFolderState(){
	        //
	        //Jeoren added the parentarray
	        //
	        if (openedArray != [0]) {
	                oarray = "&opened=";
	                for (key in openedArray) {
	                   if (openedArray[key] == 1) {
	                      oarray += key+"|";
	                   }
	                }
	        } else {
	                oarray = "&opened=";
	        }
	        //
	        // Jeroen added the parentarray
	        //
	        return oarray;
	    }
	    function saveFolderState() {
	    	var folderState = getFolderState();
			$.ajax({
				type:		"GET",
				url: 		"<?php echo $siteURL?>assets/libs/treebuilder/treebuilder.nodes.php",
				data: 		"conf=<?=$confname?>&savestateonly=1"+folderState,
				dataType:	"html"
			});
	        //new Ajax('<?=MODX_SITE_URL?>assets/libs/treebuilder/treebuilder.nodes.php?conf=<?=$confname?>&savestateonly=1'+folderState, {method: 'get'}).request();
	    }
		
	</script>
	
	<!--[if lt IE 7]>
	<style type="text/css">
		body { behavior: url(media/script/forIE/htcmime.php?file=csshover.htc) }
		img { behavior: url(media/script/forIE/htcmime.php?file=pngbehavior.htc); }
	</style>
	<![endif]-->


</head>
<!-- Raymond: add onbeforeunload -->
<body class="treeframebody">

	<!-- to be improved -->
	<div id="treeSplitter"></div>
	
	<!-- верхнее меню -->
	<table id="treeMenu" width="100%"  border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td>
			<table cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td><a href="javascript:void(0);" class="treeButton" id="Button1" onclick="expandTree();return false;" title="<?php echo $_lang['expand_tree']; ?>"><?php echo $_style['expand_tree']; ?></a></td>
				<td><a href="javascript:void(0);" class="treeButton" id="Button2" onclick="collapseTree();return false;" title="<?php echo $_lang['collapse_tree']; ?>"><?php echo $_style['collapse_tree']; ?></a></td>
				<td><a href="javascript:void(0);" class="treeButton" id="Button4" onclick="treeRebuild();" title="<?php echo $_lang['refresh_tree']; ?>"><?php echo $_style['refresh_tree']; ?></a></td>
				<?
				$i = 2;
				foreach($_3b_treebuilder['treeMenu'] as $key => $button) {
					$i++;
					$url = $siteURL.$button['image'];
					?><td><a href="javascript:void(0);" class="treeButton" id="Button<?php echo $i; ?>" <?php echo ($button['onclick'] == '') ? "" : ('onclick="' . $button['onclick'] . ((substr($button['onclick'], strlen($button['onclick'])-1, 1) != ';') ? ';' : '') . 'return false;" '); ?>title="<?php echo addslashes($button['title']); ?>"><img src="<?php echo $url; ?>" style="width:16px; height:16px" /></a></td><?
					echo "\r\n\t\t\t\t";
				}
				
				?>
				<td><a href="javascript:void(0);" id="Button10" class="treeButtonDisabled"' title="<?php echo $_lang['empty_recycle_bin_empty'] ; ?>"><?php echo $_style['empty_recycle_bin_empty'] ; ?></a></td>
			</tr>
			</table>
		</td>
		<td align="right">
			<table cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td><a href="javascript:void(0);" class="treeButton" id="Button6" onclick="top.mainMenu.hideTreeFrame();" title="<?php echo $_lang['hide_tree']; ?>"><?php echo $_style['hide_tree']; ?></a></td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
	
	<div id="treeHolder">
		<div><?php echo $_style['tree_showtree']; ?>&nbsp;<span class="rootNode" onclick="treeAction(0, '<?php echo addslashes($_3b_treebuilder['treeName']); ?>');"><b><?php echo $_3b_treebuilder['treeName']; ?></b></span><div id="treeRoot"></div></div>
	</div>
	
	
	<!-- Contextual Menu Popup Code -->
	<div id="mx_contextmenu" onselectstart="return false;">
		<div id="nameHolder">&nbsp;</div>
		<div id="mnuItem">
			<?php
			foreach($_3b_treebuilder['itemPopupMenu'] as $key => $button) {
				if($button['text'] == "-") {
					?>
					<div class="seperator"></div>
					<?php
				} else {
					?>
					<div class="menuLink" onclick="this.className='menuLink'; <?php echo $button['onclick'] . ((substr($button['onclick'], strlen($button['onclick'])-1, 1) != ';') ? ';' : ''); ?>hideMenu();">
				        <img src='<?php echo $siteURL.$button['image']?>' /><?php echo $button['text']; ?>
				    </div>
					<?php
				}
			}
			?>
		</div>
		<div id="mnuFolder">
			<?php
			foreach($_3b_treebuilder['folderPopupMenu'] as $key => $button) {
				if($button['text'] == "-") {
					?>
					<div class="seperator"></div>
					<?php
				} else {
					?>
					<div class="menuLink" onclick="this.className='menuLink'; <?php echo $button['onclick'] . ((substr($button['onclick'], strlen($button['onclick'])-1, 1) != ';') ? ';' : ''); ?>hideMenu();">
				        <img src='<?php echo $siteURL.$button['image']?>' /><?php echo $button['text']; ?>
				    </div>
					<?php
				}
			}
			?>
		</div>
	</div>

</body>
</html>
<?php
$installMode = intval($_POST['installmode']);

?>

<form name="install" id="install_form" action="index.php?action=install" method="post">
  <div>
    <input type="hidden" value="<?php echo $install_language;?>" name="language" />
    <input type="hidden" value="<?php echo $manager_language;?>" name="managerlanguage" />
    <input type="hidden" value="<?php echo $installMode; ?>" name="installmode" />
    <input type="hidden" value="1" name="options_selected" />
  </div>

<?php


# load setup information file
$setupPath = realpath(dirname(__FILE__));
include "{$setupPath}/setup.info.php";

echo "<h2>" . $_lang['optional_items'] . "</h2><p>" . $_lang['optional_items_note'] . "</p>";

$chk = isset ($_POST['installdata']) && $_POST['installdata'] == "1" ? 'checked="checked"' : "";
// toggle options
echo "<h4>" . $_lang['checkbox_select_options'] . "</h4>
    <div id=\"installChoices\">";

$options_selected = isset ($_POST['options_selected']);

/**
 * Подключаем MODX
 */
require_once('../manager/includes/protect.inc.php');
include_once('../manager/includes/config.inc.php');
include_once ('../manager/includes/document.parser.class.inc.php');
$modx = new DocumentParser;
$modx->getSettings();

$documents = $modx->getDocumentChildren(0, 1, 0, 'id, pagetitle');

foreach ($documents as $doc) {
	$output .= "<input type=\"radio\" name=\"startdoc\" value=\"{$doc['id']}\" $chk />" . $doc['pagetitle'] . "<br /><br />\n";
}

echo $output;


?>
    </div>
    <p class="buttonlinks">
        <a href="javascript:document.getElementById('install_form').action='index.php?action=mode';document.getElementById('install_form').submit();" class="prev" title="<?php echo $_lang['btnback_value']?>"><span><?php echo $_lang['btnback_value']?></span></a>
        <a href="javascript:document.getElementById('install_form').submit();" title="<?php echo $_lang['install']?>"><span><?php echo $_lang['install']?></span></a>
    </p>

</form>
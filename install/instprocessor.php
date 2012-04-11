<?php
global $moduleName;
global $moduleVersion;
global $moduleSQLBaseFile;
global $moduleSQLDataFile;

global $moduleChunks;
global $moduleTemplates;
global $moduleSnippets;
global $modulePlugins;
global $moduleModules;
global $moduleTVs;

global $errors;

$create = false;

$setupPath = realpath(dirname(__FILE__));

// set timout limit
@ set_time_limit(120); // used @ to prevent warning when using safe mode?

/**
 * Подключаем MODX
 */
require_once('../manager/includes/protect.inc.php');
include_once('../manager/includes/config.inc.php');
include_once ('../manager/includes/document.parser.class.inc.php');
$modx = new DocumentParser;
$modx->getSettings();

/**
 * Генерация GUID модуля
 */
$gid = createGUID();

/**
 * Формируем нужные таблицы
 */
$sql_array = explode("\r\n\r\n", file_get_contents($moduleSQLBaseFile));

$num = 0;
foreach($sql_array as $sql_entry) {
	$sql_do = trim($sql_entry, "\r\n; ");
	if (preg_match('/^\#/', $sql_do)) {
		continue;
	}
	$sql_do = str_replace('{prefix}', $modx->db->config['table_prefix'], $sql_do);
	$num = $num + 1;
	if ($sql_do) {
		$modx->db->query($sql_do);
	}
}

/**
 * Установка сниппета
 */
$snippetInc = $modx->db->escape(file_get_contents($setupPath . '/assets/snippets/snippet.inc'));
$ins = array(
	'name' => 'SBShop',
	'description' => $_lang['snippet_description'],
	'snippet' => $snippetInc,
	'moduleguid' => $gid
);
$modx->db->insert($ins, $modx->getFullTableName('site_snippets'));
$snippetId = $modx->db->getInsertId();

/**
 * Установка плагина
 */
$pluginInc = $modx->db->escape(file_get_contents($setupPath . '/assets/plugins/plugin.inc'));
$ins = array(
	'name' => 'SBShop',
	'description' => $_lang['plugin_description'],
	'plugincode' => $pluginInc,
	'moduleguid' => $gid
);
$modx->db->insert($ins, $modx->getFullTableName('site_plugins'));
$pluginId = $modx->db->getInsertId();
/**
 * События плагина
 */
$modx->db->insert(array('pluginid' => $pluginId, 'evtid' => 20, 'priority' => 1), $modx->getFullTableName('site_plugin_events'));
$modx->db->insert(array('pluginid' => $pluginId, 'evtid' => 90, 'priority' => 1), $modx->getFullTableName('site_plugin_events'));
$modx->db->insert(array('pluginid' => $pluginId, 'evtid' => 91, 'priority' => 1), $modx->getFullTableName('site_plugin_events'));
$modx->db->insert(array('pluginid' => $pluginId, 'evtid' => 1000, 'priority' => 1), $modx->getFullTableName('site_plugin_events'));

/**
 * Установка модуля
 */
$moduleInc = $modx->db->escape(file_get_contents($setupPath . '/assets/modules/module.inc'));
$ins = array(
	'name' => 'SBShop',
	'description' => $_lang['module_description'],
	'wrap' => 1,
	'guid' => $gid,
	'modulecode' => $moduleInc,
);
$modx->db->insert($ins, $modx->getFullTableName('site_modules'));
$moduleId = $modx->db->getInsertId();
/**
 * Зависимости
 */
$modx->db->insert(array('module' => $moduleId, 'resource' => $snippetId, 'type' => 40), $modx->getFullTableName('site_module_depobj'));
$modx->db->insert(array('module' => $moduleId, 'resource' => $pluginId, 'type' => 30), $modx->getFullTableName('site_module_depobj'));
/**
 * Копируем информацию о версии
 */
copy('version.inc.php','../assets/extends/sbshop/version.inc.php');
/**
 * Очищаем кэш
 */
clearCache();



// create globally unique identifiers (guid)
function createGUID(){
    srand((double)microtime()*1000000);
    $r = rand() ;
    $u = uniqid(getmypid() . $r . (double)microtime()*1000000,1);
    $m = md5 ($u);
    return $m;
}

/**
 * Полная очистка кэша
 */
function clearCache() {
	global $modx;
	$modx->clearCache();
	include_once '../manager/processors/cache_sync.class.processor.php';
	$sync = new synccache();
	$sync->setCachepath("../assets/cache/");
	$sync->setReport(false);
	$sync->emptyCache();
}
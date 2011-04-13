<?php
/*
 * Title: Search Class
 * Purpose:
 *    The Search class contains all functions common to AjaxSearch functionalities
 *
 *    Version: 1.8.4  - Coroico (coroico@wangba.fr)
 *
 *    20/10/2009
 *
 *    Jason Coward (opengeek - jason@opengeek.com)
 *    Kyle Jaebker (kylej - kjaebker@muddydogpaws.com)
 *    Ryan Thrash (rthrash - ryan@vertexworks.com)
*/

// Default number admitted in case of a wrong &minChars parameter
define('MIN_CHARS',3);     // minimum number of characters
define('MAX_CHARS',30);    // maximum number of characters
define('MIN_WORDS',1);     // minimum number of words
define('MAX_WORDS',10);    // maximum number of words
define('EXTRACT_MIN',50);  // minimum length of extract
define('EXTRACT_MAX',800); // maximum length of extract

define('GROUP_CONCAT_LENGTH',4096); // maximum length of the group concat
define('PURGE',200); // maximum number of logs before a purge - 0 = illimited

class Search {

  // Conversion code name between html page character encoding and Mysql character encoding
  // Some others conversions should be added if needed. Otherwise Page charset = Database charset
  var $pageCharset = array(
    'utf8' => 'UTF-8',
    'latin1' => 'ISO-8859-1',
    'latin2' => 'ISO-8859-2'
  );

  var $advSearchType = array('oneword','allwords','exactphrase','nowords');

  var $pgCharset;       // page charset
  var $dbCharset;       // database charset
  var $needsConvert;    // charset conversion boolean
  var $pcreModifier;    // PCRE modifier

  // debug
  var $dbg;       // debug flag
  var $dbgTpl;    // log templates
  var $dbgRes;    // log data results
  var $asDebug;   // debug instance

  // log
  var $log;       // log flag
  var $logcmt;    // log comment flag
  var $asLog;     // log instance
  var $logid;     // search log id

  // search statement
  var $asSelect;

/**
 * Load the configuration file
 */
  function loadConfig(& $msgErr) {

    $valid = false;
    $msgErr = '';

    // include configuration file
    if (substr($this->cfg['config'], 0, 5) != "@FILE") $configFile = AS_PATH."configs/".$this->cfg['config'].".config.php";
    else $configFile = MODX_BASE_PATH . trim(substr($this->cfg['config'], 5));

    if (file_exists($configFile)) {
      include $configFile;
      $valid = true;
    }
    else {
      $msgErr = $configFile . ' not found ! check your AjaxSearch config parameter or the existing of your configuration file!';
    }
    return $valid;
  }

/**
 * Load the language file
 */
  function loadLang() {

    $_lang = array(); // language labels

    // include default language file
    $this->language = 'english';
    include AS_PATH."lang/{$this->language}.inc.php";

    // include other language file if set
    if (($this->cfg['language'] != '') && ($this->cfg['language'] != $this->language)) {
      if (file_exists(AS_PATH."lang/{$this->cfg['language']}.inc.php"))
        include AS_PATH."lang/".$this->cfg['language'].".inc.php";
        $this->language = $this->cfg['language'];
    }
    $this->_lang = $_lang;
  }

/**
 *  setDatabaseCharset : initialize the dbCharset and the appropriate
 *                       PCRE modifiers depending of the charset of database
 */
  function setDatabaseCharset(){
    global $database_connection_charset;

    $this->dbCharset = $database_connection_charset; // database charset
    $this->pcreModifier = ($database_connection_charset == "utf8") ? 'iu' : 'i';
    return;
  }

/**
 *  setPageCharset : initialize the pageCharset, depending of the charset of database
 */
  function setPageCharset(){

    $this ->pgCharset = array_key_exists($this->dbCharset,$this->pageCharset) ? $this->pageCharset[$this->dbCharset] : $this->dbCharset;
    return;
  }

/**
 * Strip the searchString with the user StripInput function
 *
 * @param string $searchString term searched
 * @param string $advSearch adanced Search parameter
 *
 */
  function stripSearchString(&$searchString,$stripInput,&$advSearch) {

    $searchString = preg_replace('/\s\s+/', ' ', trim($searchString));
    if (function_exists($stripInput)) $searchString = $stripInput($searchString,$advSearch);
    else $searchString = $this->defaultStripInput($searchString,$this->pgCharset);
    $valid = ($searchString !== '') ? true : false;
    return $valid;
  }

/**
 * Default user StripInput function
 *
 * @param string $searchString term searched
 */
  function defaultStripInput($searchString, $pgCharset = 'UTF-8'){

    if ($searchString !== ''){
      // Remove escape characters
      $searchString = stripslashes($searchString);

      // Remove js tags
      $searchString = stripJscripts($searchString);

      // Remove modx sensitive tags
      $searchString = stripTags($searchString);

      // Strip HTML tags
      $searchString = stripHtml($searchString);

      // and finally prevent JS XSS
      // The double_encode  parameter was added with version 5.2.3
      if (version_compare(PHP_VERSION, '5.2.3', '>='))
        $searchString = htmlspecialchars($searchString, ENT_COMPAT, $pgCharset, False);
      else
        $searchString = $this->php_compat_htmlspecialchars($searchString, ENT_COMPAT, $pgCharset, False);
    }
    return $searchString;
  }

/**
 * Do the search
 *
 * @param string $searchString term searched
 * @param string $advSearch adanced Search parameter
 *
 * @author Coroico (www.modx.wangba.fr)
 */
  function doSearch() {

    global $modx;
    $records = NULL;
    $searchString = mysql_real_escape_string($this->searchString);

    $this->asSelect = '';
    if ($this->initSearchContext()) {
      $fields = $this->getFields();
      $from = $this->getFrom($searchString,$this->advSearch);
      $where = $this->getWhere();
      $groupBy = $this->getGroupBy();
      $having = $this->getHaving($searchString,$this->advSearch);
      $orderBy = $this->getOrderBy();

      $this->asSelect = "SELECT $fields FROM $from WHERE $where";
      $this->asSelect .= " GROUP BY $groupBy HAVING $having ORDER BY $orderBy";

      if ($this->dbg) $this->asDebug->dbgLog($this->printSelect($this->asSelect),"Select");
      if (isset($this->joined)) {
        $modx->db->query("SET group_concat_max_len = " . GROUP_CONCAT_LENGTH . ";"); // increase the group_concat
      }

      $records = $modx->db->query($this->asSelect);
    }
    return $records;
  }

/**
 * get the fields clause of the AS query
 *
 * @return the fiels clause
 */
  function getFields(){

    $fields = array();
    $mpref = $this->main['tb_alias'];  // main table alias

    // id of the main table
    $fields[] = $mpref . '.' . $this->main['id'];

    // displayed fields of the main table
    if (isset($this->main['displayed']))
      foreach($this->main['displayed'] as $displayed) $fields[] = $mpref . '.' . $displayed;

    // date fields of the main table
    if (isset($this->main['date']))
      foreach($this->main['date'] as $date) $fields[] = $mpref . '.' . $date;

    // id field from joined tables
    if (isset($this->joined))
      foreach($this->joined as $joined){
        $jpref = $joined['tb_alias'];
          $f = 'GROUP_CONCAT( DISTINCT CAST(n' . $jpref . '.' . $joined['id'] . ' AS CHAR)';
          $f .= ' SEPARATOR "," ) AS ' . $jpref . '_' . $joined['id'];
          $fields[] = $f;
      }
    // displayed (concatened) fields from joined tables
    if (isset($this->joined))
      foreach($this->joined as $joined){
        $jpref = $joined['tb_alias'];
        $nbd = count($joined['displayed']);
        for($d=0;$d<$nbd;$d++){
          $f = 'GROUP_CONCAT( DISTINCT n' . $jpref . '.' . $joined['displayed'][$d];
          $f .= ' SEPARATOR "' . $joined['concat_separator'] . '" ) AS ' . $jpref . '_' . $joined['displayed'][$d];
          $fields[] = $f;
        }
      }

    if (count($fields)>0) $fieldsClause = implode(', ',$fields);
    else $fieldsClause = '*';
    return $fieldsClause;
  }

/**
 * get the "FROM" clause of the AS query
 *
 * @param string $searchString Search terms
 * @param string $advSearch advSearch parameter
 * @return "FROM" clause
 */
  function getFrom($searchString,$advSearch){

    // from main table
    $from[] =  $this->main['tb_name'] . ' ' . $this->main['tb_alias'];

    //left join with jfilter tables
    if (isset($this->main['jfilters'])) foreach($this->main['jfilters'] as $filter){
      $f = 'LEFT JOIN ' . $filter['tb_name'] . ' ' . $filter['tb_alias'];
      $f .= ' ON ' . $this->main['tb_alias'] . '.' . $filter['main'] . ' = ' . $filter['tb_alias'] . '.' . $filter['join'];
      $from[] = $f;
    }

    //left join with joined table
    if (isset($this->joined))
      foreach($this->joined as $joined){
        $jpref = 'n' . $joined['tb_alias'];
        $f = 'LEFT JOIN( ' . $this->getSubSelect($joined,$searchString,$advSearch) . ' )' . ' AS ' . $jpref . ' ON ';
        $f .= $this->main['tb_alias'] . '.' . $this->main['id'] . ' = ' . $jpref . '.' . $joined['join'];
        $from[] = $f;
      }

    $fromClause = implode(' ',$from);
    return $fromClause;
  }

/**
 * get the "WHERE" clause of the AS query
 *
 * @param string $searchString Search terms
 * @param string $advSearch advSearch parameter
 * @return "WHERE" clause
 */
  function getWhere(){

    // where clauses from the main table (filters)
    if (isset($this->main['filters']))
      foreach($this->main['filters'] as $filter) $where[]= $this->getFilter($this->main['tb_alias'],$filter);

    // where clauses from Main table (joined filters)
    if (isset($this->main['jfilters']))
      foreach($this->main['jfilters'] as $filter) $where[] = $this->getFilter($filter['tb_alias'],$filter);

    if (count($where)>0) $whereClause = '(' . implode(' AND ',$where) . ')';
    else $whereClause = '1';
    return $whereClause;
  }

/**
 * get the "GROUP BY" clause of the AS query
 *
 * @return "GROUP BY" clause
 */
  function getGroupBy(){

    $groupByClause = $this->main['tb_alias'] . '.' . $this->main['id'];
    return $groupByClause;
  }

/**
 * get the "HAVING" clause of the AS query
 *
 * @return "HAVING" clause
 */
  function getHaving($searchString,$advSearch){

    $like = $this->getWhereForm($advSearch);
    $whereOper = $this->getWhereOper($advSearch);
    $whereStringOper = $this->getWhereStringOper($advSearch);

    if (isset($this->main['searchable']))
      foreach($this->main['searchable'] as $searchable) $hvg[] = '(' . $this->main['tb_alias'] . '.' . $searchable . $like .')';

    // having clause from joined tables
    if ($advSearch != 'nowords') {
      if (isset($this->joined))
        foreach($this->joined as $joined){
          $jpref = $joined['tb_alias'];
          foreach($joined['searchable'] as $searchable) $hvg[] = '(' . $jpref . '_' . $searchable . $like .')';
        }
    }
    else {
      // Aggregate queries involving NOT LIKE comparisons with columns containing NULL may yield unexpected results
      if (isset($this->joined))
        foreach($this->joined as $joined){
          $jpref = $joined['tb_alias'];
          foreach($joined['searchable'] as $searchable) {
            $hvg[] = '((' . $jpref . '_' . $searchable . $like .') OR (' . $jpref . '_' . $searchable . ' IS NULL))';
          }
        }
    }

    if (count($hvg)>0) {
      $havingSubClause = '(' . implode($whereOper,$hvg) .')';

      // build of request - where clause regarding the search string
      $search = array();
      if ($advSearch == 'exactphrase') $search[] = $searchString;
      else $search = explode(' ',$searchString);
      foreach($search as $searchTerm) $having[]= preg_replace('/word/', $searchTerm, $havingSubClause);

      $havingClause = '(' . implode($whereStringOper,$having) .')';
    }
    else $havingClause = '1';
    return $havingClause;
  }

/**
 * get the "GROUP BY" clause of the AS query
 *
 * @return "GROUP BY" clause
 */
  function getOrderBy(){

    $orderByClause = '1';
    if ($this->cfg['order']){
      $order = explode(',',$this->cfg['order']);
      foreach($order as $ord) $orderBy[] = $this->main['tb_alias'] . '.' . $ord;
      $orderByClause = implode(',',$orderBy);
    }
    return $orderByClause;
  }

/**
 * get select statement for a joined table
 *
 * @param array $joined description of the joined table
 * @param string $searchString Search terms
 * @param string $advSearch advSearch parameter
 * @return select statement for a joined table
 */
  function getSubSelect($joined,$searchString,$advSearch){

    $fields = array();
    $from = array();
    $where = array();
    $whl = array();

    // field id of the joined table
    $fields[] = $joined['tb_alias'] . '.' . $joined['id'];

    // fields of the joined table
    if (isset($joined['displayed']))
      foreach($joined['displayed'] as $displayed) $fields[] = $joined['tb_alias'] . '.' . $displayed;

    // field  'join' of the joined table  if different of 'id' field. used for join
    if ($joined['join'] != $joined['id']) $fields[] = $joined['tb_alias'] . '.' . $joined['join'];

    $fieldsClause = implode(', ',$fields);

    // from of joined table
    $from[] =  $joined['tb_name'] . ' ' . $joined['tb_alias'];

    // from of joined filtered tables
    if (isset($joined['jfilters']))
      foreach($joined['jfilters'] as $jfilter) {
        $f = 'INNER JOIN ' . $jfilter['tb_name'] . ' ' . $jfilter['tb_alias'];
        $f .= ' ON ' . $joined['tb_alias'] . '.' . $jfilter['main'] . ' = ' . $jfilter['tb_alias'] . '.' . $jfilter['join'];
        $from[] = $f;
      }
    $fromClause = implode(' ',$from);

    // where clause for joined table (filters and joined filters)
    if (isset($joined['filters']))
      foreach($joined['filters'] as $filter) {
        $where[] = $this->getFilter($joined['tb_alias'],$filter);
      }
    if (isset($joined['jfilters']))
      foreach($joined['jfilters'] as $jfilter) {
        $where[] = $this->getFilter($jfilter['tb_alias'],$jfilter);
      }

    if (count($where)>0) {
      for ($i=0;$i<count($where);$i++) $where[$i] = '(' . $where[$i] . ')';
      $whl[] = implode(' AND ',$where);
    }

    // where clause for search terms restriction
    $whl[] = '(' . $this->getSearchTermsWhere($joined,$searchString,$advSearch). ')';
    $whereClause = '(' . implode(' AND ',$whl). ')';

    $subSelect = 'SELECT DISTINCT ' . $fieldsClause . ' FROM ' . $fromClause . ' WHERE ' . $whereClause;
    return $subSelect;
  }

  function getFilter($alias, $filter){
    $where = $this->getSubFilter($alias,$filter); // first part of the statement
    if (isset($filter['or'])){
      $or = $filter['or'];
      if (isset($or['tb_alias'])) $alias = $or['tb_alias']; // joined table
      else $alias = $this->main['tb_alias']; // main table
      $where = '(' . $where . ' OR ' . $this->getFilter($alias,$or) . ')'; // recursive call
    }
    return $where;
  }

  function getSubFilter($alias, $filter){
    $where = '';
    // = (EQUAL)
    if (($filter['oper'] == '=') || ($filter['oper'] == 'EQUAL')){
      $where .= $alias . '.' . $filter['field'] . '=' . $filter['value'];
    }
    // > (GREAT THAN)
    else if (($filter['oper'] == '>') || ($filter['oper'] == 'GREAT THAN')){
      $where .= $alias . '.' . $filter['field'] . '>' . $filter['value'];
    }
    // > (LESS THAN)
    else if (($filter['oper'] == '<') || ($filter['oper'] == 'LESS THAN')){
      $where .= $alias . '.' . $filter['field'] . '<' . $filter['value'];
    }
    // in (IN)
    else if (($filter['oper'] == 'in') || ($filter['oper'] == 'IN')){
      $where .= $alias . '.' . $filter['field'] . ' IN (' . $filter['value'] . ')';
    }
    // not in (NOT IN)
    else if (($filter['oper'] == 'not in') || ($filter['oper'] == 'NOT IN')){
      $where .= $alias . '.' . $filter['field'] . ' NOT IN (' . $filter['value'] . ')';
    }
    if ($where != '') $where = '(' . $where . ')';
    return $where;
  }

  function getWhereForm($advSearch){
    $whereForm = array(
      'like' => " LIKE '%word%'",
      'notlike' => " NOT LIKE '%word%'"
    );

    if ($advSearch == 'nowords') return $whereForm['notlike'];
    else return $whereForm['like'];
  }

  function getWhereOper($advSearch){
    $whereOper = array(
      'or' => " OR ",
      'and' => " AND "
    );

    if ($advSearch == 'nowords') return $whereOper['and'];
    else return $whereOper['or'];
  }

  function getWhereStringOper($advSearch){
    $whereStringOper = array(
      'or' => " OR ",
      'and' => " AND "
    );

    if ($advSearch == 'nowords' || $advSearch == 'allwords') return $whereStringOper['and'];
    else if ($advSearch == 'exactphrase') return '';
    else return $whereStringOper['or'];
  }

/**
 * get the "WHERE" clause related to search terms for joined tables
 *
 * @param array $joined description of the joined table
 * @param string $searchString Search terms
 * @param string $advSearch advSearch parameter
 * @return where clause
 */
  function getSearchTermsWhere($joined,$searchString,$advSearch){

    $like = $this->getWhereForm($advSearch);
    $whereOper = $this->getWhereOper($advSearch);
    $type = ($advSearch == 'allwords') ? 'oneword' : $advSearch;  // required for joined table
    $whereStringOper = $this->getWhereStringOper($type);

    if (isset($joined['searchable']))
      foreach($joined['searchable'] as $searchable) $whsc[] = '(' . $joined['tb_alias'] . '.' . $searchable . $like .')';
    $whereSubClause = implode($whereOper,$whsc);

    // build of request - where clause regarding the search string
    $search = array();
    if ($advSearch == 'exactphrase') $search[] = $searchString;
    else $search = explode(' ',$searchString);

    foreach($search as $searchTerm) $where[]=   preg_replace('/word/', preg_quote($searchTerm), $whereSubClause);

    $whereClause = implode($whereStringOper,$where);
    return $whereClause;
  }

/**
 * Check and initialize the description of the tables & content fields
 *
 * @author Coroico (www.modx.wangba.fr)
 */
  function initSearchContext(){

    global $modx;

    // unset the variable to reinit the context
    unset($this->main);
    unset($this->joined);

    $mainDefined = false;
    $this->withContent = false;
    $part = explode('|',$this->cfg['whereSearch']); // which tables and which fields ?

    foreach($part as $p){

      $p_array = explode(':',$p);
      $ptable = $p_array[0];
      $pfields = $p_array[1];

      switch ($ptable){
        case 'content':
          // Content ========================================= search in content
          $this->main = array(
              'tb_name' => $this->getShortTableName('site_content'),
              'tb_alias' => 'sc',
              'id' => 'id',
              'searchable' => array('pagetitle','longtitle','description','alias','introtext','menutitle','content'),
              'displayed' => array('pagetitle','longtitle','description','alias','introtext','template','menutitle','content'),
              'date' =>array('publishedon'),
              'filters' => array(),
              'jfilters'  => array()
          );
          if ($pfields != '' ) {
            unset($this->main['searchable']);
            if ($pfields == 'null' or $pfields == 'NULL') $this->main['searchable'] = array();
            else $this->main['searchable'] = explode(',',$pfields); // overwrite the default values
          }
          $this->withContent = true;
          $mainDefined = true;

          // selected list of ID allowed
          if ($this->validListIDs($this->listIDs)) $this->main['filters'][] = array(
              'field' => 'id',
              'oper' => 'in',
              'value' => $this->listIDs
          );
          // documents should be published
          $this->main['filters'][] = array(
              'field' => 'published',
              'oper' => '=',
              'value' => '1'
          );
          // documents should be searchable
          $this->main['filters'][] = array(
              'field' => 'searchable',
              'oper' => '=',
              'value' => '1'
          );
          // documents should'nt be deleted
          $this->main['filters'][] = array(
              'field' => 'deleted',
              'oper' => '=',
              'value' => '0'
          );
          // documents hidden or not from menu
          if (($this->cfg['hideMenu'] == 0 ) || ($this->cfg['hideMenu'] == 1 )) $this->main['filters'][] = array(
              'field' => 'hidemenu',
              'oper' => '=',
              'value' => $this->cfg['hideMenu']
          );
          // content of type reference searchable or not
          if ($this->cfg['hideLink'] == 1) $this->main['filters'][] = array(
              'field' => 'type',
              'oper' => '=',
              'value' => '\'document\''
          );
          // document group allowed regarding user authentification
          if ($this->validListIDs($this->cfg['docgrp'])) {
            $this->main['jfilters'][] = array(
              'tb_name' => $this->getShortTableName('document_groups'),
              'tb_alias' => 'dg',
              'main' => 'id',
              'join' => 'document',
              'field' => 'document_group',
              'oper' => 'in',
              'value' => $this->cfg['docgrp'],
              'or' => array(
                'field' => 'privateweb',
                'oper' => '=',
                'value' => '0'
              )
            );
          }
          else {
            // documents should be public
            $this->main['filters'][] = array(
                'field' => 'privateweb',
                'oper' => '=',
                'value' => '0'
            );
          }
          break;

        // Tvs ===============================search in template variable values
        // keep care of tb_alias change. The tb_alias of joined tables is also used by tvPhx parameter
        case 'tv':
          $this->joined[] = array(
              'tb_name' => $this->getShortTableName('site_tmplvar_contentvalues'),
              'tb_alias' => 'tv',
              'id' => 'id',
              'main' => 'id',                     // main table field used for join
              'join' => 'contentid',              // joined table field used for join
              'searchable' => array('value'),
              'displayed' => array('value'),      // 'id' and 'join' field added by default
              'concat_separator' => ', ',
              'filters' => array(),
              'jfilters'  => array()
          );
          $j = count($this->joined) - 1;
          if ($pfields != '' ) {
            unset($this->joined[$j]['searchable']);
            if ($pfields == 'null' or $pfields == 'NULL') $this->main['searchable'] = array();
            else $this->main['searchable'] = explode(',',$pfields); // overwrite the default values
          }
          // tv concatenation with allowed tv name only
          if ($this->cfg['withTvs']) {
            $wtv = $this->getListTvs($this->cfg['withTvs']);
            $this->joined[$j]['jfilters'][] = array(
              'tb_name' => $this->getShortTableName('site_tmplvars'),
              'tb_alias' => 'tmpl',
              'main' => 'tmplvarid',
              'join' => 'id',
              'field' => 'name',              // 'id' and 'join' field added by default
              'oper' => $wtv['oper'],
              'value' => $wtv['list']
            );
          }
          break;

        // Jot =========================================== search in jot content
        case 'jot':
          $this->joined[] = array(
              'tb_name' => $this->getShortTableName('jot_content'),
              'tb_alias' => 'jot',
              'id' => 'id',
              'main' => 'id',
              'join' => 'uparent',
              'searchable' => array('content'),   // 'id' and 'join' field added by default
              'displayed' => array('content'),
              'concat_separator' => ', ',
              'filters' => array()
          );
          $j = count($this->joined) - 1;
          if ($pfields != '' ) {
            unset($this->joined[$j]['searchable']);
            if ($pfields == 'null' or $pfields == 'NULL') $this->main['searchable'] = array();
            else $this->main['searchable'] = explode(',',$pfields); // overwrite the default values

          }
          // comments should be published
          $j = count($this->joined) - 1;
          $this->joined[$j]['filters'][] = array(
              'field' => 'published',
              'oper' => '=',
              'value' => '1'
          );
          break;

        // Maxigallery ================================= search in image gallery
        case 'maxigallery':
          $this->joined[] = array(
              'tb_name' => $this->getShortTableName('maxigallery'),
              'tb_alias' => 'gal',
              'id' => 'id',
              'main' => 'id',
              'join' => 'gal_id',
              'searchable' => array('title','descr'),
              'displayed' => array('title','descr','filename'),
              'concat_separator' => ', ',
              'filters' => array()
          );
          $j = count($this->joined) - 1;
          if ($pfields != '' ) {
            unset($this->joined[$j]['searchable']);
            if ($pfields == 'null' or $pfields == 'NULL') $this->main['searchable'] = array();
            else $this->main['searchable'] = explode(',',$pfields); // overwrite the default values
          }
          // image should be published
          $j = count($this->joined) - 1;
          $this->joined[$j]['filters'][] = array(
              'field' => 'hide',
              'oper' => '=',
              'value' => '0'
          );
          break;

        // search in your own table
        default:
          // get the tables description by a user function otherwise ignore it!
          if (function_exists($ptable)) {
            $ptable($main,$joined,$this->listIDs,$pfields);
            if ($main) {
              $this->main = $main; // substitute the default main table
              $mainDefined = true;
            }
            if ($joined) {
              // addition of a joined table
              if (count($joined['displayed'])>0) $this->joined[] = $joined;
            }
          }
          break;
      }
    }

    if ($this->dbg) { // debug of search context
      $this->asDebug->dbgLog($this->main,"Search context main ".$this->main['tb_name']);
      if (isset($this->joined))
        foreach($this->joined as $joined) $this->asDebug->dbgLog($joined,"Search context joined ".$joined['tb_name']);
    }

    return $mainDefined;
  }

/**
 *  validListIDs : check the validity of a value separated list of Ids
 */
  function validListIDs($IDs){
    $groups = explode(',',$IDs);
    $nbg = count($groups);
    for($i=0;$i<$nbg;$i++)
      if (preg_match('/^[0-9]+$/',$groups[$i]) == 0) return false;
    return true;
  }

/**
 *  validListTVs : check the validity of a value separated list of TVs name
 */
  function validListTvs($listTvs,& $msgErr){

    global $modx;

    $tvs = explode(',',$listTvs);
    //-- get tmplvar id to check if the TV exists
    $tplName = $this->getShortTableName('site_tmplvars');
    foreach($tvs as $tv){
      $tplRS = $modx->db->select('id', $tplName, 'name="' . $tv . '"');
      if (!$modx->db->getRecordCount($tplRS)) {
        $msgErr = "<br /><h3>AjaxSearch error: tv $tv not defined - Check your withTvs parameter !</h3><br />";
        return false;
      }
    }
    return true;
  }
/**
 *  getlListTVs : get the mysql list of Tvs from withTvs parameter
 */
  function getListTvs($listTvs){

    $wtv_array = explode(':',$listTvs);
    $wtvSign = $wtv_array[0];
    if ($wtvSign == '+') $wtv['oper'] = 'in';
    else $wtv['oper'] = 'not in';

    $tvs = explode(',',$wtv_array[1]);
    $nbtvs = count($tvs);
    for ($i=0;$i<$nbtvs;$i++) $tvs[$i] = "'{$tvs[$i]}'";
    $wtv['list'] = implode(',',$tvs);

    return $wtv;
  }

/**
 * Sort results by rank value
 *
 * @param string $searchString term searched
 * @param string $advSearch adanced Search parameter
 */
  function sortResultsByRank($searchString,$advSearch){

    $rkFields = array();

    if ($this->cfg['rank']){
      $searchString = strtolower($searchString);
      // sort search results by rank (nb extracts)
      $rkParam = explode(',',$this->cfg['rank']);
      foreach($rkParam as $rk){
        $rankParam = explode(':',$rk);
        $name = $rankParam[0];
        $weight = (isset($rankParam[1]) ? $rankParam[1] : 1);
        $rkFields[] = array('name' => $name,'weight' => $weight);
      }
      // adds the rank with value as new field to each result
      $nbResults = count($this->searchResults);
      for($i=0;$i<$nbResults;$i++) {
        $this->searchResults[$i]['rank'] = 0;
        foreach($rkFields as $rf){
          $this->searchResults[$i]['rank'] += $this->getRank($searchString,$advSearch,$this->searchResults[$i][$rf['name']],$rf['weight']);
        }
      }
      // sort the results by rank
      $i = 0;
      foreach($this->searchResults as $key => $row) {
        $rank[$key] = $row['rank'];
        $ascOrder[$key] = $i++;
      }
      array_multisort($rank, SORT_DESC, $ascOrder, SORT_ASC, $this->searchResults);
    }
  }

/**
 * get the rank value
 *
 * @param string $searchString term searched
 * @param string $advSearch adanced Search parameter
 * @param string $field field used by ranking
 * @param int $weight weight linked by the user to the field
 */
  function getRank($searchString,$advSearch,$field,$weight){

    $search = array();
    $rank = 0;

    if ($advSearch != 'nowords'){
      switch( $advSearch ) {
        case 'exactphrase':
          $search[0] = $searchString;
          break;

        case 'allwords':
        case 'oneword':
          $search = explode(" ", $searchString);
      }
      if (($this->dbCharset == 'utf8') && ($this->cfg['mbstring'])) {
        $field = mb_strtolower($field);
        foreach($search as $srch) $rank += mb_substr_count($field,$srch);
      }
      else {
        $field = strtolower($field);
        foreach($search as $srch) $rank += substr_count($field,$srch);
      }
      $rank = $rank * $weight;
    }
    return $rank;
  }

/**
 * cleanText : strip function to clean outputted results
 */
  function cleanText($text,$stripOutput) {

    if (function_exists($stripOutput)) $text = $stripOutput($text);
    else $text = $this->defaultStripOutput($text);
    return $text;
  }

/**
 * defaultStripOutput : default ouput strip function
 */
  function defaultStripOutput($text){

    if ($text !== ''){
      // replace line breaking tags with whitespace
      $text = stripLineBreaking($text);
      // strip modx sensitive tags
      $text = stripTags($text);
      // strip Jscripts
      $text = stripJscripts($text);
      // strip html tags. Tags should be correctly ended
      $text = stripHTML($text);
    }
    return $text;
  }

/**
 * getSelectList : get the search terms list
 */
  function getSelectList($searchWordList){
    $list = '';
    $swl = explode(',',$searchWordList);
    $searchFunction = array_shift($swl);
    if (function_exists($searchFunction)) $list = $searchFunction($swl);
    return $list;
  }

/**
 * initBreadcrumbs : initialize the breadcrumbs variables
 */
  function initBreadcrumbs(){
    if ($this->cfg['breadcrumbs']){
      $bc = explode(',',$this->cfg['breadcrumbs']);
      if (function_exists($bc[0])) {
        $this->breadcrumbs['type'] = 'function';
      } elseif ($this->snippet_exists($bc[0])) {
        $this->breadcrumbs['type'] = 'snippet';
      } else {
        $this->cfg['breadcrumbs'] = false;
      }

      if ($this->cfg['breadcrumbs']){
        $this->breadcrumbs['name'] = array_shift($bc);
        $this->breadcrumbs['params'] = array();
        foreach($bc as $prm){
          $param = explode(':',$prm);
          $this->breadcrumbs['params'][$param[0]] = (isset($param[1]) ? $param[1] : 0);
        }
      }
    }
  }

/**
 * snippet_exists : check the existing of a snippet
 */
  function snippet_exists($snippetName){

    global $modx;

    $tbl = $modx->getFullTableName('site_snippets');
    $select= "SELECT * FROM " . $tbl . " WHERE " . $tbl . ".name='" . $modx->db->escape($snippetName) . "';";
    $rs = $modx->db->query($select);
    return $modx->recordCount($rs);
  }

/**
 * Check user params
 */
  function checkParams($cfg,& $msgErr){

    $msgErr = '';

    // Check maxWords parameter
    if (isset($cfg['maxWords'])){
      if ($cfg['maxWords'] < MIN_WORDS) $cfg['maxWords'] = MIN_WORDS;
      if ($cfg['maxWords'] > MAX_WORDS) $cfg['maxWords'] = MAX_WORDS;
      $this->cfg['maxWords'] = $cfg['maxWords'];
    }
    // Check minChars parameter
    if (isset($cfg['minChars'])){
      if ($cfg['minChars'] < MIN_CHARS) $cfg['minChars'] = MIN_CHARS;
      if ($cfg['minChars'] > MAX_CHARS) $cfg['minChars'] = MAX_CHARS;
      $this->cfg['minChars'] = $cfg['minChars'];
    }
    // Check extractLength parameter
    if (isset($cfg['extractLength'])){
      if ($cfg['extractLength'] < EXTRACT_MIN) $cfg['extractLength'] = EXTRACT_MIN;
      if ($cfg['extractLength'] > EXTRACT_MAX) $cfg['extractLength'] = EXTRACT_MAX;
      $this->cfg['extractLength'] = $cfg['extractLength'];
    }
    // Check hideMenu parameter
    if (isset($cfg['hideMenu'])){
      if (($cfg['hideMenu'] != 0) && ($cfg['hideMenu'] != 1) && ($cfg['hideMenu'] != 2)) $cfg['hideMenu'] = 2;
      $this->cfg['hideMenu'] = $cfg['hideMenu'];
    }
    // check the number of extract and the fields to use with
    if (isset($cfg['hideMenu'])){
      $extr = explode(':',$cfg['extract']);
      if (($extr[0] == '') || (!is_numeric($extr[0]))) $extr[0] = 0;         // no extracts
      if (($extr[1] == '') || (is_numeric($extr[1]))) $extr[1] = 'content';  // default field
      $this->extractNb = (int) $extr[0];
      $this->extractFields = explode(',',$extr[1]);
      $this->cfg['extract'] = $extr[0] . ":" . $extr[1];
    }
    // check opacity parameter
    if (isset($cfg['opacity'])){
        if ($cfg['opacity'] < 0.) $cfg['opacity'] = 0.;
        if ($cfg['opacity'] > 1.) $cfg['opacity'] = 1.;
        $this->cfg['opacity'] = $cfg['opacity'];
    }

    $this->cfg['ajaxSearch'] = $cfg['ajaxSearch'];
    // check that the tables where to do the search exist
    if (isset($cfg['whereSearch'])){
      if ($cfg['whereSearch'] != 'content|tv'){
        $part = explode('|',$cfg['whereSearch']); // which tables ?
        foreach($part as $p){
          $p_array = explode(':',$p);
          $table = $p_array[0];
          if (($table != 'content') && ($table != 'tv') && ($table != 'jot') && ($table != 'maxigallery') && !function_exists($table)) {
            $msgErr = "<br /><h3>AjaxSearch error: table $table not defined in the configuration file: ".$this->cfg['config']." !</h3><br />";
            return $valid;
          }
        }
      }
    }

    // check the list of tvs
    if (isset($cfg['withTvs'])){
      if ($cfg['withTvs'] != ''){
        $wtv_array = explode(':',$cfg['withTvs']);
        $wtvSign = $wtv_array[0];
        if (isset($wtv_array[1])) $wtvList = $wtv_array[1];
        if (($wtvSign != '+') && ($wtvSign != '-')) {
          $wtvList = $wtvSign;
          $wtvSign = '+';
        }
        if (!$this->validListTvs($wtvList,$msgErr)) return False;
        $cfg['withTvs'] = $wtvSign . ':' . $wtvList;
      }
      $this->cfg['withTvs'] = $cfg['withTvs'];
    }

    // check the table and the tvDisplay function
    if (isset($cfg['tvPhx'])){
      if ($cfg['tvPhx']){
        $tvphx_array = explode(':',$cfg['tvPhx']);
        $tvphx_table = $tvphx_array[0];
        if (isset($tvphx_array[1])) $tvphx_func = $tvphx_array[1];
        if (!function_exists($tvphx_func)) {
          $msgErr = "<br /><h3>AjaxSearch error: the function $tvphx_func is not defined in the configuration file: ".$this->cfg['config']." !</h3><br />";
          return false;
        }
      }
      $this->cfg['tvPhx'] = $cfg['tvPhx'];
    }

    return true;
  }

/**
 * Check search string
 */
  function checkSearchString($searchString,& $msgErr){


    if ($this->dbg) $this->asDebug->dbgLog($searchString,"AjaxSearch - Search string");   // user search string
    // Search string checking
    $words_array = explode(' ',preg_replace('/\s\s+/', ' ', trim($searchString)));
    $mbStrlen = $this->cfg['mbstring'] ? 'mb_strlen' : 'strlen';
    // check the maximum number of words
    if (count($words_array) > $this->cfg['maxWords']) {
      $msgErr = sprintf($this->_lang['as_maxWords'],$this->cfg['maxWords']);
      return false;
    }
    // check the minimum and maximum character length
    if ($this->advSearch == 'exactphrase'){
      // exactphrase
      if ($mbStrlen($searchString) < $this->cfg['minChars']){
        $msgErr = sprintf($this->_lang['as_minChars'],$this->cfg['minChars']);
        return false;
      }
      if ($mbStrlen($searchString) > MAX_CHARS){
        $msgErr = sprintf($this->_lang['as_maxChars'],MAX_CHARS);
        return false;
      }
    }
    else {
      //oneword, allwords or nowords
      foreach($words_array as $word){
        if ($mbStrlen($word) < $this->cfg['minChars']){
          $msgErr = sprintf($this->_lang['as_minChars'],$this->cfg['minChars']);
          return false;
        }
        if ($mbStrlen($searchString) > MAX_CHARS){
          $msgErr = sprintf($this->_lang['as_maxChars'],MAX_CHARS);
          return false;
        }
      }
    }
    return true;
  }

/**
 * updateConfig : update configuration
 */
  function updateConfig($newcfg){

    foreach($newcfg as $key => $value) $this->cfg[$key] = $value; //overwriting of previous values
    // Re-initialize id group if needed
    if (isset($newcfg['parents']) || isset($newcfg['documents'])) {
      $this->cfg['idType'] =  isset($newcfg['documents']) ? "documents" : "parents";
      $listIDs = ($this->cfg['idType'] == "parents") ? $newcfg['parents'] : $newcfg['documents'];
      $this->cfg['listIDs'] = $this->cleanIDs($listIDs);
    }
  }

/**
 * initTvPhx : initialize tvPhx variables
 */
  function initTvPhx(){

    if ($this->cfg['tvPhx']){
      $tvs = explode(',',$this->cfg['tvPhx']);
      foreach($tvs as $tv) {
        $tvInfo = explode(':',$tv);
        if (isset($tvInfo[1]) && (function_exists($tvInfo[1]))) {
          $this->tvphx[] = $tvInfo;
        }
      }
    }
  }

/**
 * initExtractVariables : Initialize the Extract variables
 */
  function initExtractVariables(){
    $extr = explode(':',$this->cfg['extract']);
    $this->extractNb = $extr[0];      // number of extracts per document
    $this->extractFields = explode(',',$extr[1]);   // list of fields to use for the extract
  }

/**
 * returns extracts with highlighted searchterms
 *
 * @param string content to analyse
 * @param string search terms
 * @param string advanced search parameter (oneword,allwords,exactphrase,noword)
 * @param string highlight class
 * @param int number of extracts found
 * @return final extract with highlighted searchterms
 *
 * @author Coroico (www.modx.wangba.fr)
 */
  function getExtract($text, $searchString, $advSearch, $highlightClass, & $nbExtr) {

    $finalExtract = '';

    if (($text !== '') && ($searchString !== '') && ($this->extractNb > 0) && ($advSearch !== 'nowords')){

      $extracts = array();

      if (($this->dbCharset == 'utf8') && ($this->cfg['mbstring'])) {
        // convert of all Html entities before extraction
        // require version 5.0 and upper : http://bugs.php.net/bug.php?id=25670
        if (version_compare(PHP_VERSION, '5.0.0', '>=')) $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $mbStrpos = 'mb_strpos';
        $mbStrlen = 'mb_strlen';
        $mbStrtolower = 'mb_strtolower';
        $mbSubstr = 'mb_substr';
        $mbStrrpos = 'mb_strrpos';
      }
      else {
        // convert of all Html entities before extraction
        // require PHP 4.3
        $text = html_entity_decode($text, ENT_QUOTES);
        $mbStrpos = 'strpos';
        $mbStrlen = 'strlen';
        $mbStrtolower = 'strtolower';
        $mbSubstr = 'substr';
        $mbStrrpos = 'strrpos';
      }

      $rank = 0;
      $textLength = $mbStrlen($text);

      $searchList = $this->getSearchWords($searchString,$advSearch);

      // get all the extracts
      foreach($searchList as $searchTerm){

        // rank of the searchterm
        $rank++;
        // position of the first character of searchTerm
        $wordLeft = $mbStrpos($mbStrtolower($text), $mbStrtolower($searchTerm));
        // length of the searchTerm
        $wordLength = $mbStrlen($searchTerm);

        $iExtr = 0;
        while(is_int($wordLeft) && ($iExtr < $this->extractNb)){
          // position of the last character of searchTerm
          $wordRight = $wordLeft + $wordLength - 1;

          // position of the first character of extract
          $left = intval($wordLeft - ($this->cfg['extractLength'])/2 + $wordLength/2);

          // position of the last character of extract
          $right = $left + $this->cfg['extractLength'] - 1;

          if ($left <0 ) $left = 0;
          if ($right > $textLength) $right = $textLength;

          $extrLength = $right - $left + 1;

          $extracts[]= array(
              'word' => $searchTerm,
              'wordLeft' => $wordLeft,
              'wordRight' => $wordRight,
              'rank' => $rank,
              'left' => $left,
              'right' => $right,
              'etcLeft' => $this->cfg['extractEllips'],
              'etcRight' => $this->cfg['extractEllips']
          );
          $pos = $wordLeft + $wordLength;

          $wordLeft = $mbStrpos($mbStrtolower($text), $mbStrtolower($searchTerm),$pos);
          $iExtr++;
        }
      }

      // sort the extracts by left and right position
      $nbExtr = count($extracts); // number of relevant extracts found
      if ($nbExtr > 1){
        for($i=0;$i<$nbExtr;$i++) {
          $lft[$i] = $extracts[$i]['left'];
          $rght[$i] = $extracts[$i]['right'];
        }
        array_multisort($lft, SORT_ASC, $rght, SORT_ASC, $extracts);

        // find the closest space character on the left & rigth side (west side story !!)
        for($i=0;$i<$nbExtr;$i++) {
          // on the left
          $begin = $mbSubstr($text,0,$extracts[$i]['left']);
          if ($begin != '') $extracts[$i]['left'] = (int) $mbStrrpos($begin,' ');
          // on the right
          $end = $mbSubstr($text,$extracts[$i]['right']+1,$textLength - $extracts[$i]['right']);
          if ($end != '') $dr = (int) $mbStrpos($end,' ');
          if (is_int($dr)) $extracts[$i]['right']  += $dr + 1;
        }

        // avoid the extract intersections
        if ($extracts[0]['left'] == 0) $extracts[0]['etcLeft'] = '';
        for($i=1;$i<$nbExtr;$i++){
          // intersect on the left
          if ($extracts[$i]['left'] < $extracts[$i-1]['wordRight']) {
            $extracts[$i-1]['right'] = $extracts[$i-1]['wordRight'];
            $extracts[$i]['left'] = $extracts[$i-1]['right'] + 1;
            $extracts[$i-1]['etcRight'] = $extracts[$i]['etcLeft'] = '';
          }
          else if ($extracts[$i]['left'] < $extracts[$i-1]['right']) {
            $extracts[$i-1]['right'] = $extracts[$i]['left'];
            $extracts[$i-1]['etcRight'] = $extracts[$i]['etcLeft'] = '';
          }
        }
      }

      // build the final extract
      for($i=0;$i<$nbExtr;$i++) {
        $separation = ($extracts[$i]['etcRight'] != '') ? $this->cfg['extractSeparator'] : ''; // to avoid breaking sentences
        $extract = $mbSubstr($text,$extracts[$i]['left'],$extracts[$i]['right'] - $extracts[$i]['left']+1);
        // highlight the search term if needed
        if ($this->cfg['highlightResult']){
          $rank = $extracts[$i]['rank'];
          $searchTerm = $searchList[$rank-1];
          $extract = preg_replace( '/' . preg_quote( $searchTerm, '/' ) . '/' .$this->pcreModifier, '<span class="'.$highlightClass.' '.$highlightClass.$rank.'">\0</span>', $extract);
        }
        $finalExtract .= $extracts[$i]['etcLeft'] . $extract . $extracts[$i]['etcRight'] . $separation;
      }
      $finalExtract = $mbSubstr($finalExtract,0,$mbStrlen($finalExtract)-$mbStrlen($this->cfg['extractSeparator']));
    }
    return $finalExtract;
  }

/**
 *  getSearchWords : depending advSearch, get the search words
 */
  function getSearchWords($search, $advSearch){

    $searchList = array();
    if (($advSearch == 'nowords') || (!$search)) return $searchList;

    if ($advSearch == 'exactphrase') $searchList[] = $search;
    else $searchList = explode(' ',$search);

    return $searchList;
  }

/**
 *  initClassVariables : initialize the required Class values
 */
  function initClassVariables(){
    // prefix for results
    if ($this->cfg['ajaxSearch']) $this->asClass['prefix'] = PREFIX_AJAX_RESULT_CLASS;
    else $this->asClass['prefix'] = PREFIX_RESULT_CLASS;

    // highlight Class depending of search words
    $this->asClass['highlight'] = $this->getHighlightClass($this->searchString, $this->advSearch );
  }

/**
 *  getHighlightClass : depending the search words, set up the highlight classes
 */
  function getHighlightClass($search, $advSearch){
    $hClass = '';
    $searchList = $this->getSearchWords($search, $advSearch);
    if (count($searchList)){
      $hClass = HIGHLIGHT_CLASS;
      $count = 1;
      foreach($searchList as $searchTerm) {
        $hClass .= ' ' . HIGHLIGHT_CLASS . $count;
        $count++;
      }
    }
    return $hClass;
  }

/**
 *  getListIDs : with basic filtering get the IDs where to search
 */
  function getListIDs() {

    global $modx;

    $this->listIDs = $this->cfg['listIDs'];
    $idType = $this->cfg['idType'];
    $depth = $this->cfg['depth'];

    if (!strlen($this->listIDs) && !$this->cfg['filter']) return;     //! listIDs ='' means all documents

    // get the listIDs from the parents or documents parameter
    switch($idType) {
      case "parents":
        $arrayIDs = explode(",",$this->listIDs);
        $this->listIDs = implode(',',$this->getChildIDs($arrayIDs, $depth));
        if (!($this->listIDs)) {
          $this->listIDs = '999999';
          return;
        }      break;

      case "documents":
      break;
    }

    // exclude the unwanted IDs with the filter parameter
    if ($this->cfg['filter']){
      // interpret possible searchString metacharacter before to use
      $filter = $this->interpretFilterMetaCharacters($this->searchString,$this->advSearch);
      // build the filter list
      $parsedFilters = $this->parseFilters($filter);

      // get the rows linked to the unfiltered listIDs
      $rs = $this->doSearch();
      if ($modx->db->getRecordCount($rs) > 0){
        $rows = array();
        while ($row = mysql_fetch_assoc($rs)) {
          $rows[] = $row;
        }
        // filter the listIDs
        if (!class_exists('asFilter')) include_once AS_PATH . "classes/filter.class.inc.php";
        $filter = new asFilter();
        $rows = $filter->execute($rows,$parsedFilters);
        if (count($rows) > 0) {
          foreach ($rows as $key => $value) {
            $filteredIDs[] = $value[$this->main['id']];
          }
          $this->listIDs = implode(',',$filteredIDs);
        }
      }
    }

    return;
  }

/**
 *  interpretFilterMetaCharacters : interpret the possible metacharacter of the filter order
 *
 *  Take into account of the advSearch parameter
 *  if advSearch = 'oneword','nowords','allwords' then # is replaced by as many filters as searchterms
 *  e.g: &filter=`pagetitle,#,8` with searchString='school child' and advSearch='oneword'
 *  is equivalent to `pagetitle,school,8|pagetitle,child,8`
 */
  function interpretFilterMetaCharacters($searchString,$advSearch) {
    if ($searchString == 'exactphrase') $searchString_array[] = $searchString;
    else $searchString_array = explode(' ',$searchString);
    $nbs = count($searchString_array);

    $filter_array = explode('|',$this->cfg['filter']);
    $nbf = count($filter_array);
    for($i=0;$i<$nbf;$i++){
      if (preg_match('/#/',$filter_array[$i])){
        $terms_array = explode(',',$filter_array[$i]);
        if ($searchString == 'exactphrase') $filter_array[$i] = preg_replace('/#/i',$searchString,$filter_array[$i]);
        else {
          $filter_array[$i] = preg_replace('/#/i',$searchString_array[0],$filter_array[$i]);
          for($j=1;$j<$nbs;$j++){
            $filter_array[] = $terms_array[0] . ',' . $searchString_array[$j] . ',' . $terms_array[2];
          }
        }
      }
    }
    $filter = implode('|',$filter_array);
    return $filter;
  }

/**
 *  getChildIDs : Get the IDs ready to be processed
 *  From Ditto snippet by Mark Kaplan
 */
  function getChildIDs($IDs, $depth) {
    global $modx;

    $depth = intval($depth);
    $kids = array();
    $docIDs = array();

    if ($depth == 0 && $IDs[0] == 0 && count($IDs) == 1) {
      foreach ($modx->documentMap as $null => $document) {
        foreach ($document as $parent => $id) {
          $kids[] = $id;
        }
      }
      return $kids;
    } else if ($depth == 0) {
      $depth = 10000;
      // Impliment unlimited depth...
    }

    foreach ($modx->documentMap as $null => $document) {
      foreach ($document as $parent => $id) {
        $kids[$parent][] = $id;
      }
    }

    foreach ($IDs AS $seed) {
      if (!empty($kids[intval($seed)])) {
        $docIDs = array_merge($docIDs,$kids[intval($seed)]);
        unset($kids[intval($seed)]);
      }
    }
    $depth--;

    while($depth != 0) {
      $valid = $docIDs;
      foreach ($docIDs as $child=>$id) {
        if (!empty($kids[intval($id)])) {
          $docIDs = array_merge($docIDs,$kids[intval($id)]);
          unset($kids[intval($id)]);
        }
      }
      $depth--;
      if ($valid == $docIDs) $depth = 0;
    }

    return array_unique($docIDs);
  }

/**
 *  parseFilters : Split up the filters into an array and add the required fields to the fields array
 *  From Ditto snippet by Mark Kaplan
 */
  function parseFilters($filter=false,$cFilters=false,$globalDelimiter='|',$localDelimiter=',') {
    $parsedFilters = array("basic"=>array(),"custom"=>array());
    $filters = explode($globalDelimiter, $filter);
    if ($filter && count($filters) > 0) {
      foreach ($filters AS $filter) {
        if (!empty($filter)) {
          $filterArray = explode($localDelimiter, $filter);
          $source = $filterArray[0];
          $value = $filterArray[1];
          $mode = (isset ($filterArray[2])) ? $filterArray[2] : 1;
          $parsedFilters["basic"][] = array("source"=>$source,"value"=>$value,"mode"=>$mode);
        }
      }
    }
    if ($cFilters) {
      foreach ($cFilters as $name=>$value) {
        if (!empty($name) && !empty($value)) {
          $parsedFilters["custom"][$name] = $value[1];
        }
      }
    }
    return $parsedFilters;
  }

/**
 *  cleanIDs : clean IDs list of unwanted characters
 *  From Ditto snippet by Mark Kaplan
 */
  function cleanIDs($IDs) {
    //Define the pattern to search for
    $pattern = array (
      '`(,)+`', //Multiple commas
      '`^(,)`', //Comma on first position
      '`(,)$`' //Comma on last position
    );

    //Define replacement parameters
    $replace = array (
      ',',
      '',
      ''
    );

    //Clean startID (all chars except commas and numbers are removed)
    $IDs = preg_replace($pattern, $replace, $IDs);

    return $IDs;
  }
/**
 *  setResultLink : set the ResultLink PHx
 */
  function setResultLink($row){

    global $modx;

    $id = $this->main['id'];
    $hClass = $this->asClass['highlight'];

    if ($this->cfg['highlightResult'] && $hClass) {
      $resultLink = $modx->makeUrl($row[$id],'','searched='.urlencode($this->searchString).'&amp;advsearch='.urlencode($this->advSearch).'&amp;highlight='.urlencode($hClass));
    } else {
      $resultLink = $modx->makeUrl($row[$id]);
    }

    $this->varResult['resultClass'] = $this->asClass['prefix'];
    $this->varResult['resultLinkClass'] = $this->asClass['prefix'].'Link';
    $this->varResult['resultLink'] = $resultLink;
  }
/**
 *  setComment : set Comment form
 */
  function setComment(){
    $this->varResults['showCmt'] = $this->logcmt;

    if ($this->logcmt && $this->logid){
      $chkCmt = new asChunkie($this->cfg['tplComment']);   // comment
      if ($this->dbgTpl) $this->asDebug->dbgLog($chkCmt->getTemplate($this->cfg['tplComment']),"AjaxSearch - tplComment template " . $this->cfg['tplComment']);

      $varCmt = array();
      $varCmt['hiddenFieldIntro'] = $this->_lang['as_cmtHiddenFieldIntro'];
      $varCmt['hiddenField'] = 'ajaxSearch_cmtHField';
      $varCmt['logid'] = $this->logid;
      $varCmt['cmtIntroMessage'] = $this->_lang['as_cmtIntroMessage'];
      $varCmt['cmtSubmitText'] = $this->_lang['as_cmtSubmitText'];
      $varCmt['cmtResetText'] = $this->_lang['as_cmtResetText'];
      $varCmt['cmtThksMessage'] = $this->_lang['as_cmtThksMessage'];

      // parse the template and output the comment form
      $chkCmt->AddVar("as", $varCmt);
      $this->varResults['comment'] = $chkCmt->Render()."\n";
      unset($varCmt);
      unset($chkCmt);
    }
  }
/**
 *  addExtractToRow : add the extract result to each row
 */
  function addExtractToRow($row){

    $text = '';
    $nbExtr = 0;

    if ($this->extractNb) {
      // From row get all the fields allowed to use for extract
      foreach($this->extractFields as $f) if($row[$f]) $text .= $row[$f] . ' ';

      // clean the text if needed
      $text = $this->cleanText($text,$this->cfg['stripOutput']);

      // get the extract
      $text = $this->getExtract($text,$this->searchString,$this->advSearch,HIGHLIGHT_CLASS,$nbExtr);

    }
    $row['extract'] = $text; // set the concatened extracts
    return $row;
  }

/**
 *  setResultExtract : set the ResultExtract PHx
 */
  function setResultExtract($row){
    if ($this->extractNb) {
      $this->varResult['extractShow'] = 1;
      $this->varResult['extractClass'] = $this->asClass['prefix'] . 'Extract';
      $this->varResult['extract'] = $row['extract'];
    } else {
      $this->varResult['extractShow'] = 0;
    }
  }

/**
 *  setResultBreadcrumbs : set the ResultBreadcrumbs PHx
 */
  function setResultBreadcrumbs($row){
    global $modx;

    if ($this->cfg['breadcrumbs']) {
      if ($this->breadcrumbs['type'] == 'function'){
        // Breadcrumbs as a custom function
        $bc = $this->breadcrumbs['name']($this->main,$row,$this->breadcrumbs['params']);
      }
      elseif ($this->withContent) {
        // Breadcrumb as a snippet with the content as main table
        // save current document information
        $current_id = $modx->documentObject['id'];
        $current_parent = $modx->documentObject['parent'];
        $current_pagetitle = $modx->documentObject['pagetitle'];
        $current_longtitle = $modx->documentObject['longtitle'];
        $current_menutitle = $modx->documentObject['menutitle'];
        $current_description = $modx->documentObject['description'];
        // replace it by the document found
        $id = $this->main['id'];
        $modx->documentObject['id'] = $row[$id];
        $parentIds = $modx->getParentIds($row[$id],1);
        $pid= array_pop($parentIds);
        $modx->documentObject['parent'] = $pid;
        $modx->documentObject['pagetitle'] = $row['pagetitle'];
        $modx->documentObject['longtitle'] = $row['longtitle'];
        $modx->documentObject['menutitle'] = $row['menutitle'];
        $modx->documentObject['description'] = $row['description'];
        // run the Breadcrumbs snippet
        $bc = $modx->runSnippet($this->breadcrumbs['name'],$this->breadcrumbs['params']);
        // restore the current document
        $modx->documentObject['id'] = $current_id;
        $modx->documentObject['parent'] = $current_parent;
        $modx->documentObject['pagetitle'] = $current_pagetitle;
        $modx->documentObject['longtitle'] = $current_longtitle;
        $modx->documentObject['menutitle'] = $current_menutitle;
        $modx->documentObject['description'] = $current_description;
      }
      // display result
      $this->varResult['breadcrumbsShow'] = 1;
      $this->varResult['breadcrumbsClass'] = $this->asClass['prefix'] . 'Breadcrumbs';
      $this->varResult['breadcrumbs'] = $bc;
    }
    else {
      $this->varResult['breadcrumbsShow'] = 0;
    }
  }

/**
 *  setResultTvPhx : set the resultTvPhx PHx
 */
  function setResultTvPhx($row){
    global $modx;

    if ($this->cfg['tvPhx']) {
      foreach($this->tvphx as $tbv){
        $alias = $tbv[0];
        $display = $tbv[1];
        $id = $row[$this->main['id']];  // id of the main table row
        // get from row the "id" of tv
        $nbj = count($this->joined);
        for($j=0;$j<$nbj;$j++){
          if ($this->joined[$j]['tb_alias'] == $alias) break;
        }
        if ($j < $nbj){
          $res = $display($id); // get the tv rendered output to be displayed
          if ($this->dbg) $this->asDebug->dbgLog($res,"setResultTvPhx res");
          foreach($res as $name => $output){
            // set Phx for each id of tv
            $this->varResult[$name.'Show'] = 1;
            $this->varResult[$name.'Class'] = $this->asClass['prefix'] . ucfirst($name);
            $this->varResult[$name] = $output;
          }
        }
        else {
          $this->varResult[$name.'Show'] = 0;
        }
      }
    }
  }

/**
 *  setResultNumber : set number of result as PHx
 */
  function setResultNumber($no){
  	$this->varResult['resultNumber'] = $no;
  }

/**
 *  setResultSearchable : set fields like id, displayed, date, rank as PHx
 */
  function setResultSearchable($row){

    // set Phx for the "id" of the main table
    $id = $this->main['id'];
    $this->varResult[$id] = $row[$id];

    // set Phx for date fields of the main table
    if (isset($this->main['date']))
      foreach($this->main['date'] as $field) $this->setPhxField($field,$row,'date');

    // set Phx for displayed fields of the main table
    foreach($this->main['displayed'] as $field) $this->setPhxField($field,$row,'string');

    // set Phx for "id" field from joined tables.
    if (isset($this->joined)) foreach($this->joined as $joined){
      $f = $joined['tb_alias'] . '_' . $id;
      $this->setPhxField($f,$row,'string');
    }

    // set Phx for displayed fields from joined tables.
    if (isset($this->joined)) foreach($this->joined as $joined){
      foreach($joined['displayed'] as $field) {
        $f = $joined['tb_alias'] . '_' . $field;
        $this->setPhxField($f,$row,'string');
      }
    }

    // if rank requested publish the rank value
    if ($this->cfg['rank']) $this->setPhxField('rank',$row,'int');

    return $row[$id];
  }

/**
 *  setPhxField : set a field as PHx
 */
  function setPhxField($field,$row,$type='string'){

    $showField = $field . "Show";    // boolean show
    $classField = $field . "Class";  // name of class
    $contentField = $row[$field];
    if ($contentField != '') {
      $this->varResult[$showField] = 1;
      $this->varResult[$classField] = $this->asClass['prefix'] . ucfirst($field);

      if ($type == 'string'){
        $this->varResult[$field] = $this->cleanText($contentField,$this->cfg['stripOutput']);
      }
      elseif ($type =='date'){
        $this->varResult[$field] = date($this->cfg['formatDate'],$contentField);
      }
      else {
        $this->varResult[$field] = $contentField;
      }
    }
    else {
        $this->varResult[$showField] = 0;
    }
  }

/**
 *  returns a short table name based on db settings
 */
    function getShortTableName($tbl) {
      global $modx;
      return "`" . $modx->db->config['table_prefix'] . $tbl . "`";
    }

/**
 * initIdGroup : Initialize ID group where to look for
 */
  function initIdGroup(){
    $this->cfg['idType'] = ($this->cfg['documents'] != "") ? "documents" : "parents";
    $this->dcfg['idType'] = $this->cfg['idType'];

    $listIDs = ($this->cfg['idType'] == "parents") ? $this->cfg['parents'] : $this->cfg['documents'];
    if ($listIDs != '') $this->cfg['listIDs'] = $this->cleanIDs($listIDs);
    else $this->cfg['listIDs'] = $listIDs;
    $this->dcfg['listIDs'] = $this->cfg['listIDs'];
  }

/**
 * initDocGroup : Initialize document group
 */
  function initDocGroup(){
    global $modx;
    $this->cfg['docgrp'] = '';
    if ($docgrp = $modx->getUserDocGroups()) {
      $this->cfg['docgrp'] = implode(",", $docgrp);
    }
    $this->dcfg['docgrp'] = $this->cfg['docgrp'];
  }
/**
 *  set Debug level
 */
  function setDebug(){
    $dbg = (int) $this->cfg['debug'];
    if (abs($dbg) > 0 && abs($dbg) < 4) {
      if (!class_exists('AjaxSearchDebug')) include_once AS_PATH . "classes/ajaxSearchDebug.class.inc.php";
      $this->dbg = $dbg;
      $this->asDebug = new AjaxSearchDebug($this->cfg['version'],$dbg);
    }
    else {
      $this->dbg = 0;
    }
    // set levels
    $this->dbgTpl = (abs($this->dbg) > 1);  // log templates
    $this->dbgRes = (abs($this->dbg) > 2);  // log results

    return;
  }
/**
 *  set log level
 */
  function setLog(){
    global $modx;
    $asLog_array = explode(':',$this->cfg['asLog']);
    $log = (int) $asLog_array[0];
    if ($log > 0 && $log < 3) {
      if (!class_exists('AjaxSearchLog')) include_once AS_PATH . "classes/ajaxSearchLog.class.inc.php";
      $this->log = $log;
      // initialize purge
      $purge = isset($asLog_array[2]) ? (int) $asLog_array[2] : PURGE;
      if ($purge < 0) $purge = PURGE;
      $this->asLog = new AjaxSearchLog($purge);
      // initialize the log table
      $this->asLog->initLogTable();
      // initialize comment
      $this->logcmt = isset($asLog_array[1]) ? (int) $asLog_array[1] : 0;
      if ($this->logcmt) {
        $jsInclude = AS_SPATH . 'js/ajaxSearchCmt.js';
        $modx->regClientStartupScript($jsInclude);
      }
    }
    else {
      $this->log = 0;
    }
    return;
  }
/**
 *  set log infos
 */
  function setLogInfos($nbrs,$results){
    $this->logid = 0;
    if ($this->log){
      $logInfo = array();
      // set the log info into the database
      if (($this->log == 2) || ($nbrs = 0)){
        $logInfo['searchString'] = $this->searchString;
        $logInfo['nbResults'] = $nbrs;
        $logInfo['results'] = $results;
        $logInfo['asCall'] = $this->asCall;
        $logInfo['asSelect'] = mysql_real_escape_string($this->asSelect);
        $this->logid = $this->asLog->setLogRecord($logInfo);
      }
    }
    return;
  }
/**
 * getUfcg : get the non default configuration (advSearch excepted)
 */
  function getUcfg(){
    $tpl = " &%s=`%s`";
    $ucfg = '';
    foreach($this->cfg as $key=>$value){
      if ($value != $this->dcfg[$key]) $ucfg .= sprintf($tpl,$key,$this->cfg[$key]);
    }
    return $ucfg;
  }
/**
 * getAsCall : get the AjaxSearch snippet call
 *
 * return the AjaxSearch snippet call as a string
 *
 * @param string space separated list of non default configuration parameter keys
 *
 */
  function getAsCall($ucfg){
    $call_array = explode(' ',$ucfg);
    $tpl = "&%s=`%s`";

    if ($this->advSearch != 'oneword')  $call_array[] = sprintf($tpl,'advSearch',$this->advSearch);
    $asCall = "[!AjaxSearch";
    if (count($call_array)){
      $asCall .= "? ";
      $asCall .= implode(' ',$call_array);
    }
    $asCall .= "!]";
    return $asCall;
  }
/**
 *  print Select
 */
    function printSelect($query) {
      // rought SQL beautyfuller
      $searched = array(" SELECT", " GROUP_CONCAT"," LEFT JOIN"," SELECT"," FROM"," WHERE"," GROUP BY"," HAVING"," ORDER BY");
      $replace = array(" \r\nSELECT"," \r\nGROUP_CONCAT"," \r\nLEFT JOIN"," \r\nSELECT"," \r\nFROM"," \r\nWHERE"," \r\nGROUP BY"," \r\nHAVING"," \r\nORDER BY");
      $query = str_replace($searched,$replace," ".$query);
      return $query;
    }

/**
 *  Read config file
 */
  function readConfigFile($config){

    $configFile = (substr($config, 0, 5) != "@FILE") ? AS_PATH."configs/$config.config.php" : $modx->config['base_path'].trim(substr($config, 5));
    $fh = fopen($configFile, 'r');
    $output = fread($fh, filesize($configFile));
    fclose($fh);
    return "\n" . $output;
  }

/**
* Replace function htmlspecialchars()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.htmlspecialchars
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.2 $
 * @since       PHP 5.1.0
 * @require     PHP 4.0.0 (user_error)
 */
  function php_compat_htmlspecialchars($string, $quote_style = null, $charset = null, $double_encode = true){
    // Sanity check
    if (!is_scalar($string)) {
      user_error('htmlspecialchars() expects parameter 1 to be string, ' .
              gettype($string) . ' given', E_USER_WARNING);
      return;
    }

    if (!is_int($quote_style) && $quote_style !== null) {
      user_error('htmlspecialchars() expects parameter 2 to be integer, ' .
              gettype($quote_style) . ' given', E_USER_WARNING);
        return;
    }

    if (!is_scalar($charset)) {
        user_error('htmlspecialchars() expects parameter 3 to be string, ' .
            gettype($charset) . ' given', E_USER_WARNING);
        return;
    }

    if (!is_bool($double_encode)) {
        user_error('htmlspecialchars() expects parameter 4 to be bool, ' .
            gettype($double_encode) . ' given', E_USER_WARNING);
        return;
    }

    if ($double_encode === true) {
      $string = str_replace('&amp;', '&', $string);
    }

    $tf = array('&' => '&amp;','<' => '&lt;','>' => '&gt;');

    if ($quote_style & ENT_NOQUOTES) {
      $tf['"'] = '&quot';
    }

    if ($quote_style & ENT_QUOTES) {
      $tf["'"] = '&#039;';
    }

    return str_replace(array_keys($tf), array_values($tf), $string);
  }
}
//
// =============================================================================
//
/**
 *  displayTV : render TV output.
 *  used by tvPhx parameter for tv
 *
 */
  function displayTV($docid){
    global $modx;
    return $modx->getTemplateVarOutput('*', $docid);
  }

//
// =============================================================================
//
// Below functions could be used in end-user fonctions

/**
 *  stripTags : Remove modx sensitive tags
 */
function stripTags($text){
  // Regular expressions of things to remove from search string
  $modRegExArray[] = '~\[\[(.*?)\]\]~';   // [[snippets]]
  $modRegExArray[] = '~\[!(.*?)!\]~';     // [!noCacheSnippets!]
  $modRegExArray[] = '!\[\~(.*?)\~\]!is'; // [~links~]
  $modRegExArray[] = '~\[\((.*?)\)\]~';   // [(settings)]
  $modRegExArray[] = '~{{(.*?)}}~';       // {{chunks}}
  $modRegExArray[] = '~\[\*(.*?)\*\]~';   // [*attributes*]
  $modRegExArray[] = '~\[\+(.*?)\+\]~';   // [+phx+]

  // Remove modx sensitive tags
  foreach ($modRegExArray as $mReg)$text = preg_replace($mReg,'',$text);
  return $text;
}

/**
 *  stripHtml : Remove HTML sensitive tags
 */
function stripHtml($text){
  return strip_tags($text);
}

/**
 *  stripHtmlExceptImage : Remove HTML sensitive tags except image tag
 */
function stripHtmlExceptImage($text){
  $text = strip_tags($text,'<img>');
  return $text;
}

/**
 *  stripJscript : Remove jscript
 */
function stripJscripts($text){
  // strips jscripts
  $text = preg_replace( "'<script[^>]*>.*?</script>'si", "", $text );
  $text = preg_replace( '/{.+?}/', '', $text);
  return $text;
}

/**
 *  stripLineBreaking : replace line breaking tags with whitespace
 */
function stripLineBreaking($text){
  // replace line breaking tags with whitespace
  $text = preg_replace( "'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $text );
  return $text;
}

?>
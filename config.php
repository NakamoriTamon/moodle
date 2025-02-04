<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

if (in_array($_SERVER['HTTP_HOST'], ['localhost:8000', '127.0.0.1'])) {
  $CFG->dbtype    = 'mysqli';
  $CFG->dblibrary = 'native';
  $CFG->dbhost    = 'db';
  $CFG->dbname    = 'moodle';
  $CFG->dbuser    = 'user';
  $CFG->dbpass    = 'password';
  $CFG->prefix    = 'mdl_';
  $CFG->dboptions = array(
    'dbpersist' => 0,
    'dbport' => 3306,
    'dbsocket' => '',
    'dbcollation' => 'utf8mb4_0900_ai_ci',
  );

  $CFG->wwwroot   = 'http://localhost:8000';
  $CFG->dataroot  = '/var/www/moodledata';
  $CFG->admin     = 'admin';
} else {
  $CFG->dbtype    = 'mysqli';
  $CFG->dblibrary = 'native';
  $CFG->dbhost    = 'osaka-univ-db.cisr8k3leqdh.ap-northeast-1.rds.amazonaws.com';
  $CFG->dbname    = 'moodle';
  $CFG->dbuser    = 'osakaunivdb';
  $CFG->dbpass    = '5kAMlE2mVK3AeXOT3UTy';
  $CFG->prefix    = 'mdl_';
  $CFG->dboptions = array(
    'dbpersist' => 0,
    'dbport' => 3306,
    'dbsocket' => '',
    'dbcollation' => 'utf8mb4_unicode_ci',
  );

  $CFG->wwwroot   = 'https://open-univ.osaka-u.ac.jp';
  $CFG->dataroot  = '/var/www/moodledata';
  $CFG->admin     = 'admin';
}

$CFG->slasharguments = 0;
$CFG->directorypermissions = 0777;

require_once(__DIR__ . '/lib/setup.php');

// 共通項目
$customfield_select_list = [1 => 'テキスト', 2 => 'テキストエリア', 3 => 'チェックボックス', 4 => 'ラジオ', 5 => '日付'];
$customfield_type_list = [1 => 'text', 2 => 'textarea', 3 => 'checkbox', 4 => 'radio', 5 => 'date'];
define('CUSTOMFIELD_TYPE_LIST', $customfield_type_list);
$event_kbn_list = [1 => '1度きりのイベント', 2 => '複数回シリーズのイベント'];
define('EVENT_KBN_LIST', $event_kbn_list);
$event_status_list = [1 => '開催前', 2 => '開催中', 3 => '開催終了'];
define('EVENT_STATUS_LIST', $event_status_list);

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!

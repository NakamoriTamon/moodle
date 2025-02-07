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
$lang_default = "jp";
define('LANG_DEFAULT', $lang_default);
$guardian_kbn_default = 0;
define('GUARDIAN_KBN_DEFAULT', $guardian_kbn_default);
// 都道府県プルダウン
$prefectures = [
  '' => '選択してください',
  '北海道' => '北海道',
  '青森県' => '青森県',
  '岩手県' => '岩手県',
  '宮城県' => '宮城県',
  '秋田県' => '秋田県',
  '山形県' => '山形県',
  '福島県' => '福島県',
  '茨城県' => '茨城県',
  '栃木県' => '栃木県',
  '群馬県' => '群馬県',
  '埼玉県' => '埼玉県',
  '千葉県' => '千葉県',
  '東京都' => '東京都',
  '神奈川県' => '神奈川県',
  '新潟県' => '新潟県',
  '富山県' => '富山県',
  '石川県' => '石川県',
  '福井県' => '福井県',
  '山梨県' => '山梨県',
  '長野県' => '長野県',
  '岐阜県' => '岐阜県',
  '静岡県' => '静岡県',
  '愛知県' => '愛知県',
  '三重県' => '三重県',
  '滋賀県' => '滋賀県',
  '京都府' => '京都府',
  '大阪府' => '大阪府',
  '兵庫県' => '兵庫県',
  '奈良県' => '奈良県',
  '和歌山県' => '和歌山県',
  '鳥取県' => '鳥取県',
  '島根県' => '島根県',
  '岡山県' => '岡山県',
  '広島県' => '広島県',
  '山口県' => '山口県',
  '徳島県' => '徳島県',
  '香川県' => '香川県',
  '愛媛県' => '愛媛県',
  '高知県' => '高知県',
  '福岡県' => '福岡県',
  '佐賀県' => '佐賀県',
  '長崎県' => '長崎県',
  '熊本県' => '熊本県',
  '大分県' => '大分県',
  '宮崎県' => '宮崎県',
  '鹿児島県' => '鹿児島県',
  '沖縄県' => '沖縄県'
];
define('PREFECTURES', $prefectures);

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!

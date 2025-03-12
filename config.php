<?php  // Moodle configuration file

require '/var/www/vendor/autoload.php';
use Dotenv\Dotenv;
use core\context\system;
$dotenv = Dotenv::createImmutable('/var/www/html/moodle/custom');
$dotenv->load();
unset($CFG);
global $CFG;
$CFG = new stdClass();

$env = $_ENV['APP_ENV'] ?? 'development'; // ENV設定無い人は開発環境になる
if ($env === 'development') { // 開発環境
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
} else { // 本番環境
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
$event_kbn_list = [1 => '単発のイベント', 2 => '複数回シリーズのイベント'];
define('EVENT_KBN_LIST', $event_kbn_list);
$event_status_list = [1 => '開催前', 2 => '開催中', 3 => '開催終了'];
define('EVENT_STATUS_LIST', $event_status_list);
$lang_default = "jp";
define('LANG_DEFAULT', $lang_default);
$guardian_kbn_default = 0;
define('GUARDIAN_KBN_DEFAULT', $guardian_kbn_default);
$payment_select_list = [1 => 'コンビニ決済', 2 => 'クレジット', 3 => '銀行振込'];
define('PAYMENT_SELECT_LIST', $payment_select_list);
$membership_start_date = '04-01'; // 4/1を起算日とする　※一旦固定
define('MEMBERSHIP_START_DATE', $membership_start_date);
$type_code_list = [1 => '普通会員', 2 => '賛助会員'];
define('TYPE_CODE_LIST', $type_code_list);
define('TEKIJUKU_COMMEMORATION_IS_DELETE', [ //　適塾記念会　退会状況
  'ACTIVE' => 0,   // 未退会
  'INACTIVE' => 1  // 退会
]);


// 都道府県プルダウン
$prefectures = [
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

$roles = [
  '2' => '部門管理者',
  '9' => 'システム管理者'
];
define('ROLES', $roles);

$default_thumbnail = '/custom/public/assets/img/event/event02.jpg';
define('DEFAULT_THUMBNAIL', $default_thumbnail);

// 決済情報
$komoju_api_key = 'sk_test_6nhd2x41v77mupxnbjl9nwlk'; // テスト用秘密鍵
$komoju_endpoint = 'https://komoju.com/api/v1/sessions'; // テスト環境エンドポイント
$payment_method_list = [1 => 'konbini', 2 => 'credit_card', 3 => 'bank_transfer',]; // 決済方法
$komoju_webhook_secret_key = 'secret_key_y7scduh5di2edddcfah6e58c6'; // テスト用秘密鍵

$deadline_list = [
  '1' => '受付中',
  '2' => 'もうすぐ締め切り',
  '3' => '受付終了'
];
define('DEADLINE_LIST', $deadline_list);
$deadline_end = 3;
define('DEADLINE_END', $deadline_end);

//  URLでの暗号化共通キー
$url_secret_key = 'my_secret_key_1234567890';

$weekdays = ['日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'];
define('WEEKDAYS', $weekdays);

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!

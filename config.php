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

  $CFG->wwwroot   = 'http://192.168.128.67:8000';
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
$CFG->libdir = '/var/www/html/moodle/lib';

$CFG->slasharguments = 0;
$CFG->directorypermissions = 0777;

require_once(__DIR__ . '/lib/setup.php');

// 共通項目
// 権限
$role_coursecreator = 'coursecreator';
define('ROLE_COURSECREATOR', $role_coursecreator);
$role_user = 'user';
define('ROLE_USER', $role_user);
$role_admin = 'admin';
define('ROLE_ADMIN', $role_admin);
$role_list = [2 => 'coursecreator', 7 => 'user', 9 => 'admin'];
define('ROLE_LIST', $role_list);
$customfield_select_list = [1 => 'テキスト', 2 => 'テキストエリア', 3 => 'チェックボックス', 4 => 'ラジオ', 5 => '日付'];
$customfield_type_list = [1 => 'text', 2 => 'textarea', 3 => 'checkbox', 4 => 'radio', 5 => 'date'];
define('CUSTOMFIELD_TYPE_LIST', $customfield_type_list);
$single_event = 1;
define('SINGLE_EVENT', $single_event);
$plural_event = 2;
define('PLURAL_EVENT', $plural_event);
$every_day_event = 3;
define('EVERY_DAY_EVENT', $every_day_event);
$event_kbn_list = [$single_event => '単発のイベント', $plural_event => '複数回シリーズのイベント', $every_day_event => '期間内に毎日開催のイベント'];
define('EVENT_KBN_LIST', $event_kbn_list);
$event_before = 1;
define('EVENT_BEFORE', $event_before);
$event_start = 2;
define('EVENT_start', $event_start);
$event_end = 3;
define('EVENT_END', $event_end);
$event_status_selects = [1 => '開催前', 2 => '開催中', 3 => '開催終了'];
define('EVENT_STATUS_SELECTS', $event_status_selects);
$event_status_list = ['0' => '', 1 => '開催前', 2 => '開催中', 3 => '開催終了'];
define('EVENT_STATUS_LIST', $event_status_list);
$display_status_list = [1 => '開催前', 2 => '開催中', 3 => '開催終了'];
define('DISPLAY_EVENT_STATUS_LIST', $display_status_list);
$lang_default = "jp";
define('LANG_DEFAULT', $lang_default);
$guardian_kbn_default = 0;
define('GUARDIAN_KBN_DEFAULT', $guardian_kbn_default);
$free_event = 4;
define('FREE_EVENT', $free_event);
$payment_select_list = [1 => 'コンビニ決済', 2 => 'クレジット', 3 => '銀行振込'];
define('PAYMENT_SELECT_LIST', $payment_select_list);
$membership_start_date = '04-01'; // 4/1を起算日とする　※一旦固定
define('MEMBERSHIP_START_DATE', $membership_start_date);
$type_code_list = [1 => '普通会員', 2 => '賛助会員'];
define('TYPE_CODE_LIST', $type_code_list);
define('TEKIJUKU_PAID_DEADLINE', '04-01'); // 適塾支払期限(年度切替日：mm-dd形式)
define('TEKIJUKU_COMMEMORATION_IS_DELETE', [ //　適塾記念会　退会状況
  'ACTIVE' => 0,   // 未退会
  'INACTIVE' => 1,  // 退会
]);
// イベントテーブル用 mdl_event
define('IS_APPLY_BTN', [ //　申し込みボタンを表示
  'DISABLED' => 0,   // 非表示
  'ENABLED' => 1,  // 表示
]);
// 権限テーブル用 mdl_user
define('ROLE', [ // 権限ロール
  'COURSECREATOR' => 2,
  'USER' => 7,
  'ADMIN' => 9,
]);
// ユーザーテーブル用 mdl_role_assignments
define('CONFIRMED', [ // メール確認状況
  'IS_UNCONFIRMED' => 0,
  'IS_CONFIRMED' => 1,
]);
// 申し込み～コース中間テーブル用 mdl_event_application_course_info
define('PARTICIPATION_KBN', [ // イベント参加状況
  'PARTICIPATION' => 1, // 参加
  'NON_PARTICIPATION' => 2, // 不参加 
  'CANCEL' => 3 // キャンセル
]);
define('TICKET_TYPE', [ // チケットタイプ区分
  'SELF' => 1, // 本人分
  'ADDITIONAL' => 2, // 追加分 
]);
// 申し込みテーブル用 mdl_event_application
define('EVENT_APPLICATION_PACKAGE_TYPE', [ // パッケージ種別
  'SINGLE' => 1, // 単発申し込み
  'BUNDLE' => 2, // 一括申し込み 
]);
// 適塾記念会テーブル
define('IS_SUBSCRIPTION', [ // サブスクリプション
  'SUBSCRIPTION_DISABLED' => 0, // 解約
  'SUBSCRIPTION_ENABLED' => 1 // 契約
]);
define('PAID_STATUS', [ // 決済状況
  'UNPAID'                  => 1, // 未決済
  'PROCESSING'              => 2, // 決済中
  'COMPLETED'               => 3, // 決済済
  'SUBSCRIPTION_PROCESSING' => 4  // 決済中（サブスクリプション）
]);

$lecture_format_on_site = 1; //現地開催
define('LECTURE_FORMAT_ON_SITE', $lecture_format_on_site);

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

$default_thumbnail = '/custom/public/assets/img/no_image.jpg';
define('DEFAULT_THUMBNAIL', $default_thumbnail);
$default_thumbnail_2 = '/custom/public/assets/img/no_image_2.jpg';
define('DEFAULT_THUMBNAIL_2', $default_thumbnail_2);

/* 決済情報 */
if ($env === 'development') {
  // 開発API
  $komoju_api_key = $_ENV['KOMOJU_TEST_API_KEY'];
  $komoju_webhook_secret_key = $_ENV['KOMOJU_WEBHOOK_SECRET_KEY'];
} else {
  // 本番API
  $komoju_api_key = $_ENV['KOMOJU_LIVE_API_KEY'];
  $komoju_webhook_secret_key = $_ENV['KOMOJU_WEBHOOK_SECRET_KEY'];
}

define('KOMOJU_API_KEY', $komoju_api_key);
$komoju_endpoint = 'https://komoju.com/api/v1/sessions'; // エンドポイント
define('KOMOJU_ENDPOINT', $komoju_endpoint);
$payment_method_list = [1 => 'konbini', 2 => 'credit_card', 3 => 'bank_transfer',]; // 決済方法 KOMOJUの決済方法とは関係ないため表示していないですが"4:無料,5:現金"もあります
define('PAYMENT_METHOD_LIST', $payment_method_list);

$deadline_selects = [
  '1' => '受付中',
  '2' => 'もうすぐ締め切り',
  '3' => '受付終了'
];
define('DEADLINE_SELECTS', $deadline_selects);
$deadline_list = [
  '0' => '',
  '1' => '受付中',
  '2' => 'もうすぐ締め切り',
  '3' => '受付終了'
];
define('DEADLINE_LIST', $deadline_list);
$deadline_end = 3;
define('DEADLINE_END', $deadline_end);

$display_deadline_list = [
  '1' => '受付中',
  '2' => 'もうすぐ締め切り',
  '3' => '受付終了'
];
define('DISPLAY_DEADLINE_LIST', $display_deadline_list);

//  URLでの暗号化共通キー
$url_secret_key = 'my_secret_key_1234567890';

$weekdays = ['日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'];
define('WEEKDAYS', $weekdays);

// 成人年齢
$adult_age = 18;
define('ADULT_AGE', $adult_age);

// イベント参加状態
$is_participation_list = [1 => '参加済', 2 => '不参加', 3 => 'キャンセル'];
define('IS_PARTICIPATION_LIST', $is_participation_list);

// アカウント承認状態
$is_apply_list = [0 => '未承認', 1 => '承認'];
define('IS_APPLY_LIST', $is_apply_list);

/* 
*アンケート回答項目
*/
$decision_list = [1 => 'はい', 2 => 'いいえ'];
define('DECISION_LIST', $decision_list);
// 本日のプログラムをどのようにしてお知りになりましたか
$found_method_list = [
  1 => 'チラシ',
  2 => 'ウェブサイト',
  3 => '大阪大学公開講座「知の広場」からのメール',
  4 => 'SNS（X, Instagram, Facebookなど)',
  5 => '21世紀懐徳堂からのメールマガジン',
  6 => '大阪大学卒業生メールマガジン',
  7 => '大阪大学入試課からのメール',
  8 => 'Peatixからのメール',
  9 => '知人からの紹介',
  10 => '講師・スタッフからの紹介',
  11 => '自治体の広報・掲示',
  12 => 'スマートニュース広告'
];
define('FOUND_METHOD_LIST', $found_method_list);
// 本日のテーマを受講した理由は何ですか
$reason_list = [
  1 => 'テーマに関心があったから',
  2 => '本日のプログラム内容に関心があったから',
  3 => '本日のゲストに関心があったから',
  4 => '大阪大学のプログラムに参加したかったから',
  5 => '教養を高めたいから',
  6 => '仕事に役立つと思われたから',
  7 => '日常生活に役立つと思われたから',
  8 => '余暇を有効に利用したかったから'
];
define('REASON_LIST', $reason_list);
// 本日のテーマを受講した理由は何ですか
$satisfaction_list =  [
  1 => '非常に満足',
  2 => '満足',
  3 => 'ふつう',
  4 => '不満',
  5 => '非常に不満',
];
define('SATISFACTION_LIST', $satisfaction_list);
// 本日のプログラムの理解度について、あてはまるもの1つをお選びください
$understanding_list =  [
  1 => 'よく理解できた',
  2 => '理解できた',
  3 => 'ふつう',
  4 => '理解できなかった',
  5 => '全く理解できなかった'
];
define('UNDERSTANDING_LIST', $understanding_list);
// 本日のプログラムの理解度について、あてはまるもの1つをお選びください
$good_point_list =  [
  1 => 'テーマについて考えを深めることができた',
  2 => '最先端の研究について学べた',
  3 => '大学の研究者と対話ができた',
  4 => '大学の講義の雰囲気を味わえた',
  5 => '大阪大学について知ることができた',
  6 => '身の周りの社会課題に対する解決のヒントが得られた'
];
define('GOOD_POINT_LIST', $good_point_list);
// 本日のプログラムの開催時間(90分)についてあてはまるものを1つお選びください
$time_list = [
  1 => '適当である',
  2 => '長すぎる',
  3 => '短すぎる',
];
define('TIME_LIST', $time_list);
// 本日のプログラムの開催環境について、あてはまるものを１つお選びください。
$holding_environment_list = [
  1 => 'とても快適だった',
  2 => '快適だった',
  3 => 'ふつう',
  4 => 'あまり快適ではなかった',
  5 => '全く快適ではなかった',
];
define('HOLDING_ENVIRONMENT_LIST', $holding_environment_list);
// ご職業等を教えてください
$work_list = [
  1 => '高校生以下',
  2 => '学生（高校生、大学生、大学院生等）',
  3 => '会社員',
  4 => '自営業・フリーランス',
  5 => '公務員',
  6 => '教職員',
  7 => 'パート・アルバイト',
  8 => '主婦・主夫',
  9 => '定年退職',
  10 => 'その他'
];
define('WORK_LIST', $work_list);
// 性別をご回答ください
$sex_list = [
  1 => '男性',
  2 => '女性',
  3 => 'その他'
];
define('SEX_LIST', $sex_list);
$payment_credit = 2; // 決済方法クレジット
define('PAYMENT_CREDIT', $payment_credit);
// 対象区分
$kbn_id_list = [
  1 => 'イベント',
  2 => '適塾記念会',
  3 => '全体'
];
define('KBN_ID_LIST', $kbn_id_list);
$payment_kbn_list = [
  0 => '払込待ち',
  1 => '払い済み',
  2 => '未払い(期限切れ)'
];
define('PAYMENT_KBN_LIST', $payment_kbn_list);

// DM送信対象区分
$dm_send_kbn_event = 1; // イベント単位で送信
define('DM_SEND_KBN_EVENT', $dm_send_kbn_event);
$dm_send_kbn_tekijuku = 2; // 適塾単位で送信
define('DM_SEND_KBN_TEKIJUKU', $dm_send_kbn_tekijuku);
$dm_send_kbn_all = 3; // 全体単位で送信
define('DM_SEND_KBN_ALL', $dm_send_kbn_all);


// 講義形式
$local = 1; // 会場(対面)
define('LOCAL', $local);
$on_demand = 2; // オンデマンド配信
define('ON_DEMAND', $on_demand);
$live = 3; // ライブ配信
define('LIVE', $live);

// 費用請求へ遷移できる部門管理者アカウント
$membership_access_acount = 1579;
define('MEMBERSHIP_ACCESS_ACOUNT', $membership_access_acount);
													
// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!

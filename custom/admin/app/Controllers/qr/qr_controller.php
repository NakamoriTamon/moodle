<?php

/**
 * QRコードスキャン処理用コントローラー
 * 
 * このファイルはQRコードを使用した参加者登録処理を行うためのコントローラーです。
 * 管理者がイベントを選択し、参加者がQRコードを提示する際の処理を担当します。
 *
 * @category   Controller
 */

require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/MovieModel.php');
require_once('/var/www/html/moodle/custom/app/Models/UserModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationCourseInfoModel.php');
require_once('/var/www/html/moodle/config.php');
global $DB;

/**
 * QRコード処理のためのコントローラークラス
 * 
 * QRコードを使った参加登録機能の制御を担当します。
 * カテゴリー、イベント、回数の取得や、QRコードスキャン結果の処理を行います。
 *
 * @category Controller
 * @package  Custom_Admin
 */
class QrController
{
    /**
     * カテゴリーモデルのインスタンス
     * 
     * @var CategoryModel
     */
    private $categoryModel;

    /**
     * イベントモデルのインスタンス
     * 
     * @var EventModel
     */
    private $eventModel;

    /**
     * イベント申し込み情報モデルのインスタンス
     * 
     * @var EventApplicationCourseInfoModel
     */
    private $eventApplicationCourseInfoModel;

    /**
     * コントローラーのコンストラクタ
     * 
     * 必要なモデルのインスタンスを初期化します。
     */
    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        $this->eventModel = new EventModel();
        $this->eventApplicationCourseInfoModel = new EventApplicationCourseInfoModel();
    }

    /**
     * 初期表示のための情報を取得
     * 
     * カテゴリーリストとイベントリストを取得し、初期表示用のデータを返します。
     * POSTパラメータに基づいてフィルタリングを行います。
     *
     * @return array 表示に必要なデータの配列（カテゴリーリスト、イベントリスト）
     */
    public function index()
    {
        // optional_paramを使用して安全にパラメータを取得
        $category_id = optional_param('category_id', null, PARAM_INT);
        $event_id = optional_param('event_id', null, PARAM_INT);
        $course_no = optional_param('course_no', null, PARAM_INT);
        $exclude_event_status_id = EVENT_END;

        // 入力値をセッションに保存（すべて整数型として安全に保存）
        $_SESSION['old_input'] = [
            'category_id' => $category_id,
            'event_id' => $event_id,
            'course_no' => $course_no
        ];

        $filters = [];

        // nullでない値のみフィルターに追加
        if ($category_id !== null) {
            $filters['category_id'] = $category_id;
        }

        if ($event_id !== null) {
            $filters['event_id'] = $event_id;
        }

        if ($course_no !== null) {
            $filters['course_no'] = $course_no;
        }

        // 終了したイベントを除外
        $filters['exclude_event_status'] = $exclude_event_status_id;

        // イベント検索
        $event_list = $this->eventModel->getEvents($filters, 1, 100000);
        $category_list = $this->categoryModel->getCategories();

        $data = [
            'category_list' => $category_list,
            'event_list' => $event_list,
        ];

        return $data;
    }

    /**
     * カテゴリーIDからイベントリストを取得するAPI
     * 
     * 指定されたカテゴリーIDに基づいてイベントリストを取得し、JSON形式で返します。
     * Ajax呼び出し用のエンドポイントとして機能します。
     *
     * @return void JSONレスポンスを出力して終了
     */
    public function getEventsByCategory()
    {
        header('Content-Type: application/json; charset=utf-8');

        // POSTパラメータ取得 - カテゴリIDが空（すべて選択）の場合は処理を継続
        $category_id = optional_param('category_id', null, PARAM_INT);

        // 終了したイベントを除外
        $exclude_event_status_id = EVENT_END;

        // フィルター設定 - カテゴリIDが指定されている場合のみフィルターに追加
        $filters = [
            'exclude_event_status' => $exclude_event_status_id
        ];

        // カテゴリIDが存在する場合のみフィルターに追加
        if (!empty($category_id)) {
            $filters['category_id'] = $category_id;
        }

        // イベント検索
        $events = $this->eventModel->getEvents($filters, 1, 100000);

        echo json_encode([
            'status' => 'success',
            'events' => $events
        ]);
        exit;
    }

    /**
     * イベントIDから開催回数リストを取得するAPI
     * 
     * 指定されたイベントIDに基づいて開催回数リストを生成し、JSON形式で返します。
     * Ajax呼び出し用のエンドポイントとして機能します。
     *
     * @return void JSONレスポンスを出力して終了
     */
    public function getCourseNumbers()
    {
        header('Content-Type: application/json; charset=utf-8');

        // optional_paramを使用して安全にパラメータを取得
        $event_id = optional_param('event_id', 0, PARAM_INT);

        if (!$event_id) {
            echo json_encode([
                'status' => 'error',
                'message' => 'イベントIDが指定されていません'
            ]);
            exit;
        }

        // イベント詳細を取得
        $event = $this->eventModel->getEventById($event_id);
        if (!$event) {
            echo json_encode([
                'status' => 'error',
                'message' => '指定されたイベントが見つかりません'
            ]);
            exit;
        }

        // 開催回数を取得
        $course_numbers = [];
        $total_courses = $event['total_courses'] ?? 1; // イベントの総回数

        for ($i = 1; $i <= $total_courses; $i++) {
            $course_numbers[] = $i;
        }

        // イベント名を安全にエスケープしてJSONに含める
        $event_name = isset($event['name']) ? clean_param($event['name'], PARAM_TEXT) : '';

        echo json_encode([
            'status' => 'success',
            'course_numbers' => $course_numbers,
            'event_name' => $event_name
        ]);
        exit;
    }

    /**
     * QRコードデータを処理するAPI
     * 
     * QRコードのデータを受け取り、復号化して参加登録処理を行います。
     * 処理結果をJSON形式で返します。
     * Ajax呼び出し用のエンドポイントとして機能します。
     *
     * @return void JSONレスポンスを出力して終了
     */
    public function processQr()
    {
        header('Content-Type: application/json; charset=utf-8');

        // optional_paramとclean_paramを使用して安全にパラメータを取得
        $qr_data = clean_param(optional_param('qr_data', '', PARAM_TEXT), PARAM_TEXT);
        $event_id = optional_param('event_id', 0, PARAM_INT);
        $course_no = optional_param('course_no', 0, PARAM_INT);

        if (empty($qr_data) || empty($event_id) || empty($course_no)) {
            echo json_encode([
                'status' => 'error',
                'message' => '必要なパラメータが不足しています'
            ]);
            exit;
        }

        // QRコードからユーザーID(会員番号)を抽出
        $event_application_course_info_id = $this->extractUserIdFromQr($qr_data);

        if (!$event_application_course_info_id) {
            echo json_encode([
                'status' => 'error',
                'message' => 'QRコードデータが無効です'
            ]);
            exit;
        }

        // IDと他のパラメータ（event_id, course_no）を照合
        $verification_result = $this->verifyEventApplication($event_application_course_info_id, $event_id, $course_no);

        if ($verification_result['status'] === 'error') {
            echo json_encode([
                'status' => 'error',
                'message' => $verification_result['message']
            ]);
            exit;
        }

        // 参加登録処理
        $result = $this->registerAttendance($event_application_course_info_id);

        if (!$result) {
            echo json_encode([
                'status' => 'error',
                'message' => '参加登録に失敗しました'
            ]);
            exit;
        }

        // 成功レスポンス
        echo json_encode([
            'status' => 'success',
            'message' => '参加登録が完了しました',
        ]);
        exit;
    }

    /**
     * QRコードからIDを抽出する
     * 
     * 暗号化されたQRコードデータを復号化し、イベント申し込み情報のIDを取得します。
     *
     * @param string $qr_data QRコードから読み取られた暗号化データ
     * 
     * @return int|null 復号化されたID、失敗した場合はnull
     */
    private function extractUserIdFromQr($qr_data)
    {
        global $url_secret_key;

        // まず暗号化されたデータを復号化
        try {
            $decoded_id = $this->decrypt($qr_data, $url_secret_key);
            // 復号化結果が数値であることを厳密に確認
            if ($decoded_id !== false && is_numeric($decoded_id)) {
                // 整数型として安全に変換
                $id = clean_param($decoded_id, PARAM_INT);
                if ($id > 0) { // 正の整数であることを確認
                    return $id;
                }
            }
        } catch (\Exception $e) {
            error_log('QRコード復号化エラー: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * 暗号化されたデータを復号する
     * 
     * AES-256-CBCアルゴリズムを使用して暗号化されたデータを復号します。
     *
     * @param string $value 復号する暗号化データ
     * @param string $key   復号に使用する秘密鍵
     * 
     * @return string|false 復号されたデータ、失敗時はfalse
     */
    private function decrypt($value, $key)
    {
        $iv = substr(hash('sha256', $key), 0, 16);
        return openssl_decrypt(base64_decode(urldecode($value)), 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * 参加登録処理
     * 
     * 指定されたIDの参加状態を更新します。
     * トランザクション処理を使用して、データの整合性を保ちます。
     *
     * @param int $id イベント申し込み情報のID
     * 
     * @return bool 処理成功時はtrue、失敗時はfalse
     */
    private function registerAttendance($id)
    {
        global $DB;

        try {
            // IDが有効な整数であることを確認
            $id = clean_param($id, PARAM_INT);
            if ($id <= 0) {
                throw new \Exception('無効なIDが指定されました');
            }

            // トランザクション開始
            $transaction = $DB->start_delegated_transaction();

            // 更新用のレコード準備
            $record = new \stdClass();
            $record->id = $id;
            $record->participation_kbn = PARTICIPATION_KBN['PARTICIPATION'];

            // レコード更新
            $result = $DB->update_record_raw('event_application_course_info', $record);

            if (!$result) {
                throw new \Exception('参加登録の更新に失敗しました');
            }

            // トランザクション確定
            $transaction->allow_commit();

            return true;
        } catch (\Exception $e) {
            // トランザクションロールバック
            if (isset($transaction)) {
                $transaction->rollback($e);
            }
            error_log('参加登録エラー: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * イベント申し込み情報の照合
     * 
     * QRコードから取得したIDと、選択されたイベントID、回数が一致するか確認します。
     * また、既に参加登録済みかどうかもチェックします。
     *
     * @param int $id       イベント申し込み情報のID
     * @param int $event_id イベントID
     * @param int $course_no イベント回数
     * 
     * @return array 照合結果を含む連想配列
     *               ['status' => 'success'|'error', 'message' => エラーメッセージ, 'app_info' => 申し込み情報]
     */
    private function verifyEventApplication($id, $event_id, $course_no)
    {
        // すべてのパラメータが整数であることを確認
        $id = clean_param($id, PARAM_INT);
        $event_id = clean_param($event_id, PARAM_INT);
        $course_no = clean_param($course_no, PARAM_INT);

        if ($id <= 0 || $event_id <= 0 || $course_no <= 0) {
            return [
                'status' => 'error',
                'message' => '無効なパラメータが指定されました'
            ];
        }

        // 申し込み情報を取得
        $app_info = $this->eventApplicationCourseInfoModel->getByEventApplicationCouresInfoId($id);

        // 情報が取得できなかった場合
        if (empty($app_info)) {
            return [
                'status' => 'error',
                'message' => '処理でエラーが発生しております'
            ];
        }

        // 既に参加済みの場合
        if (isset($app_info['participation_kbn']) && $app_info['participation_kbn'] == PARTICIPATION_KBN['PARTICIPATION']) {
            return [
                'status' => 'error',
                'message' => '既に参加登録されています'
            ];
        }

        // 既に開催終了している場合
        if (isset($app_info['course_date']) && isset($app_info['end_hour'])) {
            // イベント終了時刻を作成（日付と終了時間を結合）
            $course_date = date('Y-m-d', strtotime($app_info['course_date']));
            $end_time = $app_info['end_hour'];
            $event_end_datetime = $course_date . ' ' . $end_time;

            // 現在時刻と比較
            $current_datetime = date('Y-m-d H:i:s');

            if (strtotime($current_datetime) > strtotime($event_end_datetime)) {
                return [
                    'status' => 'error',
                    'message' => '既に開催終了しています'
                ];
            }
        }

        // イベントIDと回数の照合
        if ($app_info['event_id'] == $event_id && $app_info['no'] == $course_no) {
            // 照合成功
            return [
                'status' => 'success',
                'app_info' => $app_info
            ];
        }

        // 照合失敗
        return [
            'status' => 'error',
            'message' => '開催イベントが異なります'
        ];
    }
}

$course_numbers = [];
// POSTリクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_kbn = optional_param('post_kbn', '', PARAM_ALPHANUMEXT);
    $controller = new QrController();

    switch ($post_kbn) {
        case 'get_events_by_category':
            $controller->getEventsByCategory();
            break;

        case 'get_course_numbers':
            $controller->getCourseNumbers();
            break;

        case 'process_qr':
            $controller->processQr();
            break;

        default:
            // post_kbnが指定されていない場合は通常のindex処理と判断
            // 何もしない（通常のPHP処理フローに任せる）
            break;
    }
}

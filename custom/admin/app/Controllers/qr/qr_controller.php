<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/MovieModel.php');
require_once('/var/www/html/moodle/custom/app/Models/UserModel.php');
global $DB;

class QrController
{
    private $categoryModel;
    private $eventModel;
    private $movieModel;
    private $userModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        $this->eventModel = new EventModel();
        $this->movieModel = new MovieModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // 検索項目取得
        $category_id = $_POST['category_id'] ?? null;
        $event_id = $_POST['event_id'] ?? null;
        $course_no = $_POST['course_no'] ?? null;
        $exclude_event_status_id = EVENT_END;
        $_SESSION['old_input'] = $_POST;

        $filters = array_filter([
            'category_id' => $category_id,
            'exclude_event_status' => $exclude_event_status_id,
            'event_id' => $event_id,
            'course_no' => $course_no
        ]);

        // null の要素を削除しイベント検索
        $filters = array_filter($filters);
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
     */
    public function getEventsByCategory()
    {
        header('Content-Type: application/json; charset=utf-8');

        // POSTパラメータ取得
        $category_id = $_POST['category_id'] ?? null;

        if (!$category_id) {
            echo json_encode([
                'status' => 'error',
                'message' => 'カテゴリーIDが指定されていません'
            ]);
            exit;
        }

        // 終了したイベントを除外
        $exclude_event_status_id = EVENT_END;

        // フィルター設定
        $filters = [
            'category_id' => $category_id,
            'exclude_event_status' => $exclude_event_status_id
        ];

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
     */
    public function getCourseNumbers()
    {
        header('Content-Type: application/json; charset=utf-8');

        // POSTパラメータ取得
        $event_id = $_POST['event_id'] ?? null;

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

        // 開催回数を取得（回数はモデルから取得する必要があるため、仮実装）
        $course_numbers = [];
        $total_courses = $event['total_courses'] ?? 1; // イベントの総回数

        for ($i = 1; $i <= $total_courses; $i++) {
            $course_numbers[] = $i;
        }

        echo json_encode([
            'status' => 'success',
            'course_numbers' => $course_numbers,
            'event_name' => $event['name']
        ]);
        exit;
    }

    /**
     * QRコードデータを処理するAPI
     */
    public function processQr()
    {
        header('Content-Type: application/json; charset=utf-8');

        // POSTパラメータ取得
        $qr_data = $_POST['qr_data'] ?? null;
        $event_id = $_POST['event_id'] ?? null;
        $course_no = $_POST['course_no'] ?? null;

        if (!$qr_data || !$event_id || !$course_no) {
            echo json_encode([
                'status' => 'error',
                'message' => '必要なパラメータが不足しています'
            ]);
            exit;
        }

        // QRコードからユーザーIDを抽出
        $user_id = $this->extractUserIdFromQr($qr_data);

        if (!$user_id) {
            echo json_encode([
                'status' => 'error',
                'message' => 'QRコードデータが無効です'
            ]);
            exit;
        }

        // ユーザー情報取得
        $user = $this->userModel->getUserById($user_id);

        if (!$user) {
            echo json_encode([
                'status' => 'error',
                'message' => 'ユーザーが見つかりません'
            ]);
            exit;
        }

        // イベント情報取得
        $event = $this->eventModel->getEventById($event_id);

        if (!$event) {
            echo json_encode([
                'status' => 'error',
                'message' => 'イベントが見つかりません'
            ]);
            exit;
        }

        // 参加登録処理
        $result = $this->registerAttendance($user_id, $event_id, $course_no);

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
            'user_name' => $user['name'], // ユーザー名
            'event_name' => $event['name'] // イベント名
        ]);
        exit;
    }

    /**
     * QRコードからユーザーIDを抽出する
     */
    private function extractUserIdFromQr($qr_data)
    {
        // QRコードの形式に合わせて処理を実装
        // 例: JSON形式の場合
        $decoded = json_decode($qr_data, true);
        if (isset($decoded['user_id'])) {
            return $decoded['user_id'];
        }

        // 例: URLパラメータ形式の場合
        parse_str(parse_url($qr_data, PHP_URL_QUERY), $params);
        if (isset($params['user_id'])) {
            return $params['user_id'];
        }

        // 例: 単純なIDのみの場合
        if (preg_match('/^[0-9]+$/', $qr_data)) {
            return $qr_data;
        }

        return null;
    }

    /**
     * 参加登録処理
     */
    private function registerAttendance($user_id, $event_id, $course_no)
    {
        try {
            global $DB;
            $record = new \stdClass();
            $record->user_id = $user_id;
            $record->event_id = $event_id;
            $record->course_no = $course_no;
            $record->created_at = time();

            // テーブル名は実際の環境に合わせて調整
            $DB->insert_record('custom_event_attendance', $record);

            return true;
        } catch (\Exception $e) {
            error_log('参加登録エラー: ' . $e->getMessage());
            return false;
        }
    }
}

// POSTリクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_kbn = $_POST['post_kbn'] ?? '';
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

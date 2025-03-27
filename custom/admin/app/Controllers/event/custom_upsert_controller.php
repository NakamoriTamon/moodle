<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventCustomFieldModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventCustomFieldCategoryModel.php');

$EventCustomFieldModel = new EventCustomFieldModel();
$EventCustomFieldCategoryModel = new EventCustomFieldCategoryModel();

$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? null;
$sorts = $_POST['sort'] ?? null;
$selections = $_POST['selection'] ?? null;
$item_names = $_POST['item_name'] ?? null;
$field_types = $_POST['field_type'] ?? null;
$event_customfield_ids = $_POST['event_customfield_id'] ?? [];

global $DB, $CFG;
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message_error'] = '登録に失敗しました';
            if ($id) {
                header('Location: /custom/admin/app/Views/event/custom_upsert.php?id=' . $id);
            } else {
                header('Location: /custom/admin/app/Views/event/custom_upsert.php');
            }
            exit;
        }
    }

    // カテゴリ名重複チェック
    if ($id) {
        $customfield_category_list = $EventCustomFieldCategoryModel->getCustomFieldCategoryNotId($id);
    } else {
        $customfield_category_list = $DB->get_records(
            'event_customfield_category',
            ['is_delete' => false]
        );
    }

    $category_names = array_column($customfield_category_list, 'name');
    if (in_array($name, $category_names, true)) {
        $_SESSION['message_error'] = '登録に失敗しました';
        $_SESSION['errors']['name'] = 'すでに登録されています';
        header('Location: /custom/admin/app/Views/event/custom_upsert.php?id=' . $id);
        exit;
    }

    $transaction = $DB->start_delegated_transaction();
    $customfield_category = new stdClass();
    if (!$id) {
        $customfield_category->name = $name;
        $customfield_category->created_at = date('Y-m-d H:i:s');
        $customfield_category->updated_at = date('Y-m-d H:i:s');
        $id = $DB->insert_record('event_customfield_category', $customfield_category);
    } else {
        $customfield_category->id = $id;
        $customfield_category->name = $name;
        $customfield_category->updated_at = date('Y-m-d H:i:s');
        $DB->update_record('event_customfield_category', $customfield_category);
    }

    $customfield_list = $DB->get_records(
        'event_customfield',
        ['event_customfield_category_id' => $id, 'is_delete' => false]
    );

    // 削除対象のIDを取得
    $missing_ids = [];
    foreach ($customfield_list as $customfields) {
        if (!in_array($customfields->id, $event_customfield_ids)) {
            $missing_ids[] = $customfields->id;
        }
    }

    // 各フィールドごとに登録
    foreach ($item_names as $index => $item_name) {
        $sort = $sorts[$index] ?? null;
        $selection = $selections[$index] ?? null;
        $field_type = $field_types[$index] ?? null;
        $event_customfield_id = $event_customfield_ids[$index] ?? null;

        $customfield = new stdClass();
        if (!$event_customfield_id) {
            $customfield->created_at = date('Y-m-d H:i:s');
            $customfield->updated_at = date('Y-m-d H:i:s');
            $customfield->field_name = '';
            $customfield->name = $item_name;
            $customfield->sort = (int)$sort;
            $customfield->field_type = $field_type;
            $customfield->selection = $selection;
            $customfield->event_customfield_category_id = $id;
            $test = $DB->insert_record('event_customfield', $customfield);
        } else {
            $customfield->id = $event_customfield_id;
            $customfield->updated_at = date('Y-m-d H:i:s');
            $customfield->name = $item_name;
            $customfield->sort = (int)$sort;
            $DB->update_record('event_customfield', $customfield);
        }
    }

    // 削除カスタムフィールド
    if (!empty($missing_ids)) {
        foreach ($missing_ids as $id) {
            $fields = ['is_delete' => true, 'updated_at' => date('Y-m-d H:i:s')];
            foreach ($fields as $column => $value) $DB->set_field('event_customfield', $column, $value, ['id' => $id]);
        }
    }

    $transaction->allow_commit();
    $_SESSION['message_success'] = '登録が完了しました';
    header('Location: /custom/admin/app/Views/event/custom_index.php');
    exit;
} catch (Exception $e) {
    // ロールバック中に例外が再スローする事を防ぐ
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        $_SESSION['old_input'] = $_POST;
        $_SESSION['message_error'] = '登録に失敗しました';
        redirect('/custom/admin/app/Views/event/custom_upsert.php?id=' . $id);
        exit;
    }
}

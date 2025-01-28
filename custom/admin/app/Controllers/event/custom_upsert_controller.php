<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');

$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? null;
$item_names = $_POST['item_name'] ?? null;
$field_names = $_POST['field_name'] ?? null;
$sorts = $_POST['sort'] ?? null;
$field_types = $_POST['field_type'] ?? null;
$selections = $_POST['selection'] ?? null;

$name_error = validate_custom_field_category_name($name);
if ($name_error) {
    // エラーメッセージをセッションに保存
    $_SESSION['errors'] = ['name' => $name_error];
    $_SESSION['old_input'] = $_POST;
    header('Location: /custom/admin/app/Views/event/custom_upsert.php');
    exit;
} else {
    global $DB, $CFG;
    try {
        $transaction = $DB->start_delegated_transaction();
        $customfield_category = new stdClass();
        $customfield_category->name = $name;
        $customfield_category->created_at = date('Y-m-d H:i:s');
        $customfield_category->updated_at = date('Y-m-d H:i:s');
        $customfield_category_id = $DB->insert_record('event_customfield_category', $customfield_category);

        // 各フィールドごとに登録
        foreach ($field_names as $index => $field_name) {
            $item_name = $item_names[$index] ?? null;
            $sort = $sorts[$index] ?? null;
            $field_type = $field_types[$index] ?? null;
            $selection = $selections[$index] ?? null;

            $customfield = new stdClass();
            $customfield->created_at = date('Y-m-d H:i:s');
            $customfield->updated_at = date('Y-m-d H:i:s');
            $customfield->name = $item_name;
            $customfield->field_name = $field_name;
            $customfield->sort = (int)$sort;
            $customfield->field_type = $field_type;
            $customfield->selection = $selection;
            $customfield->event_customfield_category_id = $customfield_category_id;
            $DB->insert_record('event_customfield', $customfield);
        }
        $transaction->allow_commit();
        $_SESSION['message_success'] = '登録が完了しました';
        header('Location: /custom/admin/app/Views/event/custom_upsert.php');
        exit;
    } catch (PDOException $e) {
        // var_dump($e);
        $transaction->rollback($e);
        $_SESSION['message_error'] = '登録に失敗しました';
        header('Location: /custom/admin/app/Views/event/custom_upsert.php');
        exit;
    }
}

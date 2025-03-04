<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/material/material_controller.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');

global $DB;

if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['message_error'] = '登録に失敗しました。333';
    echo json_encode(['status' => 'error', 'error' => 'CSRFトークンが無効です']);
    exit;
}

$destination_dir = $CFG->dirroot . '/uploads/material';
$ids        = $_POST['ids'] ?? [];
$files      = $_FILES['files'] ?? array();
$file_ids   = $_POST['files'] ?? null;
$event_id   = $_POST['event_id'] ?? '';
$course_no  = $_POST['course_no'] ?? '';

$created_at = date('Y-m-d H:i:s');
$updated_at = date('Y-m-d H:i:s');
$_SESSION['old_input'] = $_POST;

if (!file_exists($destination_dir)) {
    mkdir($destination_dir, 0755, true);
}

$course_info = $DB->get_record_sql(
    "SELECT * FROM {event_course_info} WHERE event_id = ? AND course_info_id = ?",
    [$_POST['event_id'], $_POST['course_no']]
);

$material_list = $DB->get_records('course_material', array('course_info_id' => $course_info->id));
$validate_material_file_error = validate_material_file($files);
if ($validate_material_file_error) {
    $_SESSION['errors']['files'] = $validate_material_file_error;
    echo json_encode(['status' => 'error', 'errors' => ['files' => $validate_material_file_error]]);
    exit;
}


try {
    $transaction = $DB->start_delegated_transaction();
    foreach ($files['name'] as $index => $file) {
        if ($file == "") {
            continue;
        }
        if (!file_exists($destination_dir)) {
            mkdir($destination_dir, 0755, true);
        }
        $err_code = $files['error'][$index] ?? UPLOAD_ERR_NO_FILE;
        $tmp_name = $files['tmp_name'][$index] ?? '';
        $original_file_name = $file;
        $existingFileNames = array_column($material_list, 'file_name');

        if (in_array($original_file_name, $existingFileNames)) {
            $errors[$index][] = '既に' . $original_file_name . 'は登録されています';
            continue;
        }

        $same_material = $DB->get_record('course_material', array('file_name' => $file));
        if ($same_material) {
            $same_course_info = $DB->get_record_sql(
                "SELECT * FROM {event_course_info} WHERE id = ?",
                [$same_material->course_info_id]
            );
            $event_data = $DB->get_record_sql(
                "SELECT * FROM {event} WHERE id = ?",
                [$same_course_info->event_id]
            );
            if ($same_course_info->event_id <> $course_info->event_id) {
                $errors[$index][] = '既に' . $event_data->name . 'の第' . $same_course_info->course_info_id . '回目で登録されています';
                continue;
            }
            $existing_file_path = $CFG->dirroot . '/uploads/material/' . $same_material->file_name;
            if (file_exists($existing_file_path)) {
                unlink($existing_file_path);
                $DB->delete_records('course_material', array('file_name' => $same_material->file_name));
            }
        }

        $destination = $destination_dir . '/' . $original_file_name;

        if (!move_uploaded_file($tmp_name, $destination)) {
            $errors[$index][] = '登録に失敗しました';
            echo json_encode(['status' => 'error', 'error' => '登録に失敗しました']);
            exit;
        }
        $data = new stdClass();
        $data->file_name = $file;
        $data->course_info_id = $course_info->id;
        $data->created_at = $created_at;
        $data->updated_at = $updated_at;
        $DB->insert_record('course_material', $data);
    }
    if ($errors) {
        echo json_encode(['status' => 'error', 'errors' => ['files' => $errors]]);
        exit;
    }
    $ids = !empty($_POST['ids']) ? array_map('intval', $_POST['ids']) : [];
    $file_post = $_POST['files'] ?? [];
    foreach ($file_post as $index => $value) {
        if ($value === "delete") {
            $record_id = isset($_POST['ids'][$index]) ? (int) $_POST['ids'][$index] : 0;
            if ($record_id > 0 && isset($material_list[$record_id])) {
                $record = $material_list[$record_id];
                $file_path = $CFG->dirroot . '/uploads/material/' . $record->file_name;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                $DB->delete_records('course_material', array('id' => $record_id));
            }
        }
    }

    $file_ids = !empty($_POST['files']) ? array_map('intval', array_keys($_POST['files'])) : [];
    $max_id = !empty($file_ids) ? max($file_ids) : 0;

    foreach ($file_ids as $course_index => $record_id) {
        if (!empty($files['name'][$record_id])) {
            $posted_file = isset($files['name'][$record_id]) ? $files['name'][$record_id] : '';
            if (isset($material_list[$record_id])) {
                $record = $material_list[$record_id];
                if ($record->file_name !== $posted_file) {
                    $file_path = $CFG->dirroot . '/uploads/material/' . $record->file_name;
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                    $DB->delete_records('course_material', array('id' => $record_id));
                }
            }
        }
    }
    foreach ($material_list as $record_id => $record) {
        if ($record->id != 0 && !in_array($record->id, $ids)) {
            $file_path = $CFG->dirroot . '/uploads/material/' . $record->file_name;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            $DB->delete_records('course_material', array('id' => $record->id));
        }
    }
    $transaction->allow_commit();
    $_SESSION['message_success'] = '登録が完了しました';
    echo json_encode(['status' => 'success']);
    exit;
} catch (Exception $e) {
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        $_SESSION['message_error'] = '登録に失敗しました';
        $unlink_file = $destination_dir . '/' . $new_filename;
        if (isset($unlink_file) && file_exists($unlink_file)) {
            unlink($unlink_file);
        }
        echo json_encode(['status' => 'error', 'message' => $unlink_file]);
        exit;
    }
}

<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/material/material_controller.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');

global $DB;

if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['message_error'] = '登録に失敗しました。';
}

$upload_dir = $CFG->dirroot . '/uploads/material';
$ids        = $_POST['ids'] ?? [];
$event_id   = $_POST['event_id'] ?? '';
$course_no  = $_POST['course_no'] ?? '';
$created_at = date('Y-m-d H:i:s');
$updated_at = date('Y-m-d H:i:s');
$_SESSION['old_input'] = $_POST;
$errors = null;

if (isset($_FILES['file'])) {
    $file_name    = $_POST['file_name'];
    $chunk_index  = $_POST['chunk_index'];
    $total_chunks = $_POST['total_chunks'];
    $tmpFilePath  = $_FILES['file']['tmp_name'];
    $targetPath   = $upload_dir . '/' . $file_name . '.part' . $chunk_index;

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $storage_upload_dir = '/var/www/html/moodle/uploads/material/';
    $total_file_size    = $_POST['total_file_size'] ?? 0;
    if (!check_storage_limit($storage_upload_dir, $total_file_size)) {
        $_SESSION['message_error'] = 'ストレージ容量が不足しています';
        echo json_encode(['status' => 'error']);
        exit;
    }

    move_uploaded_file($tmpFilePath, $targetPath);

    if ($chunk_index == $total_chunks - 1) {
        $finalFilePath = $upload_dir . '/' . $file_name;
        $outputFile    = fopen($finalFilePath, 'wb');

        for ($i = 0; $i < $total_chunks; $i++) {
            $chunk_file = $upload_dir . '/' . $file_name . '.part' . $i;
            fwrite($outputFile, file_get_contents($chunk_file));
            unlink($chunk_file);
        }
        fclose($outputFile);

        $files = [
            'name'     => [$file_name],
            'type'     => [mime_content_type($finalFilePath)],
            'tmp_name' => [$finalFilePath],
            'error'    => [0],
            'size'     => [filesize($finalFilePath)]
        ];

        $course_info = $DB->get_record_sql(
            "SELECT * FROM {event_course_info} WHERE event_id = ? AND course_info_id = ?",
            [$_POST['event_id'], $_POST['course_info_id']]
        );

        $material_list = $DB->get_records('course_material', array('course_info_id' => $course_info->course_info_id));

        $validate_material_file_error = validate_material_file($files);
        if ($validate_material_file_error) {
            $_SESSION['errors']['files'] = $validate_material_file_error;
            echo json_encode(['status' => 'error', 'errors' => ['files' => $validate_material_file_error]]);
            exit;
        }

        try {
            $registered_ids = [];
            $transaction = $DB->start_delegated_transaction();
            foreach ($files['name'] as $index => $file) {
                if ($file == "") {
                    continue;
                }

                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
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
                        "SELECT * FROM {event_course_info} WHERE course_info_id = ?",
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

                $destination = $upload_dir . '/' . $original_file_name;
                if ($tmp_name !== $destination) {
                    if (!move_uploaded_file($tmp_name, $destination)) {
                        $errors[$index][] = '登録に失敗しました';
                        echo json_encode(['status' => 'error', 'error' => '登録に失敗しました']);
                        exit;
                    }
                }

                $data = new stdClass();
                $data->file_name      = $file;
                $data->course_info_id = $course_info->course_info_id;
                $data->created_at     = $created_at;
                $data->updated_at     = $updated_at;
                $registered_ids[] = $DB->insert_record('course_material', $data);
            }

            if ($errors) {
                echo json_encode(['status' => 'error', 'errors' => ['files' => $errors]]);
                exit;
            }
            $transaction->allow_commit();
            $_SESSION['message_success'] = '登録が完了しました';
            echo json_encode(['status' => 'success']);

            $posted_ids = $_POST['ids'] ?? [];
            $filtered_posted_ids = array_filter($posted_ids, function ($v) {
                return $v !== "0" && $v !== 0;
            });

            if (!isset($_SESSION['registered_material_ids'])) {
                $_SESSION['registered_material_ids'] = [];
            }
            $_SESSION['registered_material_ids'] = array_merge($_SESSION['registered_material_ids'], $filtered_posted_ids, $registered_ids);
        } catch (Exception $e) {
            try {
                $transaction->rollback($e);
            } catch (Exception $rollbackException) {
                $_SESSION['message_error'] = '登録に失敗しました';
                echo json_encode(['status' => 'error']);
                exit;
            }
        }
    }
}

$material_list = $DB->get_records('course_material', array('course_info_id' => $_POST['course_info_id']));
$ids_post = $_SESSION['registered_material_ids'] ?? $_POST['files'] ?? [];
$file_keys = array_keys($material_list);

foreach ($material_list as $key => $material_id) {
    if (!in_array($key, $ids_post)) {
        $target_id = (int)$key;
        if ($target_id > 0) {
            $record = $DB->get_record('course_material', array('id' => $target_id));
            if ($record) {
                $file_path = $CFG->dirroot . '/uploads/material/' . $record->file_name;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                $DB->delete_records('course_material', array('id' => $target_id));
            }
        }
    }
}

$_SESSION['message_success'] = '登録が完了しました';
echo json_encode(['status' => 'success']);
exit;

function check_storage_limit($upload_dir, $file_size, $max_usage_ratio = 0.9)
{
    $total_space = disk_total_space($upload_dir);
    $free_space  = disk_free_space($upload_dir);
    $used_space  = $total_space - $free_space;
    $usage_ratio = $used_space / $total_space;
    $new_usage_ratio = ($used_space + $file_size) / $total_space;

    error_log("Total Space: {$total_space} bytes");
    error_log("Used Space: {$used_space} bytes");
    error_log("Free Space: {$free_space} bytes");
    error_log("Current Usage: " . ($usage_ratio * 100) . "%");
    error_log("New Usage After Upload: " . ($new_usage_ratio * 100) . "%");

    if ($new_usage_ratio >= $max_usage_ratio) {
        return false;
    }
    return true;
}

<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/custom/helpers/form_helpers.php');
require_once($CFG->dirroot . '/custom/admin/app/Controllers/material/material_controller.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');

global $DB;

if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['message_error'] = '登録に失敗しました。';
    echo json_encode(['status' => 'error', 'error' => 'CSRF token error']);
    exit;
}

$post_files = array_filter((array)$_POST['files'], function ($value) {
    return $value !== "";
});

$post_ids = isset($_SESSION['registered_material_ids'])
    ? array_map('intval', $_SESSION['registered_material_ids'])
    : (isset($_POST['ids']) ? array_map('intval', (array)$_POST['ids']) : []);

$material_list = $DB->get_records('course_material', array('course_info_id' => $_POST['course_info_id']));

// チャンクアップロードなどのファイル処理はここで行い、最終チャンク時のみDB更新処理を実施
if (isset($_FILES['file'])) {
    $file_name    = basename($_POST['file_name']);
    $chunk_index  = intval($_POST['chunk_index']);
    $total_chunks = intval($_POST['total_chunks']);
    $tmpFilePath  = $_FILES['file']['tmp_name'];

    $upload_dir = $CFG->dirroot . '/uploads/material/' . $_POST['course_info_id'] . '/' . $_POST['course_no'] . '/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $targetPath = $upload_dir . '/' . $file_name . '.part' . $chunk_index;

    // ストレージ容量チェック
    $storage_upload_dir = '/var/www/html/moodle/uploads/material/' . $_POST['course_info_id'] . '/' . $_POST['course_no'] . '/';
    $total_file_size    = isset($_POST['total_file_size']) ? intval($_POST['total_file_size']) : 0;
    if (!check_storage_limit($storage_upload_dir, $total_file_size)) {
        echo json_encode(['status' => 'error', 'error' => '登録に失敗しました（容量制限）']);
        exit;
    }

    // 現在のチャンクを保存
    if (!move_uploaded_file($tmpFilePath, $targetPath)) {
        echo json_encode(['status' => 'error', 'error' => '登録に失敗しました（move_uploaded_fileエラー）']);
        exit;
    }

    // 中間チャンクの場合は、ここで終了（DB更新は行わない）
    if ($chunk_index < $total_chunks - 1) {
        echo json_encode(['status' => 'chunk received']);
        exit;
    }

    // 最終チャンク：全チャンクを結合して最終ファイルを作成
    $finalFilePath = $upload_dir . '/' . $file_name;
    $outputFile    = fopen($finalFilePath, 'wb');
    for ($i = 0; $i < $total_chunks; $i++) {
        $chunk_file = $upload_dir . '/' . $file_name . '.part' . $i;
        if (!file_exists($chunk_file)) {
            echo json_encode(['status' => 'error', 'error' => '登録に失敗しました（チャンクファイル欠如）']);
            exit;
        }
        fwrite($outputFile, file_get_contents($chunk_file));
        unlink($chunk_file);
    }
    fclose($outputFile);

    // ここまででファイル結合が完了したので、DBの更新処理へ移る
    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');
    $_SESSION['old_input'] = $_POST;
    $errors = [];

    try {
        $transaction = $DB->start_delegated_transaction();


        // ファイル情報を作ってバリデーションする場合など
        $files = [
            'name'     => [$file_name],
            'type'     => [mime_content_type($finalFilePath)],
            'tmp_name' => [$finalFilePath],
            'error'    => [0],
            'size'     => [filesize($finalFilePath)]
        ];

        // コース情報を取得しておく
        $course_info = $DB->get_record_sql(
            "SELECT * FROM {event_course_info} WHERE event_id = ? AND course_info_id = ?",
            [$_POST['event_id'], $_POST['course_info_id']]
        );


        if (empty($_SESSION['material_deletion_done'])) {
            foreach ($material_list as $key => $material_record) {
                if (!in_array((int)$key, $post_ids)) {
                    $target_id = (int)$key;
                    if ($target_id > 0) {
                        $record = $DB->get_record('course_material', array('id' => $target_id));
                        if ($record) {
                            $file_path = $CFG->dirroot . '/uploads/material/' . $_POST['course_info_id'] . '/' . $_POST['course_no'] . '/' . $record->file_name;
                            // ファイルがあれば削除（なくても削除処理は実施）
                            if (file_exists($file_path)) {
                                unlink($file_path);
                            }
                            $DB->delete_records('course_material', array('id' => $target_id));
                        }
                    }
                } elseif (isset($post_files[$key])) {
                    // 既存レコードとファイル名が違う => 古い方を削除/置き換え
                    $target_id = (int)$key;
                    if ($target_id > 0) {
                        $update_record = $DB->get_record('course_material', array('id' => $target_id));
                        if ($update_record) {
                            $old_file_path = $CFG->dirroot . '/uploads/material/' . $_POST['course_info_id'] . '/' . $_POST['course_no'] . '/' . $update_record->file_name;
                            // ファイルが存在する場合は削除、存在しなければDBレコードも削除
                            if (file_exists($old_file_path)) {
                                unlink($old_file_path);
                                $DB->delete_records('course_material', array('id' => $target_id));
                            }
                        }
                    }
                }
            }
            $_SESSION['material_deletion_done'] = true;
        }

        $currentId = intval($_POST['id'] ?? 0);
        $needInsert = true;

        if ($currentId > 0) {
            $currentRec = $DB->get_record('course_material', ['id' => $currentId]);
            if ($currentRec && $currentRec->file_name === $file_name) {
                // 上書き
                $currentRec->updated_at = $updated_at;
                $DB->update_record('course_material', $currentRec);
                $needInsert = false;
            }
        }

        if ($needInsert) {
            $data = new stdClass();
            $data->file_name      = $file_name;
            $data->course_info_id = $course_info->course_info_id;
            $data->created_at     = $created_at;
            $data->updated_at     = $updated_at;
            $registered_ids[]     = $DB->insert_record('course_material', $data);
        }

        // セッションに登録済みIDをマージ
        $posted_ids = $_POST['ids'] ?? [];
        $filtered_posted_ids = array_filter($posted_ids, function ($v) {
            return $v !== "0" && $v !== 0;
        });
        if (!isset($_SESSION['registered_material_ids'])) {
            $_SESSION['registered_material_ids'] = [];
        }
        $_SESSION['registered_material_ids'] = array_merge($_SESSION['registered_material_ids'], $filtered_posted_ids, $registered_ids);
    } catch (Exception $e) {
        // エラー発生時は削除処理フラグをクリアしておく
        unset($_SESSION['material_deletion_done']);
        try {
            $transaction->rollback($e);
        } catch (Exception $rollbackException) {
            error_log($rollbackException->getMessage());
            // ロールバック自体に失敗した場合
            echo json_encode([
                'status' => 'error',
                'error'  => $rollbackException->getMessage()
            ]);
            exit;
        }
        // ここでエラーの実際のメッセージを返す
        error_log($e->getMessage());
        echo json_encode(['status' => 'error']);
        $_SESSION['message_error'] = $e->getMessage();
        exit;
    }
    // コミット
    $transaction->allow_commit();
    $_SESSION['message_success'] = '登録が完了しました';
    echo json_encode(['status' => 'success']);
    exit;
} else {
    // ファイルアップロードがない場合も削除処理だけを行う
    $transaction = $DB->start_delegated_transaction();

    foreach ($material_list as $key => $material_record) {
        if (!in_array((int)$key, $post_ids)) {
            $target_id = (int)$key;
            if ($target_id > 0) {
                $record = $DB->get_record('course_material', array('id' => $target_id));
                if ($record) {
                    $file_path = $CFG->dirroot . '/uploads/material/' . $_POST['course_info_id'] . '/' . $_POST['course_no'] . '/' . $record->file_name;
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                    $DB->delete_records('course_material', array('id' => $target_id));
                }
            }
        }
    }
    $transaction->allow_commit();

    $_SESSION['message_success'] = '登録が完了しました';
    echo json_encode(['status' => 'success']);
    exit;
}

/**
 * ストレージの使用率をチェックする関数（例）
 */
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

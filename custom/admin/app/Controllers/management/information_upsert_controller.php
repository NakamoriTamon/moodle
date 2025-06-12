<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');

global $DB;

$id = $_POST['id'] ?? null;
$title = $_POST['title'] ?? null;
$body = $_POST['body'] ?? null;
$publish_start_at = $_POST['publish_start_at'] ?? null;
$publish_end_at  = $_POST['publish_end_at'] ?? null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']) || empty($USER->id)) {
            $_SESSION['message_error'] = '登録に失敗しました';
            if ($id) {
                header('Location: /custom/admin/app/Views/management/information_upsert.php?id=' . $id);
            } else {
                header('Location: /custom/admin/app/Views/management/information_upsert.php');
            }
            exit;
        }
    }
    $hasError = false;
    if (empty($title)) {
        $_SESSION['errors']['title'] = '件名は必須です';
        $hasError = true;
    }
    if (empty($body)) {
        $_SESSION['errors']['body'] = '本文は必須です';
        $hasError = true;
    }
    if ((empty($_POST['publish_start_date']) && !empty($_POST['publish_start_hour'])) ||
        (!empty($_POST['publish_start_date']) && empty($_POST['publish_start_hour']))) {
        $_SESSION['errors']['scheduled_publish_start_at'] = '掲載開始日時は日付と時刻の両方を入力してください。';
         $hasError = true;
    }
    if ((empty($_POST['publish_end_date']) && !empty($_POST['publish_end_hour'])) ||
        (!empty($_POST['publish_end_date']) && empty($_POST['publish_end_hour']))) {
        $_SESSION['errors']['scheduled_publish_end_at'] = '掲載終了日時は日付と時刻の両方を入力してください。';
        $hasError = true;
    }
    if ($hasError) {
        $_SESSION['old_input'] = $_POST;
        $_SESSION['message_error'] = '登録に失敗しました';
            if ($id) {
                redirect('/custom/admin/app/Views/management/information_upsert.php?id=' . $id);
            } else {
                redirect('/custom/admin/app/Views/management/information_upsert.php');
            }
        exit;
    }

    // body内をサニタイズ( 登録時も確認する事 )
    // $config = HTMLPurifier_Config::createDefault();

    $body = htmlspecialchars($body, ENT_QUOTES, 'UTF-8');
    var_dump("テスト2");
    exit;
    // $config->set('CSS.AllowTricky', true);
    // $config->set('HTML.TargetBlank', true);
    // $config->set('HTML.SafeInlineCSS', true);
    // $config->set('HTML.Allowed', implode(',', [
    //     'p[style]',
    //     'b',
    //     'strong',
    //     'i',
    //     'em',
    //     'ul[style]',
    //     'ol[style]',
    //     'li[style]',
    //     'a[href|target|rel|style]',
    //     'iframe[src|width|height|frameborder|allowfullscreen]',
    //     'br',
    //     'span[style]',
    //     'div[style]',
    //     'h1[style]',
    //     'h2[style]',
    //     'h3[style]',
    //     'h4[style]',
    //     'h5[style]',
    //     'h6[style]',
    //     'img[src|alt|width|height]',
    // ]));

    // $config->set('CSS.AllowedProperties', [
    //     'color',
    //     'background-color',
    //     'font-size',
    //     'text-align',
    //     'line-height',
    //     'margin',
    //     'margin-top',
    //     'margin-bottom',
    //     'margin-left',
    //     'margin-right',
    //     'padding',
    //     'padding-top',
    //     'padding-bottom',
    //     'padding-left',
    //     'padding-right',
    //     'display',
    //     'border',
    //     'border-radius'
    // ]);

    // $purifier = new HTMLPurifier($config);
    // $clean_html = $purifier->purify($body); // サニタイズ

    // $body  =  $clean_html;
    $transaction = $DB->start_delegated_transaction();
    $information = new stdClass();
    if (!$id) {
        $information->title = $title;
        $information->created_at = date('Y-m-d H:i:s');
        $information->updated_at = date('Y-m-d H:i:s');
        $information->body = $body;
        $information->publish_start_at = $publish_start_at ? date('Y-m-d H:i:s', strtotime($publish_start_at)) : null;
        $information->publish_end_at = $publish_end_at ? date('Y-m-d H:i:s', strtotime($publish_end_at)) : null;
        $DB->insert_record('information',$information);
    } else {
        $information->id = $id;
        $information->title = $title;
        $information->body = $body;
        $information->updated_at = date('Y-m-d H:i:s');
        $information->publish_start_at = $publish_start_at ? date('Y-m-d H:i:s', strtotime($publish_start_at)) : null;
        $information->publish_end_at = $publish_end_at ? date('Y-m-d H:i:s', strtotime($publish_end_at)) : null;
        $DB->update_record('information', $information);
    }
    $transaction->allow_commit();
    $_SESSION['message_success'] = '登録が完了しました';
    header('Location: /custom/admin/app/Views/management/information.php');
    exit;
} catch (Exception $e) {
    // ロールバック中に例外が再スローする事を防ぐ
    try {
        $transaction->rollback($e);
    } catch (Exception $rollbackException) {
        $_SESSION['old_input'] = $_POST;
        $_SESSION['message_error'] = '登録に失敗しました';
        redirect('/custom/admin/app/Views/management/information_upsert.php?id=' . $id);
        exit;
    }
}

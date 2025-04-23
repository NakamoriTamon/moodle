<?php
require_once($CFG->dirroot . '/user/lib.php');
require_once('/var/www/html/moodle/config.php');

class userRegistrationController
{

    public function index($id, $expiration_time)
    {

        try {
            global $DB, $url_secret_key;
            $transaction = $DB->start_delegated_transaction();

            if (empty($id) || empty($expiration_time)) {
                return 0;
            }

            $id = $this->decrypt_id($id, $url_secret_key);
            $existing = $DB->get_record('user', ['id' => $id]);

            // すでに認証を終えている場合
            if ($existing && $existing->confirmed == CONFIRMED['IS_CONFIRMED']) {
                return 2;
            }

            // 有効期限確認
            $expiration_time = (int)$this->decrypt_id($expiration_time, $url_secret_key);
            if (time() > $expiration_time) {
                // $id = $this->decrypt_id($id, $url_secret_key);
                $user = core_user::get_user($id);
                if ($user->confirmed == CONFIRMED['IS_CONFIRMED']) {
                    $transaction->allow_commit();
                    return new moodle_url('/custom/app/Views/signup_error.php');
                }
                $test = user_delete_user($user); // 有効期間切れのためユーザー情報を削除
                $transaction->allow_commit();
                return 0;
            }

            $id = $this->decrypt_id($id, $url_secret_key);
            $existing = $DB->get_record('user', ['id' => $id]);
            if ($existing && $existing->confirmed == CONFIRMED['IS_CONFIRMED']) {
                $transaction->allow_commit();
                return new moodle_url('/custom/app/Views/signup_error.php');
            }
            if ($existing) {
                $data = new stdClass();
                $data->confirmed = 1;
                $data->timemodified = time();
                $data->id = $existing->id;
                $DB->update_record('user', $data);
            } else {
                throw new Exception;
            }
            $transaction->allow_commit();
            return 1;
        } catch (Throwable $e) {
            try {
                $transaction->rollback($e);
            } catch (Throwable $e) {
                $_SESSION['message_error'] = '登録に失敗しました';
                return 0;
            }
        }
    }

    // 暗号化を解除する
    private function decrypt_id($value, $key)
    {
        $iv = substr(hash('sha256', $key), 0, 16);
        return openssl_decrypt(base64_decode(urldecode($value)), 'AES-256-CBC', $key, 0, $iv);
    }
}

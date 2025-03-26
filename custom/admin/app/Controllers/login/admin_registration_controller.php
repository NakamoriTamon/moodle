<?php
require_once($CFG->dirroot . '/user/lib.php');
require_once('/var/www/html/moodle/config.php');

class adminRegistrationController
{

    public function index($id, $expiration_time)
    {
        try {
            global $DB, $url_secret_key;
            $transaction = $DB->start_delegated_transaction();

            if (empty($id) || empty($expiration_time)) {
                return false;
            }
            // 有効期限確認
            $expiration_time = (int)$this->decrypt_id($expiration_time, $url_secret_key);
            if (time() > $expiration_time) {
                $id = $this->decrypt_id($id, $url_secret_key);
                $user = core_user::get_user($id);
                $test = user_delete_user($user); // 有効期間切れのためユーザー情報を削除
                $transaction->allow_commit();
                $_SESSION['message_error'] = '登録に失敗しました。もう一度アカウントの作成から行ってください';
                return false;
            }

            $id = $this->decrypt_id($id, $url_secret_key);
            $existing = $DB->get_record('user', ['id' => $id]);
            if ($existing) {
                $data = new stdClass();
                $data->confirmed = CONFIRMED['IS_CONFIRMED'];
                $data->timemodified = time();
                $data->id = $existing->id;
                $DB->update_record('user', $data);
            } else {
                throw new Exception;
            }
            $transaction->allow_commit();
            return true;
        } catch (Throwable $e) {
            try {
                die();
                $transaction->rollback($e);
            } catch (Throwable $e) {
                $_SESSION['message_error'] = '登録に失敗しました';
                return false;
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

<?php
class EmailSendSettingModel extends BaseModel
{
    // メール送信設定取得
    public function getEmailSendSetting($filters = [])
    {
        if ($this->pdo) {
            try {
                $sql = "SELECT * FROM mdl_email_send_setting";

                $where = "";

                // 動的に検索条件を追加
                $params = [];
                if (isset($filters['keyword']) && !empty($filters['keyword'])) {
                    $where .= " WHERE keyword = :keyword";
                    $params[':keyword'] = $filters['keyword'];
                }
                if (isset($filters['category_id']) && !empty($filters['category_id'])) {
                    $where .= empty($where) ? " WHERE" : " AND";
                    $where .= " category_id = :category_id";
                    $params[':category_id'] = $filters['category_id'];
                }
                if (isset($filters['year']) && !empty($filters['year'])) {
                    $where .= empty($where) ? " WHERE" : " AND";
                    $where .= " year = :year";
                    $params[':year'] = $filters['year'];
                }

                if(!empty($where)) {
                    $sql .= $where;
                    // クエリの実行
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute($params);
                    $email_send_setting = $stmt->fetch(PDO::FETCH_ASSOC);
                    return $email_send_setting;
                }
                
                return [];
            } catch (\PDOException $e) {
                error_log('メール送信設定取得エラー: ' . $e->getMessage());
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }
    
    // メール送信設定取得
    public function getEmailSendSettingById($id = null)
    {
        if ($this->pdo) {
            try {
                $sql = "SELECT *
                FROM mdl_email_send_setting
                WHERE id = :id";

                $params[':id'] = $id;

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                return  $user;
            } catch (\PDOException $e) {
                error_log('メール送信設定取得エラー: ' . $e->getMessage() . ' ID: ' . $id);
                echo 'データの取得に失敗しました。';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました。";
        }

        return [];
    }
}
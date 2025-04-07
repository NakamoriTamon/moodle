<?php
class EmailSendSettingModel extends BaseModel
{
    // カテゴリを全件取得
    public function getEmailSendSetting($filters = [])
    {
        if ($this->pdo) {
            try {
                $sql = "SELECT * FROM mdl_email_send_setting";

                $where = " WHERE t.is_delete = 0";

                // 動的に検索条件を追加
                $params = [];
                if (!empty($filters['keyword'])) {
                    $where .= " AND t.name LIKE :keyword";
                    $params[':keyword'] = $filters['keyword'];
                }

                // クエリの実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $email_send_setting = $stmt->fetch(PDO::FETCH_ASSOC);
                return $email_send_setting;
            } catch (\PDOException $e) {
                error_log('適塾記念会ユーザー一覧取得エラー: ' . $e->getMessage());
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }
}
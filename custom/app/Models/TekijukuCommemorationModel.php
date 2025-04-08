<?php
require_once('/var/www/html/moodle/config.php');

class TekijukuCommemorationModel extends BaseModel
{
    // 全管理者を取得
    public function getTekijukuUser($filters = [], int $page = 1, int $perPage = 15)
    {
        if ($this->pdo) {
            try {
                // ベースのSQLクエリ

                $sql = "SELECT * FROM mdl_tekijuku_commemoration as t";
                $stmt = $this->pdo->prepare($sql);

                $where = " WHERE t.is_delete = 0";

                // 動的に検索条件を追加
                $params = [];
                if (!empty($filters['keyword'])) {
                    $searchTerm = "%" . $filters['keyword'] . "%";
                    $where .= " AND t.name LIKE :keyword";
                    $params[':keyword'] = $searchTerm;
                }
                if (!empty($filters['deadline_date'])) {
                    $searchTerm = $filters['deadline_date'];
                    $where .= " AND t.created_at < :deadline_date";
                    $params[':deadline_date'] = $searchTerm;
                }

                // ページネーション用のオフセットを計算
                $offset = ($page - 1) * $perPage;
                $limit = " LIMIT $perPage OFFSET $offset";

                // 最終SQLの組み立て
                $sql .= $where;
                $sql .=  $limit;

                // クエリの実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $tekijuku_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $tekijuku_list;
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
    // 総件数を取得
    public function getTekijukuUserCount($filters)
    {
        try {
            // ベースのSQLクエリ

            $sql = "SELECT COUNT(*) AS total FROM mdl_tekijuku_commemoration as t";
            $stmt = $this->pdo->prepare($sql);

            $where = " WHERE t.is_delete = 0";

            // 動的に検索条件を追加
            $params = [];
            if (!empty($filters['keyword'])) {
                $searchTerm = "%" . $filters['keyword'] . "%";
                $where .= " AND t.name LIKE :keyword";
                $params[':keyword'] = $searchTerm;
            }
            $sql .= $where;

            // クエリの実行
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $count = $stmt->fetch(PDO::FETCH_ASSOC);

            return $count;
        } catch (\PDOException $e) {
            error_log('適塾記念会ユーザー件数取得エラー: ' . $e->getMessage());
            echo 'データの取得に失敗しました';
        }
    }
    // 適塾の支払いが完了している情報を取得
    public function getTekijukuUserByPaid($fk_user_id)
    {
        if ($this->pdo) {
            try {
                $paid_deadline = TEKIJUKU_PAID_DEADLINE; // "mm-dd" 形式（例："04-01"）
                $current_date = date('Y-m-d');

                // 年度の判定（支払期限日より前なら前年）
                if ($current_date < date('Y') . '-' . $paid_deadline) {
                    $fiscal_year = date('Y') - 1;
                } else {
                    $fiscal_year = date('Y');
                }

                // SQLをPHPで構築（paid_dateの条件を除外）
                $sql = "SELECT 
                    tc.id, 
                    tc.number, 
                    tc.type_code, 
                    tc.name, 
                    tc.kana, 
                    tc.post_code, 
                    tc.address, 
                    tc.tell_number, 
                    tc.email, 
                    tc.payment_method, 
                    latest_payment.latest_paid_date AS paid_date, 
                    tc.note, 
                    tc.is_published, 
                    tc.is_subscription, 
                    tc.is_delete, 
                    tc.department, 
                    tc.major, 
                    tc.official, 
                    tc.paid_status,
                    tc.is_university_member, 
                    tc.price,
                    tc.is_dummy_email,
                    tc.is_deposit_2025,
                    tc.is_deposit_2026,
                    tc.is_deposit_2027,
                    tc.is_deposit_2028,
                    tc.is_deposit_2029,
                    tc.is_deposit_2030
                FROM mdl_tekijuku_commemoration tc
                LEFT JOIN (
                    SELECT 
                        fk_tekijuku_commemoration_id, 
                        MAX(paid_date) AS latest_paid_date
                    FROM mdl_tekijuku_commemoration_history
                    GROUP BY fk_tekijuku_commemoration_id
                ) AS latest_payment ON latest_payment.fk_tekijuku_commemoration_id = tc.id
                WHERE tc.fk_user_id = :fk_user_id";

                // PDO でバインドする場合
                $params = [
                    ':fk_user_id' => $fk_user_id
                ];

                // SQLを実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $tekijuku = $stmt->fetch(PDO::FETCH_ASSOC);

                return $tekijuku;
            } catch (\PDOException $e) {
                error_log('支払済適塾記念会ユーザー取得エラー: ' . $e->getMessage() . ' UserID: ' . $fk_user_id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }
}

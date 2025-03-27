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
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
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
            echo 'データの取得に失敗しました: ' . $e->getMessage();
        }
    }
    
    // 適塾の支払いが完了している情報を取得
    public function getTekijukuUserByPaid($fk_user_id)
    {
        if ($this->pdo) {
            try {
                $paid_deadline = TEKIJUKU_PAID_DEADLINE; // 適塾支払期限(年度切替日：mm-dd形式)
                $current_date = date('Y-m-d');

                // 年度切り替え日を作成
                $cutoff_date = date('Y') . '-' . $paid_deadline;

                if ($current_date < $cutoff_date) {
                    $current_year = date('Y') - 1; // 4月1日より前なら前年
                } else {
                    $current_year = date('Y'); // 4月1日以降ならその年
                }

                $year_short = $current_year - 2000; // 西暦 → 和暦 (25年度なら 25)

                // SQLをPHPで構築（is_deposit_xx を動的に設定）
                $sql = "SELECT * FROM mdl_tekijuku_commemoration
                        WHERE fk_user_id = :fk_user_id
                        AND (
                            YEAR(paid_date) = :current_year
                            OR " . ($current_year <= 2030 ? "is_deposit_$current_year = 1" : "1") . "
                        )";

                // PDO でバインドする場合
                $params = [
                    ':fk_user_id' => $fk_user_id,
                    ':current_year' => $current_year
                ];

                // SQLを実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $tekijuku = $stmt->fetch(PDO::FETCH_ASSOC);

                return $tekijuku;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }
}

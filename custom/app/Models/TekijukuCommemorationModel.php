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
                $paid_deadline = TEKIJUKU_PAID_DEADLINE; // "mm-dd" 形式（例："04-01"）
                $current_date = date('Y-m-d');

                // 年度の判定（支払期限日より前なら前年）
                if ($current_date < date('Y') . '-' . $paid_deadline) {
                    $fiscal_year = date('Y') - 1;
                } else {
                    $fiscal_year = date('Y');
                }

                // 年度の開始日
                $current_fiscal_start = $fiscal_year . '-' . $paid_deadline;

                // 年度の終了日（支払期限日の前日を求める）
                $next_fiscal_start = ($fiscal_year + 1) . '-' . $paid_deadline;
                $current_fiscal_end = date('Y-m-d', strtotime("$next_fiscal_start -1 day"));

                $year_short = $fiscal_year - 2000; // 西暦 → 和暦（2025年なら25）

                // SQLをPHPで構築（is_deposit_xx を動的に設定）
                $sql = "SELECT * FROM mdl_tekijuku_commemoration
                        WHERE fk_user_id = :fk_user_id
                        AND (
                            (paid_date BETWEEN :current_fiscal_start AND :current_fiscal_end)
                            OR " . ($fiscal_year <= 2030 ? "is_deposit_$fiscal_year = 1" : "1") . "
                        )";

                // PDO でバインドする場合
                $params = [
                    ':fk_user_id' => $fk_user_id,
                    ':current_fiscal_start' => $current_fiscal_start,
                    ':current_fiscal_end' => $current_fiscal_end
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

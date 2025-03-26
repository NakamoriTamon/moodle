<?php
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
                // ベースのSQLクエリ

                $sql = "SELECT * FROM mdl_tekijuku_commemoration as t
                         WHERE t.is_delete = 0
                        AND t.fk_user_id = :fk_user_id
                        AND (t.paid_date IS NOT NULL OR t.is_deposit_2025 = 1)";

                // クエリの実行
                $stmt = $this->pdo->prepare($sql);
                
                $stmt->execute([":fk_user_id" => $fk_user_id]);
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

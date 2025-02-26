<?php
class UserModel extends BaseModel
{
    // 全管理者を取得
    public function getAdminUsers($filters = [], int $page = 1, int $perPage = 10)
    {
        if ($this->pdo) {
            try {
                // ベースのSQLクエリ
                $sql = "SELECT 
                    u.*, 
                    r.id AS role_id,
                    r.shortname AS role
                FROM mdl_user u
                JOIN mdl_role_assignments ra ON u.id = ra.userid
                JOIN mdl_role r ON ra.roleid = r.id";

                $where = " WHERE u.deleted = 0";
                $where .= " AND r.shortname IN ('admin', 'coursecreator')";
                $orderBy = ' ORDER BY u.lastname, u.firstname ASC';

                // 動的に検索条件を追加
                $params = [];
                // if (!empty($filters['category_id'])) {
                //     $sql .= ' LEFT JOIN mdl_event_category ec ON ec.event_id = e.id';
                //     $where .= ' AND ec.category_id = :category_id';
                //     $params[':category_id'] = $filters['category_id'];
                // }
                // if (!empty($filters['event_status'])) {
                //     $having = ' HAVING event_status = :event_status';
                //     $params[':event_status'] = $filters['event_status'];
                // }
                // if (!empty($filters['event_id'])) {
                //     $where .= ' AND e.id = :event_id';
                //     $params[':event_id'] = $filters['event_id'];
                // }

                // ページネーション用のオフセットを計算
                $offset = ($page - 1) * $perPage;
                $limit = " LIMIT $perPage OFFSET $offset";

                // 最終SQLの組み立て
                $sql .= $where;
                if (!empty($having)) {
                    $sql .= $having;
                }
                $sql .= $orderBy . $limit;

                // クエリの実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $admins;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }
}
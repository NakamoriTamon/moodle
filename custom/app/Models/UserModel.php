<?php
class UserModel extends BaseModel
{
    // 全管理者を取得
    public function getAdminUsers($filters = [], int $page = 1, int $perPage = 15)
    {
        if ($this->pdo) {
            try {
                // ベースのSQLクエリ
                $sql = "SELECT 
                    u.*, 
                    r.id AS role_id,
                    r.sortorder AS role_sortorder,
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

    // ユーザIDに基づいてユーザ詳細を取得
    private function getUserDetails($userID)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_each WHERE event_id = :userID");
                $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // ユーザ単件取得
    public function getUserById($id = null)
    {
        if ($this->pdo) {
            try {
                $sql = "SELECT 
                    u.*, 
                    r.id AS role_id,
                    r.sortorder AS role_sortorder,
                    r.shortname AS role
                FROM mdl_user u
                JOIN mdl_role_assignments ra ON u.id = ra.userid
                JOIN mdl_role r ON ra.roleid = r.id";

                $params[':id'] = $id;

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!empty($user)) {
                    $user['details'] = $this->getUserDetails($user['id']);
                }

                return  $user;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    public function getUser($page = 1, $perPage = 15)
    {
        if ($this->pdo) {
            try {
                // ベースのSQLクエリ
                $sql = "SELECT 
                    u.*, 
                    r.id AS role_id,
                    r.sortorder AS role_sortorder,
                    r.shortname AS role
                FROM mdl_user u
                JOIN mdl_role_assignments ra ON u.id = ra.userid
                JOIN mdl_role r ON ra.roleid = r.id";

                $where = " WHERE u.deleted = 0";
                $where .= " AND r.shortname = 'user'";

                // ページネーション用のオフセットを計算
                $offset = ($page - 1) * $perPage;
                $limit = " LIMIT $perPage OFFSET $offset";

                // 最終SQLの組み立て
                $sql .= $where;
                $sql .=  $limit;

                // クエリの実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($users as $Key => $user) {
                    $tekijuku = $this->getTekijukuByUserId($user['id']);
                    if (!$tekijuku) {
                        $users[$Key]['tekijuku'] = [];
                    } else {
                        $users[$Key]['tekijuku'] = $this->getTekijukuByUserId($user['id']);
                    }
                }

                return $users;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }
    }

    public function getUserCount()
    {
        if ($this->pdo) {
            try {
                // ベースのSQLクエリ
                $sql = "SELECT 
                    u.*, 
                    r.id AS role_id,
                    r.sortorder AS role_sortorder,
                    r.shortname AS role
                FROM mdl_user u
                JOIN mdl_role_assignments ra ON u.id = ra.userid
                JOIN mdl_role r ON ra.roleid = r.id";

                $where = " WHERE u.deleted = 0";
                $where .= " AND r.shortname = 'user'";
                $sql .= $where;

                // クエリの実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($users as $Key => $user) {
                    $tekijuku = $this->getTekijukuByUserId($user['id']);
                    if (!$tekijuku) {
                        $users[$Key]['tekijuku'] = [];
                    } else {
                        $users[$Key]['tekijuku'] = $this->getTekijukuByUserId($user['id']);
                    }
                }

                return $users;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }
    }

    private function getTekijukuByUserId($user_id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_tekijuku_commemoration WHERE is_delete = 0 AND fk_user_id = :user_id");
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }
}

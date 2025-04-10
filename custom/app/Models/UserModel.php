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
                if (!empty($filters['keyword'])) {
                    $where .= ' AND u.name LIKE :keyword';
                    $params[':keyword'] = '%' . $filters['keyword'] . '%';
                }
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
                error_log('管理者ユーザー取得エラー: ' . $e->getMessage());
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
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
                error_log('ユーザー詳細取得エラー: ' . $e->getMessage() . ' userID: ' . $userID);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
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
                error_log('ユーザー単体取得エラー: ' . $e->getMessage() . ' ID: ' . $id);
                echo 'データの取得に失敗しました。';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました。";
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
                error_log('ユーザー一覧取得エラー: ' . $e->getMessage());
                echo 'データの取得に失敗しました。';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました。";
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
                error_log('ユーザー数取得エラー: ' . $e->getMessage());
                echo 'データの取得に失敗しました。';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました。";
        }
    }

    public function getFilterUserCount($filters = [])
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

                // 動的に検索条件を追加
                $params = [];
                if (!empty($filters['keyword'])) {
                    $where .= ' AND u.name LIKE :keyword';
                    $params[':keyword'] = '%' . $filters['keyword'] . '%';
                }

                $sql .= $where;
                // クエリの実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
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
                error_log('ユーザー数取得エラー: ' . $e->getMessage());
                echo 'データの取得に失敗しました。';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました。";
        }
    }

    public function getEventEntryUser($filters = [], int $page = 1, int $perPage = 8)
    {
        if ($this->pdo) {
            try {
                // ベースのSQLクエリ
                $sql = "SELECT 
                    u.id,
                    u.name,
                    ea.pay_method,
                    ea.payment_kbn,
                    ea.payment_date,
                    ea.application_date,
                    eaci.participant_mail
                FROM mdl_user u
                LEFT OUTER JOIN mdl_event_application ea ON u.id = ea.user_id
                LEFT OUTER JOIN mdl_event_application_course_info eaci ON ea.id = eaci.event_application_id";

                $where = " WHERE u.deleted = 0
                         AND ea.payment_kbn != 2";
                // 動的に検索条件を追加
                $params = [];
                // 追加条件
                if (!empty($filters['event_id'])) {
                    $where .= ' AND ea.event_id = :event_id';
                    $params[':event_id'] = $filters['event_id'];
                }

                if (!empty($filters['keyword'])) {
                    //仕様：スペース区切りに対応している
                    //全角半角スペースを全て半角スペースに揃える
                    $keyword = str_replace('　', ' ', $filters['keyword']);

                    //配列に入れる
                    $keyword_array = explode(' ', $keyword);

                    // キーワード検索対象カラム（名前、メールアドレス）
                    $searchColumns = ['u.name', 'u.email'];

                    // AND検索の条件を組み立てる
                    foreach ($keyword_array as $index => $key) {
                        $keyParam = ":keyword" . $index; // プレースホルダ名をユニークにする
                        $where .= " AND (" . implode(" LIKE $keyParam OR ", $searchColumns) . " LIKE $keyParam)";
                        $params[$keyParam] = "%$key%"; // 部分一致検索
                    }
                }
                $sql .= $where;
                $groupBy = " GROUP BY u.id, u.username, u.email, 
                            ea.pay_method, ea.payment_kbn, ea.payment_date, ea.application_date,
                            eaci.participant_mail
                            ORDER BY u.id";
                $sql .= $groupBy;

                // クエリの実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $users;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }
        return [];
    }

    public function getUsers($filters = [], $page = 1, $perPage = 15)
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
                $orderBy = ' ORDER BY u.lastname, u.firstname ASC';

                // 動的に検索条件を追加
                $params = [];
                if (!empty($filters['keyword'])) {
                    $where .= " AND (u.id LIKE :keyword OR u.name LIKE :keyword OR u.email LIKE :keyword OR u.name_kana LIKE :keyword)";
                    $params[':keyword'] = '%' . $filters['keyword'] . '%';
                }

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
                error_log('ユーザー一覧取得エラー: ' . $e->getMessage());
                echo 'データの取得に失敗しました。';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました。";
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
                error_log('適塾情報取得エラー: ' . $e->getMessage() . ' user_id: ' . $user_id);
                echo 'データの取得に失敗しました。';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました。";
        }

        return [];
    }
}

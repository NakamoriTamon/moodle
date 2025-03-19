<?php
class SurveyApplicationModel extends BaseModel
{
    // アンケート回答を取得するメソッド
    public function getSurveyApplications($filters = [], int $page = 1, int $perPage = 10)
    {
        if ($this->pdo) {
            try {
                $now = new DateTime();
                $currentTimestamp = $now->format('Y-m-d H:i:s');
                
                // ベースのSQLクエリ
                $sql = 'WITH event_dates AS (
                            SELECT 
                                e.id AS event_id,
                                MIN(c.course_date) AS min_course_date,
                                MAX(c.course_date) AS max_course_date
                            FROM mdl_event e
                            LEFT JOIN mdl_event_course_info ec ON e.id = ec.event_id
                            LEFT JOIN mdl_course_info c ON ec.course_info_id = c.id
                            GROUP BY e.id
                        )
                        SELECT sa.*, e.name as event_name, sa.course_info_id,
                        CASE
                            WHEN :current_timestamp < ed.min_course_date THEN 1 -- 開催前
                            WHEN :current_timestamp BETWEEN ed.min_course_date AND ed.max_course_date THEN 2 -- 開催中
                            WHEN :current_timestamp > ed.max_course_date THEN 3 -- 開催終了
                            ELSE 0
                        END AS event_status
                        FROM mdl_survey_application sa
                        LEFT JOIN mdl_event e ON e.id = sa.event_id
                        LEFT JOIN event_dates ed ON e.id = ed.event_id';

                $where = ' WHERE 1=1';
                $having = '';
                $orderBy = ' ORDER BY sa.id DESC';

                // 動的に検索条件を追加
                $params = [
                    ':current_timestamp' => $currentTimestamp
                ];
                
                if (!empty($filters['category_id'])) {
                    $sql .= ' LEFT JOIN mdl_event_category ec ON ec.event_id = sa.event_id';
                    $where .= ' AND ec.category_id = :category_id';
                    $params[':category_id'] = $filters['category_id'];
                }
                
                if (!empty($filters['event_status'])) {
                    if (!empty($having)) {
                        $having .= ' AND';
                    } else {
                        $having = ' HAVING';
                    }
                    $having .= ' event_status = :event_status';
                    $params[':event_status'] = $filters['event_status'];
                }
                
                if (!empty($filters['event_id'])) {
                    $where .= ' AND sa.event_id = :event_id';
                    $params[':event_id'] = $filters['event_id'];
                }
                
                if (!empty($filters['course_info_id'])) {
                    $where .= ' AND sa.course_info_id = :course_info_id';
                    $params[':course_info_id'] = $filters['course_info_id'];
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
                $surveys = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $surveys;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // アンケート回答の総数を取得
    public function getSurveyTotal($filters = [])
    {
        if ($this->pdo) {
            try {
                $now = new DateTime();
                $currentTimestamp = $now->format('Y-m-d H:i:s');
                
                // ベースのSQLクエリ
                $sql = 'WITH event_dates AS (
                            SELECT 
                                e.id AS event_id,
                                MIN(c.course_date) AS min_course_date,
                                MAX(c.course_date) AS max_course_date
                            FROM mdl_event e
                            LEFT JOIN mdl_event_course_info ec ON e.id = ec.event_id
                            LEFT JOIN mdl_course_info c ON ec.course_info_id = c.id
                            GROUP BY e.id
                        )
                        SELECT COUNT(*) as total
                        FROM (
                            SELECT sa.id, sa.course_info_id,
                            CASE
                                WHEN :current_timestamp < ed.min_course_date THEN 1 -- 開催前
                                WHEN :current_timestamp BETWEEN ed.min_course_date AND ed.max_course_date THEN 2 -- 開催中
                                WHEN :current_timestamp > ed.max_course_date THEN 3 -- 開催終了
                                ELSE 0
                            END AS event_status
                            FROM mdl_survey_application sa
                            LEFT JOIN mdl_event e ON e.id = sa.event_id
                            LEFT JOIN event_dates ed ON e.id = ed.event_id';

                $where = ' WHERE 1=1';
                $having = '';

                // 動的に検索条件を追加
                $params = [
                    ':current_timestamp' => $currentTimestamp
                ];
                
                if (!empty($filters['category_id'])) {
                    $sql .= ' LEFT JOIN mdl_event_category ec ON ec.event_id = sa.event_id';
                    $where .= ' AND ec.category_id = :category_id';
                    $params[':category_id'] = $filters['category_id'];
                }
                
                if (!empty($filters['event_id'])) {
                    $where .= ' AND sa.event_id = :event_id';
                    $params[':event_id'] = $filters['event_id'];
                }
                
                if (!empty($filters['course_info_id'])) {
                    $where .= ' AND sa.course_info_id = :course_info_id';
                    $params[':course_info_id'] = $filters['course_info_id'];
                }

                $sql .= $where;
                
                if (!empty($filters['event_status'])) {
                    if (!empty($having)) {
                        $having .= ' AND';
                    } else {
                        $having = ' HAVING';
                    }
                    $having .= ' event_status = :event_status';
                    $params[':event_status'] = $filters['event_status'];
                }
                
                if (!empty($having)) {
                    $sql .= $having;
                }
                
                $sql .= ') as filtered_surveys';

                // クエリの実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $totalCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                return $totalCount;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return 0;
    }
} 
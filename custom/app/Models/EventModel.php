<?php
class EventModel extends BaseModel
{
    // イベントを全件取得
    public function getEvents($filters = [], int $page = 1, int $perPage = 10)
    {
        if ($this->pdo) {
            try {
                $now = new DateTime();
                $currentTimestamp = $now->format('Y-m-d H:i:s');
                // ベースのSQLクエリ
                $sql = 'WITH closest_dates AS (
                            SELECT 
                                e.id AS event_id,
                                c.course_date,
                                c.deadline_date,
                                ABS(TIMESTAMPDIFF(SECOND, NOW(), c.course_date)) AS time_diff
                            FROM mdl_event e
                            LEFT JOIN mdl_event_course_info ec ON e.id = ec.event_id
                            LEFT JOIN mdl_course_info c ON ec.course_info_id = c.id
                        ),
                        event_dates AS (
                            SELECT 
                                e.id AS event_id,
                                MIN(c.course_date) AS min_course_date,
                                MAX(c.course_date) AS max_course_date
                            FROM mdl_event e
                            LEFT JOIN mdl_event_course_info ec ON e.id = ec.event_id
                            LEFT JOIN mdl_course_info c ON ec.course_info_id = c.id
                            GROUP BY e.id
                        )
                        SELECT 
                            e.*,
                            CASE
                                WHEN :current_timestamp <= e.deadline - INTERVAL 5 DAY THEN 1 -- 受付中
                                WHEN :current_timestamp > e.deadline - INTERVAL 5 DAY 
                                AND :current_timestamp <= e.deadline THEN 2 -- もうすぐ締め切り
                                WHEN :current_timestamp > e.deadline THEN 3 -- 受付終了
                            END AS set_event_deadline_status,
                            (SELECT cd.course_date 
                            FROM closest_dates cd 
                            WHERE cd.event_id = e.id 
                            ORDER BY cd.time_diff ASC 
                            LIMIT 1) AS closest_course_date,
                            CASE
                                WHEN DATE(:current_timestamp) < DATE(ed.min_course_date) THEN 1 -- 開催前
                                WHEN DATE(:current_timestamp) >= DATE(ed.min_course_date) AND DATE(:current_timestamp) <= DATE(ed.max_course_date) THEN 2 -- 開催中
                                WHEN DATE(:current_timestamp) > DATE(ed.max_course_date) THEN 3 -- 開催終了
                                ELSE 0
                            END AS event_status,
                            CASE
                                WHEN :current_timestamp <= (
                                    COALESCE(
                                        (SELECT cd.deadline_date FROM closest_dates cd 
                                        WHERE cd.event_id = e.id 
                                        AND cd.deadline_date >= :current_timestamp 
                                        ORDER BY cd.time_diff ASC LIMIT 1),
                                        (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                    ) - INTERVAL 5 DAY
                                ) THEN 1 -- 受付中

                                WHEN :current_timestamp > (
                                    COALESCE(
                                        (SELECT cd.deadline_date FROM closest_dates cd 
                                        WHERE cd.event_id = e.id 
                                        AND cd.deadline_date >= :current_timestamp 
                                        ORDER BY cd.time_diff ASC LIMIT 1),
                                        (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                    ) - INTERVAL 5 DAY
                                ) 
                                AND :current_timestamp <= (
                                    COALESCE(
                                        (SELECT cd.deadline_date FROM closest_dates cd 
                                        WHERE cd.event_id = e.id 
                                        AND cd.deadline_date >= :current_timestamp 
                                        ORDER BY cd.time_diff ASC LIMIT 1),
                                        (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                    )
                                ) THEN 2 -- もうすぐ締め切り

                                WHEN :current_timestamp > (
                                    COALESCE(
                                        (SELECT cd.deadline_date FROM closest_dates cd 
                                        WHERE cd.event_id = e.id 
                                        AND cd.deadline_date >= :current_timestamp 
                                        ORDER BY cd.time_diff ASC LIMIT 1),
                                        (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                    )
                                ) THEN 3 -- 受付終了

                                ELSE 0
                            END AS deadline_status
                        FROM mdl_event e
                        LEFT JOIN event_dates ed ON e.id = ed.event_id
                    LEFT JOIN mdl_event_course_info eci ON eci.event_id = e.id
                    LEFT JOIN mdl_course_info ci ON eci.course_info_id = ci.id
                    LEFT JOIN mdl_event_application ea ON ea.event_id = e.id';

                $where = ' WHERE e.visible = 1';
                $groupBy = ' GROUP BY e.id';
                $orderBy = ' ORDER BY 
                    CASE 
                        WHEN event_status IN (1, 2) AND is_top = 1 THEN 1  -- 1番目: event_statusが1または2 & is_top = 1
                        WHEN event_status IN (1, 2) AND is_top = 0 THEN 2  -- 2番目: event_statusが1または2 & is_top = 0
                        WHEN event_status = 3 AND is_top = 1 THEN 3        -- 3番目: event_statusが3 & is_top = 1
                        WHEN event_status = 3 AND is_top = 0 THEN 4        -- 4番目: event_statusが3 & is_top = 0
                        ELSE 5  -- その他（万が一 event_status の値が 1, 2, 3 以外の場合）
                    END,
                    MIN(ci.course_date) ASC, MIN(ci.course_date) ASC';

                // 動的に検索条件を追加
                $params = [
                    ':current_timestamp' => $currentTimestamp
                ];
                $having = "";
                if (!empty($filters['shortname']) && !empty($filters['userid'])) {
                    if ($filters['shortname'] != ROLE_ADMIN) {
                        $where .= ' AND e.userid = :userid';
                        $params[':userid'] = $filters['userid'];
                    }
                }
                if (!empty($filters['category_id'])) {
                    $sql .= ' LEFT JOIN mdl_event_category ec ON ec.event_id = e.id';
                    if (is_array($filters['category_id'])) {
                        $placeholders = [];
                        foreach ($filters['category_id'] as $index => $id) {
                            $key = ":category_id_$index";
                            $placeholders[] = $key;
                            $params[$key] = $id;
                        }
                        $where .= ' AND ec.category_id IN (' . implode(',', $placeholders) . ')';
                    } else {
                        $where .= ' AND ec.category_id = :category_id';
                        $category_id = $filters['category_id'];
                        $params[':category_id'] = $category_id;
                    }
                }
                if (!empty($filters['lecture_format_id'])) {
                    $sql .= ' LEFT JOIN mdl_event_lecture_format elf ON elf.event_id = e.id';
                    if (is_array($filters['lecture_format_id'])) {
                        $placeholders = [];
                        foreach ($filters['lecture_format_id'] as $index => $id) {
                            $key = ":lecture_format_id_$index";
                            $placeholders[] = $key;
                            $params[$key] = $id;
                        }
                        $where .= ' AND elf.lecture_format_id IN (' . implode(',', $placeholders) . ')';
                    } else {
                        $where .= ' AND elf.lecture_format_id = :lecture_format_id';
                        $lecture_format_id = $filters['lecture_format_id'];
                        $params[':lecture_format_id'] = $lecture_format_id;
                    }
                }
                if (!empty($filters['event_status'])) {
                    if (is_array($filters['event_status'])) {
                        if (!empty($having)) {
                            $having .= ' AND';
                        } else {
                            $having = ' HAVING';
                        }
                        $placeholders = [];
                        foreach ($filters['event_status'] as $index => $id) {
                            $key = ":event_status_$index";
                            $placeholders[] = $key;
                            $params[$key] = $id;
                        }
                        $having .= ' event_status IN (' . implode(',', $placeholders) . ')';
                    } else {
                        if (!empty($having)) {
                            $having .= ' AND';
                        } else {
                            $having = ' HAVING';
                        }
                        $having .= ' event_status = :event_status';
                        $params[':event_status'] = $filters['event_status'];
                    }
                }
                if (!empty($filters['exclude_event_status'])) {
                    if (is_array($filters['exclude_event_status'])) {
                        if (!empty($having)) {
                            $having .= ' AND';
                        } else {
                            $having = ' HAVING';
                        }
                        $placeholders = [];
                        foreach ($filters['exclude_event_status'] as $index => $id) {
                            $key = ":exclude_event_status_$index";
                            $placeholders[] = $key;
                            $params[$key] = $id;
                        }
                        $having .= ' event_status NOT IN (' . implode(',', $placeholders) . ')';
                    } else {
                        if (!empty($having)) {
                            $having .= ' AND';
                        } else {
                            $having = ' HAVING';
                        }
                        $having .= ' event_status != :exclude_event_status';
                        $params[':exclude_event_status'] = $filters['exclude_event_status'];
                    }
                }
                if (!empty($filters['deadline_status'])) {
                    if (is_array($filters['deadline_status'])) {
                        if (!empty($having)) {
                            $having .= ' AND';
                        } else {
                            $having = ' HAVING';
                        }
                        $placeholders = [];
                        foreach ($filters['deadline_status'] as $index => $id) {
                            $key = ":deadline_status_$index";
                            $placeholders[] = $key;
                            $params[$key] = $id;
                        }
                        $having .= ' deadline_status IN (' . implode(',', $placeholders) . ')';
                    } else {
                        if (!empty($having)) {
                            $having .= ' AND';
                        } else {
                            $having = ' HAVING';
                        }
                        $having .= ' deadline_status = :deadline_status';
                        $params[':deadline_status'] = $filters['deadline_status'];
                    }
                }
                if (!empty($filters['event_id'])) {
                    $where .= ' AND e.id = :event_id';
                    $params[':event_id'] = $filters['event_id'];
                }
                if (!empty($filters['event_start_date'])) {
                    $where .= ' AND ci.course_date >= :event_start_date';
                    $params[':event_start_date'] = $filters['event_start_date'];
                }
                if (!empty($filters['event_end_date'])) {
                    $where .= ' AND ci.course_date <= :event_end_date';
                    $params[':event_end_date'] = $filters['event_end_date'];
                }
                if (!empty($filters['target'])) {
                    $where .= ' AND e.target = :target';
                    $params[':target'] = $filters['target'];
                }
                // キーワード　フリー入力
                if (!empty($filters['keyword'])) {
                    // 開催場所、イベント名、講師名の部分一致検索
                    $sql .= '
                        LEFT JOIN mdl_course_info_detail cid ON cid.course_info_id = ci.id
                        LEFT JOIN mdl_tutor t ON t.id = cid.tutor_id';
                    // 文字列をスペース、半角スペース区切りで分割
                    $keywordArray = preg_split('/[ 　]+/u', $filters['keyword']);
                    $keywordConditions = [];
                    foreach ($keywordArray as $index => $word) {
                        $paramName = ":keyword{$index}";
                        $params[$paramName] = '%' . $word . '%';

                        $keywordConditions[] = "(
                            e.name LIKE $paramName
                            OR e.venue_name LIKE $paramName
                            OR cid.name LIKE $paramName
                            OR t.name LIKE $paramName
                        )";
                    }

                    if (!empty($keywordConditions)) {
                        $where .= ' AND (' . implode(' AND ', $keywordConditions) . ')';
                    }
                }
                // ページネーション用のオフセットを計算
                $offset = ($page - 1) * $perPage;
                $limit = " LIMIT $perPage OFFSET $offset";

                // 最終SQLの組み立て
                $sql .= $where . $groupBy;
                if (!empty($having)) {
                    $sql .= $having;
                }
                $sql .= $orderBy . $limit;

                // クエリの実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 各イベントの詳細を追加
                foreach ($events as &$event) {
                    $event['lecture_formats'] = $this->getEventLectureFormats($event['id']);
                    $event['categorys'] = $this->getEventCategorys($event['id']);
                    $event['course_infos'] = $this->getEventCourseInfos($event['id']);
                }

                return $events;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    public function getEventTotal($filters = [])
    {
        if ($this->pdo) {
            try {
                $now = new DateTime();
                $currentTimestamp = $now->format('Y-m-d H:i:s');
                // ベースのSQLクエリ
                $sql = 'WITH closest_dates AS (
                        SELECT 
                            e.id AS event_id,
                            c.course_date,
                            c.deadline_date,
                            ABS(TIMESTAMPDIFF(SECOND, NOW(), c.course_date)) AS time_diff
                        FROM mdl_event e
                        LEFT JOIN mdl_event_course_info ec ON e.id = ec.event_id
                        LEFT JOIN mdl_course_info c ON ec.course_info_id = c.id
                    ),
                    event_dates AS (
                        SELECT 
                            e.id AS event_id,
                            MIN(c.course_date) AS min_course_date,
                            MAX(c.course_date) AS max_course_date
                        FROM mdl_event e
                        LEFT JOIN mdl_event_course_info ec ON e.id = ec.event_id
                        LEFT JOIN mdl_course_info c ON ec.course_info_id = c.id
                        GROUP BY e.id
                    )
                    SELECT 
                        e.*,
                        CASE
                            WHEN :current_timestamp <= e.deadline - INTERVAL 5 DAY THEN 1 -- 受付中
                            WHEN :current_timestamp > e.deadline - INTERVAL 5 DAY 
                            AND :current_timestamp <= e.deadline THEN 2 -- もうすぐ締め切り
                            WHEN :current_timestamp > e.deadline THEN 3 -- 受付終了
                        END AS set_event_deadline_status,
                        (SELECT cd.course_date 
                        FROM closest_dates cd 
                        WHERE cd.event_id = e.id 
                        ORDER BY cd.time_diff ASC 
                        LIMIT 1) AS closest_course_date,
                        CASE
                            WHEN :current_timestamp < ed.min_course_date THEN 1 -- 開催前
                            WHEN :current_timestamp BETWEEN ed.min_course_date AND ed.max_course_date THEN 2 -- 開催中
                            WHEN :current_timestamp > ed.max_course_date THEN 3 -- 開催終了
                            ELSE 0
                        END AS event_status,
                        CASE
                                WHEN :current_timestamp <= (
                                    COALESCE(
                                        (SELECT cd.deadline_date FROM closest_dates cd 
                                        WHERE cd.event_id = e.id 
                                        AND cd.deadline_date >= :current_timestamp 
                                        ORDER BY cd.time_diff ASC LIMIT 1),
                                        (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                    ) - INTERVAL 5 DAY
                                ) THEN 1 -- 受付中

                                WHEN :current_timestamp > (
                                    COALESCE(
                                        (SELECT cd.deadline_date FROM closest_dates cd 
                                        WHERE cd.event_id = e.id 
                                        AND cd.deadline_date >= :current_timestamp 
                                        ORDER BY cd.time_diff ASC LIMIT 1),
                                        (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                    ) - INTERVAL 5 DAY
                                ) 
                                AND :current_timestamp <= (
                                    COALESCE(
                                        (SELECT cd.deadline_date FROM closest_dates cd 
                                        WHERE cd.event_id = e.id 
                                        AND cd.deadline_date >= :current_timestamp 
                                        ORDER BY cd.time_diff ASC LIMIT 1),
                                        (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                    )
                                ) THEN 2 -- もうすぐ締め切り

                                WHEN :current_timestamp > (
                                    COALESCE(
                                        (SELECT cd.deadline_date FROM closest_dates cd 
                                        WHERE cd.event_id = e.id 
                                        AND cd.deadline_date >= :current_timestamp 
                                        ORDER BY cd.time_diff ASC LIMIT 1),
                                        (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                    )
                                ) THEN 3 -- 受付終了

                                ELSE 0
                            END AS deadline_status
                    FROM mdl_event e
                    LEFT JOIN event_dates ed ON e.id = ed.event_id
                    LEFT JOIN mdl_event_course_info eci ON eci.event_id = e.id
                    LEFT JOIN mdl_course_info ci ON eci.course_info_id = ci.id
                    LEFT JOIN mdl_event_application ea ON ea.event_id = e.id';

                $where = ' WHERE e.visible = 1';
                $groupBy = ' GROUP BY e.id';
                $orderBy = ' ORDER BY is_top DESC, MIN(ci.course_date) ASC';

                // 動的に検索条件を追加
                $params = [
                    ':current_timestamp' => $currentTimestamp
                ];
                $having = "";
                if (!empty($filters['category_id'])) {
                    $sql .= ' LEFT JOIN mdl_event_category ec ON ec.event_id = e.id';
                    if (is_array($filters['category_id'])) {
                        $placeholders = [];
                        foreach ($filters['category_id'] as $index => $id) {
                            $key = ":category_id_$index";
                            $placeholders[] = $key;
                            $params[$key] = $id;
                        }
                        $where .= ' AND ec.category_id IN (' . implode(',', $placeholders) . ')';
                    } else {
                        $where .= ' AND ec.category_id = :category_id';
                        $category_id = $filters['category_id'];
                        $params[':category_id'] = $category_id;
                    }
                }
                if (!empty($filters['lecture_format_id'])) {
                    $sql .= ' LEFT JOIN mdl_event_lecture_format elf ON elf.event_id = e.id';
                    if (is_array($filters['lecture_format_id'])) {
                        $placeholders = [];
                        foreach ($filters['lecture_format_id'] as $index => $id) {
                            $key = ":lecture_format_id_$index";
                            $placeholders[] = $key;
                            $params[$key] = $id;
                        }
                        $where .= ' AND elf.lecture_format_id IN (' . implode(',', $placeholders) . ')';
                    } else {
                        $where .= ' AND elf.lecture_format_id = :lecture_format_id';
                        $lecture_format_id = $filters['lecture_format_id'];
                        $params[':lecture_format_id'] = $lecture_format_id;
                    }
                }
                if (!empty($filters['event_status'])) {
                    if (is_array($filters['event_status'])) {
                        if (!empty($having)) {
                            $having .= ' AND';
                        } else {
                            $having = ' HAVING';
                        }
                        $placeholders = [];
                        foreach ($filters['event_status'] as $index => $id) {
                            $key = ":event_status_$index";
                            $placeholders[] = $key;
                            $params[$key] = $id;
                        }
                        $having .= ' event_status IN (' . implode(',', $placeholders) . ')';
                    } else {
                        if (!empty($having)) {
                            $having .= ' AND';
                        } else {
                            $having = ' HAVING';
                        }
                        $having .= ' event_status = :event_status';
                        $params[':event_status'] = $filters['event_status'];
                    }
                }
                if (!empty($filters['deadline_status'])) {
                    if (is_array($filters['deadline_status'])) {
                        if (!empty($having)) {
                            $having .= ' AND';
                        } else {
                            $having = ' HAVING';
                        }
                        $placeholders = [];
                        foreach ($filters['deadline_status'] as $index => $id) {
                            $key = ":deadline_status_$index";
                            $placeholders[] = $key;
                            $params[$key] = $id;
                        }
                        $having .= ' deadline_status IN (' . implode(',', $placeholders) . ')';
                    } else {
                        if (!empty($having)) {
                            $having .= ' AND';
                        } else {
                            $having = ' HAVING';
                        }
                        $having .= ' deadline_status = :deadline_status';
                        $params[':deadline_status'] = $filters['deadline_status'];
                    }
                }
                if (!empty($filters['event_id'])) {
                    $where .= ' AND e.id = :event_id';
                    $params[':event_id'] = $filters['event_id'];
                }
                if (!empty($filters['event_start_date'])) {
                    $where .= ' AND ci.course_date >= :event_start_date';
                    $params[':event_start_date'] = $filters['event_start_date'];
                }
                if (!empty($filters['event_end_date'])) {
                    $where .= ' AND ci.course_date <= :event_end_date';
                    $params[':event_end_date'] = $filters['event_end_date'];
                }
                if (!empty($filters['target'])) {
                    $where .= ' AND e.target = :target';
                    $params[':target'] = $filters['target'];
                }
                // キーワード　フリー入力
                if (!empty($filters['keyword'])) {
                    // 開催場所、イベント名、講師名の部分一致検索
                    $sql .= '
                        LEFT JOIN mdl_course_info_detail cid ON cid.course_info_id = ci.id
                        LEFT JOIN mdl_tutor t ON t.id = cid.tutor_id';
                    // 文字列をスペース、半角スペース区切りで分割
                    $keywordArray = preg_split('/[ 　]+/u', $filters['keyword']);
                    $keywordConditions = [];
                    foreach ($keywordArray as $index => $word) {
                        $paramName = ":keyword{$index}";
                        $params[$paramName] = '%' . $word . '%';

                        $keywordConditions[] = "(
                            e.name LIKE $paramName
                            OR e.venue_name LIKE $paramName
                            OR cid.name LIKE $paramName
                            OR t.name LIKE $paramName
                        )";
                    }

                    if (!empty($keywordConditions)) {
                        $where .= ' AND (' . implode(' AND ', $keywordConditions) . ')';
                    }
                }

                // 最終SQLの組み立て
                $sql .= $where . $groupBy;
                if (!empty($having)) {
                    $sql .= $having;
                }

                // クエリの実行
                // 件数取得用のSQL
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $totalCount = count($stmt->fetchAll(PDO::FETCH_ASSOC));

                return $totalCount;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // イベントIDに基づいて講義形式を取得
    private function getEventLectureFormats($eventID)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT lf.id as lecture_format_id, lf.name FROM mdl_event_lecture_format elf 
                    LEFT JOIN mdl_lecture_format lf ON lf.id = elf.lecture_format_id WHERE event_id = :eventID");
                $stmt->bindParam(':eventID', $eventID, PDO::PARAM_INT);
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

    // イベントIDに基づいてカテゴリーを取得
    private function getEventCategorys($eventID)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT c.id as category_id, c.name FROM mdl_event_category ec 
                    LEFT JOIN mdl_category c ON c.id = ec.category_id WHERE event_id = :eventID");
                $stmt->bindParam(':eventID', $eventID, PDO::PARAM_INT);
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

    // イベントIDに基づいて講座を取得
    private function getEventCourseInfos($eventID)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT ci.* FROM mdl_event_course_info eci 
                    LEFT JOIN mdl_course_info ci ON ci.id = eci.course_info_id WHERE event_id = :eventID");
                $stmt->bindParam(':eventID', $eventID, PDO::PARAM_INT);
                $stmt->execute();
                $course_infos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($course_infos as &$course_info) {
                    $course_info["details"] = $this->getEventCourseInfoDetails($course_info["id"]);
                }

                return $course_infos;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    private function getEventCourseInfosById($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_course_info 
                    WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $course_infos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($course_infos as &$course_info) {
                    $course_info["details"] = $this->getEventCourseInfoDetails($course_info["id"]);
                }

                return $course_infos;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // 講座IDに基づいて講座詳細を取得
    private function getEventCourseInfoDetails($courseInfoID)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT cid.*  FROM mdl_course_info_detail cid
                    LEFT JOIN mdl_course_info ci ON ci.id = cid.course_info_id 
                    WHERE cid.course_info_id = :courseInfoID");
                $stmt->bindParam(':courseInfoID', $courseInfoID, PDO::PARAM_INT);
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

    // イベント単件取得
    public function getEventById($id = null)
    {
        if ($this->pdo) {
            try {
                $now = new DateTime();
                $currentTimestamp = $now->format('Y-m-d H:i:s');
                // ベースのSQLクエリ - COUNT追加
                $sql = 'WITH closest_dates AS (
                        SELECT 
                            e.id AS event_id,
                            c.course_date,
                            c.deadline_date,
                            ABS(TIMESTAMPDIFF(SECOND, NOW(), c.course_date)) AS time_diff
                        FROM mdl_event e
                        LEFT JOIN mdl_event_course_info ec ON e.id = ec.event_id
                        LEFT JOIN mdl_course_info c ON ec.course_info_id = c.id
                    ),
                    event_dates AS (
                        SELECT 
                            e.id AS event_id,
                            MIN(c.course_date) AS min_course_date,
                            MAX(c.course_date) AS max_course_date,
                            COUNT(c.id) AS total_courses
                        FROM mdl_event e
                        LEFT JOIN mdl_event_course_info ec ON e.id = ec.event_id
                        LEFT JOIN mdl_course_info c ON ec.course_info_id = c.id
                        GROUP BY e.id
                    )
                    SELECT 
                        e.*,
                        COALESCE(ed.total_courses, 0) AS total_courses,
                        CASE
                            WHEN :current_timestamp <= e.deadline - INTERVAL 5 DAY THEN 1 -- 受付中
                            WHEN :current_timestamp > e.deadline - INTERVAL 5 DAY 
                            AND :current_timestamp <= e.deadline THEN 2 -- もうすぐ締め切り
                            WHEN :current_timestamp > e.deadline THEN 3 -- 受付終了
                        END AS set_event_deadline_status,
                        (SELECT cd.course_date 
                        FROM closest_dates cd 
                        WHERE cd.event_id = e.id 
                        ORDER BY cd.time_diff ASC 
                        LIMIT 1) AS closest_course_date,
                        CASE
                            WHEN DATE(:current_timestamp) < DATE(ed.min_course_date) THEN 1 -- 開催前
                            WHEN DATE(:current_timestamp) >= DATE(ed.min_course_date) AND DATE(:current_timestamp) <= DATE(ed.max_course_date) THEN 2 -- 開催中
                            WHEN DATE(:current_timestamp) > DATE(ed.max_course_date) THEN 3 -- 開催終了
                        ELSE 0
                        END AS event_status,
                        CASE
                            WHEN :current_timestamp <= (
                                COALESCE(
                                    (SELECT cd.deadline_date FROM closest_dates cd 
                                    WHERE cd.event_id = e.id 
                                    AND cd.deadline_date >= :current_timestamp 
                                    ORDER BY cd.time_diff ASC LIMIT 1),
                                    (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                ) - INTERVAL 5 DAY
                            ) THEN 1 -- 受付中

                            WHEN :current_timestamp > (
                                COALESCE(
                                    (SELECT cd.deadline_date FROM closest_dates cd 
                                    WHERE cd.event_id = e.id 
                                    AND cd.deadline_date >= :current_timestamp 
                                    ORDER BY cd.time_diff ASC LIMIT 1),
                                    (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                ) - INTERVAL 5 DAY
                            ) 
                            AND :current_timestamp <= (
                                COALESCE(
                                    (SELECT cd.deadline_date FROM closest_dates cd 
                                    WHERE cd.event_id = e.id 
                                    AND cd.deadline_date >= :current_timestamp 
                                    ORDER BY cd.time_diff ASC LIMIT 1),
                                    (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                )
                            ) THEN 2 -- もうすぐ締め切り

                            WHEN :current_timestamp > (
                                COALESCE(
                                    (SELECT cd.deadline_date FROM closest_dates cd 
                                    WHERE cd.event_id = e.id 
                                    AND cd.deadline_date >= :current_timestamp 
                                    ORDER BY cd.time_diff ASC LIMIT 1),
                                    (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                )
                            ) THEN 3 -- 受付終了

                            ELSE 0
                        END AS deadline_status
                    FROM mdl_event e
                    LEFT JOIN event_dates ed ON e.id = ed.event_id
                LEFT JOIN mdl_event_course_info eci ON eci.event_id = e.id
                LEFT JOIN mdl_course_info ci ON eci.course_info_id = ci.id
                LEFT JOIN mdl_event_application ea ON ea.event_id = e.id
                WHERE e.visible = 1 AND e.id = :id
                GROUP BY e.id
                ORDER BY MIN(ci.course_date) ASC';

                // パラメータ設定
                $params = [
                    ':id' => $id,
                    ':current_timestamp' => $currentTimestamp
                ];

                // クエリ実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $event = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!empty($event)) {
                    // 各イベントの詳細を追加
                    $event['lecture_formats'] = $this->getEventLectureFormats($event['id']);
                    $event['categorys'] = $this->getEventCategorys($event['id']);
                    $event['course_infos'] = $this->getEventCourseInfos($event['id']);
                    $event_status = $event['event_status'];
                }

                return $event;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    public function getEventByIdAndCourseInfoId($id, $courseInfoId)
    {

        if ($this->pdo) {
            try {
                $now = new DateTime();
                $currentTimestamp = $now->format('Y-m-d H:i:s');
                // ベースのSQLクエリ
                $sql = 'WITH closest_dates AS (
                            SELECT 
                                e.id AS event_id,
                                c.course_date,
                                c.deadline_date,
                                ABS(TIMESTAMPDIFF(SECOND, NOW(), c.course_date)) AS time_diff
                            FROM mdl_event e
                            LEFT JOIN mdl_event_course_info ec ON e.id = ec.event_id
                            LEFT JOIN mdl_course_info c ON ec.course_info_id = c.id
                        ),
                        event_dates AS (
                            SELECT 
                                e.id AS event_id,
                                MIN(c.course_date) AS min_course_date,
                                MAX(c.course_date) AS max_course_date
                            FROM mdl_event e
                            LEFT JOIN mdl_event_course_info ec ON e.id = ec.event_id
                            LEFT JOIN mdl_course_info c ON ec.course_info_id = c.id
                            GROUP BY e.id
                        )
                        SELECT 
                            e.*,
                            CASE
                                WHEN :current_timestamp <= e.deadline - INTERVAL 5 DAY THEN 1 -- 受付中
                                WHEN :current_timestamp > e.deadline - INTERVAL 5 DAY 
                                AND :current_timestamp <= e.deadline THEN 2 -- もうすぐ締め切り
                                WHEN :current_timestamp > e.deadline THEN 3 -- 受付終了
                            END AS set_event_deadline_status,
                            (SELECT cd.course_date 
                            FROM closest_dates cd 
                            WHERE cd.event_id = e.id 
                            ORDER BY cd.time_diff ASC 
                            LIMIT 1) AS closest_course_date,
                            CASE
                                WHEN :current_timestamp < ed.min_course_date THEN 1 -- 開催前
                                WHEN :current_timestamp BETWEEN ed.min_course_date AND ed.max_course_date THEN 2 -- 開催中
                                WHEN :current_timestamp > ed.max_course_date THEN 3 -- 開催終了
                                ELSE 0
                            END AS event_status,
                            CASE
                                WHEN :current_timestamp <= (
                                    COALESCE(
                                        (SELECT cd.deadline_date FROM closest_dates cd 
                                        WHERE cd.event_id = e.id 
                                        AND cd.deadline_date >= :current_timestamp 
                                        ORDER BY cd.time_diff ASC LIMIT 1),
                                        (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                    ) - INTERVAL 5 DAY
                                ) THEN 1 -- 受付中

                                WHEN :current_timestamp > (
                                    COALESCE(
                                        (SELECT cd.deadline_date FROM closest_dates cd 
                                        WHERE cd.event_id = e.id 
                                        AND cd.deadline_date >= :current_timestamp 
                                        ORDER BY cd.time_diff ASC LIMIT 1),
                                        (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                    ) - INTERVAL 5 DAY
                                ) 
                                AND :current_timestamp <= (
                                    COALESCE(
                                        (SELECT cd.deadline_date FROM closest_dates cd 
                                        WHERE cd.event_id = e.id 
                                        AND cd.deadline_date >= :current_timestamp 
                                        ORDER BY cd.time_diff ASC LIMIT 1),
                                        (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                    )
                                ) THEN 2 -- もうすぐ締め切り

                                WHEN :current_timestamp > (
                                    COALESCE(
                                        (SELECT cd.deadline_date FROM closest_dates cd 
                                        WHERE cd.event_id = e.id 
                                        AND cd.deadline_date >= :current_timestamp 
                                        ORDER BY cd.time_diff ASC LIMIT 1),
                                        (SELECT MAX(cd.deadline_date) FROM closest_dates cd WHERE cd.event_id = e.id)
                                    )
                                ) THEN 3 -- 受付終了

                                ELSE 0
                            END AS deadline_status
                        FROM mdl_event e
                        LEFT JOIN event_dates ed ON e.id = ed.event_id
                    LEFT JOIN mdl_event_course_info eci ON eci.event_id = e.id
                    LEFT JOIN mdl_course_info ci ON eci.course_info_id = ci.id
                    LEFT JOIN mdl_event_application ea ON ea.event_id = e.id
                    WHERE e.visible = 1 AND e.id = :id
                    GROUP BY e.id
                    ORDER BY MIN(ci.course_date) ASC';

                // 動的に検索条件を追加
                $params = [
                    ':id' => $id,
                    ':current_timestamp' => $currentTimestamp
                ];

                // クエリの実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $event = $stmt->fetch(PDO::FETCH_ASSOC);

                // 各イベントの詳細を追加
                $event['lecture_formats'] = $this->getEventLectureFormats($event['id']);
                $event['categorys'] = $this->getEventCategorys($event['id']);
                $event['course_infos'] = $this->getEventCourseInfosById($courseInfoId);
                $event_status = $event['event_status'];

                return  $event;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    //イベントの総件数を取得
    public function totalCount()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM mdl_event WHERE visible = 1 ORDER BY timestart ASC");
                $stmt->execute();
                $totalCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                return $totalCount;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // ページネーション
    public function pagenate($limit, $offset)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare(
                    "SELECT * FROM mdl_event WHERE visible = 1 ORDER BY timestart ASC LIMIT :limit OFFSET :offset"
                );

                // 値をバインド
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

                $stmt->execute();
                $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $events;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }
    }
}

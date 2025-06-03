<?php
class EventApplicationCourseInfoModel extends BaseModel
{
    // コース情報IDより申し込み状況を取得
    public function getByCourseInfoId($id, $keyword, int $page = 1, int $perPage = 15)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare(
                    "SELECT id, event_application_id, course_info_id, participant_mail, participation_kbn, ticket_type
                    FROM mdl_event_application_course_info 
                    WHERE course_info_id = ? "
                );

                $stmt->execute([$id]);
                $result_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $filtered = [];
                foreach ($result_list as $result) {
                    $application = $this->getEventApplicationByApplicationId($result['event_application_id'], $keyword);
                    if (!empty(reset($application)['user'])) {
                        $result['application'] = $application;
                        $filtered[] = $result;
                    }
                }
                foreach ($filtered as &$result) {
                    $result['course_info'] = $this->getCourseInfoById($result['course_info_id']);
                }

                $result_list = $filtered;
                $offset = ($page - 1) * $perPage;
                $result_list = array_slice($result_list, $offset, $perPage);

                return $result_list;
            } catch (\PDOException $e) {
                error_log('コース情報別イベント申込取得エラー: ' . $e->getMessage() . ' CourseInfoID: ' . $id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // コース情報IDより申し込みの総件数を取得
    public function getCountByCourseInfoId($id, $keyword)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare(
                    "SELECT id, event_application_id, course_info_id, participant_mail, participation_kbn 
                    FROM mdl_event_application_course_info 
                    WHERE course_info_id = ?"
                );

                $stmt->execute([$id]);
                $result_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 各イベントの詳細を追加
                foreach ($result_list as &$result) {
                    $result['application'] = $this->getEventApplicationByApplicationId($result['event_application_id'], $keyword);
                    $result['course_info'] = $this->getCourseInfoById($result['course_info_id']);
                }

                return count($result_list);
            } catch (\PDOException $e) {
                error_log('コース情報別イベント申込取得エラー: ' . $e->getMessage() . ' CourseInfoID: ' . $id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return 0;
    }

    // コース情報IDより申し込み状況を取得
    public function getByEventEventId($id, $keyword, int $page = 1, int $perPage = 15)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare(
                    "SELECT eaci.id, event_id, event_application_id, course_info_id, participant_mail, participation_kbn, ticket_type, payment_kbn, e.event_kbn 
                    FROM mdl_event_application_course_info eaci
                    LEFT JOIN mdl_event e ON event_id = e.id
                    WHERE event_id = ? "
                );

                $stmt->execute([$id]);
                $result_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $filtered = [];
                foreach ($result_list as $result) {
                    $application = $this->getEventApplicationByApplicationId($result['event_application_id'], $keyword);
                    if (!empty(reset($application)['user'])) {
                        $result['application'] = $application;
                        $filtered[] = $result;
                    }
                }
                foreach ($filtered as &$result) {
                    $result['course_info'] = $this->getCourseInfoById($result['course_info_id']);
                }

                $result_list = $filtered;
                $offset = ($page - 1) * $perPage;
                $result_list = array_slice($result_list, $offset, $perPage);

                return $result_list;
            } catch (\PDOException $e) {
                error_log('イベント別申込取得エラー: ' . $e->getMessage() . ' EventID: ' . $id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // コース情報IDより申し込み状況の総件数を取得
    public function getCountByEventEventId($id, $keyword)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare(
                    "SELECT eaci.id, event_id, event_application_id, course_info_id, participant_mail, participation_kbn, e.event_kbn 
                    FROM mdl_event_application_course_info eaci
                    LEFT JOIN mdl_event e ON event_id = e.id
                    WHERE event_id = ?"
                );

                $stmt->execute([$id]);
                $result_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 各イベントの詳細を追加
                foreach ($result_list as &$result) {
                    $result['application'] = $this->getEventApplicationByApplicationId($result['event_application_id'], $keyword);
                    $result['course_info'] = $this->getCourseInfoById($result['course_info_id']);
                }

                return count($result_list);
            } catch (\PDOException $e) {
                error_log('イベント別申込取得エラー: ' . $e->getMessage() . ' EventID: ' . $id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return 0;
    }

    // イベント申し込み情報を取得
    private function getEventApplicationByApplicationId($event_application_id, $keyword)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_application WHERE id = :event_application_id");
                $stmt->bindParam(':event_application_id', $event_application_id, PDO::PARAM_INT);
                $stmt->execute();
                $application_result_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 各イベントの詳細を追加
                foreach ($application_result_list as &$application_result) {
                    $application_result['user'] = $this->getUserByUserId($application_result['user_id'], $keyword);
                    $application_result['event'] = $this->getEventById($application_result['event_id']);
                }

                return $application_result_list;
            } catch (\PDOException $e) {
                error_log('イベント申込ID別詳細取得エラー: ' . $e->getMessage() . ' ApplicationID: ' . $event_application_id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // イベント参加ユーザー情報を取得
    private function getUserByUserId($user_id, $keyword)
    {
        if ($this->pdo) {
            try {
                // ベースとなる SQL
                $sql = "SELECT id, name, email, child_name, birthday, guardian_name, phone1 FROM mdl_user";
                $where = [];
                $params = [];

                // ID が指定されている場合
                if (!empty($user_id)) {
                    $where[] = "id = :id";
                    $params[':id'] = $user_id;
                }

                // キーワードが指定されている場合
                if (!empty($keyword)) {
                    $where[] = "(name LIKE :keyword OR id = :id_keyword)";
                    $params[':keyword'] = "%{$keyword}%";
                    $params[':id_keyword'] = (int) $keyword;
                }

                // WHERE 句を追加（条件がある場合のみ）
                if (!empty($where)) {
                    $sql .= " WHERE " . implode(" AND ", $where);
                }

                // SQL を prepare して実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);

                $user_result_list = $stmt->fetch(PDO::FETCH_ASSOC); // 複数件取得

                return $user_result_list;
            } catch (\PDOException $e) {
                error_log('ユーザー情報取得エラー: ' . $e->getMessage() . ' UserID: ' . $user_id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // 講座回数を取得
    private function getCourseInfoById($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT no, course_date FROM mdl_course_info WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $user_result_list = $stmt->fetch(PDO::FETCH_ASSOC);

                return $user_result_list;
            } catch (\PDOException $e) {
                error_log('コース情報取得エラー: ' . $e->getMessage() . ' ID: ' . $id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // イベント名を取得
    private function getEventById($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT 
                name, 
                real_time_distribution_url, 
                event_kbn,
                venue_name,
                start_event_date,
                end_event_date,
                start_hour,
                end_hour FROM mdl_event WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $event_result_list = $stmt->fetch(PDO::FETCH_ASSOC);

                return $event_result_list;
            } catch (\PDOException $e) {
                error_log('イベント情報取得エラー: ' . $e->getMessage() . ' ID: ' . $id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }
    }

    // ユーザーごとのお連れ様のメールアドレスを取得
    public function getByApplicationId($event_application_id, $course_info_id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("
                    SELECT participant_mail 
                    FROM mdl_event_application_course_info 
                    WHERE event_application_id = :event_application_id 
                    AND course_info_id = :course_info_id
                ");
                $stmt->bindParam(':event_application_id', $event_application_id, PDO::PARAM_INT);
                $stmt->bindParam(':course_info_id', $course_info_id, PDO::PARAM_INT);
                $stmt->execute();
                $email_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $email_list;
            } catch (\PDOException $e) {
                error_log('申込ID別参加者メール取得エラー: ' . $e->getMessage() . ' ApplicationID: ' . $event_application_id . ' CourseInfoID: ' . $course_info_id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }
    }

    // 申し込み～コース中間テーブルIDより申し込み状況を取得
    public function getByEventApplicationCouresInfoId($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare(
                    "SELECT eaci.id, eaci.event_id, eaci.course_info_id, eaci.participation_kbn, ci.no AS no, ci.course_date, e.end_hour
                    FROM mdl_event_application_course_info eaci
                    JOIN mdl_course_info ci ON eaci.course_info_id = ci.id
                    JOIN mdl_event e ON eaci.event_id = e.id
                    WHERE eaci.id = ?"
                );

                $stmt->execute([$id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                return $result;
            } catch (\PDOException $e) {
                error_log('イベント申込コース情報取得エラー: ' . $e->getMessage() . ' ID: ' . $id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // ユーザIDとイベントIDから、本人のレコードを取得
    public function getFirstByUserIdAndEventId(int $user_id, int $course_info_id): array
    {
        $sql = "SELECT eaci.* 
                    FROM mdl_event_application_course_info eaci 
                    JOIN mdl_event_application ea ON eaci.event_application_id = ea.id 
                    WHERE ea.user_id = ? AND eaci.course_info_id = ? AND eaci.ticket_type = ? 
                    AND ( ea.payment_date IS NOT NULL OR ea.pay_method = 4 ) 
                    ";

        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare($sql);

                $stmt->execute([$user_id, $course_info_id, TICKET_TYPE['SELF']]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                return $result ?: [];
            } catch (\PDOException $e) {
                error_log('イベント申込コース情報取得エラー: ' . $e->getMessage() . ' UserID: ' . $user_id . ' EventID: ' . $course_info_id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // updateを行う
    public function update(int $id, array $data)
    {
        $sql = ' UPDATE mdl_event_application_course_info SET ';

        $updateColumns = [];
        $params = [];

        if (isset($data['participant_mail'])) {
            $updateColumns[] = " participant_mail = :participant_mail ";
            $params[':participant_mail'] = $data['participant_mail'];
        }
        if (isset($data['participation_kbn'])) {
            $updateColumns[] = " participation_kbn = :participation_kbn ";
            $params[':participation_kbn'] = $data['participation_kbn'];
        }
        if (isset($data['ticket_type'])) {
            $updateColumns[] = " ticket_type = :ticket_type ";
            $params[':ticket_type'] = $data['ticket_type'];
        }

        if (empty($updateColumns)) {
            throw new Exception('更新するカラムが指定されていません');
        }

        $sql .= ' ' . implode(',', $updateColumns) . ' WHERE id = :id ';
        $params[':id'] = $id;

        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare($sql);

                // 実行
                $stmt->execute($params);

                return $stmt->rowCount();
            } catch (\PDOException $e) {
                error_log('updateエラー: ' . $e->getMessage() . ' ID: ' . $id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }
    }
}

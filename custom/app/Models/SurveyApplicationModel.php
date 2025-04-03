<?php
class SurveyApplicationModel extends BaseModel
{
    // アンケート回答を取得するメソッド
    public function getSurveyApplications($course_info_id, $event_id, int $page = 1, int $perPage = 15)
    {
        if ($this->pdo) {
            try {

                $offset = ($page - 1) * $perPage; // OFFSETの計算
                $query = "SELECT * FROM mdl_survey_application";
                $params = [];
                $conditions = [];

                if (!empty($course_info_id)) {
                    $conditions[] = "course_info_id = :course_info_id";
                    $params[':course_info_id'] = (int)$course_info_id;
                }
                if (!empty($event_id)) {
                    $conditions[] = "event_id = :event_id";
                    $params[':event_id'] = (int)$event_id;
                }
                if (!empty($conditions)) {
                    $query .= " WHERE " . implode(" AND ", $conditions);
                }
                $query .= " LIMIT :perPage OFFSET :offset";
                $stmt = $this->pdo->prepare($query);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                }
                $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

                $stmt->execute();
                $result_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($result_list as &$result) {
                    $result['course_info'] = $this->getCourseInfoById($result['course_info_id']);
                    $result['event'] = $this->getEventById($result['event_id']);
                }
                return $result_list;
            } catch (\PDOException $e) {
                error_log('アンケート回答一覧取得エラー: ' . $e->getMessage() . ' CourseInfoID: ' . $course_info_id . ' EventID: ' . $event_id);
                echo 'データの取得に失敗しました';
            }
            return [];
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
                $stmt = $this->pdo->prepare("SELECT no FROM mdl_course_info WHERE id = :id");
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
    // 開始時間と終了時間を取得
    private function getEventById($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT start_hour, end_hour, name FROM mdl_event WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $event_list = $stmt->fetch(PDO::FETCH_ASSOC);

                return $event_list;
            } catch (\PDOException $e) {
                error_log('イベント時間情報取得エラー: ' . $e->getMessage() . ' ID: ' . $id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // アンケート回答の総数を取得
    public function getCountSurveyApplications($course_info_id, $event_id)
    {
        if ($this->pdo) {
            try {
                $query = "SELECT COUNT(*) AS count FROM mdl_survey_application";
                $params = [];
                $conditions = [];

                if (!empty($course_info_id)) {
                    $conditions[] = "course_info_id = :course_info_id";
                    $params[':course_info_id'] = (int)$course_info_id;
                }
                if (!empty($event_id)) {
                    $conditions[] = "event_id = :event_id";
                    $params[':event_id'] = (int)$event_id;
                }
                if (!empty($conditions)) {
                    $query .= " WHERE " . implode(" AND ", $conditions);
                }

                $stmt = $this->pdo->prepare($query);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                }
                $stmt->execute();
                $count = $stmt->fetchColumn();

                return $count;
            } catch (\PDOException $e) {
                error_log('アンケート回答件数取得エラー: ' . $e->getMessage() . ' CourseInfoID: ' . $course_info_id . ' EventID: ' . $event_id);
                echo 'データの取得に失敗しました';
            }

            return 0;
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }
    }
}

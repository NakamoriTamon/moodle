<?php
class EventApplicationCourseInfoModel extends BaseModel
{
    // コース情報IDより申し込み状況を取得
    public function getByCourseInfoId($id, $keyword, int $page = 1, int $perPage = 15)
    {
        if ($this->pdo) {
            try {
                $offset = ($page - 1) * $perPage;
                $stmt = $this->pdo->prepare(
                    "SELECT id, event_application_id, course_info_id, participant_mail, participation_kbn 
                    FROM mdl_event_application_course_info 
                    WHERE course_info_id = ? 
                    LIMIT $perPage OFFSET $offset"
                );

                $stmt->execute([$id]);
                $result_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 各イベントの詳細を追加
                foreach ($result_list as &$result) {
                    $result['application'] = $this->getEventApplicationByApplicationId($result['event_application_id'], $keyword);
                    $result['course_info'] = $this->getCourseInfoById($result['course_info_id']);
                }

                return $result_list;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
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
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
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
                $sql = "SELECT id, name, email, child_name FROM mdl_user";
                $where = [];
                $params = [];

                // ID が指定されている場合
                if (!empty($user_id)) {
                    $where[] = "id = :id";
                    $params[':id'] = $user_id;
                }

                // キーワードが指定されている場合
                if (!empty($keyword)) {
                    $where[] = "name LIKE :keyword";
                    $params[':keyword'] = "%{$keyword}%";
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
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
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
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // イベント名を取得
    private function getEventById($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT name FROM mdl_event WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $event_result_list = $stmt->fetch(PDO::FETCH_ASSOC);

                return $event_result_list;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }
    }
}

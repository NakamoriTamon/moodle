<?php
class EventApplicationModel extends BaseModel
{
    // 各イベントごとのアンケートカスタムフィールドを取得
    public function getEventApplicationByEventId($id = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_application WHERE id = ?");
                $stmt->execute([$id]);
                $eventApplication = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $eventApplication["course_infos"] = $this->getEventApplicationCourseInfos($id);

                return $eventApplication;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // 各イベントごとのアンケートカスタムフィールドを取得
    public function getSumTicketCountByEventId($event_id = null, $course_info_id = null, $limit_flg = false)
    {
        if ($this->pdo) {
            try {
                $sql = 'WITH event_application_counts AS (
                            SELECT 
                                eac.course_info_id,
                                eac.event_id,
                                COUNT(eac.course_info_id) AS total_tickets
                            FROM mdl_event_application_course_info eac
                            JOIN mdl_event_application ea ON eac.event_application_id = ea.id
                            GROUP BY eac.course_info_id, eac.event_id
                        )
                        SELECT 
                            ci.id AS course_info_id,
                            ci.no AS course_no,
                            ci.course_date,
                            e.id AS event_id,
                            e.capacity,
                            COALESCE(eac.total_tickets, 0) AS applied_tickets,
                            (e.capacity - COALESCE(eac.total_tickets, 0)) AS available_tickets
                        FROM mdl_course_info ci
                        LEFT JOIN event_application_counts eac ON eac.course_info_id = ci.id
                        JOIN mdl_event e ON e.id = eac.event_id';
                
                $where = ' WHERE e.id = :event_id';
                $params[':event_id'] = $event_id;
                if (!empty($course_info_id)) {
                    $where .= ' AND ci.id = :course_info_id';
                    $params[':course_info_id'] = $course_info_id;
                }
                if($limit_flg) {
                    $where .= ' ORDER BY available_tickets ASC LIMIT 1';
                }
                $sql .= $where;
                
                // クエリの実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);

                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // ユーザIDからイベントを取得
    public function getEventApplicationByUserId($userId = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT ea.*, e.id as eventid, e.name as event_name , e.* FROM mdl_event_application ea
                LEFT JOIN mdl_event e ON e.id = ea.event_id 
                WHERE user_id = ?
                AND DATE(e.event_date) >= CURDATE()");
                $stmt->execute([$userId]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // ユーザIDからイベントを取得
    public function getOldEventApplicationByUserId($userId = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT ea.*, e.id as eventid, e.name as event_name , e.* FROM mdl_event_application ea
                LEFT JOIN mdl_event e ON e.id = ea.event_id 
                WHERE user_id = ?
                AND DATE(e.event_date) < CURDATE()");
                $stmt->execute([$userId]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // バッチ用 ユーザIDからイベントを取得
    public function getEventApplicationByPaymentKbn_Zero()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_application
                WHERE payment_kbn = 0");
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                return 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            return "データの取得に失敗しました";
        }

        return [];
    }

    // イベントIDに基づいて講座を取得
    private function getEventApplicationCourseInfos($eventApplicationID)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT eaci.id, ci.no, ci.course_date, eaci.course_info_id, eaci.participant_mail, eaci.participation_kbn FROM mdl_event_application_course_info eaci 
                    LEFT JOIN mdl_course_info ci ON ci.id = eaci.course_info_id WHERE eaci.event_application_id = :eventApplicationID");
                $stmt->bindParam(':eventApplicationID', $eventApplicationID, PDO::PARAM_INT);
                $stmt->execute();
                $course_infos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $course_infos;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }
}

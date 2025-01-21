<?php
class EventModel extends BaseModel
{
    // イベントを全件取得
    public function getEvents()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event WHERE visible = 1 ORDER BY timestart ASC");
                $stmt->execute();
                $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 各イベントの詳細を追加
                foreach ($events as &$event) {
                    $event['details'] = $this->getEventDetails($event['id']);
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

    // イベントIDに基づいてイベント詳細を取得
    private function getEventDetails($eventID)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_each WHERE event_id = :eventID");
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
                $stmt = $this->pdo->prepare("SELECT ci.id as course_info_id, ci.no FROM mdl_event_course_info eci 
                    LEFT JOIN mdl_course_info ci ON ci.id = eci.course_info_id WHERE event_id = :eventID");
                $stmt->bindParam(':eventID', $eventID, PDO::PARAM_INT);
                $stmt->execute();
                $course_infos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($course_infos as $course_info) {
                    $course_info["details"] = $this->getEventCourseInfoDetails($course_info["course_info_id"]);
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
                $stmt = $this->pdo->prepare("SELECT cid.tutor_id, cid.name, cid.program  FROM mdl_course_info_detail cid
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
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event WHERE id = ? AND visible = 1 ORDER BY timestart ASC");
                $stmt->execute([$id]);
                $event = $stmt->fetch(PDO::FETCH_ASSOC);
                $event['details'] = $this->getEventDetails($event['id']);
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

                // 各イベントの詳細を追加
                foreach ($events as &$event) {
                    $event['details'] = $this->getEventDetails($event['id']);
                }

                return $events;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }
    }
}

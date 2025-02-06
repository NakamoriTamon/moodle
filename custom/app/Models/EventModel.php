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
                foreach ($events as $key => $event) {
                    if (isset($event['event_date'])) {
                        $dateTime = new DateTime($event['event_date']);
                        $formattedStartDate = $dateTime->format('Y年n月j日');
                        $events[$key]['event_date_formatted'] = $formattedStartDate;
                    }

                    if (isset($event['start_hour']) && isset($event['end_hour'])) {
                        $startTime = new DateTime($event['start_hour']);
                        $endTime = new DateTime($event['end_hour']);
                        $formattedTimeRange = $startTime->format('H:i') . '～' . $endTime->format('H:i');
                        $events[$key]['time_range'] = $formattedTimeRange;
                    }

                    // $details = $this->getEventDetails($event['id']);
                    // if (!empty($details)) {
                    //     $events[$key] = array_merge($events[$key], $details[0]);
                    // }
                    // カラム名'id'が被っていて、上手くidが取得できないのでコメントアウト
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

    // イベント単件取得
    public function getEventById($id = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event WHERE id = ? AND visible = 1 ORDER BY timestart ASC");
                $stmt->execute([$id]);
                $event = $stmt->fetch(PDO::FETCH_ASSOC);

                if (isset($event['event_date'])) {
                    $dateTime = new DateTime($event['event_date']);
                    $formattedStartDate = $dateTime->format('Y年n月j日');
                    $event['event_date_formatted'] = $formattedStartDate;
                }

                if (isset($event['start_hour']) && isset($event['end_hour'])) {
                    $startTime = new DateTime($event['start_hour']);
                    $endTime = new DateTime($event['end_hour']);
                    $formattedTimeRange = $startTime->format('H:i') . '～' . $endTime->format('H:i');
                    $event['time_range'] = $formattedTimeRange;
                }

                if (isset($event['deadline'])) {
                    $dateTime = new DateTime($event['deadline']);
                    $formattedStartDate = $dateTime->format('Y年n月j日 H:i');
                    $event['deadline_formatted'] = $formattedStartDate;
                }

                // $details = $this->getEventDetails($event['id']);
                // if (!empty($details)) {
                //     $event[$key] = array_merge($event[$key], $details[0]);
                // }
                // カラム名'id'が被っていて、上手くidが取得できないのでコメントアウト

                return $event;
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

                // // 各イベントの詳細を追加
                // foreach ($events as &$event) {
                //     $event['details'] = $this->getEventDetails($event['id']);
                // }

                // 各イベントの詳細を追加
                foreach ($events as $key => $event) {
                    if (isset($event['event_date'])) {
                        $dateTime = new DateTime($event['event_date']);
                        $formattedStartDate = $dateTime->format('Y年n月j日');
                        $events[$key]['event_date_formatted'] = $formattedStartDate;
                    }

                    if (isset($event['start_hour']) && isset($event['end_hour'])) {
                        $startTime = new DateTime($event['start_hour']);
                        $endTime = new DateTime($event['end_hour']);
                        $formattedTimeRange = $startTime->format('H:i') . '～' . $endTime->format('H:i');
                        $events[$key]['time_range'] = $formattedTimeRange;
                    }

                    // $details = $this->getEventDetails($event['id']);
                    // if (!empty($details)) {
                    //     $events[$key] = array_merge($events[$key], $details[0]);
                    // }
                    // カラム名'id'が被っていて、上手くidが取得できないのでコメントアウト
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

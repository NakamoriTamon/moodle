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
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }
    
    // 各イベントごとのアンケートカスタムフィールドを取得
    public function getSumTicketCountByEventId($eventId = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT SUM(ticket_count) as sum_ticket_count FROM mdl_event_application WHERE event_id = ?");
                $stmt->execute([$eventId]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
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

    // カスタムフィールド登録
    public function insertEventCustomField($eventId, $fieldName, $name, $sort, $fieldType, $fieldOptions) {}
}

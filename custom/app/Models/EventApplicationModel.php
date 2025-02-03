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

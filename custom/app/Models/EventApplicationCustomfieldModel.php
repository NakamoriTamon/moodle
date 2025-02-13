<?php
class EventApplicationCustomfieldModel extends BaseModel
{
    // 各イベントごとのアンケートカスタムフィールドを取得
    public function getEventApplicationCustomfieldByEventApplicationId($event_application_id = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_application_customfield WHERE event_application_id = ?");
                $stmt->execute([$event_application_id]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }
}
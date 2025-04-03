<?php
class EventApplicationCompanionModel extends BaseModel
{
    // カテゴリを全件取得
    public function getByEventApplicationId($eventApplicationId)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_application_companion WHERE event_application_id = ? ORDER BY id ASC");
                $stmt->execute([$eventApplicationId]);
                $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $list;
            } catch (\PDOException $e) {
                error_log('イベント申込同伴者取得エラー: ' . $e->getMessage() . ' EventApplicationID: ' . $eventApplicationId);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }
}
?>
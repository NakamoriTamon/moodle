<?php

class EventFilesModel extends BaseModel
{
    // IDから講義資料を取得
    public function getEventFiles($eventId = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_files WHERE event_id = ?");
                $stmt->execute([$eventId]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log('イベント資料取得エラー: ' . $e->getMessage() . ' EventID: ' . $eventId);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }
}

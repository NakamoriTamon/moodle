<?php
class EventCustomFieldModel extends BaseModel
{
    // カスタムフィールドカテゴリIDからカスタムフィールドを取得
    public function getCustomFieldById($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_customfield WHERE event_customfield_category_id = ? AND is_delete = false ORDER BY sort, id");
                $stmt->execute([$id]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log('カスタムフィールド取得エラー: ' . $e->getMessage() . ' カテゴリID: ' . $id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }
        return [];
    }

    // 対象以外のIDであるカスタムフィールドを取得
    public function getNotCustomFieldByFieldId($id, $event_customfield_id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_customfield WHERE event_customfield_category_id = ? AND is_delete = false AND id != ? ORDER BY sort, id");
                $stmt->execute([$id, $event_customfield_id]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log('対象外カスタムフィールド取得エラー: ' . $e->getMessage() . ' カテゴリID: ' . $id . ' 除外ID: ' . $event_customfield_id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }
        return [];
    }
}

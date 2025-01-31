<?php
class EventCustomFieldModel extends BaseModel
{
    // カスタムフィールドカテゴリIDからカスタムフィールドを取得
    public function getCustomFieldById($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_customfield WHERE event_customfield_category_id = ? AND is_delete = false");
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

    // 対象以外のIDであるカスタムフィールドを取得
    public function getNotCustomFieldByFieldId($id, $event_customfield_id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_customfield WHERE event_customfield_category_id = ? AND is_delete = false AND id != ?");
                $stmt->execute([$id, $event_customfield_id]);
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

<?php
class EventCustomFieldCategoryModel extends BaseModel
{
    // カスタムフィールド区分を全件取得
    public function getCustomFieldCategory()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_customfield_category WHERE is_delete = false");
                $stmt->execute();
                $custom_field_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 各イベントの詳細を追加
                foreach ($custom_field_categories as &$custom_field_category) $custom_field_category['event'] = $this->getEventById($custom_field_category['id']);

                return $custom_field_categories;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // カスタムフィールドカテゴリ区分IDに基づいてイベント情報を取得
    private function getEventById($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT id, name FROM mdl_event WHERE event_customfield_category_id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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

    // カスタムフィールド区分を単件取得
    public function findCustomFieldCategory($id = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_customfield_category WHERE id = $id");
                $stmt->execute();
                $custom_field_categorys = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 各イベントの詳細を追加
                foreach ($custom_field_categorys as &$custom_field_category) $custom_field_category['detail']
                    = $this->getEventCustomField($custom_field_category['id']);

                return $custom_field_category;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // 自身以外のカテゴリー情報を取得
    public function getCustomFieldCategoryNotId($id = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_customfield_category WHERE id != $id AND is_delete = false");
                $stmt->execute();
                $custom_field_categorys = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $custom_field_categorys;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // カスタムフィールドカテゴリ区分IDに基づいてフィールド情報を取得
    private function getEventCustomField($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_customfield WHERE event_customfield_category_id = :id AND is_delete = False");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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
}

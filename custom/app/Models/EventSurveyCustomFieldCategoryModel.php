<?php
class EventSurveyCustomFieldCategoryModel extends BaseModel
{
    // カスタムフィールド区分を全件取得
    public function getSurveyCustomFieldCategory()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_survey_customfield_category WHERE is_delete = false");
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
    public function findSurveyCustomFieldCategory($id = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_survey_customfield_category WHERE id = $id");
                $stmt->execute();
                $custom_field_categorys = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 各イベントの詳細を追加
                foreach ($custom_field_categorys as &$custom_field_category) $custom_field_category['detail']
                    = $this->getEventSurveyCustomField($custom_field_category['id']);

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
    public function getSurveyCustomFieldCategoryNotId($id = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_survey_customfield_category WHERE id != $id AND is_delete = false");
                $stmt->execute();
                $survey_custom_field_categorys = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $survey_custom_field_categorys;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }
    

    // カスタムフィールドカテゴリ区分IDに基づいてフィールド情報を取得
    private function getEventSurveyCustomField($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_survey_customfield WHERE event_survey_customfield_category_id = :id AND is_delete = False ORDER BY sort, id");
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
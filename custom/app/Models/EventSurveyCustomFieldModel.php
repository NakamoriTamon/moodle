<?php
class EventSurveyCustomFieldModel extends BaseModel
{
    // カスタムフィールド区分を単件取得
    public function findSurveyCustomFieldCategory($id = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_survey_customfield_category WHERE id = $id");
                $stmt->execute();
                $survey_custom_field_categorys = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 各イベントの詳細を追加
                foreach ($survey_custom_field_categorys as &$survey_custom_field_category) $survey_custom_field_category['detail']
                    = $this->getEventSurveyCustomField($survey_custom_field_category['id']);

                return $survey_custom_field_category;
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
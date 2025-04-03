<?php
class EventSurveyCustomFieldModel extends BaseModel
{
    // カスタムフィールドカテゴリIDからカスタムフィールドを取得
    public function getEventSurveyCustomFieldById($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_event_survey_customfield WHERE event_survey_customfield_category_id = ? AND is_delete = false ORDER BY sort, id");
                $stmt->execute([$id]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log('イベントアンケートカスタムフィールド取得エラー: ' . $e->getMessage() . ' カテゴリID: ' . $id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
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
                error_log('イベントアンケートカスタムフィールド内部取得エラー: ' . $e->getMessage() . ' カテゴリID: ' . $id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }
}
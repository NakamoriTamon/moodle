<?php
class SurveyApplicationCustomfieldModel extends BaseModel
{
    // 各イベントごとのアンケートカスタムフィールドを取得
    public function getESurveyApplicationCustomfieldBySurveyApplicationId($survey_application_id = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_survey_application_customfield WHERE survey_application_id = ?");
                $stmt->execute([$survey_application_id]);
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
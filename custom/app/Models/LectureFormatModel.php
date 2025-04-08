<?php
class LectureFormatModel extends BaseModel
{
    // is_delete：0のカテゴリーを全件取得
    public function getLectureFormats()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_lecture_format WHERE is_delete = 0 ORDER BY id ASC");
                $stmt->execute();
                $lectureFormats = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $lectureFormats;
            } catch (\PDOException $e) {
                error_log('講義形式一覧取得エラー: ' . $e->getMessage());
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
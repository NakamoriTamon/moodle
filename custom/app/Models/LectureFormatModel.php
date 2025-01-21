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
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        }

        return [];
    }
}
?>
<?php
class MovieModel extends BaseModel
{
    // イベントを全件取得
    public function getMovieByInfoId($course_info_id = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_course_movie WHERE course_info_id = ?");
                $stmt->execute([$course_info_id]);

                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log('講義動画取得エラー: ' . $e->getMessage() . ' course_info_id: ' . $course_info_id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }
}

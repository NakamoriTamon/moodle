<?php
class MovieModel extends BaseModel
{
    // イベントを全件取得
    public function getMovieByInfoId($course_info_id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_course_movie");
                $stmt->execute();

                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }
}

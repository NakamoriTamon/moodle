<?php
class TutorModel extends BaseModel
{
    // is_delete：0のカテゴリーを全件取得
    public function getTutors()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_tutor WHERE is_delete = 0 ORDER BY id ASC");
                $stmt->execute();
                $tutors = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $tutors;
            } catch (\PDOException $e) {
                error_log('チューター一覧取得エラー: ' . $e->getMessage());
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }

    public function getTutorsById($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_tutor WHERE is_delete = 0 AND id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                $tutors = $stmt->fetch(PDO::FETCH_ASSOC);

                return $tutors;
            } catch (\PDOException $e) {
                error_log('チューター取得エラー: ' . $e->getMessage() . ' ID: ' . $id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }
}

<?php
class TutorModel extends BaseModel
{
    // 講師を全件取得
    public function getToturs()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_tutor WHERE is_delete = 0 ORDER BY id ASC");
                $stmt->execute();
                $tutors = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $tutors;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // 講師IDに基づいて講師詳細を取得
    private function getToturDetails($tutorID)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_tutor WHERE tutor_id = :tutorID");
                $stmt->bindParam(':tutorID', $tutorID, PDO::PARAM_INT);
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

    // 講師単件取得
    public function getToturById($id = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_tutor WHERE id = ? AND is_delete = 0 ORDER BY id ASC");
                $stmt->execute([$id]);
                $tutor = $stmt->fetch(PDO::FETCH_ASSOC);
                $tutor['details'] = $this->getEventDetails($tutor['id']);
                return  $tutor;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // 講師の総件数を取得
    public function totalCount()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM mdl_tutor WHERE is_delete = 0 ORDER BY id ASC");
                $stmt->execute();
                $totalCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                return $totalCount;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }
}

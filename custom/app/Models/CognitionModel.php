<?php
class CognitionModel extends BaseModel
{
    // is_delete：0のカテゴリーを全件取得
    public function getCognition()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_cognition WHERE is_delete = 0 ORDER BY id ASC");
                $stmt->execute();
                $cognitions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $cognitions;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        }

        return [];
    }

    // idとis_delete：0から対象のカテゴリーを取得
    public function getCognitionByIds($ids)
    {
        if ($this->pdo) {
            try {
                $placeholders = implode(',', array_fill(0, count($ids), '?')); // ?, ?, ?
                $sql = "SELECT * FROM mdl_cognition WHERE is_delete = 0 AND id IN ($placeholders) ORDER BY id ASC";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($ids);
                $cognitions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $cognitions;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }
}
?>
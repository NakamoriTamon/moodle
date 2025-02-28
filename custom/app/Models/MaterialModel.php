<?php
class MaterialModel extends BaseModel
{
    // 資料を全件取得
    public function getMaterials()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_course_material");
                $stmt->execute();
                $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $materials;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }
}

<?php
class CategoryModel extends BaseModel
{
    // is_delete：0のカテゴリーを全件取得
    public function getCategorys()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_category WHERE is_delete = 0 ORDER BY id ASC");
                $stmt->execute();
                $categorys = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $categorys;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        }

        return [];
    }
}
?>
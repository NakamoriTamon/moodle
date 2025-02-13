<?php
class CategoryModel extends BaseModel
{
    // カテゴリを全件取得
    public function getCategories()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_category WHERE is_delete = 0 ORDER BY id ASC");
                $stmt->execute();
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $categories;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // カテゴリ単権取得 
    public function find($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_category WHERE id = ? AND is_delete = 0 ORDER BY id ASC");
                $stmt->execute([$id]);
                $category = $stmt->fetch(PDO::FETCH_ASSOC);
                return  $category;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました';
            }
        } else {
            echo "データの取得に失敗しました";
        }
        return [];
    }

    // カテゴリの総件数を取得
    public function totalCount()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM mdl_category WHERE is_delete = 0 ORDER BY id ASC");
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

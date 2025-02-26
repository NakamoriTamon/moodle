<?php
class MaterialModel extends BaseModel
{
    // 資料を全件取得
    public function getMaterials()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_material WHERE is_delete = 1 ORDER BY timestart ASC");
                $stmt->execute();
                $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 各資料の詳細を追加
                foreach ($materials as &$material) $material['details'] = $this->getMaterialDetails($material['id']);

                return $materials;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // 資料IDに基づいて資料詳細を取得
    private function getMaterialDetails($materialID)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_material_each WHERE material_id = :materialID");
                $stmt->bindParam(':materialID', $materialID, PDO::PARAM_INT);
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

    // 資料単件取得
    public function getMaterialById($id = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_material WHERE id = ? AND is_delete = 1 ORDER BY timestart ASC");
                $stmt->execute([$id]);
                $material = $stmt->fetch(PDO::FETCH_ASSOC);
                $material['details'] = $this->getMaterialDetails($material['id']);
                return  $material;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    //資料の総件数を取得
    public function totalCount()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM mdl_material WHERE is_delete = 1 ORDER BY timestart ASC");
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

    // ページネーション
    public function pagenate($limit, $offset)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare(
                    "SELECT * FROM mdl_material WHERE is_delete = 1 ORDER BY timestart ASC LIMIT :limit OFFSET :offset"
                );

                // 値をバインド
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

                $stmt->execute();
                $materials = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 各資料の詳細を追加
                foreach ($materials as &$material) {
                    $material['details'] = $this->getMaterialDetails($material['id']);
                }

                return $materials;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }
    }
}

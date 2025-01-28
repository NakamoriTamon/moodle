<?php
class TargetModel extends BaseModel
{
    // 対象を全件取得
    public function getTargets()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_target WHERE is_delete = 0 ORDER BY id ASC");
                $stmt->execute();
                $targets = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // 各対象の詳細を追加
                foreach ($targets as &$target) $target['details'] = $this->getTargetDetails($target['id']);

                return $targets;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    // 対象IDに基づいて対象詳細を取得
    private function getTargetDetails($targetID)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_target WHERE id = :targetID");
                $stmt->bindParam(':targetID', $targetID, PDO::PARAM_INT);
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

    // 対象単件取得
    public function getTargetById($id = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_target WHERE id = ? AND is_delete = 0 ORDER BY id ASC");
                $stmt->execute([$id]);
                $target = $stmt->fetch(PDO::FETCH_ASSOC);
                $target['details'] = $this->getTargetDetails($target['id']);
                return  $target;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }

    //対象の総件数を取得
    public function totalCount()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM mdl_target WHERE is_delete = 0 ORDER BY id ASC");
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

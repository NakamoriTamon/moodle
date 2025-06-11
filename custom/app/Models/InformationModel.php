<?php

class InformationModel extends BaseModel
{
    // お知らせを全件取得
    public function getAllInformation( $filters = [], $page = 1,  $perPage = 15)
    {
        if ($this->pdo) {
            try {
                 $offset = ($page - 1) * $perPage;
            $params = [];
                $where = '';

                if (!empty($filters['keyword'])) {
                    $where = 'WHERE title LIKE :keyword';
                    $params[':keyword'] = '%' . $filters['keyword'] . '%';
                }

                $sql = "SELECT * FROM mdl_information $where ORDER BY id ASC LIMIT :limit OFFSET :offset";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

                // キーワードがある場合はバインド
                if (!empty($filters['keyword'])) {
                    $stmt->bindValue(':keyword', $params[':keyword'], PDO::PARAM_STR);
                }

                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log('お知らせ取得エラー: ' . $e->getMessage());
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }
    // お知らせの総件数を取得
    public function getInformationCount ($filters = [])
    {
        if ($this->pdo) {
            try {
                $where = '';
                $params = [];

                if (!empty($filters['keyword'])) {
                    $where = 'WHERE title LIKE :keyword';
                    $params[':keyword'] = '%' . $filters['keyword'] . '%';
                }

                $sql = "SELECT COUNT(*) FROM mdl_information $where";
                $stmt = $this->pdo->prepare($sql);

                if (!empty($filters['keyword'])) {
                    $stmt->bindValue(':keyword', $params[':keyword'], PDO::PARAM_STR);
                }

                $stmt->execute();
                return $stmt->fetchColumn();
            } catch (\PDOException $e) {
                error_log('お知らせ件数取得エラー: ' . $e->getMessage());
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }
        return 0;
    }
    // お知らせ単体取得
    public function find($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_information WHERE id = ?");
                $stmt->execute([$id]);
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log('お知らせ単体取得エラー: ' . $e->getMessage() . ' ID: ' . $id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }
        return [];
    }
}

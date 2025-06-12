<?php

class InformationModel extends BaseModel
{
    // お知らせを全件取得
   public function getAllInformation($filters = [], $page = 1, $perPage = 15)
    {
        if ($this->pdo) {
            try {
                $offset = ($page - 1) * $perPage;
                $params = [];
                $where = [];
                $sqlWhere = '';

                // キーワード検索
                if (!empty($filters['keyword'])) {
                    $where[] = 'title LIKE :keyword';
                    $params[':keyword'] = '%' . $filters['keyword'] . '%';
                }

                // 掲載開始日・終了日による絞り込み（limit指定時のみ）
                if (!empty($filters['limit'])) {
                    $where[] = '(publish_start_at IS NULL OR publish_start_at <= :now)';
                    $params[':now'] = date('Y-m-d H:i:s');
                    $where[] = '(publish_end_at IS NULL OR publish_end_at > :now_end)';
                    $params[':now_end'] = date('Y-m-d H:i:s');
                }

                if ($where) {
                    $sqlWhere = 'WHERE ' . implode(' AND ', $where);
                }

                if (!empty($filters['limit'])) {
                    $perPage = (int)$filters['limit'];
                    $orderBy = "ORDER BY publish_start_at IS NULL ASC, publish_start_at DESC";
                } else {
                    $orderBy = "ORDER BY id ASC";
                }

                $sql = "SELECT * FROM mdl_information $sqlWhere $orderBy LIMIT :limit OFFSET :offset";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

                if (!empty($filters['keyword'])) {
                    $stmt->bindValue(':keyword', $params[':keyword'], PDO::PARAM_STR);
                }
                if (!empty($filters['limit'])) {
                    $stmt->bindValue(':now', $params[':now'], PDO::PARAM_STR);
                    $stmt->bindValue(':now_end', $params[':now_end'], PDO::PARAM_STR);
                    $stmt->execute();
                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // 取得後に「掲載開始日があるものはpublish_start_at、nullはupdated_at」で降順ソート
                    usort($data, function($a, $b) {
                        $dateA = !empty($a['publish_start_at']) ? $a['publish_start_at'] : $a['updated_at'];
                        $dateB = !empty($b['publish_start_at']) ? $b['publish_start_at'] : $b['updated_at'];
                        return strtotime($dateB) <=> strtotime($dateA); // 降順
                    });

                    return $data;
                }else {
                    $stmt->execute();
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
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
                // 掲載開始日・終了日による絞り込み（limit指定時のみ）
                if (!empty($filters['limit'])) {
                    $where .= ($where ? ' AND ' : 'WHERE ') . '(publish_start_at IS NULL OR publish_start_at <= :now)';
                    $params[':now'] = date('Y-m-d H:i:s');
                    $where .= ($where ? ' AND ' : 'WHERE ') . '(publish_end_at IS NULL OR publish_end_at > :now_end)';
                    $params[':now_end'] = date('Y-m-d H:i:s');
                }
                if ($where) {
                    $where = ' ' . $where; // WHERE句の前にスペースを追加
                }
                
                $sql = "SELECT COUNT(*) FROM mdl_information $where";
                $stmt = $this->pdo->prepare($sql);

                if (!empty($filters['keyword'])) {
                    $stmt->bindValue(':keyword', $params[':keyword'], PDO::PARAM_STR);
                }
                if (!empty($filters['limit'])) {
                    $stmt->bindValue(':now', $params[':now'], PDO::PARAM_STR);
                    $stmt->bindValue(':now_end', $params[':now_end'], PDO::PARAM_STR);
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

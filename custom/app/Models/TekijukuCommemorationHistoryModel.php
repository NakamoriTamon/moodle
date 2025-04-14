<?php
require_once('/var/www/html/moodle/config.php');

class TekijukuCommemorationHistoryModel extends BaseModel
{
    // 適塾の支払いが完了している情報を取得
    public function getTekijukuHistoryByTekijukuId($fk_tekijuku_commemoration_id)
    {
        if ($this->pdo) {
            try {
                $paid_deadline = TEKIJUKU_PAID_DEADLINE; // "mm-dd" 形式（例："04-01"）
                $current_date = date('Y-m-d');

                // 年度の判定（支払期限日より前なら前年）
                if ($current_date < date('Y') . '-' . $paid_deadline) {
                    $fiscal_start = (date('Y') - 1) . '-' . $paid_deadline;
                    $fiscal_end = date('Y') . '-' . date('m-d', strtotime($paid_deadline . ' -1 day'));
                } else {
                    $fiscal_start = date('Y') . '-' . $paid_deadline;
                    $fiscal_end = (date('Y') + 1) . '-' . date('m-d', strtotime($paid_deadline . ' -1 day'));
                }

                // SQLをPHPで構築（paid_dateの条件を除外）
                $sql = "SELECT *
                FROM mdl_tekijuku_commemoration_history
                WHERE fk_tekijuku_commemoration_id = :fk_tekijuku_commemoration_id
                AND paid_date BETWEEN :fiscal_start AND :fiscal_end
                ORDER BY id DESC LIMIT 1";

                // PDO でバインドする場合
                $params = [
                    ':fk_tekijuku_commemoration_id' => $fk_tekijuku_commemoration_id,
                    'fiscal_start' => $fiscal_start . ' 00:00:00',
                    'fiscal_end'   => $fiscal_end . ' 23:59:59'
                ];

                // SQLを実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $tekijuku = $stmt->fetch(PDO::FETCH_ASSOC);

                return $tekijuku;
            } catch (\PDOException $e) {
                error_log('支払済適塾記念会ユーザー取得エラー: ' . $e->getMessage() . ' UserID: ' . $fk_user_id);
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }
}

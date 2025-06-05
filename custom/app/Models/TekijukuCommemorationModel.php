<?php
require_once('/var/www/html/moodle/config.php');

class TekijukuCommemorationModel extends BaseModel
{
    // 条件に一致する適塾会員を取得
    public function getTekijukuUser($filters = [], int $page = 1, int $perPage = 15)
    {
        if ($this->pdo) {
            try {
                // ベースのSQLクエリ

                $sql = "SELECT
                            c.*,
                            CASE
                                WHEN h.id IS NULL THEN '未決済'
                                ELSE '決済済'
                            END AS payment_status,
                            h.paid_date as paid_date_history
                            FROM
                            mdl_tekijuku_commemoration c
                            LEFT JOIN (
                            SELECT *
                            FROM mdl_tekijuku_commemoration_history
                            WHERE paid_date BETWEEN :start_paid_date AND :end_paid_date
                            ) h ON c.id = h.fk_tekijuku_commemoration_id
                            WHERE
                            (
                                -- 退会会員でも指定年度までに支払いがあれば表示
                                c.is_delete = 0
                                OR EXISTS (
                                SELECT 1
                                FROM mdl_tekijuku_commemoration_history h2
                                WHERE h2.fk_tekijuku_commemoration_id = c.id
                                    AND h2.paid_date BETWEEN :start_paid_date AND :end_paid_date
                                )
                            )";
                $stmt = $this->pdo->prepare($sql);

                $range = $this->get_fiscal_year_range($filters['year']);
                // 年度の範囲必須
                $params = [];
                $params[':start_paid_date'] = $range['start'];
                $params[':end_paid_date'] = $range['end'];
                $where = "";
                if (!empty($filters['keyword'])) {
                    $where .= " AND (:keyword IS NULL OR c.name LIKE CONCAT('%', :keyword, '%') OR c.kana LIKE CONCAT('%', :keyword, '%'))";
                    $params[':keyword'] = $filters['keyword'];
                }

                $sql .= $where;

                // クエリの実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $tekijuku_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $tekijuku_list = $this->filter_join_year_and_deposit($tekijuku_list, $filters['year'], $filters['payment_status'], $page, $perPage);
                return $tekijuku_list;
            } catch (\PDOException $e) {
                error_log('適塾記念会ユーザー一覧取得エラー: ' . $e->getMessage());
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }
    // 総件数を取得
    public function getTekijukuUserCount($filters)
    {
        try {
            // ベースのSQLクエリ

            $sql = "SELECT COUNT(*) AS total FROM mdl_tekijuku_commemoration as t";
            $stmt = $this->pdo->prepare($sql);

            $where = " WHERE t.is_delete = 0";

            // 動的に検索条件を追加
            $params = [];
            if (!empty($filters['keyword'])) {
                $searchTerm = "%" . $filters['keyword'] . "%";
                $where .= " AND t.name LIKE :keyword";
                $params[':keyword'] = $searchTerm;
            }
            $sql .= $where;

            // クエリの実行
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $count = $stmt->fetch(PDO::FETCH_ASSOC);

            return $count;
        } catch (\PDOException $e) {
            error_log('適塾記念会ユーザー件数取得エラー: ' . $e->getMessage());
            echo 'データの取得に失敗しました';
        }
    }
    // 適塾の支払いが完了している情報を取得
    public function getTekijukuUserByPaid($fk_user_id)
    {
        if ($this->pdo) {
            try {
                $paid_deadline = TEKIJUKU_PAID_DEADLINE; // "mm-dd" 形式（例："04-01"）
                $current_date = date('Y-m-d');

                // 年度の判定（支払期限日より前なら前年）
                if ($current_date < date('Y') . '-' . $paid_deadline) {
                    $fiscal_year = date('Y') - 1;
                } else {
                    $fiscal_year = date('Y');
                }

                // SQLをPHPで構築（paid_dateの条件を除外）
                $sql = "SELECT 
                    tc.id, 
                    tc.number, 
                    tc.type_code, 
                    tc.name, 
                    tc.kana, 
                    tc.post_code, 
                    tc.address, 
                    tc.tell_number, 
                    tc.email, 
                    tc.payment_method, 
                    latest_payment.latest_paid_date AS paid_date, 
                    tc.note, 
                    tc.is_published, 
                    tc.is_subscription, 
                    tc.is_delete, 
                    tc.department, 
                    tc.major, 
                    tc.official, 
                    tc.paid_status,
                    tc.is_university_member, 
                    tc.price,
                    tc.is_dummy_email,
                    tc.is_deposit_2025,
                    tc.is_deposit_2026,
                    tc.is_deposit_2027,
                    tc.is_deposit_2028,
                    tc.is_deposit_2029,
                    tc.is_deposit_2030
                FROM mdl_tekijuku_commemoration tc
                LEFT JOIN (
                    SELECT 
                        fk_tekijuku_commemoration_id, 
                        MAX(paid_date) AS latest_paid_date
                    FROM mdl_tekijuku_commemoration_history
                    GROUP BY fk_tekijuku_commemoration_id
                ) AS latest_payment ON latest_payment.fk_tekijuku_commemoration_id = tc.id
                WHERE tc.fk_user_id = :fk_user_id";

                // PDO でバインドする場合
                $params = [
                    ':fk_user_id' => $fk_user_id
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

    // 条件に一致する適塾会員を取得
    public function getTekijukuUserAll()
    {
        if ($this->pdo) {
            try {
                // ベースのSQLクエリ

                $sql = "SELECT * FROM mdl_tekijuku_commemoration as t WHERE t.is_delete = 0";

                // クエリの実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                $tekijuku_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $tekijuku_list;
            } catch (\PDOException $e) {
                error_log('適塾記念会ユーザー一覧取得エラー: ' . $e->getMessage());
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }

    private function get_fiscal_year_range(int $fiscalYear): array
    {
        // 定義されている起算日（月日）
        $startMonthDay = MEMBERSHIP_START_DATE; // 例: '04-01'

        // 開始日：たとえば2025-04-01
        $startDate = "{$fiscalYear}-{$startMonthDay}";

        // 終了日：開始日の1年後の前日
        $startDateTime = new DateTime($startDate);
        $endDateTime = clone $startDateTime;
        $endDateTime->modify('+1 year')->modify('-1 day');

        return [
            'start' => $startDateTime->format('Y-m-d 00:00:00'),
            'end'   => $endDateTime->format('Y-m-d 23:59:59'),
        ];
    }

    // 決済状況と既に支払い済みか確認
    private function filter_join_year_and_deposit($tekijuku_commemoration_list, $year, $paid_status, $page, $perPage)
    {
        // 入会年度をチェック
        $join_filtered_list = array_filter($tekijuku_commemoration_list, function ($tekijuku_commemoration) use ($year) {
            $date = new DateTime($tekijuku_commemoration['created_at']);
            $join_year = (int)$date->format('Y');
            $join_month = (int)$date->format('m');

            $join_year = $join_month < MEMBERSHIP_START_MONTH ? $join_year - 1 : $join_year;

            // 指定年度より前に加入していれば表示する。
            return $join_year <= $year;
        });

        // 先払いを行っているか確認する( 2024年から2030年まで対応 )
        if ($year > 2023 && $year < 2031) {
            foreach ($join_filtered_list as $key => $join_filtered) {
                $target_deposit = 'is_deposit_' . $year;
                if ($join_filtered[$target_deposit] == 1) {
                    $join_filtered_list[$key]['payment_status'] = '決済済';
                    $join_filtered_list[$key]['paid_status'] = PAID_STATUS['COMPLETED'];
                }
                // 未決済の場合は支払方法を初期化する
                if ($join_filtered_list[$key]['payment_status'] == '未決済') {
                    $join_filtered_list[$key]['payment_method'] = null;
                }
            }
        }

        // 決済状況で振り分け
        if (!empty($paid_status)) {
            $status_filtered_list = array_filter($join_filtered_list, function ($join_filtered) use ($paid_status) {
                return $join_filtered['payment_status'] == $paid_status;
            });
        }

        $filter_list = $status_filtered_list ?? $join_filtered_list;

        // ページネーション
        $offset = ($page - 1) * $perPage;
        $filter_list = array_slice($filter_list, $offset, $perPage);

        return $filter_list;
    }

    // 再入会時の最大会員IDを取得
    public function get_tekijuku_max_number()
    {
        if ($this->pdo) {
            try {
                $sql = "SELECT MAX(number) AS max_number FROM mdl_tekijuku_commemoration WHERE number >= :minval";
                $params = ['minval' => TEKIJUKU_RENUMBERING_NUM];

                // SQLを実行
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $tekijuku_max_number = $stmt->fetch(PDO::FETCH_ASSOC);

                return $tekijuku_max_number;
            } catch (\PDOException $e) {
                error_log('適塾記念会ユーザー一覧取得エラー: ' . $e->getMessage());
                echo 'データの取得に失敗しました';
            }
        } else {
            error_log('データベース接続が確立されていません');
            echo "データの取得に失敗しました";
        }

        return [];
    }
}

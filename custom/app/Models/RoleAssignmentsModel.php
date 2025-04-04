<?php
class RoleAssignmentsModel extends BaseModel
{
    // ロールの位を取得
    public function getShortname($user_id = null)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT r.id, r.shortname 
                    FROM mdl_role_assignments ra
                    JOIN mdl_role r ON ra.roleid = r.id
                    WHERE ra.userid = ?");
                $stmt->execute([$user_id]);

                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }
}

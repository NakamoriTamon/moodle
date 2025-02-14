<?php
class PaymentTypeModel extends BaseModel
{
    // is_delete：0のカテゴリーを全件取得
    public function getPaymentTypes()
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_payment_types WHERE is_delete = 0 ORDER BY id ASC");
                $stmt->execute();
                $paymentTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return $paymentTypes;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        }

        return [];
    }

    // idとis_delete：0から対象のカテゴリーを取得
    public function getPaymentTypesById($id)
    {
        if ($this->pdo) {
            try {
                $stmt = $this->pdo->prepare("SELECT * FROM mdl_payment_types WHERE is_delete = 0 AND id = :payTypeId");
                $stmt->bindParam(':payTypeId', $id, PDO::PARAM_INT);
                $stmt->execute();
                $paymentTypes = $stmt->fetch(PDO::FETCH_ASSOC);
                return $paymentTypes;
            } catch (\PDOException $e) {
                echo 'データの取得に失敗しました: ' . $e->getMessage();
            }
        } else {
            echo "データの取得に失敗しました";
        }

        return [];
    }
}
?>
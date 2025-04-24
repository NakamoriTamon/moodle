<?php
require_once('/var/www/html/moodle/config.php');
require_once($CFG->dirroot . '/lib/moodlelib.php');
require_once($CFG->dirroot . '/local/commonlib/lib.php');
require_once($CFG->dirroot . '/custom/app/Models/BaseModel.php');
require_once($CFG->dirroot . '/custom/app/Models/EventModel.php');

$baseModel = new BaseModel();
$pdo = $baseModel->getPdo();

try {
    $pdo->beginTransaction();
    $tekijuku_list = $DB->get_records('tekijuku_commemoration');
    foreach ($tekijuku_list as $tekijuku) {
        for ($i = 2024; $i < 2031; $i++) {
            $target = 'is_deposit_' . $i;
            if ($tekijuku->$target == 1) {

                $stmt = $pdo->prepare("
                    INSERT INTO mdl_tekijuku_commemoration_history (
                        paid_date,
                        price,
                        fk_tekijuku_commemoration_id, 
                        payment_method
                    ) VALUES (
                        :paid_date,
                        :price,
                        :fk_tekijuku_commemoration_id,
                        :payment_method
                    )
                ");

                $datetime = new DateTime("{$i}-04-01 00:00:00");
                $paid_date = $datetime->format('Y-m-d H:i:s');

                $stmt->execute([
                    ':paid_date' => $paid_date,
                    ':price' => $tekijuku->price,
                    ':fk_tekijuku_commemoration_id' => $tekijuku->id,
                    ':payment_method' => 100
                ]);
            }
        }
    }
    $pdo->commit();
    echo 'OK';
} catch (Exception $e) {
    try {
        var_dump($e);
        die();
        $transaction->rollback($e);
        var_dump($e);
        die();
    } catch (Exception $rollbackException) {
        echo 'NG';
        exit;
    }
}

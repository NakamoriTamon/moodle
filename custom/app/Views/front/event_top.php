<?php
require_once('/var/www/html/moodle/custom/app/Controllers/FrontController.php');
$frontController = new FrontController();
$responce = $frontController->eventTop();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>イベント一覧</title>
    <!-- スタイルは完全仮の状態なのでとりえず直書きする 後で個別ファイルに記述する -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 0;
        }

        .event-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .event-card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            height: 500px;
        }

        .event-card img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
        }

        .event-card h3 {
            font-size: 18px;
            margin: 10px 0;
        }

        .event-card p {
            font-size: 14px;
            text-align: left;
        }

        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            padding: 0;
        }

        .pagination li {
            margin: 0 5px;
        }

        .pagination a {
            text-decoration: none;
            color: #007bff;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .pagination a:hover {
            background-color: #f0f0f0;
        }

        .pagination .active a {
            background-color: #007bff;
            color: #fff;
            cursor: default;
        }

        .event_title {
            font-size: 16px;
            color: black;
            font-weight: bold;
        }

        .card_wrapper {
            padding: 16px;
        }

        .status {
            margin-top: 0vh;
            margin-bottom: 16px;
            text-align: center !important;
        }

        .event_period span {
            padding-right: 1vw !important;
        }
    </style>
</head>

<?php include('/var/www/html/moodle/custom/app/Views/common/header.php'); ?>
<div class="container">
    <div class="event-list">
        <?php foreach ($responce['eventList'] as $event) { ?>
            <a href="event_detail.php?id=<?php echo $event['id'] ?>">
                <div class="event-card">
                    <img src="/custom/upload/img/<?php echo htmlspecialchars($event['main_img_name']) ?>">
                    <div class="card_wrapper">
                        <p class="status">受付中</p>
                        <p class="event_title"><?php echo htmlspecialchars($event['name']); ?></p>
                        <?php foreach ($event['details'] as $key => $detail) { ?>
                            <?php
                            $startDate = (new DateTime($detail['start_date']))->format('Y年m月d日');
                            $endDate = (new DateTime($detail['end_date']))->format('Y年m月d日');
                            ?>
                            <p class="event_period">
                                <span>開催日</span><?php echo $key + 1 ?>回目 :
                                <?php echo htmlspecialchars($startDate) ?> ~ <?php echo htmlspecialchars($endDate) ?>
                            </p>
                            <?php
                            if ($key >= 1) {
                                break;
                            }
                            $key += 1;
                            ?>
                        <?php } ?>
                    </div>
                </div>
            </a>
        <?php } ?>
    </div>

    <!-- ページネーション -->
    <ul class="pagination">
        <!-- 前のページ -->
        <?php if ($responce['pagination']['currentPage'] > 1) { ?>
            <li><a href="?page=<?php echo $responce['pagination']['currentPage'] - 1; ?>">&laquo;</a></li>
        <?php } ?>

        <!-- ページ番号 -->
        <?php for ($i = 1; $i <= $responce['pagination']['totalPages']; $i++) { ?>
            <li class="<?php echo ($i == $responce['pagination']['currentPage']) ? 'active' : ''; ?>">
                <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php } ?>

        <!-- 次のページ -->
        <?php if ($responce['pagination']['currentPage'] < $responce['pagination']['totalPages']) { ?>
            <li><a href="?page=<?php echo $responce['pagination']['currentPage'] + 1; ?>">&raquo;</a></li>
        <?php } ?>
    </ul>
</div>
</body>

</html>
<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');

class FrontController
{
    private $eventModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
    }

    public function index()
    {
        $eventList = $this->eventModel->getEvents();
        return $eventList;
    }

    public function render($category, $viewName, $data)
    {
        extract($data);
        
        if($category == null) {
            include "/var/www/html/moodle/custom/app/Views/{$viewName}.php";
        } else {
            include "/var/www/html/moodle/custom/app/Views/{$category}/{$viewName}.php";
        }
    }

    // イベント一覧画面
    public function eventTop()
    {
        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;

        $totalCount = $this->eventModel->totalCount();
        $eventList = $this->eventModel->pagenate($limit, $offset);

        $totalPages = ceil($totalCount / $limit);

        return [
            'eventList' => $eventList,
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
            ]
        ];
        return $eventList;
    }

    public function detail($eventId)
    {
        $event = $this->eventModel->getEventById($eventId);

        return $event;
    }
}

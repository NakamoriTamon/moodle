<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');

class ContactController
{
    private $eventModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
    }

    public function getEventList()
    {
        $currentPage = 1; // 現在のページ番号
        $perPage = 9999; // 1ページあたりの件数

        $events = $this->eventModel->getEvents([
            'event_status' => [1, 2],
        ], $currentPage, $perPage);

        return $events;
    }
}

<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/MaterialModel.php');

class MaterialController
{
    private $categoryModel;
    private $eventModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        $this->eventModel = new EventModel();
        $this->materialModel = new MaterialModel();
    }

    public function index()
    {
        global $DB;
        global $USER;

        unset($_SESSION['registered_material_ids']);

        // 検索項目取得
        $category_id     = $_POST['category_id'] ?? null;
        $event_status_id = $_POST['event_status_id'] ?? null;
        $event_id        = $_POST['event_id'] ?? null;
        $course_no       = $_POST['course_no'] ?? null;
        $_SESSION['old_input'] = $_POST;

        $sql = "SELECT r.id, r.shortname 
        FROM {role_assignments} ra
        JOIN {role} r ON ra.roleid = r.id
        WHERE ra.userid = :userid";

        $params = ['userid' => $USER->id];

        $roles = $DB->get_records_sql($sql, $params);
        foreach ($roles as $role) {
            $shortname = $role->shortname;
        }

        $filters = array_filter([
            'category_id' => $category_id,
            'event_status' => $event_status_id,
            'shortname' => $shortname,
            'event_id' => $event_id,
            'userid' => $USER->id,
            'course_no' => $course_no
        ]);

        $filters = array_filter($filters);
        $event_list = $this->eventModel->getEvents($filters, 1, 100000);
        $select_event_list = $this->eventModel->getEvents($filters, 1, 100000); // イベント名選択用

        $material = [];
        $is_display = false;
        $is_single = true;
        $course_info_id = null;
        // 講義動画を取得
        foreach ($event_list as $event) {
            if ($USER->id == (int)$event['userid'] || $shortname == "admin") {
                if (!empty($event_id)) {
                    // 単発イベントの場合
                    if ($event['event_kbn'] == 1) {
                        foreach ($event['course_infos'] as $course_info) {
                            $course_info_id = $course_info['id'];
                            $course_number = [1];
                            $_SESSION['old_input']['course_no'] = "1";
                            $is_display = true;
                        }
                    }
                    // 複数回イベントの場合
                    if ($event['event_kbn'] == 2 && !empty($course_no)) {
                        foreach ($event['course_infos'] as $course_info) {
                            if ($course_info['no'] == $course_no) {
                                $course_info_id = $course_info['id'];
                                $is_display = true;
                                $is_single = false;
                            }
                        }
                        $course_count = $DB->get_field_sql("SELECT COUNT(*) FROM {event_course_info} WHERE event_id = ?", [$event_id]);
                        $course_number = range(1, $course_count);
                    }
                }
            }
        }

        $material = $DB->get_records('course_material', array('course_info_id' => $course_info_id));
        $event_list = !empty($event_id) && empty($event_status_id) && empty($category_id) ?  $select_event_list : $event_list;
        $category_list = $this->categoryModel->getCategories();


        $data = [
            'category_list'  => $category_list,
            'event_list'     => $event_list,
            'course_info'    => $course_info_id ?? '',
            'material'       => $material,
            'course_number'  => $course_number ?? [1],
            'is_display'     => $is_display,
            'is_single'      => $is_single
        ];
        return $data;
    }
}

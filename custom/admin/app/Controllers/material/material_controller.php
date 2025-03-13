<?php
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');

class MaterialController
{
    private $categoryModel;
    private $eventModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        $this->eventModel = new EventModel();
    }

    public function index()
    {
        global $DB;

        unset($_SESSION['registered_material_ids']);

        // 検索項目取得
        $category_id     = $_POST['category_id'] ?? null;
        $event_status_id = $_POST['event_status_id'] ?? null;
        $event_id        = $_POST['event_id'] ?? null;
        $course_no       = $_POST['course_no'] ?? null;
        $_SESSION['old_input'] = $_POST;

        $filters = [];
        if (!empty($category_id)) {
            $filters['category_id'] = $category_id;
        }
        if (!empty($event_status_id)) {
            $filters['event_status'] = $event_status_id;
        }
        if (!empty($event_id)) {
            $filters['event_id'] = $event_id;
        }
        if (!empty($course_no)) {
            $filters['course_no'] = $course_no;
        }

        if (!empty($event_status_id) || !empty($category_id)) {
            $event_list = $this->eventModel->getEvents($filters, 1, 100000);
        } else {
            $event_list = $this->eventModel->getEvents([], 1, 100000);
        }

        $course_info = null;
        $material    = [];

        if (!empty($event_id) && !empty($course_no)) {
            if (!empty($category_id)) {
                $course_info = $DB->get_record_sql(
                    "SELECT eci.* FROM {event_course_info} eci 
                 JOIN {event} e ON e.id = eci.event_id 
                 WHERE eci.event_id = ? AND eci.course_info_id = ? AND e.categoryid = ?",
                    [$event_id, $course_no, $category_id]
                );
                if ($course_info) {
                    $course_data = $DB->get_record('course_info', array('id' => $course_info->course_info_id, 'no' => $course_no));
                    $material = $DB->get_records('course_material', array('course_info_id' => $course_data->id));
                    $is_display = true;
                }
            } else {
                $event_course_info_test = $DB->get_records_sql(
                    "SELECT * FROM {event_course_info} WHERE event_id = ?",
                    [$event_id]
                );
                $course_info_test = $DB->get_records_sql(
                    "SELECT * FROM {course_info} WHERE no = ?",
                    [$course_no]
                );

                $course_info_by_id = [];
                foreach ($course_info_test as $ci) {
                    $course_info_by_id[$ci->id] = $ci;
                }

                foreach ($event_course_info_test as $eci_record) {
                    if (isset($course_info_by_id[$eci_record->course_info_id])) {
                        $course_info =  $course_info_by_id[$eci_record->course_info_id];
                    }
                }
                if ($course_info) {
                    $material = $DB->get_records('course_material', array('course_info_id' => $course_info->id));
                    $is_display = true;
                }
            }
        }
        $category_list = $this->categoryModel->getCategories();

        $course_count = $DB->get_field_sql("SELECT COUNT(*) FROM {event_course_info} WHERE event_id = ?", [$event_id]);
        if ($course_count) {
            if ($course_count > 1) {
                $course_number = range(1, $course_count);
                $is_simple = false;
            } else {
                $course_number = [1];
                $is_simple = true;
            }
        } else {
            $course_number = [1];
        }


        $data = [
            'category_list'  => $category_list,
            'event_list'     => $event_list,
            'course_info'    => is_object($course_info) ? ($course_info->course_info_id ?? $course_info->id) : '',
            'material'       => $material,
            'course_number'  => $course_number,
            'is_display'     => $is_display ?? false,
            'is_simple'      => $is_simple ?? false
        ];
        return $data;
    }
}

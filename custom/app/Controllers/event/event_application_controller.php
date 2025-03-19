<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventCustomFieldModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CognitionModel.php');
require_once('/var/www/html/moodle/custom/app/Models/PaymentTypeModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CategoryModel.php');
require_once('/var/www/html/moodle/custom/app/Models/LectureFormatModel.php');
require_once('/var/www/html/moodle/custom/helpers/form_helpers.php');

class EventApplicationController
{

    private $eventModel;
    private $eventCustomFieldModel;
    private $cognitionModel;
    private $paymentTypeModel;
    private $eventApplicationModel;
    private $categoryModel;
    private $lectureFormatModel;

    public function __construct()
    {
        $this->eventCustomFieldModel = new eventCustomFieldModel();
        $this->eventModel = new eventModel();
        $this->cognitionModel = new CognitionModel();
        $this->paymentTypeModel = new PaymentTypeModel();
        $this->eventApplicationModel = new EventApplicationModel();
        $this->categoryModel = new CategoryModel();
        $this->lectureFormatModel = new LectureFormatModel();
    }

    public function getEvenApplication($eventId, $courseInfoId, $formdata)
    {
        if (!empty($courseInfoId)) {
            $event = $this->eventModel->getEventByIdAndCourseInfoId($eventId, $courseInfoId);
        } else {
            $event = $this->eventModel->getEventById($eventId);
        }

        $categorys = $this->categoryModel->getCategories();
        $lectureFormats = $this->lectureFormatModel->getLectureFormats();

        $select_lecture_formats = [];
        $select_categorys = [];
        $select_courses = [];
        $now = new DateTime();
        $now = $now->format('Ymd');
        if (!empty($event)) {

            foreach ($event['lecture_formats'] as $lecture_format) {
                $lecture_format_id = $lecture_format['lecture_format_id'];

                foreach ($lectureFormats as $lectureFormat) {
                    if ($lectureFormat['id'] == $lecture_format_id) {
                        $select_lecture_formats[] = $lectureFormat;
                        break;
                    }
                }
            }

            foreach ($event['categorys'] as $select_category) {
                $category_id = $select_category['category_id'];

                foreach ($categorys as $category) {
                    if ($category['id'] == $category_id) {
                        $select_categorys[] = $category;
                        break;
                    }
                }
            }

            $deadline = null;
            if(!empty($event['deadline'])) {
                $deadline = (new DateTime($event['deadline']))->format('Ymd');
            }
            foreach ($event['course_infos'] as $select_course) {
                if($event['event_kbn'] == EVERY_DAY_EVENT && is_null($courseInfoId)) {
                    $course_date = (new DateTime($select_course['course_date']))->format('Ymd');
                    
                    if ($course_date >= $now) {
                            $event['select_course'][$select_course['no']] = $select_course;
                            break;
                    }
                } elseif($event['event_kbn'] == PLURAL_EVENT && is_null($courseInfoId)) {
                    if (!empty($deadline) && $deadline < $now) {
                            $event['select_course'] = [];
                            break;
                    } else {
                        $event['select_course'][$select_course['no']] = $select_course;
                    }
                } else {
                    $event['select_course'][$select_course['no']] = $select_course;
                }
            }
        }

        $fieldList = $this->eventCustomFieldModel->getCustomFieldById($event['event_customfield_category_id']);
        $sum_ticket_count = $this->eventApplicationModel->getSumTicketCountByEventId($eventId)['sum_ticket_count'] ?? 0;

        $cognitions = $this->cognitionModel->getCognition();
        $paymentTypes = $this->paymentTypeModel->getPaymentTypes();

        $passage = '';
        $checked = '';
        $customfield_type_list = CUSTOMFIELD_TYPE_LIST;
        $params = null;
        if (!is_null($formdata) && isset($formdata['params'])) {
            $params = $formdata['params'];
        }
        foreach ($fieldList as $fields) {
            $passage .= '<li class="long_item"><p class="list_label">' . $fields['name'] . '</p>';
            if ($fields['field_type'] == 3) {
                $passage .= '<div class="list_field list_col">';
                $options = explode(",", $fields['selection']);
                foreach ($options as $i => $option) {
                    $name = "";
                    $name = $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'];
                    $checked_param = is_null($params) ? [] : $params[$name];
                    $checked = isChoicesSelected($option, $checked_param, null) ? 'checked' : '';
                    $name .= '[]';
                    $passage .= '<p class="f_check"><label><input type="' . $customfield_type_list[$fields['field_type']] . '" name="' . $name . '" value="' . $option . '"' . $checked . '>' . $option . '</label></p>';
                }
                $passage .= '</div>';
                continue;
            }
            if ($fields['field_type'] == 4) {
                $passage .= '<div class="list_field list_row">';
                $options = explode(",", $fields['selection']);
                foreach ($options as $i => $option) {
                    $name = "";
                    $name = $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'];
                    $checked_param = is_null($params) || !isset($params[$name]) ? "" : $params[$name];
                    if (!is_null($params)) {
                        $checked = isSelected($option, $checked_param, null) ? 'checked' : '';
                    } else {
                        $checked = '';
                    }

                    $passage .= '<label class="f_radio"><input type="' . $customfield_type_list[$fields['field_type']] . '" name="' . $name . '" value="' . $option . '"' . $checked . '>' . $option . '</label>';
                }
                $passage .= '</div>';
                continue;
            }
            if ($fields['field_type'] == 2) {
                $name = $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'];
                $value = is_null($params) ? "" : $params[$name];
                $passage .= '<div class="list_field f_txtarea"><textarea name="' . $name . '" rows="4" cols="50">' . $value . '</textarea></div>';
                continue;
            }
            if ($fields['field_type'] == 1 || $fields['field_type'] == 5) {
                $name = $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'];
                $value = is_null($params) ? "" : $params[$name];
                $passage .= '<div class="list_field list_col"><input type="' . $customfield_type_list[$fields['field_type']] . '" name="' . $name . '" value="' . $value . '"></div>';
                continue;
            }
        }

        return ['passage' => $passage, 'event' => $event, 'cognitions' => $cognitions, 'paymentTypes' => $paymentTypes, 'sum_ticket_count' => $sum_ticket_count];
    }

    public function getEvenApplicationById($eventId)
    {
        $event = $this->eventApplicationModel->getEventApplicationCourseInfos($eventId);

        return $event;
    }
}

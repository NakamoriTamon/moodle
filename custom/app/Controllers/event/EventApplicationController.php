<?php
require_once('/var/www/html/moodle/config.php');
require_once('/var/www/html/moodle/custom/app/Models/BaseModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventApplicationModel.php');
require_once('/var/www/html/moodle/custom/app/Models/EventCustomFieldModel.php');
require_once('/var/www/html/moodle/custom/app/Models/CognitionModel.php');
require_once('/var/www/html/moodle/custom/app/Models/PaymentTypeModel.php');

class EventApplicationController {

    private $eventModel;
    private $eventCustomFieldModel;
    private $cognitionModel;
    private $paymentTypeModel;
    private $eventApplicationModel;

    public function __construct()
    {
        $this->eventCustomFieldModel = new eventCustomFieldModel();
        $this->eventModel = new eventModel();
        $this->cognitionModel = new CognitionModel();
        $this->paymentTypeModel = new PaymentTypeModel();
        $this->eventApplicationModel = new EventApplicationModel();
    }
    
    public function getEvenApplication($eventId) {
        $event = $this->eventModel->getEventById($eventId);
        $fieldList = $this->eventCustomFieldModel->getCustomFieldById($event['event_customfield_category_id']);
        $sum_ticket_count = $this->eventApplicationModel->getSumTicketCountByEventId($eventId)[0]['sum_ticket_count'] ?? 0;

        $cognitions = $this->cognitionModel->getCognition();
        $paymentTypes = $this->paymentTypeModel->getPaymentTypes();
        
        $passage = '';
        $checked = '';
        $customfield_type_list = CUSTOMFIELD_TYPE_LIST;
        foreach ($fieldList as $fields) {
            $passage .= '<label class="label_name" for="name">' . $fields['field_name'] . '</label>';
            if ($fields['field_type'] == 3 || $fields['field_type'] == 4) {
                $passage .= '<div class="radio-group">';
                $options = explode(",", $fields['selection']);
                foreach ($options as $i => $option) {
                    $name = "";
                    if ($fields['field_type'] == 4) {
                        // $checked = ($i == 0) ? 'checked' : '';
                        $name = $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'];
                    } else {
                        $name = $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'] . '[]';
                    }
                    $passage .= '<label class="label_d_flex"><input type="' . $customfield_type_list[$fields['field_type']] . '" name="' . $name . '" value="' . $i+1 . '"' . $checked . '>' . $option . '</label>';
                }
                $passage .= '</div>';
                continue;
            }
            if ($fields['field_type'] == 2) {
                $passage .= '<textarea name="' . $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'] . '" rows="4" cols="50"></textarea>';
                continue;
            }
            if ($fields['field_type'] == 1 || $fields['field_type'] == 5) {
                $passage .= '<input type="' . $customfield_type_list[$fields['field_type']] . '" name="' . $customfield_type_list[$fields['field_type']] . '_' . $fields['id'] . '_' . $fields['field_type'] . '">';
                continue;
            }
        }

        return ['passage' => $passage, 'event' => $event, 'cognitions' => $cognitions, 'paymentTypes' => $paymentTypes, 'sum_ticket_count' => $sum_ticket_count];
    }
}
?>
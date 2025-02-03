<?php
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
        $fieldList = $this->eventCustomFieldModel->getEventsCustomFieldByEventId($event['event_custom_field_id']);
        $sum_ticket_count = $this->eventApplicationModel->getSumTicketCountByEventId($eventId)[0]['event_date'] ?? 0;

        $cognitions = $this->cognitionModel->getCognition();
        $paymentTypes = $this->paymentTypeModel->getPaymentTypes();
        
        $passage = '';
        foreach ($fieldList as $fields) {
            $passage .= '<label class="label_name" for="name">' . $fields['field_name'] . '</label>';
            if ($fields['field_type'] == 'checkbox' || $fields['field_type'] == 'radio') {
                $options = explode(",", $fields['field_options']);
                foreach ($options as $i => $option) {
                    if ($fields['field_type'] == 'radio') {
                        $passage .= '<div class="radio-group">';
                        $checked = ($i == 0) ? 'checked' : '';
                    } else {
                        $passage .= '<div class="checkbox-group">';
                    }
                    $passage .= '<label class="label_d_flex"><input type="' . $fields['field_type'] . '" name="' . $fields['name'] . '" value="' . $option . '"' . $checked . '>' . $option . '</label></div>';
                }
                continue;
            }
            if ($fields['field_type'] == 'textarea') {
                $passage .= '<textarea name="' . $fields['name'] . '" rows="4" cols="50"></textarea>';
                continue;
            }
            $passage .= '<input type="' . $fields['field_type'] . '" name="' . $fields['name'] . '">';
            $passage .= '<input type="hidden" name="event_customfield_id" value="' . $fields['id'] . '">';
        }
        if(empty($fieldList)){
        }

        return ['passage' => $passage, 'event' => $event, 'cognitions' => $cognitions, 'paymentTypes' => $paymentTypes, 'sum_ticket_count' => $sum_ticket_count];
    }
}
?>
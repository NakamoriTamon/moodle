<?php
function isSetValue($currentValue, $oldValue)
{
    $value = "";
    if (!empty($oldValue)) {
        $value = $oldValue;
    } else if (!empty($currentValue)) {
        $value = $currentValue;
    }
    return $value;
}

function isSelected($value, $currentValue, $oldValue)
{
    return ($value == $currentValue) || ($value == $oldValue);
}

function isChoicesSelected($value, $currentValue, $oldValue)
{
    $result = false;
    if (!empty($oldValue)) {
        $result = in_array($value, $oldValue);
    } else if (!empty($currentValue)) {
        $result = in_array($value, $currentValue);
    }

    return $result;
}

function isSetDate($currentValue, $oldValue)
{
    $day = "";
    if (!empty($oldValue)) {
        $day = date("Y-m-d", strtotime($oldValue));
    } else if (!empty($currentValue)) {
        $day = date("Y-m-d", strtotime($currentValue));
    }

    return $day;
}

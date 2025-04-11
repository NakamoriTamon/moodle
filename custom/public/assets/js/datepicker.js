$(function () {
    function addYearSuffixToYearSelect() {
        $('.ui-datepicker-year option').each(function () {
            const year = $(this).val();
            $(this).text(year + '年');
        });
    }

    const options = {
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy/mm/dd',
        monthNames: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
        monthNamesShort: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
        showMonthAfterYear: !0,

        closeText: "閉じる", 
        prevText: "&#x3C;前", 
        nextText: "次&#x3E;", 
        currentText: "今日", 
        dayNames: ["日曜日", "月曜日", "火曜日", "水曜日", "木曜日", "金曜日", "土曜日"], 
        dayNamesShort: ["日", "月", "火", "水", "木", "金", "土"], 
        dayNamesMin: ["日", "月", "火", "水", "木", "金", "土"], 
        weekHeader: "週", 
        firstDay: 0, 
        isRTL: !1, 
        showMonthAfterYear: !0, 

        beforeShow: function () {
            setTimeout(addYearSuffixToYearSelect, 0);
        },
        
        onChangeMonthYear: function () {
            setTimeout(addYearSuffixToYearSelect, 0);
        },

        beforeShowDay: function(date) {
            if (date.getDay() === 0) {
                // 日曜日の場合
                return [true, "custom-calendar-sunday", ""];
            } else if (date.getDay() === 6) {
                // 土曜日の場合
                return [true, "custom-calendar-saturday", ""];
            }
            // 平日の場合
            return [true, "custom-calendar-weekday", ""];
        }
    }
    
    $('#event_start_date').datepicker(options);
    $('#event_end_date').datepicker(options);
});
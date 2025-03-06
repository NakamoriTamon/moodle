$(document).ready(function () {
    $('#clear_button').on('click', function () {
        let $form = $('#search_cont');

        // フォームをリセット
        $form[0].reset();

        // チェックボックスのチェックを外す
        $form.find('input[type="checkbox"]').prop('checked', false);

        // セレクトボックスの選択を解除
        $form.find('select').prop('selectedIndex', 0);

        // テキスト・日付入力をクリア
        $form.find('input[type="text"], input[type="date"]').val('');
    });
});

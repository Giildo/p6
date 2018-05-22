$(function () {
    $('.deleteConfirm').click(function (e) {
        var source = $(this).data('src');
        var token = $(this).data('token');
        $('#confirmDelete').data('source', source).data('token', token);
    });

    $('#confirmDelete').click(function (e) {
        var target = $(this).data('source') + '/' + $(this).data('token');
        window.location.href = target;
    });
});

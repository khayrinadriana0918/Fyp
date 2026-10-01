$('.receiver-request-row').on('click', function () {

    const requestId = $(this).data('request-id');

    const requesterName = $(this).data('requester-name');
    const requesterId = $(this).data('requester-id');
    const semester = $(this).data('semester');
    const title = $(this).data('title');
    const category = $(this).data('category');
    const label = $(this).data('label');
    const priority = $(this).data('priority');
    const status = $(this).data('status');
    const description = $(this).data('description');
    const file = $(this).data('file');

    const submitDate = $(this).data('submitted-date');
    const resolvedDate = $(this).data('resolved-date');

    $('#receiver_requester_name').text(requesterName);
    $('#receiver_requester_id').text(requesterId);
    $('#receiver_semester').text(semester);
    $('#receiver_title').text(title);
    $('#receiver_category').text(category);
    $('#receiver_labels').text(label || 'No labels');
    $('#receiver_priority').text(priority);
    $('#receiver_status').text(status);
    $('#receiver_desc').text(description);
    $('#receiver_submit_date').text(submitDate);

    if (status === 'Completed' && resolvedDate) {

        $('#receiver_resolved_date').text(resolvedDate);
        $('#receiver_resolved_container').show();

    } else {

        $('#receiver_resolved_date').text('');
        $('#receiver_resolved_container').hide();
    }

    if (file) {

        $('#receiver_file').html(
            '<a href="../uploads/hop_student/' +
            encodeURIComponent(file) +
            '" target="_blank">View Attachment</a>'
        );
    } else {

        $('#receiver_file').text('No attachment');
    }
    $('#receiver_req_popup').data(
        'request-id',
        requestId
    );
    $('#status_request_id').val(requestId);
    $('#receiver_new_status').val(status);
    $('#receiver_status_form').hide();

    $('#receiver_req_popup').css('display', 'flex');

});
$('#change_status').on('click',function(){
    $('#receiver_status_form').toggle();
});
$('#close_receiver_popup').on('click', function () {

    $('#receiver_req_popup').hide();

});
$('#update_status_form').on('submit', function (event) {

    event.preventDefault();

    $.ajax({
        url: '../includes/update-request-status.php',
        type: 'POST',
        data: $(this).serialize(),

        success: function () {
            location.reload();
        },

        error: function () {
            alert('Unable to update request status.');
        }
    });

});
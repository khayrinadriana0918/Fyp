$('.request-row').on('click', function () {
    const requestId = $(this).data('request-id');
    const requesterName = $(this).data('requester-name');
    const requesterId = $(this).data('requester-id');
    const semester = $(this).data('semester');
    const title = $(this).data('title');
    const category = $(this).data('category');
    const label = $(this).data('label');
    const priority = $(this).data('priority');
    const status = $(this).data('status');
    const desc = $(this).data('description');
    const feedback = $(this).data('feedback');

    const file = $(this).data('file');
    const submitDate = $(this).data('submitted-date');
    const resolved = $(this).data('resolved-date');

    $('#popup_requester_name').text(requesterName);
    $('#popup_requester_id').text(requesterId);
    $('#popup_semester').text(semester);
    $('#popup_title').text(title);
    $('#popup_category').text(category);
    $('#popup_labels').text(label);
    $('#popup_priority').text(priority);
    $('#popup_status').text(status);
    $('#popup_desc').text(desc);
    $('#popup_feedback').text(feedback || 'No feedback provided');

    $('#popup_submit_date').text(submitDate);
    $('#popup_resolved_date').text(resolved);



    if (priority === 'Low') {
        $('#priority_circle').css('background-color', 'green');
    } else if (priority === 'Medium') {
        $('#priority_circle').css('background-color', 'orange');
    } else if (priority === 'Urgent') {
        $('#priority_circle').css('background-color', 'red');
    }

    if (status === 'Completed' && resolved) {
        $('#popup_resolved_date').text(resolved);
        $('#resolved_date_container').show();
    } else {
        $('#popup_resolved_date').text('');
        $('#resolved_date_container').hide();
    }

    if (file) {
        $('#popup_file').html(
            '<a href="../uploads/hop_student/' +
            encodeURIComponent(file) +
            '" target="_blank">View Attachment</a>'
        );
    } else {
        $('#popup_file').text('No attachment');
    }

    $('#edit_request').data('request-id', requestId);

    $('#req_popup').css('display', 'flex');

});

$('#close_popup').on('click', function () {
    $('#req_popup').hide();
});
$('#edit_request').on('click', function () {

    const requestId = $(this).data('request-id');
    window.location.href =
        'student_hop_requests.php?edit=' +
        encodeURIComponent(requestId);
});
$('.receiver-request-row').on('click', function () {

    const requestType = $(this).data('request-type') || 'student';
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
    const feedback = $(this).data('feedback');

    const submitDate = $(this).data('submitted-date');
    const resolvedDate = $(this).data('resolved-date');

    $('#receiver_requester_name').text(requesterName);
    $('#receiver_requester_id').text(requesterId);

    if (requestType === 'student') {
        $('#receiver_semester').text(semester);
        $('#receiver_semester_container').show();
    } else {
        $('#receiver_semester_container').hide();
    }

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

        const folder =
            requestType === 'administrator'
                ? '../uploads/admin_hop/'
                : '../uploads/hop_student/';

        $('#receiver_file').html(
            '<a href="' + folder +
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
    $('#priority_request_id').val(requestId);
    $('#receiver_new_priority').val(priority);
    $('#feedback_request_id').val(requestId);
    $('#receiver_feedback').val(feedback || '');
    $('#request_type')
        .val(requestType);

    $('#update_priority_form input[name="request_type"]')
        .val(requestType);

    $('#update_feedback_form input[name="request_type"]')
        .val(requestType);
    // hide forms when popup opens
    $('#receiver_status_form').hide();
    $('#receiver_priority_form').hide();
    $('#receiver_feedback_form').hide();

    $('#receiver_action').val(''); // Reset the dropdown selection

    $('#receiver_req_popup').css('display', 'flex');

});
// give feedback
$('#give_feedback').on('click', function () {
    $('#receiver_status_form').hide();
    $('#receiver_priority_form').hide();

    $('#receiver_feedback_form').toggle();
    $('#receiver_action').val('');
});
//status change
$('#receiver_action').on('change', function () {

    const action = $(this).val();

    if (action === 'status') {

        $('#receiver_priority_form').hide();
        $('#receiver_status_form').show();

    } else if (action === 'priority') {

        $('#receiver_status_form').hide();
        $('#receiver_priority_form').show();

    }

});
$('#close_receiver_popup').on('click', function () {

    $('#receiver_req_popup').hide();

});
$('#update_status_form').on('submit', function (event) {

    event.preventDefault();

    $.ajax({
        url: '../includes/update-request-status.php', type: 'POST', data: $(this).serialize(),

        success: function () {
            location.reload();
        },

        error: function () {
            alert('Unable to update request status.');
        }
    });

});
//priority change
$('#update_priority_form').on('submit', function (event) {

    event.preventDefault();

    $.ajax({
        url: '../includes/update-request-priority.php', type: 'POST',

        data: $(this).serialize(),

        success: function (response) {
            if ($.trim(response) === 'success') {
                location.reload();
            } else {
                alert(response);
            }
        },
        error: function () {
            alert('Unable to update request priority.');
        }
    });
});
// update feedback
$('#update_feedback_form').on('submit', function (event) {

    event.preventDefault();

    $.ajax({
        url: '../includes/update-request-feedback.php', type: 'POST',
        data: $(this).serialize(),
        success: function (response) {

            if ($.trim(response) === 'success') {
                location.reload();
            } else {
                alert(response);
            }
        },
        error: function () {
            alert('Unable to save feedback.');
        }
    });
});
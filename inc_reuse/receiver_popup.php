        <div id="receiver_req_popup" class="req-popup">
            <div class="req-popup-content">
                <button type="button" id="close_receiver_popup">X</button>
                <div id="request_details">

                    <div class="popup-header">
                        <div class="student-info">
                            <h3>
                                <span id="receiver_requester_name"></span>
                                (<span id="receiver_requester_id"></span>)
                            </h3>

                            <p id="receiver_semester_container">Semester: <span id="receiver_semester"></span></p>
                        </div>
                        <div>
                            <p>Priority: <span id="receiver_priority"></span></p>
                        </div>
                    </div>

                    <div class="title-section">
                        <h2 id="receiver_title"></h2>
                        <p>Issue Category:
                            <span id="receiver_category"></span>
                        </p>
                    </div>

                    <div class="popup-meta">
                        <div class="labels-section">
                            <p>Labels:</p>
                            <div class="label-box">
                                <span id="receiver_labels"></span>
                            </div>
                        </div>

                        <div class="status-section">
                            <p>Status: <span id="receiver_status"></span></p>
                            <p>Date Submitted: <span id="receiver_submit_date"></span></p>
                            <p id="receiver_resolved_container">Date Resolved: <span id="receiver_resolved_date"></span></p>
                        </div>
                    </div>
                    <hr>
                    <div class="desc-section">
                        <h3>Description</h3>
                        <div class="desc-content">
                            <p id="receiver_desc"></p>
                        </div>
                    </div>

                    <div class="attachment-section">
                        <h3>Attachments</h3>
                        <div id="receiver_file"></div>
                    </div>



                    <div class="popup-actions">
                        <select id="receiver_action">
                            <option value="" selected disabled>
                                Actions...
                            </option>

                            <option value="status">Change Status</option>

                            <option value="priority">Change Priority</option>
                        </select>

                        <button type="button" id="give_feedback">Comment / Give Feedback</button>
                    </div>
                    <div id="receiver_status_form" style="display: none;">
                        <form id="update_status_form">
                            <input type="hidden" id="status_request_id" name="request_id">
                            <input type="hidden" id="request_type" name="request_type" value="student">

                            <label for="receiver_new_status">Status: </label>

                            <select name="status" id="receiver_new_status" required>
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                            <button type="submit">Update Status</button>
                        </form>
                    </div>
                    <div id="receiver_priority_form" style="display: none;">

                        <form id="update_priority_form">

                            <input type="hidden" id="priority_request_id" name="request_id">

                            <input type="hidden" name="request_type" value="student">

                            <label for="receiver_new_priority">
                                Priority:
                            </label>

                            <select name="priority" id="receiver_new_priority" required>

                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>

                            </select>

                            <button type="submit">Update Priority</button>

                        </form>

                    </div>
                    <div id="receiver_feedback_form" style="display: none;">

                        <form id="update_feedback_form">

                            <input type="hidden" id="feedback_request_id" name="request_id">

                            <input type="hidden"name="request_type" value="student">

                            <label for="receiver_feedback">
                                Give Feedback:
                            </label>

                            <textarea
                                name="feedback"
                                id="receiver_feedback"
                                rows="5"
                                required
                                placeholder="Enter feedback for the student regarding the request."></textarea>

                            <button type="submit">Sent Feedback</button>

                        </form>

                    </div>
                </div>
            </div>
        </div>
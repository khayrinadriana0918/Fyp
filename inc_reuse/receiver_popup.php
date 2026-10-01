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
                        <button type="button" id="change_priority">See change priority</button>
                        <button type="button" id="change_status">Change Status</button>
                        <button type="button" id="receiver_view_history">See change history</button>
                    </div>
                </div>
            </div>
        </div>
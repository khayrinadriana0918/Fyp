<form class="filters" id="req-filters" method="GET">
    <fieldset>
        <legend>Filter Requests</legend>
        <dl>
            <dt class="sort">
                <label for="req-search-sort-dropdown">sort by:</label>
            </dt>
            <dd class="sort">
                <select name="req-search[sort_dropdown]" id="req-search-sort-dropdown">
                    <option value="date_updated">Date Updated</option>
                    <option value="date_submitted">Date Submitted</option>
                    <option value="student_name">User ID</option>
                </select>
            </dd>
            <dd class="tags group">
                <dl>
                    <!--PRIORITY TAGS -->
                    <!--              -->
                    <dt id="toggle-priority-tags" class="filter-toggle-collapsed">
                        <button type="button" aria-expanded="false" aria-controls="priority_tags" id="priority">Priority</button>
                    </dt>
                    <dd id="priority_tags" hidden class="expandable tags">
                        <ul>
                            <li>
                                <label for="search-priority-urgent">
                                    <input type="radio" name="req-search[priority][]" value="urgent" id="search-priority-urgent">
                                    Urgent
                                </label>
                            </li>
                            <li>
                                <label for="search-priority-medium">
                                    <input type="radio" name="req-search[priority][]" value="medium" id="search-priority-medium">
                                    Medium
                                </label>
                            </li>
                            <li>
                                <label for="search-priority-low">
                                    <input type="radio" name="req-search[priority][]" value="low" id="search-priority-low">
                                    Low
                                </label>
                            </li>
                        </ul>
                    </dd>
                    <!--STATUS TAGS -->
                    <!--              -->
                    <dt id="toggle-status-tags" class="filter-toggle-collapsed">
                        <button type="button" aria-expanded="false" aria-controls=status_tags id="status">Status</button>
                    </dt>
                    <dd id="status_tags" hidden class="expandable tags">
                        <ul>
                            <li>
                                <label for="search-status-completed">
                                    <input type="radio" name="req-search[status][]" value="completed" id="search-status-completed">
                                    Completed
                                </label>
                            </li>
                            <li>
                                <label for="search-status-wip">
                                    <input type="radio" name="req-search[status][]" value="wip" id="search-status-wip">
                                    Work in Progress
                                </label>
                            </li>
                            <li>
                                <label for="search-status-pending">
                                    <input type="radio" name="req-search[status][]" value="pending" id="search-status-pending">
                                    Pending
                                </label>
                            </li>
                            <li>
                                <label for="search-status-rejected">
                                    <input type="radio" name="req-search[status][]" value="rejected" id="search-status-rejected">
                                    Rejected
                                </label>
                            </li>
                        </ul>
                    </dd>
                    <!--PROGRAMME TAGS -->
                    <!--              -->
                    <dt id="toggle-programme-tags" class="filter-toggle-collapsed">
                        <button type="button" aria-expanded="false" aria-controls=programme_tags id="programme">Programme</button>
                    </dt>
                    <dd id="programme_tags" hidden class="expandable tags">
                        <ul>
                            <?php foreach ($programmes as $programme): ?>
                                <li>
                                    <label for="search-prog-<?php echo htmlspecialchars($programme['programme_id']); ?>">

                                        <input
                                            type="radio" name="req-search[programme][]"
                                            value="<?php echo htmlspecialchars($programme['programme_id']); ?>"
                                            id="search-prog-<?php echo htmlspecialchars($programme['programme_id']); ?>">

                                        <?php echo htmlspecialchars($programme['programme_name']); ?>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </dd>
                    <!-- CATEGORY TAGS -->
                    <!--               -->
                    <dt id="toggle-category-tags" class="filter-toggle-collapsed">
                        <button type="button" aria-expanded="false" aria-controls=category_tags id="category">Category</button>
                    </dt>
                    <dd id="category_tags" hidden class="expandable tags">
                        <ul>
                            <?php foreach ($c as $category): ?>
                                <li>
                                    <label for="search-category-<?php echo htmlspecialchars($category['category_id']); ?>">

                                        <input
                                            type="radio" name="req-search[category][]"
                                            value="<?php echo htmlspecialchars($category['category_id']); ?>"
                                            id="search-category-<?php echo htmlspecialchars($category['category_id']); ?>">

                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </dd>
                </dl>
            </dd>
        </dl>
    </fieldset>
</form>
<script>
    $('#priority').on('click', function() {
        const expanded = $(this).attr('aria-expanded') === 'true';
        $(this).attr('aria-expanded', !expanded);
        $('#priority_tags').prop('hidden', expanded);
    });

    $('#status').on('click', function() {
        const expanded = $(this).attr('aria-expanded') === 'true';
        $(this).attr('aria-expanded', !expanded);
        $('#status_tags').prop('hidden', expanded);
    });

    $('#programme').on('click', function() {
        const expanded = $(this).attr('aria-expanded') === 'true';
        $(this).attr('aria-expanded', !expanded);
        $('#programme_tags').prop('hidden', expanded);
    });

    $('#category').on('click', function() {
        const expanded = $(this).attr('aria-expanded') === 'true';
        $(this).attr('aria-expanded', !expanded);
        $('#category_tags').prop('hidden', expanded);
    });
</script>
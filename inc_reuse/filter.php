<?php

// Get categories
$filterCategoryStmt = $pdo->prepare("
    SELECT category_id, category_name
    FROM category
    ORDER BY category_name ASC
");

$filterCategoryStmt->execute();

$filterCategories =
    $filterCategoryStmt->fetchAll(PDO::FETCH_ASSOC);


// Current filter values
$filterSearch =
    trim($_GET['search'] ?? '');

$filterPriority =
    $_GET['priority'] ?? '';

$filterStatus =
    $_GET['status'] ?? '';

$filterCategory =
    $_GET['category'] ?? '';

$filterSort =
    $_GET['sort'] ?? 'newest';
?>

<form
    class="filters"
    id="req-filters"
    method="GET">

    <fieldset>

        <legend>Filter Requests</legend>


        <!-- =========================
             SEARCH
        ========================== -->
        <div class="filter-search">

            <label for="request-search">
                Search
            </label>

            <input
                type="text"
                id="request-search"
                name="search"
                placeholder="Request ID or title..."
                value="<?= htmlspecialchars($filterSearch); ?>">

        </div>


        <!-- =========================
             SORT
        ========================== -->
        <div class="sort">

            <label for="req-search-sort-dropdown">
                Sort by:
            </label>

            <select
                name="sort"
                id="req-search-sort-dropdown">

                <option
                    value="newest"
                    <?= $filterSort === 'newest'
                        ? 'selected'
                        : ''; ?>>

                    Newest

                </option>


                <option
                    value="oldest"
                    <?= $filterSort === 'oldest'
                        ? 'selected'
                        : ''; ?>>

                    Oldest

                </option>


                <option
                    value="title"
                    <?= $filterSort === 'title'
                        ? 'selected'
                        : ''; ?>>

                    Title

                </option>

            </select>

        </div>


        <!-- =========================
             PRIORITY
        ========================== -->
        <div class="filter-group">

            <button
                type="button"
                id="priority"
                aria-expanded="false">

                Priority
            </button>


            <div
                id="priority_tags"
                class="filter-options"
                hidden>

                <?php
                $priorities = [
                    'Low',
                    'Medium',
                    'High',
                    'Urgent'
                ];
                ?>


                <?php foreach ($priorities as $priority): ?>

                    <label>

                        <input
                            type="radio"
                            name="priority"
                            value="<?= htmlspecialchars($priority); ?>"
                            <?= $filterPriority === $priority
                                ? 'checked'
                                : ''; ?>>

                        <?= htmlspecialchars($priority); ?>

                    </label>

                <?php endforeach; ?>

            </div>

        </div>


        <!-- =========================
             STATUS
        ========================== -->
        <div class="filter-group">

            <button
                type="button"
                id="status"
                aria-expanded="false">

                Status
            </button>


            <div
                id="status_tags"
                class="filter-options"
                hidden>

                <?php
                $statuses = [
                    'Pending',
                    'In Progress',
                    'Completed',
                    'Rejected'
                ];
                ?>


                <?php foreach ($statuses as $status): ?>

                    <label>

                        <input
                            type="radio"
                            name="status"
                            value="<?= htmlspecialchars($status); ?>"
                            <?= $filterStatus === $status
                                ? 'checked'
                                : ''; ?>>

                        <?= htmlspecialchars($status); ?>

                    </label>

                <?php endforeach; ?>

            </div>

        </div>


        <!-- =========================
             CATEGORY
        ========================== -->
        <div class="filter-group">

            <button
                type="button"
                id="category"
                aria-expanded="false">

                Category
            </button>


            <div
                id="category_tags"
                class="filter-options"
                hidden>

                <?php foreach ($filterCategories as $category): ?>

                    <label>

                        <input
                            type="radio"
                            name="category"
                            value="<?= htmlspecialchars(
                                $category['category_id']
                            ); ?>"
                            <?= (string)$filterCategory ===
                                (string)$category['category_id']
                                ? 'checked'
                                : ''; ?>>

                        <?= htmlspecialchars(
                            $category['category_name']
                        ); ?>

                    </label>

                <?php endforeach; ?>

            </div>

        </div>


        <!-- =========================
             ACTION BUTTONS
        ========================== -->
        <div class="filter-actions">

            <button type="submit">
                Apply Filters
            </button>

            <a href="<?= htmlspecialchars(
                strtok($_SERVER['REQUEST_URI'], '?')
            ); ?>">
                Clear Filters
            </a>

        </div>

    </fieldset>

</form>


<script>
    $('#priority').on('click', function() {

        const expanded =
            $(this).attr('aria-expanded') === 'true';

        $(this).attr(
            'aria-expanded',
            expanded ? 'false' : 'true'
        );

        $('#priority_tags').prop(
            'hidden',
            expanded
        );
    });


    $('#status').on('click', function() {

        const expanded =
            $(this).attr('aria-expanded') === 'true';

        $(this).attr(
            'aria-expanded',
            expanded ? 'false' : 'true'
        );

        $('#status_tags').prop(
            'hidden',
            expanded
        );
    });


    $('#category').on('click', function() {

        const expanded =
            $(this).attr('aria-expanded') === 'true';

        $(this).attr(
            'aria-expanded',
            expanded ? 'false' : 'true'
        );

        $('#category_tags').prop(
            'hidden',
            expanded
        );
    });
</script>
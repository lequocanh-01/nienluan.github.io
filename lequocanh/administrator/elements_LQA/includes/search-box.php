<?php

/**
 * Reusable search box component for all tables
 * Include this file in any view that needs a search box
 *
 * Usage:
 * include './elements_LQA/includes/search-box.php';
 *
 * Parameters (optional - set them before including this file):
 * $searchFormId = ''; // ID for the search form, defaults to "search-form"
 * $tableBodyId = ''; // ID for the table body, defaults to "data-list"
 * $placeholderText = ''; // Placeholder text for the search input, defaults to "Tìm kiếm..."
 */

// Set default values if not already set
if (!isset($searchFormId)) $searchFormId = 'search-form';
if (!isset($tableBodyId)) $tableBodyId = 'data-list';
if (!isset($placeholderText)) $placeholderText = 'Tìm kiếm...';

// Output the search box HTML
?>
<div class="search-box">
    <form id="<?php echo $searchFormId; ?>" class="search-form" onsubmit="return false;">
        <input type="text" id="<?php echo $searchFormId; ?>-input" placeholder="<?php echo $placeholderText; ?>" />
        <button type="button" onclick="performTableSearch('<?php echo $searchFormId; ?>', '<?php echo $tableBodyId; ?>')">
            <i class="fas fa-search"></i> Tìm kiếm
        </button>
    </form>
</div>

<div id="no-results-message" class="no-results-message">
    Không tìm thấy kết quả phù hợp
</div>

<!-- Include the necessary JS and CSS only once -->
<?php if (!isset($searchScriptsIncluded)): ?>
    <link rel="stylesheet" href="./css_LQA/table-search.css">
    <script src="./js_LQA/table-search.js"></script>
    <?php $searchScriptsIncluded = true; ?>
<?php endif; ?>

<script>
    // Initialize the search functionality when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        setupTableSearch('<?php echo $searchFormId; ?>', '<?php echo $tableBodyId; ?>', '<?php echo $placeholderText; ?>');
    });

    // Function to perform search when button is clicked
    function performTableSearch(formId, tableBodyId) {
        const searchInput = document.getElementById(formId + '-input');
        const query = searchInput.value.toLowerCase().trim();
        const tableBody = document.getElementById(tableBodyId);
        const rows = tableBody.querySelectorAll('tr');
        let matchCount = 0;

        rows.forEach(function(row) {
            const text = row.textContent.toLowerCase();
            if (text.includes(query)) {
                row.style.display = '';
                matchCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Show or hide the no results message
        const noResultsMessage = document.getElementById('no-results-message');
        if (matchCount === 0 && query !== '') {
            noResultsMessage.classList.add('show');
        } else {
            noResultsMessage.classList.remove('show');
        }
    }
</script>
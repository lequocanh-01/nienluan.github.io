/**
 * Search Suggestions Script
 * Handles autocomplete search functionality for product search
 */
$(document).ready(function () {
  // Variables
  const searchInput = $("#searchInput");
  const searchResults = $("#searchResults");
  let searchTimeout;

  // Function to handle search input
  function handleSearchInput() {
    const term = searchInput.val().trim();

    // Clear previous results
    searchResults.empty();

    // Clear any pending timeout
    clearTimeout(searchTimeout);

    // Only search if term is at least 2 characters
    if (term.length < 2) {
      searchResults.hide();
      return;
    }

    // Set a small timeout to prevent searching on every keystroke
    searchTimeout = setTimeout(function () {
      // Show loading indicator
      searchResults.html(
        '<div class="text-center p-2"><i class="fas fa-spinner fa-spin"></i> Đang tìm kiếm...</div>'
      );
      searchResults.show();

      // Ajax request to get search suggestions
      $.ajax({
        url: "search_suggestions.php",
        method: "GET",
        data: { query: term },
        dataType: "json",
        success: function (data) {
          // Clear previous results
          searchResults.empty();

          if (data.length === 0) {
            searchResults.html(
              '<div class="text-center p-3">Không tìm thấy sản phẩm nào</div>'
            );
            return;
          }

          // Create results list
          const resultsList = $('<div class="search-results-list"></div>');

          // Add each item to results
          data.forEach(function (item) {
            const resultItem = `
                            <a href="index.php?reqHanghoa=${item.id}" class="search-item">
                                <div class="search-item-image">
                                    <img src="${item.image}" alt="${item.name}" onerror="this.src='uploads/products/default.jpg'">
                                </div>
                                <div class="search-item-info">
                                    <div class="search-item-name">${item.name}</div>
                                    <div class="search-item-price">${item.price}</div>
                                </div>
                            </a>
                        `;
            resultsList.append(resultItem);
          });

          // Append results to container
          searchResults.append(resultsList);
          searchResults.show();
        },
        error: function (xhr, status, error) {
          console.error("Search error:", error);
          searchResults.html(
            '<div class="text-center p-3 text-danger">Có lỗi xảy ra khi tìm kiếm</div>'
          );
        },
      });
    }, 300); // 300ms delay
  }

  // Event handlers
  searchInput.on("input", handleSearchInput);

  // Hide search results when clicking outside
  $(document).on("click", function (e) {
    if (!$(e.target).closest(".search-container").length) {
      searchResults.hide();
    }
  });

  // Prevent form submission when selecting a search result
  searchResults.on("click", "a", function (e) {
    e.preventDefault();
    window.location.href = $(this).attr("href");
  });

  // Handle Enter key press
  searchInput.on("keypress", function (e) {
    if (e.which === 13) {
      // If a search result is focused, navigate to it instead of submitting form
      const focusedResult = searchResults.find(".search-item:focus");
      if (focusedResult.length) {
        e.preventDefault();
        window.location.href = focusedResult.attr("href");
      }
      // Otherwise, form will submit normally
    }
  });
});

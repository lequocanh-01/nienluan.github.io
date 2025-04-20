/**
 * Table search functionality for admin tables
 * This script handles client-side search filtering
 */
console.log("Table search script loaded");

/**
 * Sets up table search functionality
 * @param {string} formId - ID of the search form
 * @param {string} tableBodyId - ID of the table body to search
 * @param {string} placeholderText - Placeholder text for the search input
 */
function setupTableSearch(formId, tableBodyId, placeholderText) {
  console.log("Setting up table search for:", {
    formId,
    tableBodyId,
  });
  const searchForm = document.getElementById(formId);
  const searchInput = document.getElementById(formId + "-input");
  const tableBody = document.getElementById(tableBodyId);
  const noResultsMessage = document.getElementById("no-results-message");

  if (!searchForm || !searchInput || !tableBody) {
    console.error("Search elements not found:", {
      formId,
      inputId: formId + "-input",
      tableBodyId,
    });
    return;
  }

  // Ensure the form doesn't submit
  searchForm.setAttribute("onsubmit", "return false;");

  // Handle input changes for real-time filtering
  searchInput.addEventListener("input", function () {
    const query = this.value.trim();

    // If query is empty, show all rows
    if (query === "") {
      resetTableRows(tableBody);
      noResultsMessage.classList.remove("show");
      return;
    }

    // Otherwise filter the rows
    filterTableRows(tableBody, query, noResultsMessage);
  });
}

/**
 * Resets all table rows to visible
 * @param {HTMLElement} tableBody - The table body element
 */
function resetTableRows(tableBody) {
  const rows = tableBody.querySelectorAll("tr");
  rows.forEach((row) => {
    row.style.display = "";
  });
}

/**
 * Filters table rows based on query
 * @param {HTMLElement} tableBody - The table body element
 * @param {string} query - The search query
 * @param {HTMLElement} noResultsMessage - Element to show when no results found
 */
function filterTableRows(tableBody, query, noResultsMessage) {
  const rows = tableBody.querySelectorAll("tr");
  let matchCount = 0;
  const searchTerms = query.toLowerCase().split(" ");

  rows.forEach((row) => {
    const text = row.textContent.toLowerCase();

    // Check if row matches all search terms
    const matchesAllTerms = searchTerms.every((term) => text.includes(term));

    if (matchesAllTerms) {
      row.style.display = "";
      matchCount++;
    } else {
      row.style.display = "none";
    }
  });

  // Show or hide the no results message
  if (matchCount === 0) {
    noResultsMessage.classList.add("show");
  } else {
    noResultsMessage.classList.remove("show");
  }
}

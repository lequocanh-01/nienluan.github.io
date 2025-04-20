/**
 * Test script to verify search functionality
 * This will be automatically loaded by the browser
 */
document.addEventListener("DOMContentLoaded", function () {
  console.log("Search test script loaded");

  // Check if product search elements exist
  const productSearchForm = document.getElementById("product-search");
  const productSearchInput = document.getElementById("product-search-input");
  const productList = document.getElementById("product-list");

  console.log("Product search elements detected:", {
    form: productSearchForm ? "Found" : "Not found",
    input: productSearchInput ? "Found" : "Not found",
    list: productList ? "Found" : "Not found",
  });

  // If elements exist, verify event listeners
  if (productSearchForm && productSearchInput) {
    console.log(
      "Search form onsubmit attribute:",
      productSearchForm.getAttribute("onsubmit")
    );

    // Add test input event
    productSearchInput.addEventListener("focus", function () {
      console.log("Product search input focused - events are working");
    });

    // Monitor search function calls
    const originalFilterTableRows = window.filterTableRows;
    if (typeof originalFilterTableRows === "function") {
      window.filterTableRows = function (tableBody, query, noResultsMessage) {
        console.log("Filter table rows called with query:", query);
        return originalFilterTableRows(tableBody, query, noResultsMessage);
      };
    }
  }
});

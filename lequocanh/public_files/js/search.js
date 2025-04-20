document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("search-input");
  const searchResultsContainer = document.getElementById("search-results");

  if (!searchInput || !searchResultsContainer) {
    console.error("Search elements not found.");
    return;
  }

  let debounceTimer;
  const debounceDelay = 300; // milliseconds

  // Function to fetch search suggestions
  function fetchSearchSuggestions(query) {
    if (!query || query.length < 2) {
      hideSearchResults();
      return;
    }

    fetch(`search_suggestions.php?query=${encodeURIComponent(query)}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        displaySearchResults(data);
      })
      .catch((error) => {
        console.error("Error fetching search suggestions:", error);
        hideSearchResults();
      });
  }

  // Function to display search results
  function displaySearchResults(results) {
    // Clear previous results
    searchResultsContainer.innerHTML = "";

    // Hide results container if no results
    if (!results || results.length === 0) {
      hideSearchResults();
      return;
    }

    // Create result list
    const resultsList = document.createElement("ul");
    resultsList.className = "search-results-list";

    // Add each result to the list
    results.forEach((product) => {
      const listItem = document.createElement("li");
      listItem.className = "search-item";

      // Create link to product
      const productLink = document.createElement("a");
      productLink.href = product.url;
      productLink.className = "search-item-link";

      // Product image
      const imageContainer = document.createElement("div");
      imageContainer.className = "search-item-image";
      const image = document.createElement("img");
      image.src = product.image;
      image.alt = product.name;
      image.onerror = function () {
        this.src = "uploads/products/default.jpg";
      };
      imageContainer.appendChild(image);

      // Product info
      const infoContainer = document.createElement("div");
      infoContainer.className = "search-item-info";

      // Product name
      const name = document.createElement("div");
      name.className = "search-item-name";
      name.textContent = product.name;

      // Product price
      const price = document.createElement("div");
      price.className = "search-item-price";
      price.textContent = product.price;

      // Add brand if available
      if (product.brand) {
        const brand = document.createElement("div");
        brand.className = "search-item-brand";
        brand.textContent = product.brand;
        infoContainer.appendChild(brand);
      }

      // Assemble product info
      infoContainer.appendChild(name);
      infoContainer.appendChild(price);

      // Assemble product link
      productLink.appendChild(imageContainer);
      productLink.appendChild(infoContainer);

      // Add to list item
      listItem.appendChild(productLink);

      // Add to results list
      resultsList.appendChild(listItem);
    });

    // Add results list to container
    searchResultsContainer.appendChild(resultsList);

    // Show results container
    showSearchResults();

    // Add click event to close search results when clicking outside
    document.addEventListener("click", handleOutsideClick);
  }

  // Function to show search results
  function showSearchResults() {
    searchResultsContainer.style.display = "block";
  }

  // Function to hide search results
  function hideSearchResults() {
    searchResultsContainer.style.display = "none";
  }

  // Handle clicks outside the search container
  function handleOutsideClick(event) {
    if (
      !searchResultsContainer.contains(event.target) &&
      event.target !== searchInput
    ) {
      hideSearchResults();
      document.removeEventListener("click", handleOutsideClick);
    }
  }

  // Add event listener for input changes with debounce
  searchInput.addEventListener("input", function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      const query = this.value.trim();
      fetchSearchSuggestions(query);
    }, debounceDelay);
  });

  // Show results when focusing on the input if there's text
  searchInput.addEventListener("focus", function () {
    const query = this.value.trim();
    if (query.length >= 2) {
      fetchSearchSuggestions(query);
    }
  });

  // Add event listener for search form submission
  const searchForm = searchInput.closest("form");
  if (searchForm) {
    searchForm.addEventListener("submit", function (e) {
      const query = searchInput.value.trim();
      if (query.length < 2) {
        e.preventDefault();
      }
    });
  }
});

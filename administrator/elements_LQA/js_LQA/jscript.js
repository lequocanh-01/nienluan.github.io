$(document).ready(function () {
  // ... existing code ...

  //
  let searchTimeout;
  const searchInput = $("#searchInput");
  const searchResults = $("#searchResults");

  // Xử lý sự kiện nhập vào ô tìm kiếm
  searchInput.on("input", function () {
    clearTimeout(searchTimeout);
    const query = $(this).val().trim();

    if (query.length >= 2) {
      searchTimeout = setTimeout(() => {
        console.log("Searching for:", query);
        $.ajax({
          url: window.location.origin + "/search_suggestions.php",
          method: "GET",
          data: { term: query },
          dataType: "json",
          success: function (data) {
            console.log("Search results:", data);
            if (data && data.length > 0) {
              let html = "";
              data.forEach((item) => {
                console.log("Processing item:", item);
                // Debug thêm thông tin về ảnh
                console.log("Image URL:", item.image);
                
                // Kiểm tra xem image có tồn tại không
                let img = new Image();
                img.onload = function() {
                  console.log("Image loaded successfully:", item.image);
                };
                img.onerror = function() {
                  console.error("Image failed to load:", item.image);
                  // Cố gắng tải lại với đường dẫn tuyệt đối
                  this.src = window.location.origin + '/img_LQA/updating-image.png';
                  console.log("Using fallback image:", this.src);
                };
                img.src = item.image;
                
                // Sử dụng đường dẫn tuyệt đối cho ảnh fallback
                html += `
                                <a href="index.php?reqHanghoa=${item.id}" class="text-decoration-none text-dark">
                                    <div class="search-suggestion">
                                        <img src="${item.image}" alt="${item.name}" onerror="this.src='${window.location.origin}/img_LQA/updating-image.png'; console.log('Image load error, using absolute fallback for: ' + item.name);">
                                        <div>
                                            <div class="fw-bold">${item.name}</div>
                                            <div class="text-muted">${item.price}</div>
                                        </div>
                                    </div>
                                </a>`;
              });
              searchResults.html(html).show();
              console.log("Search results displayed");
            } else {
              searchResults
                .html('<div class="p-3">Không tìm thấy sản phẩm nào</div>')
                .show();
              console.log("No search results found");
            }
          },
          error: function (xhr, status, error) {
            console.error("Search error:", error, xhr.responseText);
            searchResults
              .html('<div class="p-3">Có lỗi xảy ra khi tìm kiếm</div>')
              .show();
          },
        });
      }, 300);
    } else {
      searchResults.hide();
    }
  });

  // ... existing code ...
});

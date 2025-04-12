$(document).ready(function () {
  // Add debugging to console
  console.log("Modal handler script loaded");

  // Generic handler for all update buttons
  $(document).on("click", ".generic-update-btn", function (e) {
    e.preventDefault();

    // Get data attributes
    var module = $(this).data("module");
    var updateUrl = $(this).data("update-url");
    var idParam = $(this).data("id-param");
    var title = $(this).data("title");
    var id = $(this).data("id");

    console.log("Update button clicked for", module, "with ID", id);
    console.log("Loading from URL:", updateUrl);

    if (
      module === "mloaihang" ||
      module === "mnhanvien" ||
      module === "mdonvitinh" ||
      module === "mthuoctinhhh" ||
      module === "mthuonghieu"
    ) {
      // Remove any existing dynamically created modal
      $("#dynamic-update-modal").remove();

      // Create a new modal dynamically
      var modalHtml = `
        <div id="dynamic-update-modal" style="
          display: block; 
          position: fixed; 
          z-index: 10000; 
          top: 50%; 
          left: 50%; 
          transform: translate(-50%, -50%); 
          background: white; 
          padding: 25px; 
          border-radius: 8px; 
          box-shadow: 0 5px 20px rgba(0,0,0,0.3); 
          width: 600px; 
          max-height: 90vh; 
          overflow-y: auto;
          border: 2px solid #3498db;">
          <button id="dynamic-close-btn" style="
        position: absolute;
            top: 10px;
            right: 10px;
            background: #f44336;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
        border-radius: 50%;
            font-weight: bold;
            cursor: pointer;">X</button>
          <h3 style="margin-top: 0; color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px;">${title}</h3>
          <div id="dynamic-update-form" style="margin-top: 15px;"></div>
        </div>
      `;

      // Append to body
      $("body").append(modalHtml);

      // Create parameter object
      var params = {};
      params[idParam] = id;

      // Load the update form into the dynamic modal
      $.ajax({
        url: updateUrl,
        type: "POST",
        data: params,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
        success: function (response) {
          $("#dynamic-update-form").html(response);
          console.log("Update form loaded successfully into dynamic modal");

          // Bind form submission for loaihang
          $(document).on("submit", "#formupdatelh", function (e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
              url: "./elements_LQA/mLoaihang/loaihangAct.php?reqact=updateloaihang",
              type: "POST",
              data: formData,
              headers: {
                "X-Requested-With": "XMLHttpRequest",
              },
              success: function (response) {
                console.log("Update response received:", response);

                // Force reload regardless of response content
                $("#dynamic-update-modal").remove();
                console.log("Reloading page now...");

                // Try multiple reload methods
                try {
                  // Method 1: Direct location change
                  window.location.href =
                    "index.php?req=loaihangview&t=" + new Date().getTime();
                } catch (e) {
                  console.error("Method 1 failed:", e);

                  // Method 2: Reload current page
                  try {
                    window.location.reload(true);
                  } catch (e2) {
                    console.error("Method 2 failed:", e2);

                    // Method 3: Replace location
                    window.location.replace("index.php?req=loaihangview");
                  }
                }
              },
              error: function (xhr, status, error) {
                $("#noteForm").html(
                  '<div style="color: red; font-weight: bold;">Lỗi: ' +
                    error +
                    "</div>"
                );
              },
              cache: false,
              contentType: false,
              processData: false,
            });
          });

          // Bind form submission for nhanvien
          $(document).on("submit", "#updatenhanvien", function (e) {
            e.preventDefault();
            console.log("Nhanvien form submitted");
            var formData = new FormData(this);

            // Debug values
            for (var pair of formData.entries()) {
              console.log(pair[0] + ": " + pair[1]);
            }

            $.ajax({
              url: "./elements_LQA/mnhanvien/nhanvienAct.php?reqact=updatenhanvien",
              type: "POST",
              data: formData,
              headers: {
                "X-Requested-With": "XMLHttpRequest",
              },
              success: function (response) {
                console.log("Update response received:", response);

                // Force reload regardless of response content
                $("#dynamic-update-modal").remove();
                console.log("Reloading page now...");

                // Try multiple reload methods
                try {
                  // Method 1: Direct location change
                  window.location.href =
                    "index.php?req=nhanvienview&t=" + new Date().getTime();
                } catch (e) {
                  console.error("Method 1 failed:", e);

                  // Method 2: Reload current page
                  try {
                    window.location.reload(true);
                  } catch (e2) {
                    console.error("Method 2 failed:", e2);

                    // Method 3: Replace location
                    window.location.replace("index.php?req=nhanvienview");
                  }
                }
              },
              error: function (xhr, status, error) {
                $("#noteForm").html(
                  '<div style="color: red; font-weight: bold;">Lỗi: ' +
                    error +
                    "</div>"
                );
              },
              cache: false,
              contentType: false,
              processData: false,
            });
          });

          // Bind form submission for donvitinh
          $(document).on("submit", "#updatedonvitinh", function (e) {
            e.preventDefault();
            console.log("Donvitinh form submitted");
            var formData = new FormData(this);

            // Debug values
            console.log("Form data being sent:");
            for (var pair of formData.entries()) {
              console.log(pair[0] + ": " + pair[1]);
            }

            $.ajax({
              url: "./elements_LQA/mdonvitinh/donvitinhAct.php?reqact=updatedonvitinh",
              type: "POST",
              data: formData,
              headers: {
                "X-Requested-With": "XMLHttpRequest",
              },
              success: function (response) {
                console.log("Full response:", response);

                try {
                  // Try to parse if it's a string
                  if (typeof response === "string") {
                    response = JSON.parse(response);
                  }

                  console.log("Parsed response:", response);

                  if (response.debug) {
                    console.log("Debug info:", response.debug);
                  }
                } catch (e) {
                  console.error("Error parsing response:", e);
                }

                // Force reload regardless of response content
                $("#dynamic-update-modal").remove();
                console.log("Reloading page now...");

                // Try multiple reload methods
                try {
                  // Method 1: Direct location change with cache busting
                  window.location.href =
                    "index.php?req=donvitinhview&t=" + new Date().getTime();
                } catch (e) {
                  console.error("Method 1 failed:", e);

                  // Method 2: Reload current page
                  try {
                    window.location.reload(true);
                  } catch (e2) {
                    console.error("Method 2 failed:", e2);

                    // Method 3: Replace location
                    window.location.replace("index.php?req=donvitinhview");
                  }
                }
              },
              error: function (xhr, status, error) {
                $("#noteForm").html(
                  '<div style="color: red; font-weight: bold;">Lỗi: ' +
                    error +
                    "</div>"
                );
              },
              cache: false,
              contentType: false,
              processData: false,
            });
          });

          // Bind form submission for thuoctinhhh
          $(document).on("submit", "#updatethuoctinhhh", function (e) {
            e.preventDefault();
            console.log("Thuoctinhhh form submitted");
            var formData = new FormData(this);

            // Debug values
            console.log("Form data being sent:");
            for (var pair of formData.entries()) {
              console.log(pair[0] + ": " + pair[1]);
            }

            $.ajax({
              url: "./elements_LQA/mthuoctinhhh/thuoctinhhhAct.php?reqact=updatethuoctinhhh",
              type: "POST",
              data: formData,
              headers: {
                "X-Requested-With": "XMLHttpRequest",
              },
              success: function (response) {
                console.log("Update response received:", response);

                // Force reload regardless of response content
                $("#dynamic-update-modal").remove();
                console.log("Reloading page now...");

                // Try multiple reload methods
                try {
                  // Method 1: Direct location change
                  window.location.href =
                    "index.php?req=thuoctinhhhview&t=" + new Date().getTime();
                } catch (e) {
                  console.error("Method 1 failed:", e);

                  // Method 2: Reload current page
                  try {
                    window.location.reload(true);
                  } catch (e2) {
                    console.error("Method 2 failed:", e2);

                    // Method 3: Replace location
                    window.location.replace("index.php?req=thuoctinhhhview");
                  }
                }
              },
              error: function (xhr, status, error) {
                console.error("AJAX error:", xhr, status, error);
                $("#noteForm").html(
                  '<div style="color: red; font-weight: bold;">Lỗi: ' +
                    error +
                    "</div>"
                );
              },
              cache: false,
              contentType: false,
              processData: false,
            });
          });

          // Bind form submission for thuonghieu
          $(document).on("submit", "#update-form", function (e) {
            e.preventDefault();
            console.log("Thuonghieu form submitted");
            var formData = new FormData(this);

            // Debug values
            console.log("Form data being sent:");
            for (var pair of formData.entries()) {
              console.log(pair[0] + ": " + pair[1]);
            }

            $.ajax({
              url: "./elements_LQA/mthuonghieu/thuonghieuAct.php?reqact=updatethuonghieu",
              type: "POST",
              data: formData,
              headers: {
                "X-Requested-With": "XMLHttpRequest",
              },
              success: function (response) {
                console.log("Update response received:", response);

                // Force reload regardless of response content
                $("#dynamic-update-modal").remove();
                console.log("Reloading page now...");

                // Try multiple reload methods
                try {
                  // Method 1: Direct location change
                  window.location.href =
                    "index.php?req=thuonghieuview&t=" + new Date().getTime();
                } catch (e) {
                  console.error("Method 1 failed:", e);

                  // Method 2: Reload current page
                  try {
                    window.location.reload(true);
                  } catch (e2) {
                    console.error("Method 2 failed:", e2);

                    // Method 3: Replace location
                    window.location.replace("index.php?req=thuonghieuview");
                  }
                }
              },
              error: function (xhr, status, error) {
                $("#noteForm").html(
                  '<div style="color: red; font-weight: bold;">Lỗi: ' +
                    error +
                    "</div>"
                );
              },
              cache: false,
              contentType: false,
              processData: false,
            });
          });
        },
        error: function (xhr, status, error) {
          console.error("Error loading update form:", error);
          $("#dynamic-update-form").html(
            '<div style="color: red">Lỗi tải biểu mẫu: ' + error + "</div>"
          );
        },
      });

      // Close button handler for dynamic modal
      $(document).on("click", "#dynamic-close-btn", function () {
        $("#dynamic-update-modal").remove();
      });
    }
  });

  // Close button handler for original modal
  $(document).on("click", "#w_close_btn", function (e) {
    e.preventDefault();
    $("#w_update").hide().removeClass("visible-modal");
  });
});

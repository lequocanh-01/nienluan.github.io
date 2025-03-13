$(document).ready(function () {
  // Shopping Cart Functionality
  $('.cart-quantity button').on('click', function() {
    const input = $(this).siblings('input');
    const currentValue = parseInt(input.val());
    
    if ($(this).hasClass('decrease')) {
      if (currentValue > 1) {
        input.val(currentValue - 1);
        updateCartItem(input);
      }
    } else {
      input.val(currentValue + 1);
      updateCartItem(input);
    }
  });

  $('.cart-quantity input').on('change', function() {
    updateCartItem($(this));
  });

  $('.cart-remove').on('click', function() {
    const cartItem = $(this).closest('.cart-item');
    const itemId = cartItem.data('id');
    
    $.ajax({
      url: './elements_LQA/mgiohang/giohangAct.php?reqact=remove',
      type: 'POST',
      data: { id: itemId },
      success: function(response) {
        cartItem.fadeOut(300, function() {
          $(this).remove();
          updateCartTotal();
        });
      },
      error: function(error) {
        console.error('Error removing item:', error);
      }
    });
  });

  function updateCartItem(input) {
    const cartItem = input.closest('.cart-item');
    const itemId = cartItem.data('id');
    const quantity = parseInt(input.val());
    
    $.ajax({
      url: './elements_LQA/mgiohang/giohangAct.php?reqact=update',
      type: 'POST',
      data: {
        id: itemId,
        quantity: quantity
      },
      success: function(response) {
        updateCartTotal();
      },
      error: function(error) {
        console.error('Error updating quantity:', error);
      }
    });
  }

  function updateCartTotal() {
    $.ajax({
      url: './elements_LQA/mgiohang/giohangAct.php?reqact=gettotal',
      type: 'GET',
      success: function(response) {
        $('.cart-total-amount').text(response.total);
      },
      error: function(error) {
        console.error('Error updating total:', error);
      }
    });
  }

  // Menu interaction
  $(".itemOrder").hide();
  $(".cateOrder").click(function () {
    $(this).next().slideDown();
  });
  $(".itemOrder").mouseleave(function () {
    $(this).slideUp();
  });

  // Form validation and submission
  $("#formreg").submit(function (event) {
    event.preventDefault();
    $("#noteForm").html("");
    var isValid = true;

    // Form validation logic (Username, Password, Hoten, etc.)
    // ...

    if (isValid) {
      $.ajax({
        url: "./elements_LQA/mUser/userAct.php?reqact=addnew",
        type: "POST",
        data: $("#formreg").serialize(),
        success: function (response) {
          console.log("Form submitted successfully:", response);
        },
        error: function (error) {
          console.error("Error submitting form:", error);
        },
      });
    }
  });

  // Setup for loaihang update
  $("#w_update").hide();
  $(".w_update_btn_open").click(function (e) {
    e.preventDefault();
    $("#w_update").css("left", e.pageX + 5);
    $("#w_update").css("top", e.pageY + 5);

    var $idloaihang = $(this).attr("value");

    $("#w_update_form").load(
      "./elements_LQA/mloaihang/loaihangUpdate.php",
      { idloaihang: $idloaihang },
      function (response, status, request) {
        this;
      }
    );
    $("#w_update").show();
  });
  $("#w_close_btn").click(function (e) {
    e.preventDefault();
    $("#w_update").hide();
  });

  // Setup for hanghoa update
  $("#w_update_hh").hide();
  $(".w_update_btn_open_hh").click(function (e) {
    e.preventDefault();
    $("#w_update_hh").css("top", e.pageY + 5);
    $("#w_update_hh").css("left", e.pageX + 5);

    var $idhanghoa = $(this).attr("value");
    $("#w_update_form_hh").load(
      "./elements_LQA/mhanghoa/hanghoaUpdate.php",
      { idhanghoa: $idhanghoa },
      function (response, status, request) {
        this;
      }
    );
    $("#w_update_hh").show();
  });
  $("#w_close_btn_hh").click(function (e) {
    e.preventDefault();
    $("#w_update_hh").hide();
  });

  // Setup for dongia update
  $("#w_update_dg").hide();
  $(".w_update_btn_open_dg").click(function (e) {
    e.preventDefault();

    $("#w_update_dg").css("top", e.pageY + 5);
    $("#w_update_dg").css("left", e.pageX + 5);
    var $idDonGia = $(this).data("id");
    $("#w_update_form_dg").load(
      "./elements_LQA/mdongia/dongiaUpdate.php",
      { idDonGia: $idDonGia },
      function (response, status, request) {
        this;
      }
    );

    $("#w_update_dg").show();
  });

  $("#w_close_btn_dg").click(function (e) {
    e.preventDefault();
    $("#w_update_dg").hide();
  });

  // Setup for thuonghieu update
  $("#w_update_th").hide();
  $(".w_update_btn_open_th").click(function (e) {
    e.preventDefault();
    $("#w_update_th").css("top", e.pageY + 5);
    $("#w_update_th").css("left", e.pageX + 5);

    var $idThuongHieu = $(this).attr("value");
    $("#w_update_form_th").load(
      "./elements_LQA/mthuonghieu/thuonghieuUpdate.php",
      { idThuongHieu: $idThuongHieu },
      function (response, status, request) {
        this;
      }
    );
    $("#w_update_th").show();
  });
  $("#w_close_btn_th").click(function (e) {
    e.preventDefault();
    $("#w_update_th").hide();
  });

  // Setup for nhanvien update
  $("#w_update_nv").hide();
  $(".w_update_btn_open_nv").click(function (e) {
    e.preventDefault();
    $("#w_update_nv").css("top", e.pageY + 5);
    $("#w_update_nv").css("left", e.pageX + 5);

    var $idNhanVien = $(this).attr("value");
    $("#w_update_form_nv").load(
      "./elements_LQA/mnhanvien/nhanvienUpdate.php",
      { idNhanVien: $idNhanVien },
      function (response, status, request) {
        this;
      }
    );
    $("#w_update_nv").show();
  });
  $("#w_close_btn_nv").click(function (e) {
    e.preventDefault();
    $("#w_update_nv").hide();
  });

  // Setup for donvitinh update
  $("#w_update_dvt").hide();
  $(".w_update_btn_open_dvt").click(function (e) {
    e.preventDefault();
    $("#w_update_dvt").css("top", e.pageY + 5);
    $("#w_update_dvt").css("left", e.pageX + 5);

    var $idDonViTinh = $(this).attr("value");
    $("#w_update_form_dvt").load(
      "./elements_LQA/mdonvitinh/donvitinhUpdate.php",
      { idDonViTinh: $idDonViTinh },
      function (response, status, request) {
        this;
      }
    );
    $("#w_update_dvt").show();
  });
  $("#w_close_btn_dvt").click(function (e) {
    e.preventDefault();
    $("#w_update_dvt").hide();
  });

  // Setup for thuoctinh update
  $("#w_update_tt").hide();
  $(".w_update_btn_open_tt").click(function (e) {
    e.preventDefault();
    $("#w_update_tt").css("top", e.pageY + 5);
    $("#w_update_tt").css("left", e.pageX + 5);

    var $idThuocTinh = $(this).attr("value");
    $("#w_update_form_tt").load(
      "./elements_LQA/mthuoctinh/thuoctinhUpdate.php",
      { idThuocTinh: $idThuocTinh },
      function (response, status, request) {
        if (status === "error") {
          console.error(
            "Error loading update form:",
            request.status,
            request.statusText
          );
        }
      }
    );
    $("#w_update_tt").show();
  });
  $("#w_close_btn_tt").click(function (e) {
    e.preventDefault();
    $("#w_update_tt").hide();
  });
  //update thuoctinhhh
  $("#w_update_tthh").hide();
  $(".w_update_btn_open_tthh").click(function (e) {
    e.preventDefault();
    $("#w_update_tthh").css("top", e.pageY + 5);
    $("#w_update_tthh").css("left", e.pageX + 5);

    var $idThuocTinhHH = $(this).attr("value");
    $("#w_update_form_tthh").load(
      "./elements_LQA/mthuoctinhhh/thuoctinhhhUpdate.php",
      { idThuocTinhHH: $idThuocTinhHH },
      function (response, status, request) {
        this;
      }
    );
    $("#w_update_tthh").show();
  });
  $("#w_close_btn_tthh").click(function (e) {
    e.preventDefault();
    $("#w_update_tthh").hide();
  });
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
        $.ajax({
          url: window.location.origin + "/search_suggestions.php",
          method: "GET",
          data: { term: query },
          dataType: "json",
          success: function (data) {
            if (data && data.length > 0) {
              let html = "";
              data.forEach((item) => {
                html += `
                                <a href="index.php?reqHanghoa=${item.id}" class="text-decoration-none text-dark">
                                    <div class="search-suggestion">
                                        <img src="data:image/png;base64,${item.image}" alt="${item.name}">
                                        <div>
                                            <div class="fw-bold">${item.name}</div>
                                            <div class="text-muted">${item.price}</div>
                                        </div>
                                    </div>
                                </a>`;
              });
              searchResults.html(html).show();
            } else {
              searchResults
                .html('<div class="p-3">Không tìm thấy sản phẩm nào</div>')
                .show();
            }
          },
          error: function () {
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

  // Ẩn kết quả khi click ra ngoài
  $(document).on("click", function (e) {
    if (!$(e.target).closest(".search-container").length) {
      searchResults.hide();
    }
  });

  // Xử lý form submit
  $("#searchForm").on("submit", function (e) {
    const query = searchInput.val().trim();
    if (query.length < 2) {
      e.preventDefault();
      alert("Vui lòng nhập ít nhất 2 ký tự để tìm kiếm");
    }
  });
  // Carousel initialization
  document.addEventListener("DOMContentLoaded", function () {
    var carousel = new bootstrap.Carousel(
      document.getElementById("productCarousel"),
      {
        interval: 3000, // Thời gian chuyển slide (3 giây)
        wrap: true, // Cho phép quay vòng
        keyboard: true, // Cho phép điều khiển bằng bàn phím
        pause: "hover", // Tạm dừng khi di chuột qua
      }
    );
  });
  //jscript của hinhanhview
  document.getElementById("selectAll").addEventListener("change", function () {
    const checkboxes = document.getElementsByClassName("image-checkbox");
    for (let checkbox of checkboxes) {
      checkbox.checked = this.checked;
    }
  });

  document
    .getElementById("selectAllBtn")
    .addEventListener("click", function () {
      const selectAllCheckbox = document.getElementById("selectAll");
      selectAllCheckbox.checked = !selectAllCheckbox.checked;
      const event = new Event("change");
      selectAllCheckbox.dispatchEvent(event);
    });

  document
    .getElementById("deleteSelectedBtn")
    .addEventListener("click", function () {
      const selectedImages = [];
      const checkboxes = document.getElementsByClassName("image-checkbox");
      for (let checkbox of checkboxes) {
        if (checkbox.checked) {
          selectedImages.push(checkbox.value);
        }
      }

      if (selectedImages.length === 0) {
        alert("Vui lòng chọn ít nhất một hình ảnh để xóa");
        return;
      }

      if (confirm("Bạn có chắc chắn muốn xóa các hình ảnh đã chọn?")) {
        deleteMultipleImages(selectedImages);
      }
    });

  function deleteImage(id) {
    if (confirm("Bạn có chắc chắn muốn xóa hình ảnh này?")) {
      fetch("elements_LQA/mhinhanh/hinhanhAct.php?reqact=deleteimage", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "id=" + id,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            location.reload();
          } else {
            alert("Có lỗi xảy ra khi xóa hình ảnh: " + data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Có lỗi xảy ra khi xóa hình ảnh");
        });
    }
  }

  function deleteMultipleImages(ids) {
    fetch("elements_LQA/mhinhanh/hinhanhAct.php?reqact=deletemultiple", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "ids=" + JSON.stringify(ids),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          location.reload();
        } else {
          alert("Có lỗi xảy ra khi xóa hình ảnh: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Có lỗi xảy ra khi xóa hình ảnh");
      });
  }
  // Kiểm tra xem element carousel có tồn tại không trước khi khởi tạo
  document.addEventListener("DOMContentLoaded", function () {
    const carouselElement = document.getElementById("productCarousel");
    if (carouselElement) {
      new bootstrap.Carousel(carouselElement, {
        interval: 3000,
        wrap: true,
        keyboard: true,
        pause: "hover",
      });
    }
  });
});
// xử lý xóa checkbox bảng hình ảnh
document.addEventListener("DOMContentLoaded", function () {
  const selectAll = document.getElementById("select-all");
  const selectItems = document.querySelectorAll(".select-item");
  const deleteSelectedBtn = document.getElementById("delete-selected");
  const deleteButtons = document.querySelectorAll(".delete-btn");

  // Xử lý chọn tất cả
  selectAll.addEventListener("change", function () {
    selectItems.forEach((item) => {
      item.checked = this.checked;
    });
    updateDeleteSelectedButton();
  });

  // Xử lý chọn từng item
  selectItems.forEach((item) => {
    item.addEventListener("change", function () {
      updateDeleteSelectedButton();
      // Kiểm tra nếu tất cả các item đều được chọn thì check selectAll
      const allChecked = Array.from(selectItems).every((item) => item.checked);
      selectAll.checked = allChecked;
    });
  });

  // Cập nhật trạng thái nút xóa đã chọn
  function updateDeleteSelectedButton() {
    const checkedItems = document.querySelectorAll(".select-item:checked");
    deleteSelectedBtn.style.display =
      checkedItems.length > 0 ? "inline-block" : "none";
  }

  // Xử lý xóa nhiều ảnh
  deleteSelectedBtn.addEventListener("click", function () {
    const selectedIds = Array.from(
      document.querySelectorAll(".select-item:checked")
    ).map((checkbox) => checkbox.value);

    if (selectedIds.length === 0) {
      alert("Vui lòng chọn ít nhất một hình ảnh để xóa");
      return;
    }

    if (confirm("Bạn có chắc chắn muốn xóa các hình ảnh đã chọn?")) {
      fetch("elements_LQA/mhinhanh/hinhanhAct.php?reqact=deletemultiple", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          ids: selectedIds,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            location.reload();
          } else {
            alert("Có lỗi xảy ra khi xóa hình ảnh: " + data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Có lỗi xảy ra khi xóa hình ảnh");
        });
    }
  });

  // Xử lý xóa một ảnh
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const imageId = this.getAttribute("data-id");
      if (confirm("Bạn có chắc chắn muốn xóa hình ảnh này?")) {
        fetch("elements_LQA/mhinhanh/hinhanhAct.php?reqact=deleteimage", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: "id=" + imageId,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              location.reload();
            } else {
              alert("Có lỗi xảy ra khi xóa hình ảnh: " + data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("Có lỗi xảy ra khi xóa hình ảnh");
          });
      }
    });
  });
});
// jscript hanghoaView
document.addEventListener("DOMContentLoaded", function () {
  // Xử lý nhấp chuột xem trước hình ảnh
  const previewItems = document.querySelectorAll(".preview-item");
  const imageSelect = document.querySelector('select[name="id_hinhanh"]');

  previewItems.forEach((item) => {
    item.addEventListener("click", function () {
      const img = this.querySelector(".preview-img");
      const imageId = img.getAttribute("data-id");
      imageSelect.value = imageId;

      // Xóa lớp đã chọn khỏi tất cả các mục
      previewItems.forEach((item) => item.classList.remove("selected"));
      // Thêm lớp đã chọn vào mục đã nhấp
      this.classList.add("selected");
    });
  });

  // Xử lý lựa chọn thay đổi
  imageSelect.addEventListener("change", function () {
    const selectedId = this.value;
    previewItems.forEach((item) => {
      const img = item.querySelector(".preview-img");
      if (img.getAttribute("data-id") === selectedId) {
        item.classList.add("selected");
      } else {
        item.classList.remove("selected");
      }
    });
  });
});
// jscript userview
$(document).ready(function () {
  // Shopping Cart Functionality
  $('.cart-quantity button').on('click', function() {
    const input = $(this).siblings('input');
    const currentValue = parseInt(input.val());
    
    if ($(this).hasClass('decrease')) {
      if (currentValue > 1) {
        input.val(currentValue - 1);
        updateCartItem(input);
      }
    } else {
      input.val(currentValue + 1);
      updateCartItem(input);
    }
  });

  $('.cart-quantity input').on('change', function() {
    updateCartItem($(this));
  });

  $('.cart-remove').on('click', function() {
    const cartItem = $(this).closest('.cart-item');
    const itemId = cartItem.data('id');
    
    $.ajax({
      url: './elements_LQA/mgiohang/giohangAct.php?reqact=remove',
      type: 'POST',
      data: { id: itemId },
      success: function(response) {
        cartItem.fadeOut(300, function() {
          $(this).remove();
          updateCartTotal();
        });
      },
      error: function(error) {
        console.error('Error removing item:', error);
      }
    });
  });

  function updateCartItem(input) {
    const cartItem = input.closest('.cart-item');
    const itemId = cartItem.data('id');
    const quantity = parseInt(input.val());
    
    $.ajax({
      url: './elements_LQA/mgiohang/giohangAct.php?reqact=update',
      type: 'POST',
      data: {
        id: itemId,
        quantity: quantity
      },
      success: function(response) {
        updateCartTotal();
      },
      error: function(error) {
        console.error('Error updating quantity:', error);
      }
    });
  }

  function updateCartTotal() {
    $.ajax({
      url: './elements_LQA/mgiohang/giohangAct.php?reqact=gettotal',
      type: 'GET',
      success: function(response) {
        $('.cart-total-amount').text(response.total);
      },
      error: function(error) {
        console.error('Error updating total:', error);
      }
    });
  }

  // Xử lý xác nhận xóa người dùng
  function confirmDelete(username) {
    if (username === "admin") {
      const adminPass = prompt(
        "Vui lòng nhập mật khẩu admin để xóa tài khoản admin:"
      );
      if (!adminPass) {
        return false;
      }
      // Thêm mật khẩu vào URL
      const currentUrl = window.location.href;
      const separator = currentUrl.includes("?") ? "&" : "?";
      window.location.href =
        currentUrl +
        separator +
        "admin_password=" +
        encodeURIComponent(adminPass);
      return false;
    }
    return confirm("Bạn có chắc muốn xóa người dùng này không?");
  }

  // Xử lý cập nhật người dùng
  $(".update-user").click(function (e) {
    e.preventDefault();
    const username = $(this).data("username");
    const userId = $(this).data("userid");

    if (username === "admin") {
      const adminPass = prompt(
        "Vui lòng nhập mật khẩu xác thực để cập nhật tài khoản admin:"
      );
      if (!adminPass) {
        return;
      }

      // Kiểm tra mật khẩu xác thực
      $.ajax({
        url: "./elements_LQA/mUser/userAct.php",
        method: "POST",
        data: {
          reqact: "checkadmin",
          admin_password: adminPass,
        },
        success: function (response) {
          if (response.success) {
            window.location.href = `index.php?req=updateuser&iduser=${userId}`;
          } else {
            alert("Mật khẩu xác thực không chính xác!");
          }
        },
        error: function () {
          alert("Có lỗi xảy ra khi xác thực!");
        },
      });
    } else {
      window.location.href = `index.php?req=updateuser&iduser=${userId}`;
    }
  });

  // Xử lý khóa/mở khóa tài khoản
  $(".status-icon").click(function (e) {
    const username = $(this).closest("tr").find("td:eq(1)").text();
    if (username === "admin") {
      e.preventDefault();
      const adminPass = prompt(
        "Vui lòng nhập mật khẩu admin để thay đổi trạng thái tài khoản admin:"
      );
      if (!adminPass) {
        return;
      }

      // Kiểm tra mật khẩu admin bằng AJAX
      $.ajax({
        url: "./elements_LQA/mUser/userAct.php",
        method: "POST",
        data: {
          reqact: "checkadmin",
          admin_password: adminPass,
        },
        success: function (response) {
          if (response.success) {
            const currentHref = $(this).attr("href");
            window.location.href =
              currentHref + "&admin_password=" + encodeURIComponent(adminPass);
          } else {
            alert("Mật khẩu admin không chính xác!");
          }
        },
        error: function () {
          alert("Có lỗi xảy ra khi xác thực mật khẩu admin!");
        },
      });
    }
  });

  // Xử lý form cập nhật
  $("#formupdateuser").on("submit", function (e) {
    const username = $(this).find('input[name="username"]').val();
    if (username === "admin") {
      const adminPass = $('input[name="admin_password"]').val();
      if (!adminPass) {
        e.preventDefault();

        return;
      }
    }
  });

  // Thêm style cho nút disabled
  $(".iconimg.disabled").css({
    opacity: "0.5",
    cursor: "not-allowed",
  });

  // Xử lý submit form
  $("#formreg, #formupdateuser").on("submit", function (e) {
    e.preventDefault();

    // Validate form
    let isValid = true;
    $(this)
      .find("input[required]")
      .each(function () {
        if (!$(this).val()) {
          isValid = false;
          $(this).addClass("is-invalid");
        } else {
          $(this).removeClass("is-invalid");
        }
      });

    if (!isValid) {
      alert("Vui lòng điền đầy đủ thông tin");
      return;
    }

    // Validate số điện thoại
    const phone = $(this).find('input[name="dienthoai"]').val();
    if (phone) {
      // Chỉ kiểm tra nếu có nhập số điện thoại
      const phoneRegex = /^[0-9]{10}$/;
      if (!phoneRegex.test(phone)) {
        alert("Số điện thoại phải có 10 chữ số");
        return;
      }
    }

    // Nếu là form cập nhật admin
    const username = $(this).find('input[name="username"]').val();
    if (username === "admin") {
      const verifyPassword = $(this)
        .find('input[name="verify_password"]')
        .val();
      if (!verifyPassword) {
        alert("Vui lòng nhập mật khẩu xác thực để hoàn tất cập nhật!");
        return;
      }
    }

    // Submit form nếu validate ok
    this.submit();
  });

  // Remove invalid class on input
  $("input").on("input", function () {
    $(this).removeClass("is-invalid");
  });

  // Toggle password visibility
  $(".toggle-password").click(function () {
    const passwordField = $(this).closest(".password-field");
    const dots = passwordField.find(".password-dots");
    const text = passwordField.find(".password-text");

    if (dots.is(":visible")) {
      dots.hide();
      text.show();
      $(this).removeClass("fa-eye").addClass("fa-eye-slash");
    } else {
      dots.show();
      text.hide();
      $(this).removeClass("fa-eye-slash").addClass("fa-eye");
    }
  });
});

// Xử lý checkbox và nút xóa nhiều hình ảnh
document.addEventListener("DOMContentLoaded", function () {
  const selectAllCheckbox = document.getElementById("select-all");
  const imageCheckboxes = document.querySelectorAll(".image-checkbox");
  const deleteSelectedButton = document.getElementById("delete-selected");

  // Xử lý checkbox "Chọn tất cả"
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener("change", function () {
      imageCheckboxes.forEach((checkbox) => {
        if (!checkbox.closest("tr").querySelector(".delete-btn").disabled) {
          checkbox.checked = selectAllCheckbox.checked;
        }
      });
      updateDeleteButtonVisibility();
    });
  }

  // Xử lý các checkbox riêng lẻ
  imageCheckboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", function () {
      updateDeleteButtonVisibility();
      // Cập nhật trạng thái của checkbox "Chọn tất cả"
      if (selectAllCheckbox) {
        selectAllCheckbox.checked = Array.from(imageCheckboxes).every(
          (cb) =>
            cb.checked || cb.closest("tr").querySelector(".delete-btn").disabled
        );
      }
    });
  });

  // Cập nhật hiển thị nút xóa
  function updateDeleteButtonVisibility() {
    const checkedBoxes = document.querySelectorAll(".image-checkbox:checked");
    if (deleteSelectedButton) {
      deleteSelectedButton.style.display =
        checkedBoxes.length > 0 ? "block" : "none";
    }
  }

  // Xử lý sự kiện click nút xóa nhiều
  if (deleteSelectedButton) {
    deleteSelectedButton.addEventListener("click", function () {
      const checkedBoxes = document.querySelectorAll(".image-checkbox:checked");
      const imageIds = Array.from(checkedBoxes).map((cb) => cb.dataset.id);

      if (imageIds.length === 0) {
        alert("Vui lòng chọn ít nhất một hình ảnh để xóa");
        return;
      }

      if (confirm("Bạn có chắc chắn muốn xóa các hình ảnh đã chọn?")) {
        fetch("./elements_LQA/mhinhanh/hinhanhAct.php?reqact=deletemultiple", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ ids: imageIds }),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              alert(data.message);
              // Reload trang sau khi xóa thành công
              window.location.reload();
            } else {
              alert(data.message);
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            alert("Có lỗi xảy ra khi xóa hình ảnh");
          });
      }
    });
  }
});

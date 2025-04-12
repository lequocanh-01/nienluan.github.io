$(document).ready(function () {
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
  $(document).on(
    "click",
    '.w_update_btn_open_hh, .generic-update-btn[data-module="mhanghoa"]',
    function (e) {
      e.preventDefault();
      e.stopPropagation();

      // Hiển thị ở giữa màn hình
      var windowHeight = $(window).height();
      var windowWidth = $(window).width();
      var popupHeight = 500; // Chiều cao ước tính của popup
      var popupWidth = 700; // Chiều rộng ước tính của popup

      // Đảm bảo popup hiển thị ở giữa màn hình
      $("#w_update_hh").css({
        top: "50%",
        left: "50%",
        transform: "translate(-50%, -50%)",
      });

      let iddata = $(this).attr("value") || $(this).data("id");
      console.log("Cập nhật hàng hóa ID:", iddata);

      // Clear any existing content
      $("#w_update_form_hh").empty();

      // Add a loading indicator
      $("#w_update_form_hh").html(
        "<div style='text-align:center;padding:20px;'>Đang tải...</div>"
      );

      // Hiển thị popup ngay lập tức
      $("#w_update_hh").show();

      $("#w_update_form_hh").load(
        "./elements_LQA/mhanghoa/hanghoaUpdate.php",
        { idhanghoa: iddata },
        function (response, status, xhr) {
          if (status == "error") {
            console.error(
              "Error loading popup: " + xhr.status + " " + xhr.statusText
            );
            $("#w_update_form_hh").html(
              "<div style='color:red;padding:20px;'>Lỗi khi tải form: " +
                xhr.status +
                " " +
                xhr.statusText +
                "</div>"
            );
            return;
          }

          // Gán sự kiện cho nút đóng trong form
          $("#w_update_form_hh #close-btn").on("click", function () {
            $("#w_update_hh").hide();
          });

          // Focus vào form sau khi hiển thị
          setTimeout(function () {
            $("#w_update_form_hh input:first").focus();
          }, 100);

          console.log("Form cập nhật hàng hóa đã được tải");
        }
      );
    }
  );

  // Đóng popup khi click vào overlay
  $(document).on("click", "#w_update_hh", function (e) {
    if ($(e.target).is("#w_update_hh")) {
      $(this).hide();
    }
  });

  // Xử lý nút đóng chính
  $("#w_close_btn_hh").click(function (e) {
    e.preventDefault();
    $("#w_update_hh").hide();
  });

  // Setup for thuonghieu update
  $("#w_update_th").hide();
  $(document).on("click", ".w_update_btn_open_th", function (e) {
    e.preventDefault();
    e.stopPropagation();

    // Hiển thị ở giữa màn hình
    var windowHeight = $(window).height();
    var windowWidth = $(window).width();
    var popupHeight = 400; // Chiều cao ước tính của popup
    var popupWidth = 600; // Chiều rộng ước tính của popup

    $("#w_update_th").css("top", (windowHeight - popupHeight) / 2 + "px");
    $("#w_update_th").css("left", (windowWidth - popupWidth) / 2 + "px");

    // Lấy ID từ thuộc tính value của nút
    var $idThuongHieu = $(this).attr("value");
    console.log("Cập nhật thương hiệu ID:", $idThuongHieu);

    // Clear form trước khi load
    $("#w_update_form_th").empty();

    // Hiển thị thông báo đang tải
    $("#w_update_form_th").html(
      "<div style='text-align:center;padding:20px;'>Đang tải...</div>"
    );

    // Hiển thị popup ngay lập tức để người dùng thấy phản hồi
    $("#w_update_th").show();

    // Load form cập nhật
    $("#w_update_form_th").load(
      "./elements_LQA/mthuonghieu/thuonghieuUpdate.php",
      { idThuongHieu: $idThuongHieu },
      function (response, status, request) {
        if (status === "error") {
          $("#w_update_form_th").html(
            "<div style='color:red;padding:20px;'>Lỗi khi tải form: " +
              request.status +
              " " +
              request.statusText +
              "</div>"
          );
          return;
        }

        // Gán sự kiện cho nút đóng trong form
        $("#w_update_form_th #close-btn").on("click", function () {
          $("#w_update_th").hide();
        });

        console.log("Form cập nhật thương hiệu đã được tải");
      }
    );
  });

  // Xử lý nút đóng chính
  $("#w_close_btn_th").click(function (e) {
    e.preventDefault();
    $("#w_update_th").hide();
  });

  // Đóng khi click vào overlay
  $(document).on("click", "#w_update_th", function (e) {
    if ($(e.target).is("#w_update_th")) {
      $("#w_update_th").hide();
    }
  });

  // Setup message listener for close event from iframe
  $(window).on("message", function (e) {
    var message = e.originalEvent.data;
    if (message === "closeUpdateForm") {
      // Đóng tất cả các cửa sổ popup có thể có
      $("#w_update_th").hide();
      $("#w_update_tt").hide();
      $("#w_update_dvt").hide();
      $("#w_update_hh").hide();
      $("#w_update_nv").hide();
      $("#w_update_tthh").hide();
    }
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
  $(document).on("click", ".w_update_btn_open_dvt", function (e) {
    e.preventDefault();
    e.stopPropagation();

    // Hiển thị ở giữa màn hình
    var windowHeight = $(window).height();
    var windowWidth = $(window).width();
    var popupHeight = 400; // Chiều cao ước tính của popup
    var popupWidth = 600; // Chiều rộng ước tính của popup

    $("#w_update_dvt").css("top", (windowHeight - popupHeight) / 2 + "px");
    $("#w_update_dvt").css("left", (windowWidth - popupWidth) / 2 + "px");

    // Lấy ID từ thuộc tính value của nút
    var $idDonViTinh = $(this).attr("value");
    console.log("Cập nhật đơn vị tính ID:", $idDonViTinh);

    // Clear form trước khi load
    $("#w_update_form_dvt").empty();

    // Hiển thị thông báo đang tải
    $("#w_update_form_dvt").html(
      "<div style='text-align:center;padding:20px;'>Đang tải...</div>"
    );

    // Hiển thị popup ngay lập tức để người dùng thấy phản hồi
    $("#w_update_dvt").show();

    // Load form cập nhật
    $("#w_update_form_dvt").load(
      "./elements_LQA/mdonvitinh/donvitinhUpdate.php",
      { idDonViTinh: $idDonViTinh },
      function (response, status, request) {
        if (status === "error") {
          $("#w_update_form_dvt").html(
            "<div style='color:red;padding:20px;'>Lỗi khi tải form: " +
              request.status +
              " " +
              request.statusText +
              "</div>"
          );
          return;
        }

        // Gán sự kiện cho form
        $("#updatedonvitinh").on("submit", function (e) {
          e.preventDefault();

          var formData = new FormData(this);

          $.ajax({
            url: "./elements_LQA/mdonvitinh/donvitinhAct.php?reqact=updatedonvitinh",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
              $("#w_update_dvt").hide();
              window.location.href =
                "index.php?req=donvitinhview&t=" + new Date().getTime();
            },
            error: function (xhr, status, error) {
              alert("Có lỗi xảy ra khi cập nhật đơn vị tính: " + error);
            },
          });
        });

        console.log("Form cập nhật đơn vị tính đã được tải");
      }
    );
  });

  // Xử lý nút đóng cho đơn vị tính
  $("#w_close_btn_dvt").click(function (e) {
    e.preventDefault();
    $("#w_update_dvt").hide();
  });

  // Đóng khi click vào overlay cho đơn vị tính
  $(document).on("click", "#w_update_dvt", function (e) {
    if ($(e.target).is("#w_update_dvt")) {
      $("#w_update_dvt").hide();
    }
  });

  // Setup for thuoctinh update
  $("#w_update_tt").hide();
  $(document).on("click", ".w_update_btn_open_tt", function (e) {
    e.preventDefault();
    e.stopPropagation();

    // Hiển thị ở giữa màn hình
    var windowHeight = $(window).height();
    var windowWidth = $(window).width();
    var popupHeight = 400; // Chiều cao ước tính của popup
    var popupWidth = 600; // Chiều rộng ước tính của popup

    $("#w_update_tt").css("top", (windowHeight - popupHeight) / 2 + "px");
    $("#w_update_tt").css("left", (windowWidth - popupWidth) / 2 + "px");

    // Lấy ID từ thuộc tính value của nút
    var $idThuocTinh = $(this).attr("value");
    console.log("Cập nhật thuộc tính ID:", $idThuocTinh);

    // Clear form trước khi load
    $("#w_update_form_tt").empty();

    // Hiển thị thông báo đang tải
    $("#w_update_form_tt").html(
      "<div style='text-align:center;padding:20px;'>Đang tải...</div>"
    );

    // Hiển thị popup ngay lập tức để người dùng thấy phản hồi
    $("#w_update_tt").show();

    // Load form cập nhật
    $("#w_update_form_tt").load(
      "./elements_LQA/mthuoctinh/thuoctinhUpdate.php",
      { idThuocTinh: $idThuocTinh },
      function (response, status, request) {
        if (status === "error") {
          $("#w_update_form_tt").html(
            "<div style='color:red;padding:20px;'>Lỗi khi tải form: " +
              request.status +
              " " +
              request.statusText +
              "</div>"
          );
          return;
        }

        // Gán sự kiện cho nút đóng trong form
        $("#w_update_form_tt #close-btn").on("click", function () {
          $("#w_update_tt").hide();
        });

        console.log("Form cập nhật thuộc tính đã được tải");
      }
    );
  });

  // Xử lý nút đóng chính
  $("#w_close_btn_tt").click(function (e) {
    e.preventDefault();
    $("#w_update_tt").hide();
  });

  // Đóng khi click vào overlay
  $(document).on("click", "#w_update_tt", function (e) {
    if ($(e.target).is("#w_update_tt")) {
      $("#w_update_tt").hide();
    }
  });

  // Xử lý cập nhật người dùng
  $(document).on("click", ".update-user", function (e) {
    e.preventDefault();
    e.stopPropagation();

    var $idUser = $(this).data("userid");
    console.log("Cập nhật người dùng ID:", $idUser);

    // Chuyển đến trang cập nhật người dùng
    window.location.href = "index.php?req=userupdate&iduser=" + $idUser;
  });

  // Xử lý chung cho các nút update generic
  $(document).on("click", ".generic-update-btn", function (e) {
    e.preventDefault();
    e.stopPropagation();

    let module = $(this).data("module");
    let id = $(this).data("id");
    let idParam = $(this).data("id-param") || "id";
    let updateUrl = $(this).data("update-url");
    let title = $(this).data("title") || "Cập nhật";

    console.log(
      "Cập nhật " + module + " với ID: " + id + ", dùng param: " + idParam
    );

    if (module === "mhanghoa") {
      // Đã xử lý riêng bên trên
      return;
    }

    // Xác định popup ID dựa vào module
    let popupId, formId, closeBtnId;
    switch (module) {
      case "mnhanvien":
        popupId = "#w_update_nv";
        formId = "#w_update_form_nv";
        closeBtnId = "#w_close_btn_nv";
        break;
      case "mdonvitinh":
        popupId = "#w_update_dvt";
        formId = "#w_update_form_dvt";
        closeBtnId = "#w_close_btn_dvt";
        break;
      case "mthuoctinh":
        popupId = "#w_update_tt";
        formId = "#w_update_form_tt";
        closeBtnId = "#w_close_btn_tt";
        break;
      // Thêm các module khác nếu cần
      default:
        console.error("Module không được hỗ trợ: " + module);
        return;
    }

    // Hiển thị ở giữa màn hình
    var windowHeight = $(window).height();
    var windowWidth = $(window).width();
    var popupHeight = 400; // Chiều cao ước tính của popup
    var popupWidth = 600; // Chiều rộng ước tính của popup

    $(popupId).css({
      top: "50%",
      left: "50%",
      transform: "translate(-50%, -50%)",
    });

    // Clear form trước khi load
    $(formId).empty();

    // Hiển thị thông báo đang tải
    $(formId).html(
      "<div style='text-align:center;padding:20px;'>Đang tải...</div>"
    );

    // Hiển thị popup ngay lập tức để người dùng thấy phản hồi
    $(popupId).show();

    // Chuẩn bị dữ liệu gửi đi
    var postData = {};
    postData[idParam] = id;

    // Load form cập nhật
    $(formId).load(updateUrl, postData, function (response, status, request) {
      if (status === "error") {
        $(formId).html(
          "<div style='color:red;padding:20px;'>Lỗi khi tải form: " +
            request.status +
            " " +
            request.statusText +
            "</div>"
        );
        return;
      }

      // Gán sự kiện cho nút đóng trong form
      $(formId + " #close-btn").on("click", function () {
        $(popupId).hide();
      });

      console.log("Form " + title + " đã được tải");
    });
  });
});

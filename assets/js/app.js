$(document).ready(function () {
  // Handle pincode lookup with jQuery
  $("#pincode").on("change", function () {
    const pincode = $(this).val();
    if (pincode.length === 6) {
      $.ajax({
        url: `https://api.postalpincode.in/pincode/${pincode}`,
        method: "GET",
        beforeSend: function () {
          $("#city").val("Loading...");
          $("#state").val("Loading...");
        },
        success: function (data) {
          if (data[0].Status === "Success") {
            const location = data[0].PostOffice[0];
            $("#city").val(location.District);
            $("#state").val(location.State);
          } else {
            $("#city").val("");
            $("#state").val("");
            showError("Invalid pincode. Please try again.");
          }
        },
        error: function () {
          $("#city").val("");
          $("#state").val("");
          showError("Error looking up pincode. Please try again.");
        },
      });
    }
  });

  // Handle form submission with AJAX
  $("#addressForm").on("submit", function (e) {
    e.preventDefault();
    const formData = $(this).serializeArray();

    // Validate pincode
    const pincode = $("#pincode").val().trim();
    if (!/^[0-9]{6}$/.test(pincode)) {
      showError("Please enter a valid 6-digit pincode");
      return;
    }

    // Validate address is not empty
    const address = $("#address").val().trim();
    if (!address) {
      showError("Address cannot be empty");
      return;
    }

    $.ajax({
      url: "/FurniCart/update_profile.php",
      method: "POST",
      data: formData,
      dataType: "json",
      beforeSend: function () {
        $(".save-btn").prop("disabled", true).text("Saving...");
        clearErrors();
      },
      success: function (response) {
        if (response.success) {
          showSuccess(response.message);
          setTimeout(() => {
            window.location.href = "/FurniCart/profile.php";
          }, 1500);
        } else {
          showError(response.message);
          if (response.errors) {
            showValidationErrors(response.errors);
          }
        }
      },
      error: function (xhr, status, error) {
        console.error(xhr.responseText);
        showError("An error occurred. Please try again.");
      },
      complete: function () {
        $(".save-btn").prop("disabled", false).text("Save Changes");
      },
    });
  });

  // Add input validation on blur
  $("#addressForm input").on("blur", function () {
    const field = $(this);
    const value = field.val().trim();
    const fieldName = field.attr("name");

    // Clear previous error
    $(`#${fieldName}-error`).text("");
    field.removeClass("error");

    // Validate empty or whitespace
    if (!value || /^\s*$/.test(value)) {
      field.addClass("error");
      $(`#${fieldName}-error`).text(
        `${
          fieldName.charAt(0).toUpperCase() + fieldName.slice(1)
        } cannot be empty`
      );
    }

    // Special validation for pincode
    if (fieldName === "pincode" && value && !/^[0-9]{6}$/.test(value)) {
      field.addClass("error");
      $("#pincode-error").text("Please enter a valid 6-digit pincode");
    }
  });

  // Helper functions for displaying messages
  function showError(message) {
    const errorHtml = `<div class="error-message">${message}</div>`;
    $("#message-container").html(errorHtml).show();
  }

  function showSuccess(message) {
    const successHtml = `<div class="success-message">${message}</div>`;
    $("#message-container").html(successHtml).show();
  }

  function showValidationErrors(errors) {
    Object.keys(errors).forEach((field) => {
      $(`#${field}`).addClass("error");
      $(`#${field}-error`).text(errors[field]);
    });
  }

  function clearErrors() {
    $(".error-message, .success-message").remove();
    $(".error").removeClass("error");
    $(".field-error").text("");
  }
});

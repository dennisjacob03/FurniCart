document.addEventListener("DOMContentLoaded", function () {
  const radioButtons = document.querySelectorAll('input[name="image_type"]');
  const fileInput = document.getElementById("file-input");
  const urlInput = document.getElementById("url-input");

  radioButtons.forEach((radio) => {
    radio.addEventListener("change", function () {
      if (this.value === "file") {
        fileInput.style.display = "block";
        urlInput.style.display = "none";
        document.getElementById("image").required = true;
        document.getElementById("image_url").required = false;
      } else {
        fileInput.style.display = "none";
        urlInput.style.display = "block";
        document.getElementById("image").required = false;
        document.getElementById("image_url").required = true;
      }
    });
  });
});

const accountType = document.getElementById("account_type");
const additionalFields = document.getElementById("additional-fields");
accountType.addEventListener("change", function () {
  if (this.value === "trainer" || this.value === "fizjo") {
    additionalFields.style.display = "block";
    document.getElementById("specialization").required = true;
    document.getElementById("bio").required = true;
    document.getElementById("hourly-rate").required = true;
  } else {
    additionalFields.style.display = "none";
    document.getElementById("specialization").required = false;
    document.getElementById("bio").required = false;
    document.getElementById("hourly-rate").required = false;
  }
});

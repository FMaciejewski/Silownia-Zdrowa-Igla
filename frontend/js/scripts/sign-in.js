const accountType = document.getElementById("account_type");
const additionalFieldsTrainer = document.getElementById(
  "additional-fields-trainer",
);
const additionalFieldsDoctor = document.getElementById(
  "additional-fields-doctor",
);
accountType.addEventListener("change", function () {
  if (this.value === "trainer") {
    additionalFieldsTrainer.style.display = "block";
    additionalFieldsDoctor.style.display = "none";
    document.getElementById("specialization").required = true;
    document.getElementById("bio").required = true;
    document.getElementById("hourly-rate").required = true;
    document.getElementById("specialization-doc").required = false;
    document.getElementById("degree").required = false;
    document.getElementById("work-start").required = false;
    document.getElementById("work-end").required = false;
  } else if (this.value === "fizjo") {
    additionalFieldsDoctor.style.display = "block";
    additionalFieldsTrainer.style.display = "none";
    document.getElementById("specialization-doc").required = true;
    document.getElementById("degree").required = true;
    document.getElementById("work-start").required = true;
    document.getElementById("work-end").required = true;
    document.getElementById("specialization").required = false;
    document.getElementById("bio").required = false;
    document.getElementById("hourly-rate").required = false;
  } else {
    additionalFieldsTrainer.style.display = "none";
    additionalFieldsDoctor.style.display = "none";
    document.getElementById("specialization").required = false;
    document.getElementById("bio").required = false;
    document.getElementById("hourly-rate").required = false;
    document.getElementById("specialization-doc").required = false;
    document.getElementById("degree").required = false;
    document.getElementById("work-start").required = false;
    document.getElementById("work-end").required = false;
  }
});

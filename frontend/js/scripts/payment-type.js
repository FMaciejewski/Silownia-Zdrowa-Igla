document.addEventListener("DOMContentLoaded", () => {
  const paymentTypeInputs = document.querySelectorAll(
    'input[name="payment-type"]',
  );
  const cardFields = document.querySelector(".card-fields");
  const blikFields = document.querySelector(".blik-fields");
  const googlePayFields = document.querySelector(".google-pay-fields");

  paymentTypeInputs.forEach((input) => {
    input.addEventListener("change", function () {
      const selectedValue = this.value;

      if (selectedValue === "card") {
        cardFields.style.display = "block";
        blikFields.style.display = "none";
        googlePayFields.style.display = "none";

        setCardFieldsRequired(true);
        setBlikFieldsRequired(false);
      } else if (selectedValue === "blik") {
        cardFields.style.display = "none";
        blikFields.style.display = "block";
        googlePayFields.style.display = "none";

        setCardFieldsRequired(false);
        setBlikFieldsRequired(true);
      } else if (selectedValue === "google-pay") {
        cardFields.style.display = "none";
        blikFields.style.display = "none";
        googlePayFields.style.display = "block";

        setCardFieldsRequired(false);
        setBlikFieldsRequired(false);
      }
    });
  });

  function setCardFieldsRequired(required) {
    document.getElementById("name").required = required;
    document.getElementById("surname").required = required;
    document.getElementById("card-number").required = required;
    document.getElementById("expiry-date").required = required;
    document.getElementById("cvv").required = required;
    document.getElementById("address").required = required;
    document.getElementById("postal-code").required = required;
    document.getElementById("city").required = required;
    document.getElementById("country").required = required;
  }

  function setBlikFieldsRequired(required) {
    document.getElementById("blik-code").required = required;
  }
});

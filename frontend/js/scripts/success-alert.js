const params = new URLSearchParams(window.location.search);
const successMessage = document.getElementById("success-message");

if (successMessage) {
  switch (params.get("success")) {
    case "1":
      successMessage.textContent =
        "Rejestracja przebiegła pomyślnie. Możesz się teraz zalogować.";
      showMessage();
      break;
    case "2":
      successMessage.textContent =
        "Hasło zostało zmienione. Możesz się teraz zalogować.";
      showMessage();
      break;
    case "3":
      successMessage.textContent =
        "Na podany adres email został wysłany link do zmiany hasła.";
      showMessage();
      break;
    case "4":
      successMessage.textContent = "Zalogowano pomyślnie!";
      showMessage();
      break;
    case "5":
      successMessage.textContent = "Karnet został zakupiony!";
      showMessage();
      break;
    case "6":
      successMessage.textContent = "Pomyślnie zapisano się na zajęcia.";
      showMessage();
      break;
    default:
      console.log("Nieznany przypadek success.");
      successMessage.style.opacity = "0";
      successMessage.style.display = "none";
      successMessage.style.visibility = "hidden";
      break;
  }
} else {
  console.error("Element #success-message nie istnieje!");
}

function showMessage() {
  successMessage.style.display = "block";
  successMessage.style.opacity = "1";
  setTimeout(() => {
    successMessage.style.opacity = "0";
    setTimeout(() => {
      successMessage.style.display = "none";
    }, 500);
  }, 6000);
}

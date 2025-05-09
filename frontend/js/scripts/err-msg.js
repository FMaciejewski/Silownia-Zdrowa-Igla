const urlParams = new URLSearchParams(window.location.search);
const error = urlParams.get("error");

if (error) {
  const errorMessage = {
    missmatch: "Hasła nie pasują do siebie.",
    required: "Wszystkie pola są wymagane.",
    already_registered: "Login lub email już istnieje.",
    empty: 'Hasła nie pasują do siebie.',
    wrong: 'Hasło lub login są błędne.',
    wronglogin: "Brak takiego loginu.",
  };

  const errorMsg = document.getElementById("error-message");
  errorMsg.textContent =
    errorMessage[error] || "Wystąpił nieznany błąd.";
}
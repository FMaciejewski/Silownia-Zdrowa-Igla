function getTokenFromURL() {
  const params = new URLSearchParams(window.location.search);
  return params.get("token");
}

const token = getTokenFromURL();
async function validateToken() {
  try {
    const response = await fetch(
      "../../backend/api/check-token.php?token=" + token,
    );
    const data = await response.json();

    if (!data.valid) {
      const container = document.querySelector(".new-pass");
      if (container) {
        container.innerHTML =
          '<h2 class="error-header">Ten link wygasł lub jest nieprawidłowy.</h2>';
      }
    }
  } catch (error) {
    console.error("Error fetching token validation:", error);
  }
}

validateToken();
document.addEventListener("DOMContentLoaded", () => {
  const input = document.querySelector("#token");
  if (input) {
    input.value = token;
  }
});

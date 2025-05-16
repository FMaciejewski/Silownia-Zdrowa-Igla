function zamiana($a, $b) {
  const section1 = document.querySelector(
    ".profile-section:nth-of-type(" + $a + ")",
  );
  const section2 = document.querySelector(
    ".profile-section:nth-of-type(" + $b + ")",
  );
  section1.style.display = "none";
  section2.style.display = "block";
}
document.addEventListener("DOMContentLoaded", () => {
  fetch("../../backend/api/profile.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error("Nieautoryzowany dostęp albo błąd serwera");
      }
      return response.json();
    })
    .then((data) => {
      const profileSection = document.getElementById("profile-info");
      const profileForm = document.getElementById("profile-form");
      profileSection.innerHTML = `
        <h2>Profil użytkownika</h2>
        <img src="../${data.ProfilePicture}" alt="Profile Image" class="profile-image">
        <p><strong>Imię:</strong> ${data.FirstName}</p>
        <p><strong>Nazwisko:</strong> ${data.LastName}</p>
        <p><strong>Login:</strong> ${data.Login}</p>
        <p><strong>Email:</strong> ${data.Email}</p>
        <p><strong>Numer telefonu:</strong> ${data.PhoneNumber}</p>
    `;
      const nrTelefonu = data.PhoneNumber.slice(3, 12);
      var innerForm = `
        <h2>Edytuj profil</h2>
        <form id="edit-profile-form" method="POST" action="../../backend/api/edit_profile.php" enctype="multipart/form-data">
            <label for="profile-picture">Zdjęcie profilowe:</label>
            <input type="file" id="profile-picture" name="profile-picture" accept=".jpg, .jpeg, .png">
            <label for="first-name">Imię:</label>
            <input type="text" id="first-name" name="first-name" value="${data.FirstName}" class="profile-input" required>
            <label for="last-name">Nazwisko:</label>
            <input type="text" id="last-name" name="last-name" value="${data.LastName}" class="profile-input" required>
            <label for="login">Login:</label>
            <input type="text" id="login" name="login" value="${data.Login}" class="profile-input" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="${data.Email}" class="profile-input" required>
            <label for="phone-number">Numer telefonu:</label>
            <input type="tel" required pattern="[0-9]{3}[0-9]{3}[0-9]{3}" id="phone-number" name="phone-number" value="${nrTelefonu}" class="profile-input" required>`;
      if (data.Role === "trainer") {
        profileSection.innerHTML += `	
            <p><strong>Specjalizacja:</strong> ${data.Specialization}</p>
            <p><strong>Biografia:</strong> ${data.Bio}</p>
            <p><strong>Godzinówka:</strong> ${data.HourlyRate}</p>`;
        innerForm += `
            <label for="specialization">Specjalizacja:</label>
            <input type="text" id="specialization" name="specialization" value="${data.Specialization}" required class="profile-input">
            <label for="bio">Biografia:</label>
            <textarea id="bio" name="bio" required class="profile-input">${data.Bio}</textarea>
            <label for="hourly-rate">Godzinówka:</label>
            <input type="number" id="hourly-rate" name="hourly-rate" value="${data.HourlyRate}" required class="profile-input">
        `;
      }
      innerForm += `
            <button type="submit" class="profile-button">Zapisz zmiany</button>
        </form>`;

      profileForm.innerHTML = innerForm;
    });
  const urlParams = new URLSearchParams(window.location.search);
  const error = urlParams.get("error");

  if (error) {
    const errorMessage = {
      missmatch: "Hasła nie pasują do siebie.",
      wrongpassword: "Stare hasło jest błędne.",
    };

    const errorMsg = document.getElementById("error-message");
    errorMsg.textContent = errorMessage[error] || "Wystąpił nieznany błąd.";
    zamiana(1, 3);
  }
});

fetch("../backend/api/trainers.php")
  .then((response) => response.json())
  .then((data) => {
    data.forEach((trener) => {
      const trenerDiv = document.createElement("div");
      trenerDiv.classList.add("trener");
      trenerDiv.innerHTML = `
        <img src="${trener.ProfilePicture}" alt="${trener.FirstName} ${trener.LastName}" />
        <h4>${trener.FirstName} ${trener.LastName}</h4>
        <h3>${trener.Specialization}</h3>
        <p>${trener.Bio}</p>
        <p>Telefon: ${trener.PhoneNumber}</p>
        <p>Email: <a href="mailto:${trener.Email}">${trener.Email}</a></p>
        <p>Cena: ${trener.HourlyRate} zł/h</p>
    `;
      document.getElementById("trenerzy").appendChild(trenerDiv);
    });
  });

fetch("../../backend/api/get_chat_users.php", { credentials: "include" })
  .then((res) => res.json())
  .then((users) => {
    const container = document.getElementById("user-list");
    container.innerHTML = "";

    if (users.length === 0) {
      container.textContent = "Brak wiadomości od użytkowników.";
      return;
    }

    users.forEach((user) => {
      const div = document.createElement("div");
      div.className = "user-item";

      const link = document.createElement("a");
      link.className = "user-link";
      link.href = `chat.html?receiver_id=${user.UserID}`;
      link.textContent = `${user.FirstName} ${user.LastName}`;

      div.appendChild(link);
      container.appendChild(div);
    });
  })
  .catch((error) => {
    console.error("Błąd:", error);
    document.getElementById("user-list").textContent =
      "Błąd ładowania użytkowników.";
  });

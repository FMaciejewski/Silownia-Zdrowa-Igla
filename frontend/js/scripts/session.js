fetch("/Silownia-Zdrowa-Igla/backend/api/session.php", { cache: "no-store" })
  .then((response) => response.json())
  .then((data) => {
    const sesja = data.sesja;
    const buttons = document.querySelector(".logging");
    if (sesja !== null) {
      buttons.innerHTML = `
                <button class="menu-toggle" onclick="toggleSidebar()" id="menu-btn">☰ Menu</button>`;
      const navbarMenu = document.getElementById("navbar-menu");
      if (navbarMenu) {
        navbarMenu.style.display = "none";
      }
    } else {
      buttons.innerHTML = `
                <a class="signin-btn" href="sites/sign-in.html">Zarejestruj się</a>
                <a class="login-btn" href="sites/log-in.html">Zaloguj się</a>`;
    }
  });

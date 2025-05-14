fetch("../../backend/api/session.php" , { cache: "no-store" })
    .then(response => response.json())
    .then(data => {
        const sesja = data.sesja;
        const buttons = document.querySelector('.logging');
        if(sesja !== null){
            buttons.innerHTML = `
                <a class="signin-btn" href="sites/profile.html">Profil</a>
                <a class="login-btn" href="../backend/api/logout.php">Wyloguj się</a>`;
        }
        else{
             buttons.innerHTML = `
                <a class="signin-btn" href="sites/sign-in.html">Zarejestruj się</a>
                <a class="login-btn" href="sites/log-in.html">Zaloguj się</a>`;
        }
    })
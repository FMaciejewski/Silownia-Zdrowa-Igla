fetch("/Silownia-Zdrowa-Igla/backend/api/session.php", { cache: "no-store" })
    .then((response) => response.json())
    .then((data) => {
        if(!data.loggedIn){
            window.location.href = "/Silownia-Zdrowa-Igla/frontend/sites/log-in.html";
        }
    })
    .catch((error) => {
        console.error("Error fetching session data:", error);
    });
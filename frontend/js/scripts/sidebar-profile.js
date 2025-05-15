fetch("/Silownia-Zdrowa-Igla/backend/api/sidebar-profile.php")
    .then((response) => {
        if (!response.ok) {
            throw new Error("Network response was not ok");
        }
        return response.json();
    })
    .then((data) => {
        const sidebarProfile = document.getElementById("sidebar-profile");
        const profileImage = document.getElementById("sidebar-picture");
        const profileName = document.getElementById("sidebar-name");
        profileImage.src = "/Silownia-Zdrowa-Igla/frontend/" + data.ProfilePicture;
        profileName.innerHTML = data.FirstName + " " + data.LastName;
    })
const params = new URLSearchParams(window.location.search);
        const successMessage = document.getElementById("success-message");
          if (params.get("success") === "1") {
                successMessage.textContent =
                  "Rejestracja przebiegła pomyślnie. Możesz się teraz zalogować.";
                successMessage.style.opacity = "1";
                setTimeout(() => {
                  successMessage.style.opacity = "0";
                  successMessage.style.display = "none";
                }, 6000);
            } else if (params.get("success") === "2") {
                const successMessage = document.getElementById("success-message");
                successMessage.textContent =
                  "Hasło zostało zmienione. Możesz się teraz zalogować.";
                successMessage.style.opacity = "1";
                setTimeout(() => {
                  successMessage.style.opacity = "0";
                  successMessage.style.display = "none";
                }, 6000);
            } else if (params.get("success") === "3") {
                    const successMessage = document.getElementById("success-message");
                    successMessage.textContent =
                      "Na podany adres email został wysłany link do zmiany hasła.";
                    successMessage.style.opacity = "1";
                    setTimeout(() => {
                      successMessage.style.opacity = "0";
                      successMessage.style.display = "none";
                    }, 6000);
            }else if (params.get("success") === "4") {
              const successMessage = document.getElementById("success-message");
              successMessage.textContent = "Zalogowano pomyślnie!";
              successMessage.style.opacity = "1";
              setTimeout(() => {
                successMessage.style.opacity = "0";
                successMessage.style.display = "none";
              }, 6000);
            }else if(params.get("success") === "6"){
                const successMessage = document.getElementById("success-message");
                successMessage.textContent =
                  "Pomyślnie zapisano się na zajęcia.";
                successMessage.style.opacity = "1";
                setTimeout(() => {
                  successMessage.style.opacity = "0";
                  successMessage.style.display = "none";
                }, 6000);
            } else {
                successMessage.style.opacity = "0";
                successMessage.style.display = "none";
                successMessage.style.visibility = "hidden";
            }
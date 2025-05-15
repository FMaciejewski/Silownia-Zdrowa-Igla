window.toggleSidebar = function() {
      const sidebar = document.getElementById("sidebar");
      const main = document.getElementById("main-content");
      const footer = document.getElementById("kontakt");
      if (sidebar.style.width === "250px") {
          sidebar.style.width = "0";
          main.style.marginLeft = "0";
            footer.style.marginLeft = "0";
      } else {
          sidebar.style.width = "250px";
            main.style.marginLeft = "250px";
            footer.style.marginLeft = "250px";
      }
  }
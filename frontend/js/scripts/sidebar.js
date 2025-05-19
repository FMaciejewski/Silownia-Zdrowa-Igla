window.toggleSidebar = function () {
  const sidebar = document.getElementById("sidebar");
  const main = document.getElementById("main-content");
  const footer = document.getElementById("kontakt");
  const menuBtn = document.getElementById("menu-btn");
  const header = document.getElementsByTagName("header");
  if (sidebar.style.width === "250px") {
    sidebar.style.width = "0";
    main.style.marginLeft = "0";
    footer.style.marginLeft = "0";
    menuBtn.style.display = "block";
    header[0].style.marginLeft = "0";
  } else {
    sidebar.style.width = "250px";
    main.style.marginLeft = "250px";
    footer.style.marginLeft = "250px";
    menuBtn.style.display = "none";
    header[0].style.marginLeft = "250px";
  }
};

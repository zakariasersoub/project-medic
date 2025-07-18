(() => {
    const state = {
      isMobileMenuOpen: false,
      toggleMobileMenu() {
        state.isMobileMenuOpen = !state.isMobileMenuOpen;
        update();
      },
    };
  
    function update() {
      const mobileMenu = document.querySelector(".nav-menu");
      const menuButton = document.querySelector(".mobile-menu-toggle");
  
      if (state.isMobileMenuOpen) {
        mobileMenu.classList.add("active");
      } else {
        mobileMenu.classList.remove("active");
      }
    }
  
    // Initialize event listeners
    document
      .querySelector(".mobile-menu-toggle")
      .addEventListener("click", () => {
        state.toggleMobileMenu();
      });
  
    // Initialize with default state
    update();
  })();
  
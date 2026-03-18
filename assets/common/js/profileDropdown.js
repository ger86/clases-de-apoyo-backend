const dropdownBtn = document.getElementById("profileDropdownBtn");
const dropdownMenu = document.getElementById("profileDropdownMenu");

const dropdownHeader = {
  init() {
    const toggleDropdown = function () {
      if (dropdownMenu.classList.contains('visible')) {
        dropdownMenu.classList.remove("visible");
        dropdownMenu.classList.add('invisible');
        dropdownMenu.classList.add('opacity-0');
      } else {
        dropdownMenu.classList.remove("invisible");
        dropdownMenu.classList.add('visible');
        dropdownMenu.classList.add('opacity-100');
      }
    };
    
    if (dropdownBtn) {
      dropdownBtn.addEventListener("click", function (e) {
        e.stopPropagation();
        toggleDropdown();
      });
    }

    document.documentElement.addEventListener("click", function () {
      if (dropdownMenu && dropdownMenu.classList.contains("visible")) {
        toggleDropdown();
      }
    });
  }
}

export default dropdownHeader;
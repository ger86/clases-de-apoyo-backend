const menu = {
  init() {
    document.addEventListener('DOMContentLoaded', function() {
      const openBtn = document.querySelector('#openSidebarMenuBtn');
      const headerNav = document.querySelector('#headerNav');
      const overlay = document.querySelector('#headerOverlay');
      const closeBtn = document.querySelector('#closeSidebarMenuBtn');
    
      openBtn.addEventListener('click', function(ev) {
        ev.preventDefault();
        headerNav.classList.add('w-[250px]');
        overlay.classList.add('opacity-100');
        overlay.classList.remove('hidden');
        document.body.classList.add('overflow-y-hidden','lg:overflow-auto');
      }, false);
    
      closeBtn.addEventListener('click', function(ev) {
        ev.preventDefault();
        headerNav.classList.remove('w-[250px]');
        overlay.classList.remove('opacity-100');
        overlay.classList.add('hidden');
        document.body.classList.remove('overflow-y-hidden', 'lg:overflow-auto');
      }, false);
    });
  }
}

export default menu;
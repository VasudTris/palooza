(function() {
    'use strict';

    class Menu {
        constructor(settings) {
            this.menuRootNode = settings.menuRootNode;
            this.isOpened = false;
        }

        toggle() {
            this.isOpened = !this.isOpened;
            this.menuRootNode.classList.toggle('js-cdpn-mobile-menu_activated', this.isOpened);
            this.menuRootNode.querySelector(`.${settings.menuClassesNames.toggleClass}`).setAttribute("aria-expanded", this.isOpened);
        }
    }

    const menuClassesNames = {
        rootClass: 'js-cdpn-mobile-menu',
        toggleClass: 'js-cdpn-mobile-menu__toggle'
    };

    const jsMenuNode = document.querySelector(`.${menuClassesNames.rootClass}`);
    const demoMenu = new Menu({ menuRootNode: jsMenuNode, menuClassesNames });

    //! Toggle menu bij klikken op de toggle knop
    jsMenuNode.querySelector(`.${menuClassesNames.toggleClass}`).addEventListener('click', function(event) {
        event.stopPropagation(); // Voorkom dat het click-event ook de document listener activeert
        demoMenu.toggle();
    });

    //! Sluit het menu als je buiten de menu-items klikt
    document.addEventListener('click', function(event) {
        const menu = jsMenuNode;
        const toggleButton = menu.querySelector(`.${menuClassesNames.toggleClass}`);
        const menuItems = menu.querySelectorAll('ul'); // Selecteer alle <a> knoppen

        //! Controleer of je buiten het menu en de knoppen hebt geklikt
        const isClickInsideMenu = menu.contains(event.target);
        const isClickOnToggle = toggleButton.contains(event.target);
        const isClickOnMenuItem = Array.from(menuItems).some(item => item.contains(event.target));

        if (!isClickInsideMenu && !isClickOnToggle && !isClickOnMenuItem && demoMenu.isOpened) {
            demoMenu.toggle(); //! Sluit het menu
        }
    });
})();

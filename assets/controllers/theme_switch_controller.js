import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        // Au démarrage, on lit localStorage pour voir si un thème est stocké
        const storedTheme = localStorage.getItem('steamTheme');

        // Si c'est "steam-blue" ou "steam-green", on l'applique
        if (storedTheme === 'steam-blue' || storedTheme === 'steam-green') {
            this.element.classList.remove('steam-blue', 'steam-green');
            this.element.classList.add(storedTheme);
        } else {
            // S'il n'y a rien, on suppose "steam-blue" par défaut ou "steam-green"
            if (!this.element.classList.contains('steam-blue') &&
                !this.element.classList.contains('steam-green')) {
                this.element.classList.add('steam-blue');
            }
        }
    }

    toggleTheme() {
        if (this.element.classList.contains('steam-blue')) {
            this.element.classList.remove('steam-blue');
            this.element.classList.add('steam-green');
            localStorage.setItem('steamTheme', 'steam-green');
        } else {
            this.element.classList.remove('steam-green');
            this.element.classList.add('steam-blue');
            localStorage.setItem('steamTheme', 'steam-blue');
        }
    }
}

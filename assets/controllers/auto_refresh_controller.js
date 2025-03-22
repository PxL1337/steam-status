import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        interval: Number // nombre de secondes avant le reload
    }

    static targets = ['countdown']

    connect() {
        // Nombre de secondes restant
        this.timeLeft = this.intervalValue || 60; // par défaut 60
        this.updateCountdown(); // Affiche la valeur initiale

        // On lance un timer qui s'exécute chaque seconde
        this.timer = setInterval(() => this.tick(), 1000);
    }

    tick() {
        this.timeLeft--;
        if (this.timeLeft <= 0) {
            // Reload la page
            window.location.reload();
        } else {
            this.updateCountdown();
        }
    }

    updateCountdown() {
        // On met à jour le texte dans countdownTarget
        if (this.hasCountdownTarget) {
            this.countdownTarget.textContent = this.timeLeft;
        }
    }

    disconnect() {
        // Nettoyer le timer si on quitte la page
        clearInterval(this.timer);
    }
}

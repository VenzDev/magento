/**
 * Prosty komponent JS oparty o natywny stack Magento: RequireJS + wzorzec
 * widgetu jQuery UI (dokładnie ten sam mechanizm co np. Magento_Catalog's
 * "gallery" czy Magento_Checkout's komponenty). Zliczanie kliknięć zapisywane
 * przez fetch() do REST endpointu z Etapu 6 (webapi.xml, /V1/training/greetings).
 *
 * WAŻNE (przeczytaj przed pisaniem TODO): nasz endpoint w etc/webapi.xml ma
 * ACL resource wymagający uprawnień ADMINA (Training_Greeting::greeting*) —
 * to jest kod frontendowy (storefront), gdzie NIE MA zalogowanego admina i
 * NIE MA (i nie powinno być!) osadzonego tokena admina w JS wysyłanym do
 * przeglądarki klienta — to byłaby poważna dziura bezpieczeństwa.
 *
 * Do celów wyłącznie ćwiczeniowych/lokalnych: wygeneruj token tak jak w
 * Etapie 6 (POST /rest/V1/integration/admin/token z loginem/hasłem admina —
 * NIGDY tak w produkcji) i wklej go tymczasowo w opcji `token` poniżej albo w
 * data-mage-init w footer.phtml. Zobaczysz błąd 401, jeśli tego nie zrobisz —
 * to oczekiwane, sam ACL działa poprawnie, po prostu ten konkretny scenariusz
 * (frontend wołający chroniony REST) wymaga świadomej decyzji o
 * uwierzytelnianiu, której na razie nie podjęliśmy.
 */
define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('training.greetingCounter', {
        options: {
            restUrl: '/rest/V1/training/greetings',
            token: '' // tylko do lokalnych testów — patrz komentarz wyżej
        },

        /** @inheritdoc */
        _create: function () {
            this.countElement = this.element.find('[data-role="count"]');
            this.element.on('click', '[data-role="button"]', $.proxy(this._onButtonClick, this));
        },

        /**
         * TODO:
         * 1. Zbuduj body wiadomości do wysłania na REST (POST):
         *    { greeting: { message: 'Kliknięto: ' + new Date().toISOString() } }
         * 2. Wyślij fetch(this.options.restUrl, { method: 'POST', headers: {...}, body: JSON.stringify(...) })
         *    — nagłówki: 'Content-Type': 'application/json', i jeśli
         *    this.options.token jest ustawiony, 'Authorization': 'Bearer ' + this.options.token.
         * 3. Po sukcesie (response.ok): zwiększ this.countElement o 1 (albo
         *    wywołaj osobny GET po total_count, jeśli wolisz pokazywać
         *    prawdziwą liczbę z bazy zamiast lokalnego licznika w przeglądarce).
         * 4. Obsłuż błąd: response.status === 401 to prawdopodobnie brak/zły
         *    token — wypisz to jawnie przez console.warn, żeby było
         *    jednoznaczne co się stało, zamiast cichego niepowodzenia.
         */
        _onButtonClick: function () {
        }
    });

    return $.training.greetingCounter;
});

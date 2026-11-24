/**
 * De regels van het IAMA, voor de motor in ../assets/app.js.
 *
 * - bij vraag 4.2.1 stelt de tool nee voor, als het algoritme geen AI-systeem is
 * - bij 4.3 krijgt het veld voor een grondrecht de aangetaste grondrechten uit 4.1.2 als suggestie
 * - bij de standpunten krijgt het veld voor een rol de namen uit het team als suggestie
 * - bij de balans neemt de tool de antwoorden bij 4.4.1 en 4.5.1 alvast over
 *
 * PHP rekent bij het versturen alles opnieuw uit. Het voorstel staat alleen
 * hier: het helpt bij het invullen, het antwoord telt.
 */
(function () {
    'use strict';

    var cm = window.cisomatic;

    cm.regels({
        voorstel: voorstel,
        motivatie: motivatie,
        ververs: function (form, a) { vulSuggesties(form, a); }
    });

    function voorstel(key, a) {
        return key === 'ia_hoog_risico' && a.ia_ai_systeem === 'nee' ? 'nee' : '';
    }

    /** De balans vraagt opnieuw wat bij 4.4.1 en 4.5.1 al staat. */
    function motivatie(voor, a) {
        if (voor === 'kans' && a.ia_doeltreffend) {
            return ['Bij vraag 4.4.1: ' + cm.optieLabel('ia_doeltreffend', a.ia_doeltreffend).toLowerCase() + '.']
                .concat(cm.tekstregels(a.ia_doeltreffend_toelichting)).join('\n');
        }
        if (voor === 'noodzaak') {
            return cm.tekstregels(a.ia_subsidiariteit).join('\n');
        }
        return '';
    }

    /** Bij 4.3 de grondrechten uit 4.1.2, bij de standpunten de namen uit het team. */
    function vulSuggesties(form, a) {
        vul(form.querySelector('#ia_inbreuken-suggesties'), cm.tekstregels(a.ia_grondrechten_negatief));
        vul(form.querySelector('#ia_standpunten-suggesties'), [].map.call(
            form.querySelectorAll('input[name^="ia_team["][name$="[naam]"]'),
            function (veld) { return veld.value.trim(); }
        ).filter(Boolean));
    }

    function vul(lijst, namen) {
        if (!lijst || lijst.dataset.namen === namen.join('\n')) { return; }

        lijst.dataset.namen = namen.join('\n');
        lijst.textContent = '';
        namen.forEach(function (naam) {
            var optie = document.createElement('option');
            optie.textContent = naam;
            lijst.appendChild(optie);
        });
    }
}());

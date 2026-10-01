/**
 * De regels van de business case en intaketoets, voor de motor in ../assets/app.js.
 *
 * - het uitkomstblok aan het eind van elke toets zegt meteen hoe die toets uitvalt
 * - het uitkomstblok bij het voorstel zegt hoe jullie moeten inkopen
 *
 * De niveaus en teksten staan in vragen.php en afleiding.php, en komen via
 * data-groepen op het uitkomstblok mee. Hier staat alleen de rekenregel; die
 * spiegelt bc_toets_niveau() en bc_inkoop_route(). PHP rekent bij het
 * versturen alles opnieuw uit.
 */
(function () {
    'use strict';

    window.cisomatic.regels({ afgeleid: afgeleid });

    function afgeleid(bron, a) {
        var blok = document.querySelector('[data-afgeleid="' + bron + '"]');
        var groepen = blok && JSON.parse(blok.dataset.groepen);

        return bron === 'inkoop' ? inkoop(groepen, a) : toets(groepen, a);
    }

    function inkoop(wegen, a) {
        var vorm = a.inkoopvorm;
        var weg = wegen && wegen[vorm === 'geen' || vorm === 'nadere' ? vorm : a.bc_opdrachtwaarde];

        return weg
            ? { waarde: weg.label, niveau: weg.niveau, redenen: weg.redenen }
            : { waarde: 'nog te bepalen', niveau: '', redenen: ['Kies eerst de soort inkoop en het bedrag.'] };
    }

    function toets(p, a) {
        var rang = window.cisomatic.rang;
        var niveau = function (key) { return (p.vragen[key] || {})[a[key]] || ''; };
        var niveaus = Object.keys(p.vragen).filter(function (key) { return p.of.indexOf(key) === -1; }).map(niveau);

        if (p.of.length) {
            var of = p.of.map(niveau).sort(function (x, y) { return rang(x) - rang(y); });
            niveaus.push(of.indexOf('l') !== -1 ? 'l' : (of.indexOf('') !== -1 ? '' : of[0]));
        }

        if (niveaus.indexOf('') !== -1) {
            return { waarde: 'nog te bepalen', niveau: '', redenen: ['Beantwoord eerst alle vragen van deze toets.'] };
        }

        var hoogste = niveaus.sort(function (x, y) { return rang(y) - rang(x); })[0];

        return { waarde: p.labels[hoogste], niveau: hoogste, redenen: p.redenen[hoogste] };
    }
}());

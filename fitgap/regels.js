/**
 * De regels van de fit/gap, voor de motor in ../assets/app.js. Beide fit/gap
 * formulieren gebruiken dit bestand.
 *
 * - elke regel van een norm toont het antwoord, in de kleur van het niveau
 * - onder elk deel staat hoe ver het is
 * - bij de verklaring staat de uitkomst
 *
 * De delen, normen en keuzes komen uit PHP, als data-groepen op het
 * uitkomstblok van de verklaring. Dit spiegelt fg_norm_compleet(),
 * fg_norm_samenvatting() en fg_uitslag(); PHP rekent bij het versturen alles
 * opnieuw uit.
 */
(function () {
    'use strict';

    var cm = window.cisomatic;
    var model = null;

    cm.regels({ afgeleid: afgeleid, ververs: ververs });

    function laad() {
        if (!model) {
            var blok = document.querySelector('[data-afgeleid="uitkomst"]');
            model = blok ? JSON.parse(blok.dataset.groepen) : null;
        }
        return model;
    }

    /** Spiegelt fg_norm_compleet(): een antwoord, met de velden die daarbij horen. */
    function compleet(key, a) {
        var keuze = model.keuzes[a[key]];
        return !!keuze && keuze.velden.every(function (veld) { return String(a[key + veld] || '').trim() !== ''; });
    }

    /** Spiegelt fg_norm_samenvatting(). */
    function samenvatting(key, a) {
        var waarde = String(a[key] || '');
        var datum = String(a[key + '_datum'] || '').trim();

        if (!waarde) { return 'nog in te vullen'; }
        return model.keuzes[waarde].kort + (waarde === 'nee' && datum ? ' · ' + datum : '');
    }

    /**
     * Telt de normen van een deel. Een verborgen norm staat niet in a, en telt
     * niet mee. Een norm zonder antwoord, of zonder de velden die erbij horen,
     * is open.
     */
    function tel(deel, a) {
        var uit = { ja: 0, nee: 0, nvt: 0, open: 0, totaal: 0, gaten: [] };

        deel.normen.forEach(function (norm) {
            var key = norm[0];
            if (!(key in a)) { return; }

            uit.totaal++;
            if (!compleet(key, a)) { uit.open++; return; }

            uit[a[key]]++;
            if (a[key] === 'nee') { uit.gaten.push(norm[1]); }
        });

        return uit;
    }

    function aantal(n, een, meer) {
        return n + ' ' + (n === 1 ? een : meer);
    }

    function afgeleid(bron, a) {
        if (!laad()) { return null; }
        if (bron === 'uitkomst') { return uitkomst(a); }

        var deel = model.delen.filter(function (d) { return d.id === bron; })[0];
        return deel ? stand(tel(deel, a)) : null;
    }

    /** De stand van één deel. */
    function stand(t) {
        var redenen = ['Voldoet: ' + t.ja + '. Nog niet: ' + t.nee + '.'
            + (t.nvt ? ' Niet van toepassing: ' + t.nvt + '.' : '')];

        if (t.gaten.length) { redenen.push('Nog niet: ' + cm.opsomming(t.gaten) + '.'); }
        if (t.open) {
            redenen.push(aantal(t.open, 'norm is', 'normen zijn') + ' nog niet helemaal ingevuld.');
            return { waarde: 'nog ' + t.open + ' open', niveau: '', redenen: redenen };
        }

        return { waarde: t.nee ? 'voldoet nog niet' : 'voldoet', niveau: t.nee ? 'h' : '', redenen: redenen };
    }

    /** De uitkomst over alle delen die gelden. */
    function uitkomst(a) {
        var som = { ja: 0, nee: 0, nvt: 0, open: 0, totaal: 0, gaten: [] };
        var open = [];

        model.delen.forEach(function (deel) {
            var t = tel(deel, a);
            ['ja', 'nee', 'nvt', 'open', 'totaal'].forEach(function (k) { som[k] += t[k]; });
            som.gaten = som.gaten.concat(t.gaten);
            if (t.open) { open.push(deel.naam); }
        });

        if (som.open) {
            return {
                waarde: 'nog te bepalen',
                niveau: '',
                redenen: [aantal(som.open, 'norm is', 'normen zijn') + ' nog niet helemaal ingevuld, '
                    + (open.length > 3 ? 'in ' + open.length + ' delen.' : 'bij ' + cm.opsomming(open) + '.')]
            };
        }

        if (som.nee) {
            return {
                waarde: 'voldoet nog niet',
                niveau: 'h',
                redenen: [
                    'Van de ' + aantal(som.totaal, 'norm', 'normen') + ' die gelden, voldoet de dienst aan ' + som.nee
                        + ' nog niet: ' + cm.opsomming(som.gaten) + '.',
                    'De dienstverlening kan pas starten als de dienst aan alle normen voldoet.'
                ]
            };
        }

        return {
            waarde: 'voldoet',
            niveau: '',
            redenen: ['De dienst voldoet aan alle ' + aantal(som.totaal, 'norm', 'normen') + ' die gelden.'
                + (som.nvt ? ' ' + aantal(som.nvt, 'norm is', 'normen zijn') + ' niet van toepassing.' : '')]
        };
    }

    /**
     * Elke regel toont zijn antwoord. Een regel gaat nooit vanzelf dicht
     * terwijl iemand ermee bezig is: alleen bij de start en na het inladen van
     * een bestand worden ze opnieuw ingedeeld. Dan staat open wat nog niet
     * helemaal is ingevuld.
     */
    function ververs(form, a, opnieuw) {
        if (!laad()) { return; }

        cm.elk(form.querySelectorAll('details[data-groep]'), function (regel) {
            var key = regel.dataset.groep;
            var waarde = String(a[key] || '');

            if (opnieuw) {
                regel.open = !compleet(key, a) || !!regel.querySelector('[aria-invalid="true"]');
            }

            var uit = regel.querySelector('[data-samenvatting]');
            uit.textContent = samenvatting(key, a);
            uit.parentNode.setAttribute('data-niveau', waarde ? model.keuzes[waarde].niveau : '');
        });
    }
}());

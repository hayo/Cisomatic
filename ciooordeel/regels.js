/**
 * De regels van het CIO oordeel, voor de motor in ../assets/app.js.
 *
 * - elke regel van een aspect toont zijn oordeel, in de kleur van het niveau
 * - onder elk gebied staat het risico van dat gebied
 * - bij het oordeel staat een voorstel, en een eerste versie van de kern
 *
 * De gebieden, keuzes en oordelen komen uit PHP, als data-groepen op het
 * uitkomstblok van het oordeel. Dit spiegelt co_gebied(), co_voorstel() en
 * co_uitkomst(); PHP rekent bij het versturen alles opnieuw uit.
 */
(function () {
    'use strict';

    var cm = window.cisomatic;
    var model = null;
    var gebiedVan = {};
    var snelVorig = {};

    cm.regels({ voorstel: voorstel, motivatie: motivatie, uitkomst: uitkomst, afgeleid: afgeleid, ververs: ververs });

    function laad() {
        if (!model) {
            var blok = document.querySelector('[data-afgeleid="oordeel"]');
            model = blok ? JSON.parse(blok.dataset.groepen) : null;
            (model ? model.gebieden : []).forEach(function (g) {
                g.aspecten.forEach(function (key) { gebiedVan[key] = g.id; });
            });
        }
        return model;
    }

    /**
     * Spiegelt co_gebied(): het zwaarste niveau, en hoeveel aspecten nog open
     * zijn. Een verborgen aspect staat niet in a, en telt niet mee.
     */
    function gebied(g, a) {
        var uit = { niveau: 'l', open: 0, voorwaarden: 0, aanbevelingen: 0 };

        g.aspecten.forEach(function (key) {
            if (!(key in a)) { return; }

            // Een gewist voorstel staat hier nog als lege lijst.
            var waarde = String(a[key] || '');
            if (!waarde) { uit.open++; return; }

            var niveau = model.keuzes[waarde].niveau;
            if (niveau && cm.rang(niveau) > cm.rang(uit.niveau)) { uit.niveau = niveau; }
            if (waarde === 'risico' || waarde === 'aandacht') {
                if (a[key + '_voorwaarde'] === 'ja') { uit.voorwaarden++; } else { uit.aanbevelingen++; }
            }
        });

        if (uit.open) { uit.niveau = ''; }
        return uit;
    }

    function alleGebieden(a) {
        return model.gebieden.map(function (g) { return { g: g, uit: gebied(g, a) }; });
    }

    /** Spiegelt co_voorstel(). */
    function berekenVoorstel(a) {
        if (!laad()) { return ''; }

        var lijst = alleGebieden(a);
        if (lijst.some(function (x) { return x.uit.open; })) { return ''; }

        var hoog = lijst.filter(function (x) { return x.uit.niveau === 'h'; }).length;
        var niveaus = lijst.map(function (x) { return x.uit.niveau; });

        if (hoog >= model.grens) { return 'negatief'; }
        if (lijst.some(function (x) { return x.uit.voorwaarden; })) { return 'voorwaarden'; }
        if (cm.heeft(niveaus, 'm') || cm.heeft(niveaus, 'h')) { return 'aanbevelingen'; }
        return 'positief';
    }

    /**
     * Het oordeel, of bij een aspect ‘Op orde’ als het hele gebied op orde is.
     * Kiest de adviseur daarna ‘Nee, er speelt iets’, dan wist een leeg
     * voorstel dat ‘Op orde’ weer; een eigen keuze blijft staan.
     */
    function voorstel(key, a) {
        if (key === 'co_oordeel') { return berekenVoorstel(a); }
        if (!laad() || !gebiedVan[key]) { return ''; }

        var snel = a['co_' + gebiedVan[key] + '_snel'];
        if (snel === 'orde') { return 'orde'; }
        return snel === 'nee' && a[key] === 'orde' ? [] : '';
    }

    /** Spiegelt co_uitkomst(). */
    function uitkomst(naam, a) {
        if (naam !== 'afwijking') { return ''; }

        var v = berekenVoorstel(a);
        return !v || !a.co_oordeel ? '' : (v === a.co_oordeel ? 'nee' : 'ja');
    }

    function afgeleid(bron, a) {
        if (!laad()) { return null; }
        if (bron === 'stukken') { return stukken(a); }
        if (bron === 'oordeel') { return oordeel(a); }

        var g = model.gebieden.filter(function (x) { return x.id === bron; })[0];
        if (!g) { return null; }

        var uit = gebied(g, a);
        if (uit.open) {
            return {
                waarde: 'nog te bepalen',
                niveau: '',
                redenen: ['Nog ' + uit.open + (uit.open === 1 ? ' aspect' : ' aspecten') + ' open.']
            };
        }

        return {
            waarde: cm.LABEL[uit.niveau],
            niveau: uit.niveau,
            redenen: [{
                l: 'Alles is op orde, of niet van toepassing.',
                m: 'Er is een aandachtspunt, of iets wat niet te beoordelen was.',
                h: 'Er is minstens één risico.'
            }[uit.niveau]]
        };
    }

    function stukken(a) {
        var missen = Object.keys(model.minimum).filter(function (s) { return !cm.heeft(a.co_stukken, s); })
            .map(function (s) { return model.minimum[s]; });

        return missen.length
            ? { waarde: 'nee', niveau: 'm', redenen: ['Er ontbreekt nog: ' + cm.opsomming(missen) + '.',
                'Je kunt verder, maar het rapport vraagt om de stukken die ontbreken.'] }
            : { waarde: 'ja', niveau: '', redenen: ['Het minimum is er: een business case, een planning en een '
                + 'architectuurschets.'] };
    }

    function oordeel(a) {
        var lijst = alleGebieden(a);
        var open = lijst.reduce(function (som, x) { return som + x.uit.open; }, 0);

        if (open) {
            return {
                waarde: 'nog te bepalen',
                niveau: '',
                redenen: ['Nog ' + open + (open === 1 ? ' aspect' : ' aspecten') + ' open. Daarna verschijnt hier het '
                    + 'voorstel.']
            };
        }

        var v = berekenVoorstel(a);
        var hoog = lijst.filter(function (x) { return x.uit.niveau === 'h'; })
            .map(function (x) { return x.g.kort; });
        var redenen = [model.oordelen[v].betekenis];

        if (hoog.length) {
            redenen.push('Hoog risico bij ' + cm.opsomming(hoog) + '. Bij ' + model.grens
                + ' of meer gebieden met een hoog risico is het voorstel negatief.');
        }

        return { waarde: model.oordelen[v].label, niveau: v === 'negatief' ? 'h' : (v === 'voorwaarden' ? 'm' : ''),
            redenen: redenen };
    }

    /** Een eerste versie van de kern, zolang de adviseur die niet zelf aanpast. */
    function motivatie(voor, a) {
        if (voor !== 'kern' || !laad()) { return ''; }

        var v = a.co_oordeel || berekenVoorstel(a);
        if (!v) { return ''; }

        var lijst = alleGebieden(a);
        var hoog = lijst.filter(function (x) { return x.uit.niveau === 'h'; }).map(function (x) { return x.g.kort; });
        var voorwaarden = lijst.reduce(function (som, x) { return som + x.uit.voorwaarden; }, 0);
        var aanbevelingen = lijst.reduce(function (som, x) { return som + x.uit.aanbevelingen; }, 0);
        var zinnen = ['Het oordeel over ' + (a.projectnaam || 'het project') + ' is '
            + model.oordelen[v].label.toLowerCase() + '.'];

        if (hoog.length) {
            zinnen.push('De grootste risico’s liggen bij ' + cm.opsomming(hoog) + '.');
        }
        if (voorwaarden) {
            zinnen.push((voorwaarden === 1 ? 'Eén punt moet' : voorwaarden + ' punten moeten')
                + ' geregeld zijn voordat het project verder mag.');
        }
        if (aanbevelingen) {
            var is = aanbevelingen === 1 ? 'is' : 'zijn';
            zinnen.push((voorwaarden ? 'Daarnaast ' + is + ' er ' : 'Er ' + is + ' ')
                + (aanbevelingen === 1 ? 'één aanbeveling' : aanbevelingen + ' aanbevelingen')
                + ' om de kans op succes te vergroten.');
        }
        if (!hoog.length && !voorwaarden && !aanbevelingen) {
            zinnen.push('Alle aspecten zijn op orde.');
        }

        return zinnen.join(' ');
    }

    /** Spiegelt co_aspect_stand(): alleen op orde en niet van toepassing mogen dicht. */
    function open(waarde) {
        return !cm.heeft(['orde', 'nvt'], waarde);
    }

    /**
     * Elke regel toont zijn oordeel. Een regel zonder oordeel, of met werk
     * erin, staat open. Een regel gaat nooit vanzelf dicht terwijl iemand
     * ermee bezig is: alleen bij de start, na het inladen van een bestand en
     * na een snelle keuze voor een gebied worden ze opnieuw ingedeeld.
     */
    function ververs(form, a, opnieuw) {
        if (!laad()) { return; }

        // Na een snelle keuze voor een gebied worden zijn regels opnieuw ingedeeld.
        var herschik = {};
        model.gebieden.forEach(function (g) {
            var snel = a['co_' + g.id + '_snel'] || '';
            herschik[g.id] = !opnieuw && snel !== snelVorig[g.id];
            snelVorig[g.id] = snel;
        });

        cm.elk(form.querySelectorAll('details[data-groep]'), function (regel) {
            var key = regel.dataset.groep;
            var waarde = String(a[key] || '');

            if (opnieuw || herschik[gebiedVan[key]]) {
                regel.open = open(waarde) || !!regel.querySelector('[aria-invalid="true"]');
            }

            var samenvatting = regel.querySelector('[data-samenvatting]');
            samenvatting.textContent = !waarde ? 'nog te beoordelen'
                : model.keuzes[waarde].label + (a[key + '_voorwaarde'] === 'ja' ? ' · voorwaarde' : '');
            samenvatting.parentNode.setAttribute('data-niveau', waarde ? model.keuzes[waarde].niveau || '' : '');
        });
    }
}());

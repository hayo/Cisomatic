/**
 * De regels van de soevereiniteitsscan, voor de motor in ../assets/app.js.
 *
 * - de soort leverancier vult alle thema's alvast in, als voorstel
 * - onder elk thema staat meteen het label van dat thema
 * - bij de uitkomst staat het label van de hele dienst, met de score
 *
 * De getallen staan in vragen.php en afleiding.php, en komen via data-groepen
 * mee: de voorstellen op de vraag naar de leverancier, de rest op het
 * uitkomstblok. Dit spiegelt sv_thema() en sv_evalueer(); PHP rekent bij het
 * versturen alles opnieuw uit.
 */
(function () {
    'use strict';

    var cm = window.cisomatic;
    var model = null;
    var profielen = null;

    cm.regels({ voorstel: voorstel, afgeleid: afgeleid });

    function gegevens(selector) {
        var blok = document.querySelector(selector);
        return blok ? JSON.parse(blok.dataset.groepen) : null;
    }

    /** Het antwoord dat bij deze leverancier past. Leeg wist een voorstel van een vorige keuze. */
    function voorstel(key, a) {
        profielen = profielen || gegevens('[data-vraag="sv_profiel"]') || {};
        var profiel = profielen[a.sv_profiel] || {};

        return profiel[key] || [];
    }

    function afgeleid(bron, a) {
        model = model || gegevens('[data-afgeleid="label"]');
        if (!model) { return null; }
        if (bron === 'label') { return totaal(a); }

        var t = model.themas.filter(function (t) { return t.id === bron; })[0];
        return t ? themaUitkomst(t, a) : null;
    }

    function label(niveau) {
        return model.labels[niveau].letter + ' · ' + model.labels[niveau].naam;
    }

    /** Spiegelt sv_eis(). */
    function eis(t, a) {
        var nodig = model.belang[a.sv_belang] || 0;
        return t.eis === 'kern' ? nodig : (t.eis === 'rest' ? Math.max(0, nodig - 1) : 0);
    }

    /**
     * Spiegelt sv_thema(): het zwakste antwoord, en het deel van de punten.
     * Een verborgen vraag staat niet in a, een open vraag is leeg.
     */
    function thema(t, a) {
        var uit = { open: 0, niveaus: [], zwakste: [], onbekend: false };

        Object.keys(t.vragen).forEach(function (key) {
            if (!(key in a)) { return; }
            if (!a[key]) { uit.open++; return; }

            var niveau = t.vragen[key].niveaus[a[key]];
            if (niveau === null || niveau === undefined) { return; }

            uit.niveaus.push(niveau);
            uit.onbekend = uit.onbekend || a[key] === 'onbekend';
        });

        uit.niveau = uit.niveaus.length ? Math.min.apply(null, uit.niveaus) : 0;
        uit.deel = uit.niveaus.length
            ? uit.niveaus.reduce(function (som, n) { return som + n; }, 0) / (4 * uit.niveaus.length)
            : 0;
        uit.eis = eis(t, a);

        Object.keys(t.vragen).forEach(function (key) {
            if (a[key] && t.vragen[key].niveaus[a[key]] === uit.niveau) { uit.zwakste.push('‘' + t.vragen[key].kort + '’'); }
        });

        return uit;
    }

    function themaUitkomst(t, a) {
        var uit = thema(t, a);

        if (uit.open) {
            return { waarde: 'nog te bepalen', niveau: '', redenen: ['Beantwoord eerst alle vragen hierboven.'] };
        }

        var redenen = ['Het zwakste antwoord bepaalt het label. Dat is ' + cm.opsomming(uit.zwakste) + '.'];

        if (uit.eis > 0) {
            redenen.push((uit.niveau < uit.eis ? 'Te laag' : 'Hoog genoeg') + ': voor deze informatie is minimaal '
                + model.labels[uit.eis].letter + ' nodig.');
        } else if (t.eis === 'geen') {
            redenen.push('Dit thema telt mee in de score, maar het houdt niets tegen.');
        }
        if (uit.onbekend) {
            redenen.push('‘Weet ik niet’ telt als het laagste niveau.');
        }

        return { waarde: label(uit.niveau), niveau: uit.niveau < uit.eis ? 'h' : '', redenen: redenen };
    }

    /** Spiegelt sv_evalueer(): de gewogen score, en het label dat daarbij hoort. */
    function totaal(a) {
        var open = 0;
        var score = 0;
        var teLaag = [];

        model.themas.forEach(function (t) {
            var uit = thema(t, a);
            open += uit.open;
            score += t.gewicht * uit.deel;
            if (uit.niveau < uit.eis) { teLaag.push(t.naam.toLowerCase()); }
        });

        if (open) {
            return {
                waarde: 'nog te bepalen',
                niveau: '',
                redenen: ['Nog ' + open + (open === 1 ? ' vraag' : ' vragen') + ' open. Daarna verschijnt hier het label.']
            };
        }

        score = Math.round(score);
        var niveau = Math.min(4, Math.floor((score + 12) / 25));

        return {
            waarde: label(niveau),
            niveau: teLaag.length ? 'h' : '',
            redenen: [
                'Score: ' + score + ' van de 100.',
                model.labels[niveau].betekenis,
                teLaag.length
                    ? 'Dit past niet bij de informatie: te laag bij ' + cm.opsomming(teLaag) + '.'
                    : 'Dit past bij de informatie: elk thema is hoog genoeg.'
            ]
        };
    }
}());

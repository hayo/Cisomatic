/**
 * De regels van de Online communicatie check, voor de motor in ../assets/app.js.
 *
 * - wat het kader van het gekozen platform eist
 * - de uitkomst: voldoet, of uitleg nodig (comply of explain)
 * - bij uitleg de afwijkingen, voorgevuld
 *
 * Spiegelt oc_evalueer() in afleiding.php; PHP rekent bij het versturen alles
 * opnieuw uit. De kaders en teksten komen uit PHP, als data-groepen op de vraag
 * naar het platform.
 */
(function () {
    'use strict';

    var cm = window.cisomatic;
    var gegevens = null;

    cm.regels({
        afgeleid: afgeleid,
        uitkomst: function (naam, a) {
            return naam === 'uitkomst' ? evalueer(a).uitkomst : '';
        },
        motivatie: function (voor, a) {
            var u = evalueer(a);
            return voor === 'afwijking' && u.uitkomst === 'explain' ? u.afwijkingen.join('\n') : '';
        }
    });

    function data() {
        if (!gegevens) {
            gegevens = JSON.parse(document.querySelector('[data-vraag="sm_platform"]').dataset.groepen);
        }
        return gegevens;
    }

    /** Spiegelt oc_kader(). */
    function kader(a) {
        var k = data().kaders[a.sm_platform];
        if (!k) { return null; }

        var kopie = Object.assign({}, k);
        if (a.sm_platform === 'anders' && String(a.sm_anders_naam || '').trim()) {
            kopie.naam = String(a.sm_anders_naam).trim();
        }
        return kopie;
    }

    /** Spiegelt oc_tekst(). */
    function tekst(sleutel, naam, grond) {
        return data().teksten[sleutel].replace('{naam}', naam || '').replace('{grond}', grond || '').trim();
    }

    /** Spiegelt oc_voorwaarden_gelden(): niet op het basiskanaal. */
    function geldt(a) {
        return !(kader(a) || {}).basis;
    }

    /** Spiegelt oc_compleet(). */
    function compleet(a) {
        return !!a.sm_platform
            && (!geldt(a) || (!!a.oc_contact && !!a.sm_eigen_kanalen && !!a.sm_doorverwijzen));
    }

    /** Spiegelt oc_platform_tekst(). */
    function platformTekst(k) {
        if (k.inzet === 'geen') { return tekst('geen_kader', k.naam); }
        if (k.explain) { return tekst('explain', k.naam, k.grond); }
        return k.basis ? tekst('basis', k.naam) : tekst('inzetbaar', k.naam);
    }

    /** Spiegelt oc_evalueer(). */
    function evalueer(a) {
        var k = kader(a) || data().kaders.anders;
        var naam = k.naam;
        var eigen = geldt(a) ? (a.sm_eigen_kanalen || '') : '';
        var contact = geldt(a) && a.oc_contact === 'ja';

        var afwijkingen = [
            k.explain ? platformTekst(k) : '',
            contact ? tekst('contact', naam) : '',
            eigen === 'deels' ? tekst('eigen_deels') : (eigen === 'nee' ? tekst('eigen_nee') : ''),
            geldt(a) && a.sm_doorverwijzen === 'nee' ? tekst('doorverwijzen', naam) : ''
        ].filter(Boolean);

        var uitkomst = !compleet(a) ? '' : (afwijkingen.length ? 'explain' : 'comply');

        return {
            uitkomst: uitkomst,
            afwijkingen: afwijkingen,
            redenen: uitkomst === 'explain' ? afwijkingen : [tekst('comply')]
        };
    }

    function afgeleid(bron, a) {
        var k = kader(a);
        if (!k) {
            return { waarde: 'nog te bepalen', niveau: '', redenen: ['Kies eerst het platform.'] };
        }

        if (bron === 'kader') {
            var inzet = data().inzet[k.inzet];
            var letOp = [
                k.toestel ? tekst('toestel', k.naam) : '',
                k.gedrag ? tekst('gedrag', k.naam) : ''
            ].filter(Boolean);
            return { waarde: inzet.label, niveau: inzet.niveau, redenen: [platformTekst(k)].concat(letOp) };
        }
        if (bron === 'uitkomst') {
            var u = evalueer(a);
            if (!u.uitkomst) {
                return { waarde: 'nog te bepalen', niveau: '', redenen: ['Beantwoord eerst de vragen van stap 1 en 2.'] };
            }
            var soort = data().uitkomsten[u.uitkomst];
            return { waarde: soort.label, niveau: soort.niveau, redenen: u.redenen };
        }

        return null;
    }
}());

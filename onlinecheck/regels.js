/**
 * De regels van de Online communicatie check, voor de motor in ../assets/app.js.
 *
 * - het kader van het gekozen platform
 * - of de doelen binnen het kader vallen
 * - de uitkomst: comply of explain
 * - bij een explain de afwijkingen, voorgevuld
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
    function tekst(sleutel, naam, lijst) {
        return data().teksten[sleutel]
            .replace('{naam}', naam || '')
            .replace('{lijst}', cm.opsomming(lijst || []));
    }

    /** De gekozen doelen met deze ruimte in het kader, als labels, in vaste volgorde. */
    function doelen(a, k, ruimte) {
        return Object.keys(data().doelen)
            .filter(function (d) { return cm.heeft(a.oc_doelen || [], d) && k.ruimte[d] === ruimte; })
            .map(function (d) { return data().doelen[d]; });
    }

    /** Spiegelt oc_eigen_geldt(). */
    function eigenGeldt(a) {
        return a.sm_platform !== 'mastodon';
    }

    /** Spiegelt oc_compleet(). */
    function compleet(a) {
        return !!a.sm_platform
            && (a.oc_doelen || []).length > 0
            && !!a.sm_doorverwijzen
            && (!eigenGeldt(a) || !!a.sm_eigen_kanalen);
    }

    /** Spiegelt oc_evalueer(). */
    function evalueer(a) {
        var k = kader(a) || data().kaders.anders;
        var naam = k.naam;
        // Midden in een zin met een kleine letter, zoals in oc_evalueer().
        var buiten = doelen(a, k, 'nee').map(function (l) { return l.charAt(0).toLowerCase() + l.slice(1); });
        var eigen = eigenGeldt(a) ? (a.sm_eigen_kanalen || '') : '';

        var afwijkingen = [
            k.inzet === 'geen' ? tekst('geen_kader', naam) : (k.explain ? tekst('explain', naam) : ''),
            buiten.length ? tekst('doelen', naam, buiten) : '',
            eigen === 'deels' ? tekst('eigen_deels') : (eigen === 'nee' ? tekst('eigen_nee') : ''),
            a.sm_doorverwijzen === 'nee' ? tekst('doorverwijzen', naam) : ''
        ].filter(Boolean);

        var uitkomst = !compleet(a) ? '' : (afwijkingen.length ? 'explain' : 'comply');

        return {
            buiten: buiten,
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

        var inzet = data().inzet[k.inzet];
        var u = evalueer(a);

        if (bron === 'kader') {
            return { waarde: inzet.label, niveau: inzet.niveau, redenen: [k.uitleg, 'Doelgroep: ' + k.doelgroep] };
        }
        if (bron === 'doelen') {
            if (!(a.oc_doelen || []).length) {
                return { waarde: 'nog te bepalen', niveau: '', redenen: ['Kies eerst de doelen.'] };
            }
            if (k.inzet === 'geen') {
                return { waarde: 'Geen kader', niveau: 'm', redenen: [tekst('doelen_geen_kader')] };
            }
            var betaald = doelen(a, k, 'betaald');
            var extra = betaald.length ? [tekst('betaald', k.naam, betaald)] : [];
            return u.buiten.length
                ? { waarde: 'Niet helemaal', niveau: 'm', redenen: [tekst('doelen', k.naam, u.buiten)].concat(extra, ['Dan werk je straks een explain uit.']) }
                : { waarde: 'Ja', niveau: 'l', redenen: [tekst('doelen_past', k.naam)].concat(extra) };
        }
        if (bron === 'uitkomst') {
            if (!u.uitkomst) {
                return { waarde: 'nog te bepalen', niveau: '', redenen: ['Beantwoord eerst de vragen van de stappen 1 tot en met 3.'] };
            }
            var soort = data().uitkomsten[u.uitkomst];
            return { waarde: soort.label, niveau: soort.niveau, redenen: u.redenen };
        }

        return null;
    }
}());

/**
 * De regels van de kanalenscan, voor de motor in ../assets/app.js.
 *
 * - het kader van het gekozen platform, en de risico's in de weging
 * - of de inzet per soort gebruik binnen het kader valt
 * - de uitkomst: comply, explain of niet inzetten
 * - bij een explain de afwijkingen, voorgevuld
 * - bij een vertrek het advies over het vertrek
 *
 * Spiegelt sm_evalueer() in afleiding.php en sm_exit_advies() in exit.php;
 * PHP rekent bij het versturen alles opnieuw uit. De kaders en teksten komen
 * uit PHP, als data-groepen op de vraag naar het platform.
 */
(function () {
    'use strict';

    var cm = window.cisomatic;
    var gegevens = null;

    cm.regels({
        afgeleid: afgeleid,
        uitkomst: function (naam, a) {
            if (naam === 'uitkomst') { return evalueer(a).uitkomst; }
            if (naam === 'toestel') { return (kader(a) || {}).toestel ? 'ja' : 'nee'; }
            return '';
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

    /** Spiegelt sm_kader(). */
    function kader(a) {
        var k = data().kaders[a.sm_platform];
        if (!k) { return null; }

        var kopie = Object.assign({}, k, { risicos: k.risicos.slice() });
        if (a.sm_platform === 'anders') {
            if (String(a.sm_anders_naam || '').trim()) { kopie.naam = String(a.sm_anders_naam).trim(); }
            if (a.sm_anders_land === 'offensief') {
                kopie.toestel = true;
                kopie.risicos.push(data().land.offensief);
            }
            if (a.sm_anders_land === 'buiten') { kopie.risicos.push(data().land.buiten); }
        }
        return kopie;
    }

    /** Spiegelt sm_tekst(). */
    function tekst(sleutel, naam, lijst) {
        return data().teksten[sleutel]
            .replace('{naam}', naam || '')
            .replace('{lijst}', cm.opsomming(lijst || []));
    }

    /** Spiegelt sm_gebruik(): de gekozen soorten, in vaste volgorde. */
    function gebruik(a) {
        return Object.keys(data().toetsen).filter(function (soort) { return cm.heeft(a.sm_gebruik || [], soort); });
    }

    /** Spiegelt sm_buiten(): per tekstsleutel de labels buiten de ruimte. */
    function buiten(a, k) {
        var uit = {};

        gebruik(a).forEach(function (soort) {
            data().toetsen[soort].forEach(function (toets) {
                var ruimte = k[toets.ruimte];
                var labels = data()[toets.ruimte];
                var lijst = !ruimte ? [] : Object.keys(labels)
                    .filter(function (x) { return cm.heeft(a[toets.vraag] || [], x) && !cm.heeft(ruimte, x); })
                    .map(function (x) { return labels[x]; });

                if (lijst.length) { uit[toets.tekst] = lijst; }
            });
        });

        return uit;
    }

    /** Spiegelt sm_compleet(). */
    function compleet(a) {
        var velden = ['sm_accounttype', 'sm_bereik', 'sm_alternatief', 'sm_doorverwijzen', 'sm_weging'];
        var lijsten = [];

        if (a.sm_platform !== 'mastodon') { velden.push('sm_eigen_kanalen'); }
        if (a.sm_platform === 'anders') { velden.push('sm_anders_land'); }
        if ((kader(a) || {}).toestel) { velden.push('sm_toestel'); }
        gebruik(a).forEach(function (soort) {
            data().toetsen[soort].forEach(function (toets) { lijsten.push(toets.vraag); });
            if (soort === 'betaald') { velden.push('sm_doelgericht'); }
        });

        return !!a.sm_platform
            && gebruik(a).length > 0
            && velden.every(function (veld) { return !!a[veld]; })
            && lijsten.every(function (lijst) { return (a[lijst] || []).length > 0; });
    }

    /** Spiegelt sm_evalueer() voor een nieuw of bestaand kanaal. */
    function evalueer(a) {
        if (a.sm_situatie === 'exit') { return { uitkomst: '', afwijkingen: [], buiten: {}, redenen: [] }; }

        var k = kader(a) || data().kaders.anders;
        var naam = k.naam;
        var uit = buiten(a, k);
        var eigen = a.sm_eigen_kanalen || '';
        var bewindspersoon = a.sm_accounttype === 'bewindspersoon';
        var betaald = cm.heeft(gebruik(a), 'betaald');

        var niet = [
            a.sm_bereik === 'nee' ? tekst('bereik') : '',
            a.sm_alternatief === 'ja' ? tekst('alternatief') : '',
            a.sm_weging === 'nee' ? tekst('weging') : '',
            k.toestel && a.sm_toestel === 'rijk' ? tekst('rijk_toestel', naam) : '',
            bewindspersoon && k.bewindspersoon === 'verboden' ? tekst('bewindspersoon_verboden', naam) : ''
        ].filter(Boolean);

        var afwijkingen = [k.inzet === 'geen' ? tekst('geen_kader', naam) : (k.explain ? tekst('explain', naam) : '')]
            .concat(Object.keys(uit).map(function (t) { return tekst(t, naam, uit[t]); }))
            .concat([
                eigen === 'deels' ? tekst('eigen_deels') : (eigen === 'nee' ? tekst('eigen_nee') : ''),
                a.sm_doorverwijzen === 'nee' ? tekst('doorverwijzen', naam) : '',
                k.toestel && a.sm_toestel === 'prive' ? tekst('prive_toestel') : '',
                bewindspersoon && k.bewindspersoon === 'nee' ? tekst('bewindspersoon', naam) : '',
                bewindspersoon && betaald ? tekst('bewindspersoon_betaald') : ''
            ])
            .filter(Boolean);

        var uitkomst = !compleet(a) ? '' : (niet.length ? 'niet' : (afwijkingen.length ? 'explain' : 'comply'));

        return {
            buiten: uit,
            uitkomst: uitkomst,
            afwijkingen: afwijkingen,
            redenen: uitkomst === 'niet' ? niet : (uitkomst === 'explain' ? afwijkingen : [tekst('comply')])
        };
    }

    /** Spiegelt sm_exit_advies(). */
    function exitAdvies(a) {
        var velden = ['sm_exit_besluit', 'sm_exit_doel', 'sm_exit_opvang', 'sm_exit_impact', 'sm_exit_aanpak', 'sm_exit_type'];
        if (!velden.every(function (veld) { return !!a[veld]; })) {
            return { waarde: 'nog te bepalen', niveau: '', redenen: ['Beantwoord eerst de vragen over het vertrek.'] };
        }

        var e = data().exit;
        var aanpak = a.sm_exit_aanpak;
        var type = a.sm_exit_type;
        var zwaar = cm.heeft(['moeilijk', 'niet'], a.sm_exit_opvang) || a.sm_exit_impact === 'ernstig';

        var redenen = [
            a.sm_exit_scope === 'platform' ? e.teksten.platform : '',
            a.sm_exit_doel === 'ja' && a.sm_exit_besluit !== 'opgelegd' ? e.teksten.doel : '',
            zwaar && aanpak !== 'soft' && a.sm_exit_besluit !== 'opgelegd' ? e.teksten.zwaar_soft : '',
            zwaar && aanpak === 'soft' && type !== 'hybride' ? e.teksten.zwaar_hybride : '',
            type === 'verwijderen' ? e.teksten.verwijderen : ''
        ].filter(Boolean);
        if (!redenen.length || (redenen.length === 1 && redenen[0] === e.teksten.platform)) {
            redenen.push(aanpak === 'soft' && type === 'deactiveren' ? e.teksten.aangeraden : e.teksten.past);
        }

        return {
            waarde: e.aanpak[aanpak] + ', ' + e.type[type],
            niveau: type === 'verwijderen' ? 'h' : (zwaar || aanpak !== 'soft' || type === 'inactief' ? 'm' : 'l'),
            redenen: redenen
        };
    }

    function afgeleid(bron, a) {
        if (bron === 'exit') { return exitAdvies(a); }

        var k = kader(a);
        if (!k) {
            return { waarde: 'nog te bepalen', niveau: '', redenen: ['Kies eerst het platform.'] };
        }

        var inzet = data().inzet[k.inzet];
        var u = evalueer(a);

        if (bron === 'kader') {
            return { waarde: inzet.label, niveau: inzet.niveau, redenen: [k.uitleg, 'Doelgroep: ' + k.doelgroep] };
        }
        if (bron === 'risicos') {
            return { waarde: k.naam, niveau: inzet.niveau, redenen: k.risicos };
        }
        if (bron === 'doel') {
            if (!gebruik(a).length) {
                return { waarde: 'nog te bepalen', niveau: '', redenen: ['Kies eerst hoe je het kanaal gebruikt.'] };
            }
            if (!k.doelen) {
                return { waarde: 'Geen kader', niveau: 'm', redenen: [tekst('doel_geen_kader')] };
            }
            var redenen = Object.keys(u.buiten).map(function (t) { return tekst(t, k.naam, u.buiten[t]); });
            return redenen.length
                ? { waarde: 'Niet helemaal', niveau: 'm', redenen: redenen.concat(['Dan werk je straks een explain uit.']) }
                : { waarde: 'Ja', niveau: 'l', redenen: [tekst('doel_past', k.naam)] };
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

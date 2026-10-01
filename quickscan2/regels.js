/**
 * De regels van de quickscan voor de BIO2, voor de motor in ../assets/app.js.
 *
 * - eisen die uit eerdere antwoorden volgen, verschijnen meteen met uitleg
 * - waar de tool een waarde voorstelt, staat die alvast aangevinkt
 * - een motivatie vult de tool met zijn eigen redenering
 * - een bijstelling kan niet onder de ondergrens, behalve door de beoordelaar
 *
 * De regels hieronder zijn een kopie van afleiding.php. PHP blijft de bron
 * van waarheid en rekent bij het versturen alles opnieuw uit; dit is puur om
 * het invullen makkelijker te maken. Pas je daar iets aan, pas het hier dan ook aan.
 */
(function () {
    'use strict';

    var cm = window.cisomatic;
    var LABEL = cm.LABEL;
    var vorige = null;

    cm.regels({
        voorstel: voorstel,
        motivatie: motivatieVoorstel,
        afgeleid: afgeleid,
        ververs: ververs
    });

    // 'Geen van deze' sluit de soorten schade uit, en andersom. In de capture-fase,
    // zodat de motor bij deze invoer al met de juiste vinkjes rekent.
    document.addEventListener('input', function (e) {
        var vakje = e.target;
        if (vakje.name !== 'tbb_soorten[]' || !vakje.checked) { return; }

        cm.elk(vakje.form.elements['tbb_soorten[]'], function (ander) {
            if (ander !== vakje && (vakje.value === 'geen' || ander.value === 'geen')) { ander.checked = false; }
        });
    }, true);

    function avgMinimum(pgCategorieen, pgBijzonder) {
        if (pgBijzonder === 'meerdere') { return 'zh'; }
        if (pgBijzonder === 'een' || pgCategorieen === 'meerdere') { return 'h'; }
        if (pgCategorieen === 'een') { return 'm'; }
        return 'zl';
    }

    function niveauUitProces(proces) {
        if (proces === 'kritisch-strategisch') { return 'h'; }
        if (proces === 'strategisch') { return 'm'; }
        if (proces === 'bijdragend') { return 'l'; }
        return 'zl';
    }

    function niveauOmhoog(niveau) {
        return { zl: 'l', l: 'm', m: 'h', h: 'zh', zh: 'zh' }[niveau] || niveau;
    }

    /**
     * Spiegelt q2_voorstel_beschikbaarheid() en q2_voorstel_rpo(). Bedrijfskritisch
     * is alleen een voorstel: een vitaal systeem voor de kern van het werk.
     */
    function voorstel(key, a) {
        if (key === 'bedrijfskritisch') {
            if (!a.proces_classificatie || !a.systeem_classificatie) { return ''; }
            return a.systeem_classificatie === 'vitaal'
                && (a.proces_classificatie === 'strategisch' || a.proces_classificatie === 'kritisch-strategisch')
                ? 'ja' : 'nee';
        }
        if (key === 'beschikbaarheid') {
            if (!a.proces_classificatie) { return ''; }
            var niveau = niveauUitProces(a.proces_classificatie);
            if (a.systemen_afhankelijk === 'veel') { niveau = niveauOmhoog(niveau); }
            return niveau;
        }
        if (key === 'rpo') {
            return {
                zh: '1-uur', h: '4-uur', m: '24-uur', l: '24-uur', zl: '1-week'
            }[a.beschikbaarheid] || '';
        }
        return '';
    }

    /** Wat de motor in een uitkomstblok zet. */
    function afgeleid(bron, a) {
        if (bron === 'pg') {
            var pg = afgeleidePersoonsgegevens(a);
            return { waarde: pg.label, redenen: pg.redenen, niveau: pg.zwaar ? 'h' : 'l' };
        }
        if (bron === 'i' || bron === 'v') {
            var eis = bron === 'i' ? afgeleideIntegriteit(a) : afgeleideVertrouwelijkheid(a);
            return { waarde: LABEL[eis.niveau], redenen: eis.redenen, niveau: eis.niveau };
        }
        if (bron === 'dreiging') {
            var dreiging = afgeleidDreigingsprofiel(a);
            return { waarde: dreiging.label, redenen: dreiging.redenen, niveau: dreiging.niveau === 'hoog' ? 'zh' : 'zl' };
        }
        if (bron === 'tbb') {
            var tbb = afgeleideTbb(a);
            return { waarde: tbb.label, redenen: tbb.redenen, niveau: tbb.cruciaal ? 'zh' : (tbb.niveau ? 'm' : 'zl') };
        }
        return null;
    }

    function ververs(form, a) {
        var i = afgeleideIntegriteit(a);
        var v = afgeleideVertrouwelijkheid(a);

        toonOndergrens('i', i.ondergrens);
        toonOndergrens('v', v.ondergrens);
        beperkBijstelling(form, 'integriteit_bijstelling', i, a);
        beperkBijstelling(form, 'vertrouwelijkheid_bijstelling', v, a);
        meldGevolgen({
            i: i.niveau,
            v: v.niveau,
            dreiging: afgeleidDreigingsprofiel(a).niveau,
            tbb: afgeleideTbb(a).label
        });
    }

    /** Spiegelt q2_afgeleide_persoonsgegevens(). */
    function persoonsgegevens(a) {
        var blok = document.querySelector('[data-groepen]');
        var groepen = blok ? JSON.parse(blok.dataset.groepen) : { gewoon: [], bijzonder: [] };
        var gekozen = a.informatie_soorten || [];

        var gewoon = groepen.gewoon.filter(function (k) { return gekozen.indexOf(k) !== -1; });
        var bijzonder = groepen.bijzonder.filter(function (k) { return gekozen.indexOf(k) !== -1; });

        return {
            heeft: gewoon.length > 0 || bijzonder.length > 0,
            gewoon: gewoon,
            bijzonderLijst: bijzonder,
            bsn: gekozen.indexOf('bsn') !== -1,
            categorieen: gewoon.length >= 2 ? 'meerdere' : (gewoon.length === 1 ? 'een' : ''),
            bijzonder: bijzonder.length >= 2 ? 'meerdere' : (bijzonder.length === 1 ? 'een' : 'nee')
        };
    }

    function noem(sleutels) {
        var labels = sleutels.map(function (k) {
            return cm.optieLabel('informatie_soorten', k).toLowerCase();
        });

        return labels.length === 1
            ? labels[0]
            : labels.slice(0, -1).join(', ') + ' en ' + labels[labels.length - 1];
    }

    function afgeleidePersoonsgegevens(a) {
        var pg = persoonsgegevens(a);

        if (!pg.heeft) {
            return {
                label: 'Geen persoonsgegevens',
                redenen: ['Er is niets aangekruist dat over een herkenbaar persoon gaat. De AVG is '
                    + 'daarmee niet van toepassing en de vragen over doel en grondslag vervallen.'],
                zwaar: false
            };
        }

        var redenen = [];

        if (pg.gewoon.length) {
            redenen.push((pg.gewoon.length === 1 ? 'Eén categorie' : pg.gewoon.length + ' categorieën')
                + ' gewone persoonsgegevens: ' + noem(pg.gewoon) + '.');
        }
        if (pg.bijzonderLijst.length) {
            redenen.push((pg.bijzonderLijst.length === 1 ? 'Eén categorie' : pg.bijzonderLijst.length + ' categorieën')
                + ' bijzondere persoonsgegevens: ' + noem(pg.bijzonderLijst)
                + '. Die wegen zwaarder en tillen de eisen omhoog.');
        }
        if (pg.bsn) {
            redenen.push('Het burgerservicenummer zit erbij. Daarvoor moet een wettelijke grondslag '
                + 'worden aangewezen.');
        }

        var minimum = avgMinimum(pg.categorieen, pg.bijzonder);
        redenen.push('Daarmee geldt een ondergrens van ' + LABEL[minimum]
            + ' voor zowel integriteit als vertrouwelijkheid.');

        var totaal = pg.gewoon.length + pg.bijzonderLijst.length;
        var label = totaal === 1 ? 'Eén categorie persoonsgegevens' : totaal + ' categorieën persoonsgegevens';
        if (pg.bijzonderLijst.length) { label += ', waarvan ' + pg.bijzonderLijst.length + ' bijzonder'; }

        return { label: label, redenen: redenen, zwaar: pg.bijzonderLijst.length > 0 };
    }

    /** Spiegelt q2_afgeleide_integriteit(). */
    function afgeleideIntegriteit(a) {
        var pg = persoonsgegevens(a);

        // Harde ondergrens uit de quickscan zelf (AVG beginsel 'Juistheid').
        var ondergrens = avgMinimum(pg.categorieen, pg.bijzonder);
        var redenen = [];

        if (ondergrens !== 'zl') {
            redenen.push('Het AVG beginsel ‘Juistheid’ legt bij deze persoonsgegevens een '
                + 'ondergrens van ' + LABEL[ondergrens] + ' op.');
        }

        // Voorstel op basis van het procesbelang. Dat staat niet in het document;
        // het is een startwaarde waar je onder mag gaan zitten.
        var voorgesteld = niveauUitProces(a.proces_classificatie || '');
        if (a.proces_classificatie) {
            redenen.push('Het proces is '
                + cm.optieLabel('proces_classificatie', a.proces_classificatie).toLowerCase()
                + '; als startwaarde stelt de tool daar ' + LABEL[voorgesteld] + ' bij voor. Dat is een '
                + 'voorstel, geen regel uit de quickscan.');
        }

        var niveau = cm.rang(voorgesteld) > cm.rang(ondergrens) ? voorgesteld : ondergrens;

        if (!redenen.length) {
            redenen.push('Er zijn geen persoonsgegevens en het proces is ondersteunend, dus er '
                + 'gelden geen bijzondere eisen aan de juistheid van de gegevens.');
        }

        return { niveau: niveau, ondergrens: ondergrens, redenen: redenen };
    }

    /** Spiegelt q2_afgeleide_vertrouwelijkheid(). */
    function afgeleideVertrouwelijkheid(a) {
        var pg = persoonsgegevens(a);
        var uitRubricering = {
            'stg-confidentieel': 'zh', 'stg-geheim': 'zh', 'stg-zeer-geheim': 'zh',
            'dep-vertrouwelijk': 'h', intern: 'm', openbaar: 'zl'
        }[a.rubricering] || 'zl';

        var uitAvg = avgMinimum(pg.categorieen, pg.bijzonder);
        var ondergrens = cm.rang(uitAvg) > cm.rang(uitRubricering) ? uitAvg : uitRubricering;
        var redenen = [];

        if (a.rubricering) {
            redenen.push('De informatie is aangemerkt als ‘' + cm.optieLabel('rubricering', a.rubricering)
                + '’; dat komt overeen met vertrouwelijkheid ' + LABEL[uitRubricering] + '.');
        }
        if (uitAvg !== 'zl') {
            redenen.push('De verwerkte persoonsgegevens leggen een ondergrens van '
                + LABEL[uitAvg] + ' op.');
        }

        var niveau = ondergrens;

        if (afgeleidDreigingsprofiel(a).niveau === 'hoog' && cm.rang(niveau) < cm.rang('h')) {
            niveau = 'h';
            redenen.push('Het dreigingsprofiel is hoog. Informatie die bescherming nodig heeft tegen '
                + 'inlichtingendiensten, terreurgroepen of georganiseerde criminaliteit kan niet '
                + 'tegelijk laag vertrouwelijk zijn, dus stelt de tool Hoog voor.');
        }

        if (!redenen.length) {
            redenen.push('Vul hierboven in hoe gevoelig de informatie is, dan verschijnt hier de eis.');
        }

        return { niveau: niveau, ondergrens: ondergrens, redenen: redenen };
    }

    /** Spiegelt q2_afgeleid_dreigingsprofiel(). */
    function afgeleidDreigingsprofiel(a) {
        var kenmerken = {
            besluitvorming: 'er nog niet openbare politieke besluitvorming of standpuntbepaling in zit',
            onderhandeling: 'er onderhandelingen of contracten met een groot belang in zitten',
            internationaal: 'het om internationale betrekkingen, diplomatie of defensie gaat',
            opsporing: 'het om informatie over opsporing, inlichtingen of veiligheid gaat',
            bewindspersonen: 'er persoonsgegevens van bewindspersonen of topambtenaren in zitten',
            vitaal: 'er een koppeling is met een vitaal proces of vitale infrastructuur',
            identiteit: 'er identificerende gegevens van grote aantallen burgers in zitten'
        };

        var gekozen = a.dreiging_kenmerken || [];
        var redenen = [];

        Object.keys(kenmerken).forEach(function (sleutel) {
            if (gekozen.indexOf(sleutel) !== -1) {
                redenen.push('Het profiel is hoog omdat ' + kenmerken[sleutel] + '.');
            }
        });

        if ((a.rubricering || '').indexOf('stg-') === 0) {
            redenen.push('Het profiel is hoog omdat er staatsgeheime informatie wordt verwerkt.');
        }
        if (a.dreiging_verhoging === 'hoog' && !redenen.length) {
            redenen.push('Het profiel is handmatig op hoog gezet.');
        }

        if (!redenen.length) {
            return {
                niveau: 'normaal',
                label: 'Normaal',
                redenen: ['Geen van de kenmerken die een systeem interessant maken voor '
                    + 'inlichtingendiensten, terreurgroepen of georganiseerde criminaliteit is van '
                    + 'toepassing.']
            };
        }

        return { niveau: 'hoog', label: 'Hoog', redenen: redenen };
    }

    /** Spiegelt q2_tbb(). TBB 1 is het zwaarst; 0 betekent geen TBB. */
    function afgeleideTbb(a) {
        var bronnen = [];

        // Elke soort schade is een vakje bij tbb_soorten, met de sleutel van zijn vraag als waarde.
        cm.elk(document.querySelectorAll('[name="tbb_soorten[]"]'), function (vakje) {
            var keuze = a[vakje.value] || '';
            if (keuze.indexOf('tbb') === 0) {
                bronnen.push([parseInt(keuze.slice(3), 10),
                    cm.optieLabel('tbb_soorten', vakje.value) + ', ' + cm.optieLabel(vakje.value, keuze).toLowerCase()]);
            }
        });
        if (!bronnen.length) { bronnen.push([0, 'Geen van de soorten schade']); }

        bronnen.push([
            { 'dep-vertrouwelijk': 4, 'stg-confidentieel': 3, 'stg-geheim': 2, 'stg-zeer-geheim': 1 }[a.rubricering] || 0,
            'Rubricering ‘' + (a.rubricering ? cm.optieLabel('rubricering', a.rubricering) : 'Niet ingevuld') + '’'
        ]);

        var biv = [
            a.beschikbaarheid || 'zl',
            a.integriteit_bijstelling || afgeleideIntegriteit(a).niveau,
            a.vertrouwelijkheid_bijstelling || afgeleideVertrouwelijkheid(a).niveau
        ];
        var zeerHoog = biv.filter(function (n) { return n === 'zh'; }).length;
        bronnen.push([[0, 3, 3, 2][zeerHoog],
            'Betrouwbaarheidseisen, ' + ['geen eis', 'één eis', 'twee eisen', 'alle drie'][zeerHoog] + ' Zeer hoog']);

        var niveaus = bronnen.map(function (b) { return b[0]; }).filter(Boolean);
        var niveau = niveaus.length ? Math.min.apply(null, niveaus) : 0;
        var cruciaal = niveau >= 1 && niveau <= 3;

        var redenen = bronnen.map(function (b) {
            return b[1] + ': ' + (b[0] ? 'TBB ' + b[0] : 'geen TBB') + '.';
        });
        redenen.push(niveau
            ? 'Het zwaarste niveau telt, dus de uitkomst is TBB ' + niveau + '.'
            : 'Niets haalt TBB 4, dus de uitkomst is geen TBB.');
        redenen.push(cruciaal
            ? 'Bij TBB 1, 2 of 3 is het systeem cruciaal. Het komt op het overzicht van cruciale systemen van de '
                + 'organisatie, en er volgt een volledige risicoanalyse.'
            : 'Het systeem is niet cruciaal. De basis van de BIO2 blijft gelden.');

        return {
            niveau: niveau,
            cruciaal: cruciaal,
            label: (niveau ? 'TBB ' + niveau : 'Geen TBB') + (cruciaal ? ', cruciaal systeem' : ''),
            redenen: redenen
        };
    }

    /** Onder de eis: het vaste minimum, en of je daaronder mag. */
    function toonOndergrens(bron, ondergrens) {
        var blok = document.querySelector('[data-afgeleid="' + bron + '"]');
        if (!blok) { return; }

        var grens = blok.querySelector('[data-afgeleid-ondergrens]');
        if (!grens) {
            grens = document.createElement('p');
            grens.setAttribute('data-afgeleid-ondergrens', '');
            blok.appendChild(grens);
        }

        grens.textContent = 'Vast minimum: ';
        var sterk = document.createElement('strong');
        sterk.textContent = LABEL[ondergrens];
        grens.appendChild(sterk);
        grens.appendChild(document.createTextNode(ondergrens === 'zl'
            ? '. Er is geen wettelijke ondergrens, dus je bent vrij om bij te stellen.'
            : '. Dat komt uit de quickscan zelf en kan niet lager, behalve door de beoordelaar in stap 2.'));
    }

    /** Spiegelt q2_motivatie_voorstel(). */
    function motivatieVoorstel(voor, a) {
        if (voor === 'beschikbaarheid') {
            var b = voorstel('beschikbaarheid', a);
            if (!b) { return ''; }

            var tekst = 'Voorgesteld op ' + LABEL[b] + ' omdat het proces is aangemerkt als '
                + cm.optieLabel('proces_classificatie', a.proces_classificatie).toLowerCase() + '.';

            if (a.systemen_afhankelijk === 'veel') {
                tekst += ' Een niveau hoger dan het procesbelang alleen, omdat veel processen of een '
                    + 'kritiek proces van dit systeem afhankelijk worden en daarmee de uitval erven.';
            }

            return tekst;
        }

        if (voor === 'integriteit') { return afgeleideIntegriteit(a).redenen.join(' '); }
        if (voor === 'vertrouwelijkheid') { return afgeleideVertrouwelijkheid(a).redenen.join(' '); }
        if (voor === 'dreiging') { return afgeleidDreigingsprofiel(a).redenen.join(' '); }
        if (voor === 'tbb') { return afgeleideTbb(a).redenen.join('\n'); }

        if (voor === 'rpo') {
            // 'Niets' en 'Niet van toepassing' passen niet in deze zin.
            if (!a.beschikbaarheid || !a.rpo || a.rpo === 'geen' || a.rpo === 'nvt') { return ''; }

            return 'Past bij de beschikbaarheidseis ' + LABEL[a.beschikbaarheid]
                + ': als het systeem binnen die tijd weer moet draaien, hoort daar een terugvalpunt '
                + 'van ' + cm.optieLabel('rpo', a.rpo).toLowerCase() + ' bij.';
        }

        return '';
    }

    /**
     * Welke bijstellingen hier zijn toegestaan. Een behoeftesteller mag tot aan
     * de ondergrens uit de quickscan; de beoordelaar mag alles, want dat is wat
     * stap 2 is.
     */
    function beperkBijstelling(form, key, bron, a) {
        var select = form.elements[key];
        if (!select || select.tagName !== 'SELECT') { return; }

        var alles = a.rol === 'beoordelaar';

        Array.prototype.forEach.call(select.options, function (optie) {
            if (optie.value === '') { return; }

            var mag = alles
                ? optie.value !== bron.niveau
                : (cm.rang(optie.value) >= cm.rang(bron.ondergrens) && optie.value !== bron.niveau);

            optie.hidden = !mag;
            optie.disabled = !mag;
            if (!mag && optie.selected) { select.value = ''; }
        });
    }

    /**
     * Laat zien dat een antwoord verderop gevolgen had. Zonder dit verandert
     * bijvoorbeeld de vertrouwelijkheidseis een paar secties verderop zonder
     * dat iemand het merkt.
     */
    function meldGevolgen(nu) {
        if (!vorige) {
            vorige = nu;
            return;
        }

        var meldingen = [];

        if (nu.v !== vorige.v) {
            meldingen.push('De eis aan vertrouwelijkheid is nu ' + LABEL[nu.v] + '.');
        }
        if (nu.i !== vorige.i) {
            meldingen.push('De eis aan integriteit is nu ' + LABEL[nu.i] + '.');
        }
        if (nu.dreiging !== vorige.dreiging) {
            meldingen.push('Het dreigingsprofiel is nu ' + nu.dreiging + '.');
        }
        if (nu.tbb !== vorige.tbb) {
            meldingen.push('Te beschermen belangen: ' + nu.tbb + '.');
        }

        vorige = nu;

        if (meldingen.length) { cm.toast(meldingen.join(' ')); }
    }
}());

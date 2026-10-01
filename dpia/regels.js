/**
 * De regels van de DPIA, voor de motor in ../assets/app.js.
 *
 * - waar de tool een waarde voorstelt, staat die alvast ingevuld
 * - een risico staat op één regel, en klapt open vanaf omvang Hoog
 * - de stromen krijgen de groepen en partijen als suggestie
 *
 * uitkomst() en omvang() zijn een kopie van afleiding.php en risicos.php. PHP
 * rekent bij het versturen alles opnieuw uit. De voorstellen staan alleen
 * hier: ze helpen bij het invullen, het antwoord telt.
 */
(function () {
    'use strict';

    var cm = window.cisomatic;
    var LABEL = cm.LABEL;
    var SCHAAL = ['zl', 'l', 'm', 'h', 'zh'];
    var MATRIX = {
        zl: { zl: 'zl', l: 'l', m: 'l', h: 'm', zh: 'm' },
        l: { zl: 'l', l: 'l', m: 'm', h: 'm', zh: 'h' },
        m: { zl: 'l', l: 'm', m: 'm', h: 'h', zh: 'h' },
        h: { zl: 'm', l: 'm', m: 'h', h: 'h', zh: 'zh' },
        zh: { zl: 'm', l: 'h', m: 'h', h: 'zh', zh: 'zh' }
    };

    // Risico's waarbij de schade pas via een klacht, een boete of een doorlopend lek ontstaat.
    var INDIRECT = ['r2', 'r3', 'r4', 'r5', 'r6', 'r7', 'r9', 'r10', 'r11'];
    var BRON = { ip: 'systeem', gebruik: 'systeem', locatie: 'systeem', functie: 'eigen', financieel: 'eigen' };

    var formulier = document.querySelector('#cisomatic > form');
    var elk = cm.elk;
    var heeft = cm.heeft;
    var rang = cm.rang;
    var regels = cm.tekstregels;

    cm.regels({
        voorstel: function (key, a) { return voorstelMet(key, a).waarde; },
        motivatie: motivatie,
        uitkomst: uitkomst,
        afgeleid: afgeleid,
        ververs: function (form, a, opnieuw) {
            regelRisicoRegels(form, a, opnieuw);
            vulSuggesties(form, a);
        }
    });

    // ------------------------------------------------------------- hulpjes

    function stap(niveau, stappen, bodem) {
        var huidig = rang(niveau);
        if (!huidig) { return niveau; }
        return SCHAAL[Math.max(rang(bodem || 'zl'), Math.min(5, huidig + stappen)) - 1];
    }

    function hoogste(niveaus) {
        return niveaus.reduce(function (max, n) { return rang(n) > rang(max) ? n : max; }, '');
    }

    function aantal(n, enkelvoud, meervoud) {
        return n === 1 ? 'Eén ' + enkelvoud : n + ' ' + meervoud;
    }

    function keuzesMet(keuzes, waarde) {
        return Object.keys(keuzes || {}).filter(function (k) { return keuzes[k] === waarde; });
    }

    // ------------------------------------------------- gespiegeld uit PHP

    /** Spiegelt dp_omvang(). */
    function omvang(kans, impact) {
        return (MATRIX[kans] || {})[impact] || '';
    }

    /** Spiegelt dp_doorgifte(). */
    function doorgifte(a) {
        var plekken = [];
        if (a.cloud === 'ja' || a.cloud === 'mogelijk') { plekken.push(a.cloud_locatie); }
        if (a.pg_subverwerkers === 'ja') { plekken.push(a.pg_subverwerkers_locatie); }

        return ['buiten-eer', 'modelcontract', 'adequaatheid'].filter(function (p) {
            return heeft(plekken, p);
        })[0] || '';
    }

    /** Of er bij een risico een nieuwe maatregel komt: gekozen, of zelf ingevuld. */
    function heeftExtra(id, a) {
        return keuzesMet(a[id + '_maatregelen'], 'komt').length > 0 || regels(a[id + '_extra']).length > 0;
    }

    /** Spiegelt dp_uitkomst(). */
    function uitkomst(naam, a) {
        if (naam.indexOf('omvang:') === 0) {
            var id = naam.slice(7);
            return omvang(a[id + '_kans'], a[id + '_impact']);
        }
        if (naam.indexOf('extra:') === 0) {
            return heeftExtra(naam.slice(6), a) ? 'ja' : 'nee';
        }
        if (naam === 'groepen') {
            return groepsnamen(a).length >= 2 ? 'meerdere' : 'een';
        }
        if (naam === 'verwerker') {
            return (a.partijen || []).some(function (p) {
                return p.rol === 'verwerker' || p.rol === 'subverwerker';
            }) ? 'ja' : 'nee';
        }
        if (naam === 'doorgifte') { return doorgifte(a) ? 'ja' : 'nee'; }
        if (naam === 'toestemming') {
            return a.pg_grondslag === 'toestemming' || a.uitzondering === 'toestemming' ? 'ja' : 'nee';
        }

        return '';
    }

    /** Spiegelt dp_groepen(): elke andere groep telt als eigen groep. */
    function groepsnamen(a) {
        var namen = [];
        (a.betrokkenen || []).forEach(function (g) {
            if (g === 'groep_anders') {
                namen.push.apply(namen, regels(a.betrokkenen_anders));
            } else {
                namen.push(cm.optieLabel('betrokkenen', g));
            }
        });
        return namen;
    }

    // ---------------------------------------------------------- voorstellen

    function persoonsgegevens(a) {
        var blok = document.querySelector('[data-groepen]');
        var groepen = blok ? JSON.parse(blok.dataset.groepen) : { gewoon: [], identificerend: [], bijzonder: [] };
        var gekozen = a.informatie_soorten || [];

        function binnen(groep) {
            return groepen[groep].filter(function (k) { return heeft(gekozen, k); });
        }

        return { gewoon: binnen('gewoon'), identificerend: binnen('identificerend'), bijzonder: binnen('bijzonder') };
    }

    /** Wat de tool voorstelt voor een vraag, met de redenen erbij. */
    function voorstelMet(key, a) {
        if (key.indexOf('bron_') === 0) {
            return { waarde: BRON[key.slice(5)] || 'betrokkene', redenen: [] };
        }
        if (key.indexOf('pg_groepen_') === 0) {
            return { waarde: (a.betrokkenen || []).slice(), redenen: [] };
        }

        var risico = /^(r\d+)_(maatregelen|impact|kans|kans_na)$/.exec(key);
        if (!risico) { return { waarde: '', redenen: [] }; }

        var id = risico[1];
        if (risico[2] === 'maatregelen') { return { waarde: bekendeMaatregelen(id, a), redenen: [] }; }
        if (risico[2] === 'impact') { return voorstelImpact(id, a); }
        if (risico[2] === 'kans') { return voorstelKans(id, a); }

        var kans = a[id + '_kans'] || '';
        return { waarde: kans && heeftExtra(id, a) ? stap(kans, -1) : '', redenen: [] };
    }

    /**
     * Maatregelen die al vaststaan uit eerdere antwoorden. Zo hoef je ze niet
     * twee keer aan te geven, en kun je jezelf niet tegenspreken.
     */
    function bekendeMaatregelen(id, a) {
        var bekend = {
            r2: { verklaring: heeft(['verklaring', 'beide'], a.informeren) },
            r3: {
                verklaring: heeft(['verklaring', 'beide'], a.informeren),
                melding: heeft(['melding', 'beide'], a.informeren)
            },
            r6: { uitleg: regels(a.rechten_regeling).length > 0, systeem: a.pg_rechten === 'volledig' },
            r7: { register: a.verzoeken_register === 'ja', bewaking: a.verzoeken_register === 'ja' },
            r11: {
                vastgesteld: heeft(['selectielijst', 'wettelijk', 'eigen'], a.bewaartermijn),
                automatisch: a.archief_vernietiging === 'automatisch'
            },
            r12: { overeenkomst: a.verwerkersovereenkomst === 'getekend', scc: doorgifte(a) === 'modelcontract' }
        }[id] || {};

        var keuzes = {};
        Object.keys(bekend).forEach(function (maatregel) {
            if (bekend[maatregel]) { keuzes[maatregel] = 'al'; }
        });

        return keuzes;
    }

    function voorstelImpact(id, a) {
        var pg = persoonsgegevens(a);
        var niveau = 'm';
        var redenen = [];

        // Zeer hoog alleen via de stappen hieronder. Anders levert elke DPIA met
        // gezondheidsgegevens een restrisico op dat de AP moet zien, hoe goed de
        // maatregelen ook zijn.
        if (pg.bijzonder.length) {
            niveau = 'h';
            redenen.push('Er zitten bijzondere persoonsgegevens in. Daar hoort Hoog bij.');
        } else if (pg.identificerend.length || heeft(a.informatie_soorten, 'financieel')) {
            niveau = 'h';
            redenen.push('Er zitten gegevens in waarmee fraude mogelijk is, zoals een BSN of financiële gegevens. '
                + 'Daar hoort Hoog bij.');
        } else {
            redenen.push('Het gaat om gewone persoonsgegevens. Daar hoort Midden bij.');
        }

        if (heeft(a.betrokkenen, 'kinderen') || heeft(a.betrokkenen, 'kwetsbaar')) {
            niveau = stap(niveau, 1);
            redenen.push('Het gaat ook om kinderen of mensen in een kwetsbare positie, dus een stap hoger.');
        }
        if (a.pg_aantal_betrokkenen === 'meer') {
            niveau = stap(niveau, 1);
            redenen.push('Het gaat om meer dan 100.000 mensen, dus een stap hoger.');
        }
        if (heeft(INDIRECT, id)) {
            niveau = stap(niveau, -1, 'l');
            redenen.push('De schade ontstaat hier minder direct dan bij misbruik, dus een stap lager.');
        }
        if (id === 'r13' && rang(niveau) < rang('h')) {
            niveau = 'h';
            redenen.push('Een besluit over mensen raakt hen direct, dus minstens Hoog.');
        }

        return { waarde: niveau, redenen: redenen };
    }

    function voorstelKans(id, a) {
        var feit = kansUitAntwoorden(id, a);
        var niveau = feit[0];
        var redenen = [feit[1]];
        var keuzes = a[id + '_maatregelen'] || {};
        var totaal = formulier.querySelectorAll('[data-vraag="' + id + '_maatregelen"] [data-maatregel]').length;
        var al = keuzesMet(keuzes, 'al').length;

        if (totaal && al && al + keuzesMet(keuzes, 'nee').length === totaal) {
            niveau = stap(niveau, -1);
            redenen.push('Alle maatregelen die nodig zijn, zijn er al. Daarom een stap lager.');
        }

        return { waarde: niveau, redenen: redenen };
    }

    function kansUitAntwoorden(id, a) {
        var standaard = ['m', 'Er zijn geen antwoorden die de kans groter of kleiner maken. Daarom Midden.'];
        var bericht = ['l', 'Betrokkenen krijgen bericht als hun gegevens worden verzameld.'];

        switch (id) {
            case 'r2':
                return a.informeren === 'nee'
                    ? ['h', 'Betrokkenen horen nog niet dat hun gegevens worden gebruikt.']
                    : ['l', 'Betrokkenen horen dat hun gegevens worden gebruikt.'];
            case 'r3':
                return {
                    nee: ['h', 'De informatieplicht is nog niet geregeld.'],
                    verklaring: ['m', 'Betrokkenen horen het alleen via de privacyverklaring. Die lezen weinig mensen.'],
                    melding: bericht,
                    beide: bericht
                }[a.informeren] || standaard;
            case 'r4':
                if (a.pg_subsidiariteit === 'kan_minder') { return ['h', 'Het doel kan ook met minder gegevens.']; }
                if (a.pg_proportionaliteit === 'twijfel') {
                    return ['m', 'Er is twijfel of de verwerking in verhouding staat tot het doel.'];
                }
                return ['l', 'Volgens de afweging zijn de gegevens nodig voor het doel.'];
            case 'r5':
                return heeft(a.verwerking_vormen, 'verstrekken')
                    ? ['m', 'Er gaan gegevens naar andere organisaties.']
                    : ['l', 'Er gaan geen gegevens naar andere organisaties.'];
            case 'r6':
                return {
                    nee: ['h', 'Het systeem kan inzage, correctie en verwijdering niet aan.'],
                    deels: ['m', 'Het systeem kan inzage, correctie en verwijdering maar deels aan.'],
                    volledig: ['l', 'Het systeem kan inzage, correctie en verwijdering aan.']
                }[a.pg_rechten] || standaard;
            case 'r7':
                return {
                    nee: ['m', 'Verzoeken worden niet in een register bewaakt.'],
                    ja: ['l', 'Verzoeken worden in een register bewaakt.']
                }[a.verzoeken_register] || standaard;
            case 'r11':
                if (a.bewaartermijn === 'onbepaald') { return ['h', 'Er is nog geen bewaartermijn bepaald.']; }
                if (a.na_termijn === 'overbrengen') {
                    return ['l', 'De gegevens gaan na de termijn naar een archiefdienst.'];
                }
                return {
                    nee: ['h', 'Het systeem kan de gegevens niet vernietigen.'],
                    handmatig: ['m', 'Vernietigen kan alleen met de hand.'],
                    automatisch: ['l', 'Het systeem vernietigt de gegevens automatisch.']
                }[a.archief_vernietiging] || standaard;
            case 'r12':
                return {
                    'buiten-eer': ['h', 'Gegevens gaan naar een land zonder adequaatheidsbesluit en zonder modelcontract.'],
                    modelcontract: ['m', 'Gegevens gaan buiten de EER, op basis van een modelcontract.'],
                    adequaatheid: ['l', 'Gegevens gaan naar een land met een adequaatheidsbesluit.']
                }[doorgifte(a)] || standaard;
        }

        return standaard;
    }

    /** Tekst die de tool alvast in een tekstvak zet. */
    function motivatie(voor, a) {
        if (voor === 'scope') {
            var systeem = regels(a.systeem_omschrijving).join(' ').replace(/\.$/, '');
            var proces = regels(a.proces_omschrijving).join(' ').replace(/\.$/, '');
            return [
                systeem ? 'Het systeem of de dienst: ' + systeem + '.' : '',
                proces ? 'Het proces: ' + proces + '.' : ''
            ].filter(Boolean).join('\n');
        }

        if (voor === 'proportionaliteit') {
            if (a.pg_proportionaliteit !== 'in_verhouding') { return ''; }
            var labels = (a.informatie_soorten || []).map(function (k) {
                return cm.optieLabel('informatie_soorten', k).toLowerCase();
            });
            return 'De verwerking gebruikt alleen de gegevens die nodig zijn voor het doel: '
                + cm.opsomming(labels) + '.' + (a.belang === 'kritisch'
                    ? '\nZonder deze verwerking valt het proces stil.'
                    : '');
        }

        if (voor === 'subsidiariteit') {
            return a.pg_subsidiariteit === 'minst_ingrijpend'
                ? 'Het doel kan redelijkerwijs niet worden bereikt op een manier die minder nadelig is voor de betrokkenen.'
                : '';
        }

        if (voor === 'conclusie') {
            var risicos = risicoUitkomsten(a);
            if (!risicos.length) { return ''; }

            var rest = hoogste(risicos.map(function (r) { return r.rest; }));
            var zonder = hoogste(risicos.map(function (r) { return r.omvang; }));
            var naam = a.projectnaam || 'deze verwerking';
            var zinnen = [rang(rest) <= rang('l')
                ? 'Met de maatregelen zijn de privacyrisico’s van ' + naam + ' beperkt.'
                : (rest === 'm'
                    ? 'Met de maatregelen blijven er risico’s van Midden over. Die zijn aanvaardbaar als de '
                        + 'maatregelen echt worden uitgevoerd.'
                    : 'Met de maatregelen blijft er een hoog risico over. Daarom moet de organisatie eerst de '
                        + 'Autoriteit Persoonsgegevens raadplegen.')];

            if (rang(zonder) > rang(rest)) {
                zinnen.push('Zonder de nieuwe maatregelen is het grootste risico ' + LABEL[zonder] + '.');
            }

            return zinnen.join('\n');
        }

        return '';
    }

    /** Elk ingevuld risico met zijn omvang, zoals dp_risico_uitkomsten(). */
    function risicoUitkomsten(a) {
        var lijst = [];

        function voegToe(titel, impact, kans, kansNa, extra) {
            if (!impact || !kans) { return; }
            var na = extra && kansNa ? kansNa : kans;
            lijst.push({ titel: titel, omvang: omvang(kans, impact), rest: omvang(na, impact) });
        }

        elk(formulier.querySelectorAll('details[data-groep]'), function (regel) {
            if (regel.closest('[hidden]')) { return; }
            var id = regel.dataset.groep;
            voegToe(regel.querySelector('summary > span').textContent, a[id + '_impact'], a[id + '_kans'],
                a[id + '_kans_na'], heeftExtra(id, a));
        });

        (a.eigen_risicos || []).forEach(function (rij) {
            voegToe(rij.risico, rij.impact, rij.kans, rij.kans_na, regels(rij.extra).length > 0);
        });

        return lijst;
    }

    /** Spiegelt dp_suggesties(): namen om uit te kiezen bij de stromen. */
    function vulSuggesties(form, a) {
        var namen = groepsnamen(a).concat((a.partijen || []).map(function (p) { return (p.organisatie || '').trim(); }))
            .filter(function (naam, i, alle) { return naam && alle.indexOf(naam) === i; });

        elk(form.querySelectorAll('datalist[data-suggesties]'), function (lijst) {
            if (lijst.dataset.namen === namen.join('\n')) { return; }
            lijst.dataset.namen = namen.join('\n');
            lijst.textContent = '';
            namen.forEach(function (naam) {
                var optie = document.createElement('option');
                optie.textContent = naam;
                lijst.appendChild(optie);
            });
        });
    }

    // ---------------------------------------------------------- uitkomsten

    /** Wat de motor in een uitkomstblok zet. */
    function afgeleid(bron, a) {
        if (bron === 'pg') { return uitkomstPersoonsgegevens(a); }
        if (bron === 'conclusie') { return uitkomstConclusie(a); }
        return uitkomstRisico(bron, a);
    }

    function uitkomstPersoonsgegevens(a) {
        var pg = persoonsgegevens(a);
        var totaal = pg.gewoon.length + pg.identificerend.length + pg.bijzonder.length;
        var redenen = [];

        function noem(sleutels) {
            return cm.opsomming(sleutels.map(function (k) { return cm.optieLabel('informatie_soorten', k).toLowerCase(); }));
        }

        if (pg.gewoon.length) {
            redenen.push(aantal(pg.gewoon.length, 'soort', 'soorten') + ' gewone persoonsgegevens: ' + noem(pg.gewoon) + '.');
        }
        if (pg.identificerend.length) {
            redenen.push('Gegevens waarmee iemand te identificeren is: ' + noem(pg.identificerend)
                + '. Daar gelden eigen regels voor.');
        }
        if (pg.bijzonder.length) {
            redenen.push('Bijzondere persoonsgegevens: ' + noem(pg.bijzonder)
                + '. Die zijn extra beschermd, en in stap 2 komt er een vraag over.');
        }
        if (!totaal) {
            redenen.push('Kruis hierboven aan welke persoonsgegevens er in de verwerking komen.');
        }

        return {
            waarde: totaal
                ? aantal(totaal, 'soort', 'soorten') + ' persoonsgegevens'
                    + (pg.bijzonder.length ? ', waarvan ' + pg.bijzonder.length + ' bijzonder' : '')
                : 'nog niets aangekruist',
            niveau: pg.bijzonder.length ? 'h' : '',
            redenen: redenen
        };
    }

    function uitkomstRisico(id, a) {
        var impact = a[id + '_impact'];
        var kans = a[id + '_kans'];
        var o = omvang(kans, impact);

        function uitleg(wat, gekozen, voorgesteld) {
            var reden = voorgesteld.redenen.join(' ');
            if (!gekozen) { return wat + ': kies een niveau. ' + reden; }
            return gekozen === voorgesteld.waarde
                ? wat + ' ' + LABEL[gekozen] + '. ' + reden
                : wat + ' ' + LABEL[gekozen] + ', zelf gekozen. De tool stelde ' + LABEL[voorgesteld.waarde]
                    + ' voor. ' + reden;
        }

        var redenen = [uitleg('Impact', impact, voorstelImpact(id, a)), uitleg('Kans', kans, voorstelKans(id, a))];

        if (o) {
            redenen.push('Kans ' + LABEL[kans] + ' en impact ' + LABEL[impact] + ' geven samen ' + LABEL[o] + '.');
        }

        var kansNa = a[id + '_kans_na'];
        if (o && kansNa && heeftExtra(id, a)) {
            redenen.push('Met de nieuwe maatregelen wordt de omvang ' + LABEL[omvang(kansNa, impact)] + '.');
        }

        return { waarde: o ? LABEL[o] : 'nog te bepalen', niveau: o, redenen: redenen };
    }

    function uitkomstConclusie(a) {
        var risicos = risicoUitkomsten(a);
        var rest = hoogste(risicos.map(function (r) { return r.rest; }));

        var redenen = risicos.filter(function (r) { return rang(r.omvang) >= rang('m'); }).map(function (r) {
            return r.titel + ': ' + LABEL[r.omvang] + (r.rest !== r.omvang ? ', met de maatregelen ' + LABEL[r.rest] : '') + '.';
        });

        if (!risicos.length) {
            redenen.push('Beoordeel eerst de risico’s in de stap hiervoor.');
        } else if (!redenen.length) {
            redenen.push('Geen enkel risico komt boven Laag uit.');
        }

        return { waarde: rest ? LABEL[rest] : 'nog te bepalen', niveau: rest, redenen: redenen };
    }

    /**
     * Een risico staat dicht zolang het voorstel volstaat, en klapt open zodra
     * de omvang Hoog of hoger wordt: dan moet er een maatregel bij komen. Bij
     * Midden zou bijna elk risico openstaan. Een regel gaat nooit vanzelf dicht
     * terwijl iemand ermee bezig is. Alleen bij de start en na het inladen van
     * een bestand worden alle regels opnieuw ingedeeld.
     */
    function regelRisicoRegels(form, a, opnieuw) {
        elk(form.querySelectorAll('details[data-groep]'), function (regel) {
            var id = regel.dataset.groep;
            var o = omvang(a[id + '_kans'], a[id + '_impact']);
            var nodig = rang(o) >= rang('h');

            if (opnieuw) {
                regel.open = nodig || !!regel.querySelector('[aria-invalid="true"]');
            } else if (nodig && !regel.dataset.nodig) {
                regel.open = true;
            }
            regel.dataset.nodig = nodig ? 'ja' : '';

            var samenvatting = regel.querySelector('[data-samenvatting]');
            samenvatting.textContent = risicoSamenvatting(id, a);
            samenvatting.parentNode.setAttribute('data-niveau', o);
        });
    }

    /** Spiegelt dp_risico_samenvatting(). */
    function risicoSamenvatting(id, a) {
        var impact = a[id + '_impact'];
        var kans = a[id + '_kans'];
        if (!impact || !kans) { return 'nog te beoordelen'; }

        var klein = function (n) { return LABEL[n].toLowerCase(); };
        var o = omvang(kans, impact);
        var rest = a[id + '_kans_na'] && heeftExtra(id, a) ? omvang(a[id + '_kans_na'], impact) : o;

        return 'impact ' + klein(impact) + ' · kans ' + klein(kans) + ' · omvang ' + klein(o)
            + (rest !== o ? ' · met maatregelen ' + klein(rest) : '');
    }
}());

/**
 * Dossiers: de formulieren van één project bij elkaar.
 *
 * - een dossier begint met de business case en de quickscan van zijn normenkader
 * - de quickscan stelt andere formulieren voor, zoals de DPIA; die komen erbij
 * - een antwoord dat in meer formulieren staat, geldt in het hele dossier
 * - wijzigt zo'n antwoord nadat een document gemaakt is, dan is dat document verouderd
 * - per formulier zie je hoe ver het is, en wat er nog uitgezocht moet worden
 *
 * Alle dossiers staan in localStorage, onder één sleutel. Er gaat niets naar
 * de server. Het projectbestand is het dossier als JSON; de voordeur laadt het
 * weer in.
 *
 * Op de voordeur tekent dit script de lijst met dossiers, en met ?dossier=id
 * de pagina van één dossier. Op een formulier gebruikt app.js de functies in
 * window.cisomaticDossier.
 */
(function () {
    'use strict';

    var SLEUTEL = 'cisomatic-dossiers';

    // Keuzes die zeggen: dat weten we nog niet. Die worden open punten.
    var OPEN = ['niet_gekeken', 'onbekend', 'weet_niet', 'twijfel'];

    // Antwoorden die bij één document horen, en dus niet meegaan naar een ander formulier.
    var EIGEN = ['versie', 'versie_toelichting', 'rol', 'beoordelaar_naam', 'opmerkingen'];

    window.cisomaticDossier = {
        OPEN: OPEN,
        schoon: schoon,
        uitAdres: uitAdres,
        lees: lees,
        antwoordenVoor: antwoordenVoor,
        bewaarFormulier: bewaarFormulier,
        bewaarDocument: bewaarDocument,
        adres: adres
    };

    document.addEventListener('DOMContentLoaded', start);

    // ------------------------------------------------------------- hulpjes

    function heeft(lijst, waarde) {
        return (lijst || []).indexOf(waarde) !== -1;
    }

    function gelijk(x, y) {
        return JSON.stringify(x === undefined ? null : x) === JSON.stringify(y === undefined ? null : y);
    }

    function kopie(waarde) {
        return waarde === undefined ? undefined : JSON.parse(JSON.stringify(waarde));
    }

    function isLeeg(waarde) {
        if (Array.isArray(waarde)) { return !waarde.length; }
        if (waarde && typeof waarde === 'object') { return !Object.keys(waarde).length; }
        return !String(waarde === undefined || waarde === null ? '' : waarde).trim();
    }

    function isObject(waarde) {
        return !!waarde && typeof waarde === 'object' && !Array.isArray(waarde);
    }

    function nu() {
        return new Date().toISOString();
    }

    function datum(iso) {
        var moment = new Date(iso);
        return isNaN(moment.getTime())
            ? ''
            : moment.toLocaleDateString('nl-NL', { day: 'numeric', month: 'long', year: 'numeric' });
    }

    /** Spiegelt cm_slug(): alleen a-z, 0-9 en koppeltekens. */
    function slug(tekst) {
        var uit = String(tekst || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase()
            .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 60).replace(/-+$/, '');
        return uit || 'naamloos';
    }

    function nieuwId() {
        var getallen = new Uint32Array(2);
        window.crypto.getRandomValues(getallen);
        return getallen[0].toString(36) + getallen[1].toString(36);
    }

    /** Een element met attributen en kinderen. Tekst gaat altijd als tekst, nooit als HTML. */
    function el(naam, attributen, kinderen) {
        var element = document.createElement(naam);

        Object.keys(attributen || {}).forEach(function (key) {
            var waarde = attributen[key];
            if (waarde === false || waarde === null || waarde === undefined) { return; }
            if (key === 'tekst') { element.textContent = waarde; return; }
            element.setAttribute(key, waarde === true ? '' : String(waarde));
        });

        [].concat(kinderen || []).forEach(function (kind) {
            if (kind === null || kind === undefined || kind === false) { return; }
            element.appendChild(typeof kind === 'string' ? document.createTextNode(kind) : kind);
        });

        return element;
    }

    function toon(element, tekst, isFout) {
        if (!element) { return; }
        if (isFout) { element.setAttribute('role', 'alert'); } else { element.removeAttribute('role'); }
        element.textContent = tekst;
        element.hidden = false;
    }

    // ------------------------------------------------------------ opslag

    /** Alle dossiers, op id. Null als de browser niets mag bewaren. */
    function alle() {
        try {
            var ruw = localStorage.getItem(SLEUTEL);
            var dossiers = ruw ? JSON.parse(ruw) : {};
            return isObject(dossiers) ? dossiers : {};
        } catch (e) {
            return null;
        }
    }

    function schrijf(dossiers) {
        try {
            localStorage.setItem(SLEUTEL, JSON.stringify(dossiers));
            return true;
        } catch (e) {
            return false;
        }
    }

    /** Een dossier met alle onderdelen, ook als het uit een ouder of vreemd bestand komt. */
    function schoon(ruw, id) {
        var d = isObject(ruw) ? ruw : {};
        var obj = function (waarde) { return isObject(waarde) ? waarde : {}; };

        return {
            id: String(id || d.id || nieuwId()),
            naam: String(d.naam || ''),
            normenkader: d.normenkader === 'bio' ? 'bio' : 'bio2',
            aangemaakt_op: String(d.aangemaakt_op || nu()),
            bijgewerkt_op: String(d.bijgewerkt_op || nu()),
            formulieren: (Array.isArray(d.formulieren) ? d.formulieren : []).filter(function (f) {
                return typeof f === 'string' && /^[a-z0-9]+$/.test(f);
            }),
            antwoorden: obj(d.antwoorden),
            documenten: obj(d.documenten),
            voortgang: obj(d.voortgang),
            open: obj(d.open),
            voorstellen: obj(d.voorstellen),
            afgewezen: obj(d.afgewezen),
            regels: obj(d.regels),
            namen: obj(d.namen)
        };
    }

    function lees(id) {
        var dossiers = alle();
        return dossiers && dossiers[id] ? schoon(dossiers[id], id) : null;
    }

    function bewaar(dossier) {
        var dossiers = alle();
        if (!dossiers) { return false; }

        dossier.bijgewerkt_op = nu();
        dossiers[dossier.id] = dossier;
        return schrijf(dossiers);
    }

    function verwijder(id) {
        var dossiers = alle();
        if (!dossiers) { return; }

        delete dossiers[id];
        schrijf(dossiers);
    }

    /** Het id van het dossier in het adres van deze pagina. */
    function uitAdres() {
        var id = new URLSearchParams(window.location.search).get('dossier');
        return id && /^[a-z0-9]{1,40}$/.test(id) ? id : null;
    }

    /** Een adres met het dossier erachter, en eventueel een vraag om naartoe te springen. */
    function adres(basis, id, vraag) {
        return basis + (basis.indexOf('?') === -1 ? '?' : '&') + 'dossier=' + encodeURIComponent(id)
            + (vraag ? '#vraag-' + encodeURIComponent(vraag) : '');
    }

    // ------------------------------------------------- antwoorden en regels

    /**
     * Wat een formulier uit het dossier kan overnemen. Dezelfde sleutel in twee
     * formulieren is hetzelfde antwoord, net als bij het inladen van een bestand.
     * De regels van 'overnemen' gelden ook hier: wat het formulier overslaat,
     * wat het onder een andere sleutel zet, en wat het vast invult.
     *
     * @return {{antwoorden: Object, bronnen: Array}}
     */
    function antwoordenVoor(dossier, eigen, overnemen) {
        var uit = {};
        var bronnen = [];

        if (dossier.naam) { uit.projectnaam = dossier.naam; }

        volgorde(dossier).forEach(function (bron) {
            var a = dossier.antwoorden[bron];
            if (bron === eigen || !isObject(a)) { return; }

            var regel = (overnemen || {})[bron] || {};
            var gebruikt = false;

            Object.keys(a).forEach(function (key) {
                if (heeft(EIGEN, key) || heeft(regel.overslaan, key) || isLeeg(a[key])) { return; }
                if (!(key in uit) || isLeeg(uit[key])) {
                    uit[key] = kopie(a[key]);
                    gebruikt = true;
                }
            });

            Object.keys(regel.als || {}).forEach(function (van) {
                var naar = regel.als[van];
                if (!isLeeg(a[van]) && isLeeg(uit[naar])) {
                    uit[naar] = kopie(a[van]);
                    gebruikt = true;
                }
            });

            Object.keys(regel.zet || {}).forEach(function (key) {
                uit[key] = kopie(regel.zet[key]);
            });

            if (gebruikt) { bronnen.push(regel.naam || ('de ' + (dossier.namen[bron] || bron))); }
        });

        return { antwoorden: uit, bronnen: bronnen };
    }

    /** De formulieren met antwoorden: eerst die van het dossier, in volgorde, dan de rest. */
    function volgorde(dossier) {
        var rest = Object.keys(dossier.antwoorden).filter(function (f) { return !heeft(dossier.formulieren, f); });
        return dossier.formulieren.concat(rest);
    }

    /**
     * Bewaart de antwoorden van een formulier in het dossier. Een gewijzigd
     * antwoord gaat naar elk ander formulier met dezelfde sleutel. Had dat
     * formulier al een document, dan is dat nu verouderd. Een wijziging in het
     * formulier zelf maakt zijn eigen document ook verouderd.
     */
    function bewaarFormulier(id, eigen, antwoorden, extra) {
        var dossier = lees(id);
        if (!dossier) { return null; }

        var oud = dossier.antwoorden[eigen];
        var gewijzigd = isObject(oud)
            ? Object.keys(antwoorden).filter(function (key) { return !gelijk(oud[key], antwoorden[key]); })
            : [];

        dossier.antwoorden[eigen] = antwoorden;
        dossier.voortgang[eigen] = extra.voortgang || null;
        dossier.open[eigen] = extra.open || [];
        if (extra.overnemen) { dossier.regels[eigen] = extra.overnemen; }
        if (extra.naam) { dossier.namen[eigen] = extra.naam; }
        if (!heeft(dossier.formulieren, eigen)) { dossier.formulieren.push(eigen); }
        if (String(antwoorden.projectnaam || '').trim()) { dossier.naam = String(antwoorden.projectnaam).trim(); }

        if (gewijzigd.length && dossier.documenten[eigen]) { dossier.documenten[eigen].verouderd = true; }

        gewijzigd.forEach(function (key) {
            if (heeft(EIGEN, key)) { return; }

            Object.keys(dossier.antwoorden).forEach(function (ander) {
                var hun = dossier.antwoorden[ander];
                if (ander === eigen || !isObject(hun) || !(key in hun)) { return; }
                if (heeft(((dossier.regels[ander] || {})[eigen] || {}).overslaan, key)) { return; }
                if (heeft(((dossier.regels[eigen] || {})[ander] || {}).overslaan, key)) { return; }
                if (gelijk(hun[key], antwoorden[key])) { return; }

                hun[key] = kopie(antwoorden[key]);
                if (dossier.documenten[ander]) { dossier.documenten[ander].verouderd = true; }
            });
        });

        bewaar(dossier);
        return dossier;
    }

    /**
     * Legt vast dat een formulier een document heeft, uit het antwoordbestand
     * op de resultaatpagina. Een quickscan geeft ook mee welke formulieren hij
     * voorstelt; die komen erbij, tenzij iemand ze eerder uit het dossier haalde.
     */
    function bewaarDocument(id, data) {
        var dossier = lees(id);
        if (!dossier || !isObject(data) || typeof data.soort !== 'string') { return null; }

        neemDocumentOver(dossier, data);
        bewaar(dossier);
        return dossier;
    }

    function neemDocumentOver(dossier, data) {
        var soort = data.soort;

        if (!isObject(dossier.antwoorden[soort]) && isObject(data.antwoorden)) {
            dossier.antwoorden[soort] = data.antwoorden;
        }
        if (!heeft(dossier.formulieren, soort)) { dossier.formulieren.push(soort); }
        delete dossier.afgewezen[soort];

        dossier.documenten[soort] = {
            kenmerk: String(data.kenmerk || ''),
            gemaakt_op: String(data.opgeslagen_op || nu()),
            verouderd: false
        };

        if (dossier.voortgang[soort]) { dossier.voortgang[soort].ingevuld = dossier.voortgang[soort].verplicht; }
        if (!dossier.naam && data.projectnaam) { dossier.naam = String(data.projectnaam); }

        neemVoorstellenOver(dossier, soort, data.vervolgformulieren);
    }

    function neemVoorstellenOver(dossier, bron, vervolg) {
        if (!isObject(vervolg)) { return; }

        Object.keys(vervolg).forEach(function (f) {
            var voorstel = vervolg[f];
            if (!isObject(voorstel)) { return; }

            dossier.voorstellen[f] = {
                voorgesteld: !!voorstel.voorgesteld,
                reden: String(voorstel.reden || ''),
                door: bron
            };

            if (voorstel.voorgesteld && !heeft(dossier.formulieren, f) && !dossier.afgewezen[f]) {
                dossier.formulieren.push(f);
            }
        });
    }

    // ------------------------------------------------------------ voordeur

    var meta = {};          // per formulier: titel, naam, fase, normenkader, dossier, opvolger, href
    var volgordeMeta = [];  // de ids in de volgorde van de voordeur
    var kaders = {};
    var artikel = null;

    function start() {
        artikel = document.getElementById('cisomatic');
        if (!artikel || !artikel.hasAttribute('data-kiezer')) { return; }

        // Zonder opslag in de browser kan er geen dossier bestaan.
        if (alle() === null) { return; }

        var gegevens = JSON.parse(document.getElementById('cisomatic-formulieren').textContent);
        gegevens.formulieren.forEach(function (f) {
            meta[f.id] = f;
            volgordeMeta.push(f.id);
        });
        kaders = gegevens.normenkaders;

        regelNieuw();
        regelInladen();

        window.addEventListener('storage', function (e) {
            if (e.key === SLEUTEL) { teken(); }
        });

        teken();
    }

    function voordeurAdres() {
        return window.location.pathname;
    }

    /** De lijst, of één dossier als het adres dat noemt. */
    function teken() {
        var id = uitAdres();
        var dossier = id ? lees(id) : null;
        var lijst = artikel.querySelector('[data-dossiers]');
        var pagina = artikel.querySelector('[data-dossier]');

        artikel.toggleAttribute('data-in-dossier', !!dossier);
        lijst.hidden = !!dossier;
        pagina.hidden = !dossier;

        if (dossier) {
            tekenDossier(pagina, dossier);
            return;
        }

        tekenLijst(lijst.querySelector('[data-dossier-lijst]'));

        if (id) {
            toon(lijst.querySelector('[data-dossier-melding]'), 'Dat dossier staat niet in deze browser. Laad het '
                + 'projectbestand in, dan staat het er weer.', true);
        }
    }

    function tekenLijst(ul) {
        var dossiers = alle() || {};
        var ids = Object.keys(dossiers).sort(function (x, y) {
            return String(dossiers[y].bijgewerkt_op).localeCompare(String(dossiers[x].bijgewerkt_op));
        });

        ul.textContent = '';
        ul.hidden = !ids.length;

        ids.forEach(function (id) {
            var dossier = schoon(dossiers[id], id);
            var stand = standVan(dossier);

            ul.appendChild(el('li', {}, [
                el('a', { href: adres(voordeurAdres(), id), tekst: dossier.naam || 'Naamloos project' }),
                el('span', {
                    tekst: stand.klaar + ' van ' + stand.totaal + ' formulieren klaar. Bijgewerkt op '
                        + datum(dossier.bijgewerkt_op) + '.'
                })
            ]));
        });
    }

    function regelNieuw() {
        var form = artikel.querySelector('[data-dossier-nieuw]');

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var veld = form.elements.dossier_naam;
            var fout = form.querySelector('[data-fout]');
            var naam = veld.value.trim();

            if (!naam) {
                veld.setAttribute('aria-invalid', 'true');
                toon(fout, 'Geef het project een naam.', false);
                veld.focus();
                return;
            }

            veld.removeAttribute('aria-invalid');
            fout.hidden = true;

            var dossier = schoon({ naam: naam, normenkader: form.elements.dossier_normenkader.value });
            dossier.formulieren = beginformulieren(dossier.normenkader);
            dossier.antwoorden = {};

            if (bewaar(dossier)) {
                window.location.href = adres(voordeurAdres(), dossier.id);
            } else {
                toon(fout, 'Je browser bewaart niets, dus een dossier kan hier niet.', true);
            }
        });
    }

    /** Waar een dossier mee begint: de start, en de quickscan van het normenkader. */
    function beginformulieren(kader) {
        return volgordeMeta.filter(function (f) {
            var m = meta[f];
            return m.dossier !== false && (m.fase === 'start' || (m.normenkader === kader && m.fase === 'ontwerp'));
        });
    }

    /** Past dit formulier in een dossier met dit normenkader? */
    function pastIn(f, kader) {
        var m = meta[f];
        return !!m && m.dossier !== false && (!m.normenkader || m.normenkader === kader);
    }

    /** De formulieren van het dossier, in de volgorde van de voordeur. */
    function formulierenVan(dossier) {
        return volgordeMeta.filter(function (f) {
            return heeft(dossier.formulieren, f) && pastIn(f, dossier.normenkader);
        });
    }

    // ------------------------------------------------------------- inladen

    function leesBestand(invoer, klaar) {
        var bestand = invoer.files && invoer.files[0];
        if (!bestand) { return; }

        var lezer = new FileReader();
        lezer.onload = function () {
            var data = null;
            try { data = JSON.parse(lezer.result); } catch (e) { /* hieronder gemeld */ }
            invoer.value = '';
            klaar(isObject(data) ? data : null, bestand.name);
        };
        lezer.readAsText(bestand);
    }

    function regelInladen() {
        var invoer = artikel.querySelector('[data-dossier-inladen]');
        var melding = artikel.querySelector('[data-dossier-melding]');

        invoer.addEventListener('change', function () {
            leesBestand(invoer, function (data) {
                if (!data) {
                    toon(melding, 'Dit bestand is geen projectbestand of antwoordbestand.', true);
                    return;
                }

                var dossier = data.soort === 'dossier' ? uitProjectbestand(data, melding) : uitAntwoordbestand(data, melding);
                if (!dossier) { return; }

                if (bewaar(dossier)) {
                    window.location.href = adres(voordeurAdres(), dossier.id);
                } else {
                    toon(melding, 'Je browser bewaart niets, dus een dossier kan hier niet.', true);
                }
            });
        });
    }

    function uitProjectbestand(data, melding) {
        var dossier = schoon(data);
        var bestaand = lees(dossier.id);

        if (bestaand && !window.confirm('Het dossier ‘' + (bestaand.naam || 'Naamloos project') + '’ staat al in '
            + 'deze browser. Vervangen door het projectbestand?')) {
            toon(melding, 'Niets ingeladen. Het dossier in deze browser is niet veranderd.', false);
            return null;
        }

        return dossier;
    }

    /** Een antwoordbestand van één formulier: een nieuw dossier met dat formulier erin. */
    function uitAntwoordbestand(data, melding) {
        var soort = typeof data.soort === 'string' ? data.soort : 'quickscan';
        var m = meta[soort];

        if (!isObject(data.antwoorden) || !m) {
            toon(melding, 'Dit bestand bevat geen antwoorden van een formulier van deze tool.', true);
            return null;
        }
        if (m.dossier === false) {
            toon(melding, 'De ' + m.naam + ' hoort niet in een dossier. Laad dit bestand in op het formulier zelf.', true);
            return null;
        }

        var dossier = schoon({
            naam: data.projectnaam || data.antwoorden.projectnaam || '',
            normenkader: m.normenkader || 'bio2'
        });
        dossier.formulieren = beginformulieren(dossier.normenkader);
        dossier.namen[soort] = m.naam;
        neemDocumentOver(dossier, data);

        return dossier;
    }

    // ------------------------------------------------------------- het dossier

    /** Hoe ver een formulier is. */
    function statusVan(dossier, f) {
        var doc = dossier.documenten[f];
        var voorstel = dossier.voorstellen[f];
        var voortgang = dossier.voortgang[f];

        if (doc) {
            return doc.verouderd
                ? { code: 'verouderd', label: 'Verouderd', actie: 'Maak het document opnieuw',
                    uitleg: 'Een antwoord is gewijzigd nadat het document gemaakt is.' }
                : { code: 'klaar', label: 'Klaar', actie: 'Bekijk de antwoorden',
                    uitleg: 'Document gemaakt op ' + datum(doc.gemaakt_op)
                        + (doc.kenmerk ? ', met kenmerk ' + doc.kenmerk : '') + '.' };
        }

        if (isObject(dossier.antwoorden[f])) {
            var rest = voortgang ? voortgang.verplicht - voortgang.ingevuld : 0;
            return { code: 'bezig', label: 'Bezig', actie: 'Verder invullen',
                uitleg: voortgang
                    ? voortgang.ingevuld + ' van ' + voortgang.verplicht + ' vragen ingevuld.'
                        + (rest > 0 ? '' : ' Je kunt het document maken.')
                    : '' };
        }

        if (voorstel && !voorstel.voorgesteld) {
            return { code: 'niet-nodig', label: 'Niet nodig', actie: 'Toch invullen', uitleg: '' };
        }

        return { code: 'nieuw', label: 'Niet begonnen', actie: 'Begin', uitleg: '' };
    }

    /** Hoeveel formulieren klaar zijn, en hoeveel open punten er zijn. */
    function standVan(dossier) {
        var lijst = formulierenVan(dossier);
        var telt = lijst.filter(function (f) { return statusVan(dossier, f).code !== 'niet-nodig'; });

        return {
            klaar: telt.filter(function (f) { return statusVan(dossier, f).code === 'klaar'; }).length,
            totaal: telt.length,
            open: lijst.reduce(function (som, f) { return som + (dossier.open[f] || []).length; }, 0)
        };
    }

    function titelVan(dossier, f) {
        return meta[f] ? meta[f].titel : (dossier.namen[f] || f);
    }

    function tekenDossier(pagina, dossier) {
        var stand = standVan(dossier);
        var lijst = formulierenVan(dossier);

        pagina.textContent = '';

        pagina.appendChild(el('p', {}, [el('a', { href: voordeurAdres(), tekst: '← Alle dossiers en formulieren' })]));
        pagina.appendChild(el('h2', { tekst: dossier.naam || 'Naamloos project' }));
        pagina.appendChild(el('p', {
            class: 'dossier-meta',
            tekst: 'Begonnen op ' + datum(dossier.aangemaakt_op) + '. Bijgewerkt op ' + datum(dossier.bijgewerkt_op)
                + '. Dit dossier staat alleen in deze browser.'
        }));

        pagina.appendChild(el('div', { class: 'dossier-stand' }, [
            el('progress', { max: Math.max(1, stand.totaal), value: stand.klaar, 'aria-labelledby': 'dossier-stand' }),
            el('p', { id: 'dossier-stand', tekst: standZin(stand) })
        ]));

        pagina.appendChild(tekenNormenkader(dossier));
        pagina.appendChild(el('h3', { tekst: 'Formulieren' }));

        var ol = el('ol', { class: 'dossier-formulieren' });
        lijst.forEach(function (f) { ol.appendChild(tekenFormulier(dossier, f)); });
        pagina.appendChild(ol);

        pagina.appendChild(tekenToevoegen(dossier, lijst));
        pagina.appendChild(tekenProjectbestand(dossier));
    }

    function standZin(stand) {
        var delen = [stand.klaar + ' van ' + stand.totaal + ' formulieren klaar.'];

        if (stand.open) {
            delen.push(stand.open === 1 ? 'Eén punt moet nog worden uitgezocht.'
                : stand.open + ' punten moeten nog worden uitgezocht.');
        } else if (stand.klaar === stand.totaal && stand.totaal) {
            delen.push('Er staat niets meer open.');
        }

        return delen.join(' ');
    }

    function tekenNormenkader(dossier) {
        var veldset = el('fieldset', { class: 'dossier-kader' }, [
            el('legend', { tekst: 'Normenkader' }),
            el('p', { tekst: 'Bepaalt welke quickscan in het dossier staat. Antwoorden van de andere blijven bewaard.' })
        ]);

        Object.keys(kaders).forEach(function (kader) {
            var invoer = el('input', {
                type: 'radio', name: 'dossier_normenkader', id: 'kader-' + kader, value: kader,
                checked: dossier.normenkader === kader
            });

            invoer.addEventListener('change', function () {
                var vers = lees(dossier.id);
                wisselKader(vers, kader);
                bewaar(vers);
                teken();
            });

            veldset.appendChild(el('label', { for: 'kader-' + kader }, [invoer, el('span', { tekst: kaders[kader] })]));
        });

        return veldset;
    }

    /** Zet elk formulier met een ander normenkader om naar zijn tegenhanger, zoals quickscan 2.0 en 1. */
    function wisselKader(dossier, kader) {
        dossier.normenkader = kader;
        dossier.formulieren = dossier.formulieren.map(function (f) {
            var m = meta[f];
            if (!m || !m.normenkader || m.normenkader === kader) { return f; }

            var tegenhanger = m.opvolger || volgordeMeta.filter(function (ander) {
                return meta[ander].opvolger === f;
            })[0];

            return tegenhanger && meta[tegenhanger] && meta[tegenhanger].normenkader === kader ? tegenhanger : f;
        });
    }

    function tekenFormulier(dossier, f) {
        var status = statusVan(dossier, f);
        var voorstel = dossier.voorstellen[f];
        var open = dossier.open[f] || [];
        var href = meta[f] ? adres(meta[f].href, dossier.id) : '';

        var li = el('li', { 'data-status': status.code }, [
            el('h4', {}, [
                el('a', { href: href, tekst: titelVan(dossier, f) }),
                ' ',
                el('output', { tekst: status.label })
            ]),
            status.uitleg ? el('p', { tekst: status.uitleg }) : null,
            voorstel ? el('p', { class: 'dossier-voorstel' }, [
                el('strong', { tekst: voorstel.voorgesteld
                    ? 'Voorgesteld door de ' + (dossier.namen[voorstel.door] || 'quickscan') + '.'
                    : 'Volgens de ' + (dossier.namen[voorstel.door] || 'quickscan') + ' niet nodig.' }),
                ' ' + voorstel.reden
            ]) : null
        ]);

        if (open.length) {
            var ul = el('ul');
            open.forEach(function (punt) {
                ul.appendChild(el('li', {}, [
                    el('a', { href: meta[f] ? adres(meta[f].href, dossier.id, punt.key) : '', tekst: punt.vraag }),
                    ' ',
                    el('span', { tekst: punt.antwoord })
                ]));
            });

            li.appendChild(el('details', { class: 'dossier-open', open: open.length <= 5 }, [
                el('summary', { tekst: open.length === 1 ? 'Eén punt nog uitzoeken' : open.length + ' punten nog uitzoeken' }),
                ul
            ]));
        }

        var weg = el('button', { type: 'button', tekst: 'Haal uit het dossier' });
        weg.addEventListener('click', function () {
            var vers = lees(dossier.id);
            vers.formulieren = vers.formulieren.filter(function (ander) { return ander !== f; });
            vers.afgewezen[f] = true;
            bewaar(vers);
            teken();
            toast(titelVan(dossier, f) + ' is uit het dossier. De antwoorden blijven bewaard.');
        });

        li.appendChild(el('p', {}, [el('a', { href: href, class: 'knop', tekst: status.actie }), weg]));

        return li;
    }

    function tekenToevoegen(dossier, lijst) {
        var opties = volgordeMeta.filter(function (f) {
            return pastIn(f, dossier.normenkader) && !heeft(lijst, f);
        });

        if (!opties.length) { return el('p', { hidden: true }); }

        var keuze = el('select', { id: 'dossier-erbij' }, opties.map(function (f) {
            return el('option', { value: f, tekst: meta[f].titel });
        }));
        var knop = el('button', { type: 'button', tekst: 'Zet in het dossier' });

        knop.addEventListener('click', function () {
            var vers = lees(dossier.id);
            if (!heeft(vers.formulieren, keuze.value)) { vers.formulieren.push(keuze.value); }
            delete vers.afgewezen[keuze.value];
            bewaar(vers);
            teken();
        });

        return el('div', { class: 'dossier-erbij' }, [
            el('label', { for: 'dossier-erbij', tekst: 'Een ander formulier in het dossier zetten' }),
            el('p', {}, [keuze, knop])
        ]);
    }

    function tekenProjectbestand(dossier) {
        var melding = el('p', { 'data-dossier-melding': true, hidden: true });

        var download = el('button', { type: 'button', tekst: 'Download het projectbestand' });
        download.addEventListener('click', function () {
            var vers = lees(dossier.id);
            var data = Object.assign({ soort: 'dossier', formaat: 1, opgeslagen_op: nu() }, vers);
            var link = el('a', {
                href: URL.createObjectURL(new Blob([JSON.stringify(data, null, 2)], { type: 'application/json;charset=utf-8' })),
                download: new Date().toISOString().slice(0, 10) + '_dossier_' + slug(vers.naam) + '.json'
            });
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        });

        var invoer = el('input', { type: 'file', id: 'dossier-antwoordbestand', accept: 'application/json,.json' });
        invoer.addEventListener('change', function () {
            leesBestand(invoer, function (data) {
                voegAntwoordbestandToe(dossier.id, data, melding);
            });
        });

        var wis = el('button', { type: 'button', class: 'gevaar', tekst: 'Verwijder dit dossier uit deze browser' });
        wis.addEventListener('click', function () {
            if (!window.confirm('Dossier ‘' + (dossier.naam || 'Naamloos project') + '’ verwijderen uit deze browser? '
                + 'Download eerst het projectbestand als je het wilt bewaren.')) { return; }
            verwijder(dossier.id);
            window.location.href = voordeurAdres();
        });

        return el('section', { class: 'dossier-bestand' }, [
            el('h3', { tekst: 'Projectbestand' }),
            el('p', { tekst: 'Het projectbestand is het hele dossier in één bestand: alle antwoorden, en welke '
                + 'documenten er zijn. Bewaar het, of geef het door. Op de voordeur laad je het weer in.' }),
            el('p', {}, [download]),
            el('label', { for: 'dossier-antwoordbestand', tekst: 'Het antwoordbestand van één formulier toevoegen' }),
            el('p', { class: 'hint', tekst: 'Bijvoorbeeld een DPIA die iemand anders al maakte. Je browser leest het '
                + 'bestand zelf, dus er gaat niets naar de server.' }),
            invoer,
            melding,
            el('p', {}, [wis])
        ]);
    }

    function voegAntwoordbestandToe(id, data, melding) {
        var dossier = lees(id);
        var soort = data && (typeof data.soort === 'string' ? data.soort : 'quickscan');

        if (!data || !dossier) {
            toon(melding, 'Dit bestand is geen antwoordbestand.', true);
            return;
        }
        if (soort === 'dossier') {
            toon(melding, 'Dit is een projectbestand. Laad het in op de voordeur.', true);
            return;
        }
        if (!isObject(data.antwoorden) || !meta[soort]) {
            toon(melding, 'Dit bestand bevat geen antwoorden van een formulier van deze tool.', true);
            return;
        }
        if (!pastIn(soort, dossier.normenkader)) {
            toon(melding, meta[soort].dossier === false
                ? 'De ' + meta[soort].naam + ' hoort niet in een dossier.'
                : 'Dit formulier hoort bij een ander normenkader. Kies dat eerst.', true);
            return;
        }
        if (isObject(dossier.antwoorden[soort]) && !window.confirm('Het dossier heeft al antwoorden voor de '
            + meta[soort].naam + '. Vervangen door dit bestand?')) {
            return;
        }

        dossier.antwoorden[soort] = data.antwoorden;
        delete dossier.voortgang[soort];
        dossier.open[soort] = [];
        dossier.namen[soort] = meta[soort].naam;
        neemDocumentOver(dossier, data);
        bewaar(dossier);
        teken();
        toast(titelVan(dossier, soort) + ' staat in het dossier.');
    }

    function toast(tekst) {
        var melding = artikel.querySelector('[data-toast]');
        if (!melding) { return; }

        melding.textContent = tekst;
        melding.hidden = false;
        melding.classList.add('toast-zichtbaar');
        setTimeout(function () {
            melding.classList.remove('toast-zichtbaar');
            setTimeout(function () { melding.hidden = true; }, 300);
        }, 5000);
    }
}());

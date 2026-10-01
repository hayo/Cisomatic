/**
 * De motor achter elk formulier van de cisomatic.
 *
 * - vragen die niet van toepassing zijn, verdwijnen
 * - het formulier gaat stap voor stap, en controleert elke stap
 * - waar de tool een waarde voorstelt, staat die alvast ingevuld
 * - tekstvakken met een voorstel vult de tool zolang niemand ze aanraakt
 * - een groep, zoals een risico, staat op één regel die openklapt
 * - een lijst met rijen krijgt knoppen om rijen toe te voegen en weg te halen
 * - antwoorden blijven tussentijds bewaard, en een bestand laadt ze terug
 *
 * Wat een formulier zelf uitrekent, staat in regels.js in de map van dat
 * formulier. Dat bestand meldt zich aan met cisomatic.regels({...}). De
 * motor start pas als alle scripts geladen zijn, dus de volgorde telt niet.
 */
(function () {
    'use strict';

    var LABEL = { zl: 'Zeer laag', l: 'Laag', m: 'Midden', h: 'Hoog', zh: 'Zeer hoog' };
    var SCHAAL = ['zl', 'l', 'm', 'h', 'zh'];

    // Deze staan bewust hier: ververs() draait al tijdens start(), en met
    // var-hoisting zouden ze dan nog undefined zijn.
    var regels = {};
    var opslagsleutel = '';
    var stapModus = true;
    var huidigeStap = 0;
    var aangeraakt = {};
    var bewaarTimer = null;
    var toastTimer = null;

    window.cisomatic = {
        LABEL: LABEL,
        regels: function (r) { regels = r; },
        elk: elk,
        heeft: heeft,
        rang: rang,
        tekstregels: tekstregels,
        opsomming: opsomming,
        optieLabel: optieLabel,
        toast: toast
    };

    document.addEventListener('DOMContentLoaded', start);

    function start() {
        var artikel = document.getElementById('cisomatic');
        if (!artikel) { return; }

        opslagsleutel = artikel.dataset.formulier + '-concept';
        var formulier = artikel.querySelector(':scope > form');

        if (formulier) {
            regelRijen(formulier);
            regelStappen(formulier);
            regelInvoer(formulier);
            regelInladen(formulier);
            regelEerderBegonnen(formulier);
        }

        if (artikel.hasAttribute('data-klaar')) {
            wisLokaal();
        }

        regelDocumentknoppen();
        regelFoutsamenvatting();
    }

    // ------------------------------------------------------------- hulpjes

    function elk(lijst, functie) {
        Array.prototype.forEach.call(lijst, functie);
    }

    function rang(niveau) {
        return SCHAAL.indexOf(niveau) + 1;
    }

    function heeft(lijst, waarde) {
        return (lijst || []).indexOf(waarde) !== -1;
    }

    /** De niet-lege regels uit een tekstvak. */
    function tekstregels(tekst) {
        return String(tekst || '').split('\n').map(function (r) { return r.trim(); }).filter(Boolean);
    }

    function opsomming(delen) {
        return delen.length < 2 ? (delen[0] || '') : delen.slice(0, -1).join(', ') + ' en ' + delen[delen.length - 1];
    }

    /** Het label van een keuze, zoals het formulier het toont. */
    function optieLabel(key, waarde) {
        // Een keuzelijst heeft option-elementen in plaats van inputs met een naam.
        var optie = document.querySelector('select[name="' + key + '"] option[value="' + waarde + '"]');
        if (optie) { return optie.textContent.trim(); }

        var input = document.querySelector('[name="' + key + '"][value="' + waarde + '"]')
            || document.querySelector('[name="' + key + '[]"][value="' + waarde + '"]');
        var tekst = input && input.closest('label').querySelector('span');

        // Alleen het eerste stuk tekst: de markering 'voorgesteld' hoort er niet bij.
        return tekst ? tekst.firstChild.textContent.trim() : waarde;
    }

    function roep(naam) {
        var args = Array.prototype.slice.call(arguments, 1);
        return typeof regels[naam] === 'function' ? regels[naam].apply(null, args) : '';
    }

    // ------------------------------------------------------------ formulier

    function regelInvoer(form) {
        // 'input' vuurt bij een keuze voor 'change'. Zouden we pas bij 'change'
        // onthouden dat iemand zelf iets koos, dan zet de eerste ronde het
        // voorstel er alweer overheen.
        function opInvoer(e) {
            if (e.target && e.target.name) {
                aangeraakt[e.target.name.replace(/\[.*$/, '')] = true;
            }
            ververs(form, false);
        }

        form.addEventListener('input', opInvoer);
        form.addEventListener('change', opInvoer);

        form.addEventListener('submit', function (e) {
            // Bewaren mag halverwege; alleen het document vraagt om alles.
            if (e.submitter && e.submitter.name === 'bewaar') { return; }
            var mis = valideerAlles(form);
            if (mis) {
                e.preventDefault();
                springNaarFout(form, mis);
            }
        });

        ververs(form, true);
    }

    /**
     * Werkt alles bij. Met opnieuw mag het formulier ook zijn eigen onderdelen
     * opnieuw indelen, zoals welke risicoregels open staan.
     */
    function ververs(form, opnieuw) {
        var a = regelZichtbaarheid(form);

        vulVoorstellen(form, a);
        a = regelZichtbaarheid(form);

        toonAfgeleiden(form, a);
        roep('ververs', form, a, opnieuw);
        hernummerSecties(form);
        werkStappenBij(form);
        bewaarStraks(form);
    }

    /**
     * Leest het formulier zoals de server het zou lezen: wat verborgen is, telt
     * niet mee, en een rij zonder tekst ook niet. Voor het tussentijds bewaren
     * telt alles wel mee, zodat heen en weer klikken niets wist.
     */
    function leesAntwoorden(form, ookVerborgen) {
        var a = {};
        var gevuld = {};

        elk(form.elements, function (el) {
            if (!el.name || (!ookVerborgen && el.closest('[hidden]'))) { return; }

            var rij = /^(\w+)\[(\d+)\]\[(\w+)\]$/.exec(el.name);
            if (rij) {
                var rijen = a[rij[1]] = a[rij[1]] || [];
                rijen[rij[2]] = rijen[rij[2]] || {};
                rijen[rij[2]][rij[3]] = el.value;
                if (el.tagName !== 'SELECT' && el.value.trim()) { gevuld[rij[1] + rij[2]] = true; }
                return;
            }

            // Een vraag van het type maatregelen: per maatregel één keuze.
            var keuze = /^(\w+)\[(\w+)\]$/.exec(el.name);
            if (keuze) {
                a[keuze[1]] = a[keuze[1]] || {};
                if (el.checked) { a[keuze[1]][keuze[2]] = el.value; }
                return;
            }

            var naam = el.name.replace(/\[\]$/, '');

            if (el.type === 'checkbox') {
                a[naam] = a[naam] || [];
                if (el.checked) { a[naam].push(el.value); }
            } else if (el.type === 'radio') {
                if (el.checked) { a[naam] = el.value; } else if (!(naam in a)) { a[naam] = ''; }
            } else {
                a[naam] = el.value;
            }
        });

        elk(form.querySelectorAll('[data-type="rijen"]'), function (blok) {
            var key = blok.dataset.vraag;
            a[key] = (a[key] || []).filter(function (rij, i) { return gevuld[key + i]; });
        });

        return a;
    }

    /**
     * Zet een waarde in een veld, maar alleen als die waarde er ook kan staan.
     * Een onbekende waarde uit een bestand wist dus geen bestaand antwoord.
     */
    function zetWaarde(form, key, waarde) {
        if (waarde && typeof waarde === 'object' && !Array.isArray(waarde)) {
            var maatregelen = form.querySelector('[data-type="maatregelen"][data-vraag="' + key + '"]');
            if (!maatregelen) { return false; }
            elk(maatregelen.querySelectorAll('input'), function (keuze) {
                keuze.checked = waarde[/\[(\w+)\]$/.exec(keuze.name)[1]] === keuze.value;
            });
            return true;
        }

        var veld = form.elements[key] || form.elements[key + '[]'];
        if (!veld) { return false; }

        var velden = veld.tagName ? [veld] : Array.prototype.slice.call(veld);
        var eerste = velden[0];

        if (eerste.type === 'checkbox' || eerste.type === 'radio') {
            var waarden = [].concat(waarde).map(String);
            var past = velden.some(function (v) { return heeft(waarden, v.value); });
            if (!past && waarden.length) { return false; }
            velden.forEach(function (v) { v.checked = heeft(waarden, v.value); });
            return true;
        }

        if (typeof waarde !== 'string') { return false; }
        if (eerste.tagName === 'SELECT' && !Array.prototype.some.call(eerste.options, function (o) {
            return o.value === waarde;
        })) { return false; }

        eerste.value = waarde;
        return true;
    }

    /**
     * Vult voorstellen en teksten in, op volgorde van het formulier. Wat eerder
     * staat, is dan al bijgewerkt: een tekst volgt de keuzes die net zijn
     * voorgesteld. Een radiovraag laat ook zien welke keuze de tool voorstelt.
     */
    function vulVoorstellen(form, a) {
        elk(form.querySelectorAll('[data-voorstel], [data-motivatie-voor]'), function (blok) {
            var key = blok.dataset.vraag;
            var waarde = blok.dataset.motivatieVoor
                ? roep('motivatie', blok.dataset.motivatieVoor, a)
                : roep('voorstel', key, a);

            elk(blok.querySelectorAll('label mark'), function (merk) {
                merk.hidden = !waarde || merk.closest('label').querySelector('input').value !== waarde;
            });

            if (aangeraakt[key]) { return; }
            if (zetWaarde(form, key, waarde)) { a[key] = waarde; }
        });
    }

    function matcht(blok, attribuut, waardeVan) {
        if (!blok.dataset[attribuut]) { return true; }

        var voorwaarden = JSON.parse(blok.dataset[attribuut]);

        return Object.keys(voorwaarden).every(function (key) {
            var waarde = waardeVan(key);
            return Array.isArray(waarde)
                ? waarde.some(function (w) { return heeft(voorwaarden[key], w); })
                : heeft(voorwaarden[key], waarde === undefined ? '' : waarde);
        });
    }

    /**
     * Toont en verbergt wat van antwoorden afhangt. Een verborgen antwoord telt
     * niet mee, dus dat kan weer iets anders verbergen: daarom een paar rondes,
     * tot er niets meer verandert.
     */
    function regelZichtbaarheid(form) {
        for (var ronde = 0; ronde < 6; ronde++) {
            var a = leesAntwoorden(form);
            var veranderd = false;

            elk(form.querySelectorAll('[data-toon-als], [data-toon-als-uitkomst]'), function (blok) {
                var verbergen = !matcht(blok, 'toonAls', function (k) { return a[k]; })
                    || !matcht(blok, 'toonAlsUitkomst', function (k) { return roep('uitkomst', k, a); });

                if (blok.hidden !== verbergen) {
                    blok.hidden = verbergen;
                    veranderd = true;
                }
            });

            // Alleen de opties die in de andere vraag zijn aangekruist.
            elk(form.querySelectorAll('[data-opties-uit]'), function (blok) {
                var toegestaan = a[blok.dataset.optiesUit] || [];
                elk(blok.querySelectorAll('input'), function (vakje) {
                    var verbergen = !heeft(toegestaan, vakje.value);
                    if (vakje.closest('label').hidden !== verbergen) {
                        vakje.closest('label').hidden = verbergen;
                        veranderd = true;
                    }
                });
            });

            if (!veranderd) { return a; }
        }

        return leesAntwoorden(form);
    }

    /** Vult de uitkomstblokken met wat regels.js uitrekent: waarde, zwaarte en redenen. */
    function toonAfgeleiden(form, a) {
        elk(form.querySelectorAll('[data-afgeleid] > [data-afgeleid-redenen]'), function (lijst) {
            var blok = lijst.parentNode;
            var uit = roep('afgeleid', blok.dataset.afgeleid, a);
            if (!uit) { return; }

            blok.querySelector('[data-afgeleid-waarde]').textContent = uit.waarde;
            blok.setAttribute('data-niveau', uit.niveau || '');
            lijst.textContent = '';
            uit.redenen.forEach(function (reden) {
                var li = document.createElement('li');
                li.textContent = reden;
                lijst.appendChild(li);
            });
        });
    }

    // --------------------------------------------------------------- rijen

    function regelRijen(form) {
        elk(form.querySelectorAll('[data-type="rijen"]'), function (blok) {
            var knop = blok.querySelector('[data-rij-erbij]');
            knop.hidden = false;

            // Zonder script stonden er lege rijen klaar. Met de knop zijn die niet nodig.
            var rijen = blok.querySelectorAll('[data-rij]');
            for (var i = rijen.length - 1; i > 0 && rijIsLeeg(rijen[i]); i--) {
                rijen[i].remove();
            }

            knop.addEventListener('click', function () {
                voegRijToe(blok).querySelector('input, textarea, select').focus();
                ververs(form, false);
            });

            blok.addEventListener('click', function (e) {
                var weg = e.target.closest('[data-rij-weg]');
                if (!weg) { return; }

                var rij = weg.closest('[data-rij]');
                if (blok.querySelectorAll('[data-rij]').length === 1) {
                    maakRijLeeg(rij);
                } else {
                    rij.remove();
                }

                hernummerRijen(blok);
                knop.focus();
                ververs(form, false);
            });

            hernummerRijen(blok);
        });
    }

    function rijIsLeeg(rij) {
        return !Array.prototype.some.call(rij.querySelectorAll('input, textarea'), function (v) {
            return v.value.trim() !== '';
        });
    }

    function maakRijLeeg(rij) {
        elk(rij.querySelectorAll('input, textarea'), function (v) { v.value = ''; v.removeAttribute('aria-invalid'); });
        elk(rij.querySelectorAll('select'), function (s) { s.selectedIndex = 0; s.removeAttribute('aria-invalid'); });
    }

    function voegRijToe(blok) {
        var alle = blok.querySelectorAll('[data-rij]');
        var nieuw = alle[alle.length - 1].cloneNode(true);

        maakRijLeeg(nieuw);
        alle[alle.length - 1].after(nieuw);
        hernummerRijen(blok);

        return nieuw;
    }

    function hernummerRijen(blok) {
        var key = blok.dataset.vraag;

        elk(blok.querySelectorAll('[data-rij]'), function (rij, i) {
            var legenda = rij.querySelector('legend');
            legenda.textContent = legenda.textContent.replace(/\d+$/, String(i + 1));
            rij.querySelector('[data-rij-weg]').hidden = false;

            elk(rij.querySelectorAll('[name]'), function (veld) {
                var kolom = /\[(\w+)\]$/.exec(veld.name)[1];
                veld.name = key + '[' + i + '][' + kolom + ']';
                veld.id = key + '-' + i + '-' + kolom;
                veld.closest('label').htmlFor = veld.id;
            });
        });
    }

    /** Precies zoveel rijen als er in het bestand staan, en minstens één. */
    function vulRijen(form, key, rijen) {
        var blok = form.querySelector('[data-type="rijen"][data-vraag="' + key + '"]');
        if (!blok) { return false; }

        while (blok.querySelectorAll('[data-rij]').length < Math.max(1, rijen.length)) { voegRijToe(blok); }

        var alle = blok.querySelectorAll('[data-rij]');
        for (var i = alle.length - 1; i >= Math.max(1, rijen.length); i--) { alle[i].remove(); }
        hernummerRijen(blok);

        elk(blok.querySelectorAll('[data-rij]'), function (rij, index) {
            maakRijLeeg(rij);
            Object.keys(rijen[index] || {}).forEach(function (kolom) {
                zetWaarde(form, key + '[' + index + '][' + kolom + ']', String(rijen[index][kolom]));
            });
        });

        return true;
    }

    // ------------------------------------------------------- sectienummers

    function secties(form) {
        return Array.prototype.slice.call(form.querySelectorAll('[data-sectie]'));
    }

    /** Alleen de secties die van toepassing zijn, in volgorde. */
    function actieveSecties(form) {
        return secties(form).filter(function (sectie) { return !sectie.hidden; });
    }

    /**
     * Nummert de zichtbare secties door. Zonder dit valt er een gat in de
     * nummering zodra een sectie wegvalt.
     */
    function hernummerSecties(form) {
        actieveSecties(form).forEach(function (sectie, i) {
            sectie.querySelector('[data-nummer]').textContent = (i + 1) + '.';
        });
    }

    // ------------------------------------------------------------- stappen

    function regelStappen(form) {
        var voortgang = form.querySelector('[data-voortgang]');
        if (!voortgang) { return; }

        voortgang.hidden = false;

        try {
            stapModus = localStorage.getItem(opslagsleutel + '-alles') !== 'ja';
        } catch (e) { /* privémodus: dan gewoon de standaard */ }

        form.querySelector('[data-toon-alles]').addEventListener('click', function () {
            stapModus = !stapModus;
            try {
                localStorage.setItem(opslagsleutel + '-alles', stapModus ? 'nee' : 'ja');
            } catch (e) { /* niets aan te doen */ }
            werkStappenBij(form);
            if (!stapModus) { form.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        });

        form.querySelector('[data-vorige]').addEventListener('click', function () {
            gaNaarStap(form, huidigeStap - 1);
        });

        form.querySelector('[data-volgende]').addEventListener('click', function () {
            var mis = valideerSectie(form, actieveSecties(form)[huidigeStap]);

            if (mis) {
                springNaarFout(form, mis);
                return;
            }

            gaNaarStap(form, huidigeStap + 1);
        });

        werkStappenBij(form);
    }

    function gaNaarStap(form, index) {
        var lijst = actieveSecties(form);
        huidigeStap = Math.max(0, Math.min(index, lijst.length - 1));
        werkStappenBij(form);

        var sectie = lijst[huidigeStap];
        if (sectie) {
            sectie.focus({ preventScroll: true });
            // Naar het begin van het formulier, niet van de pagina: binnen de
            // site staat daar nog een kop met menu boven.
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function werkStappenBij(form) {
        var voortgang = form.querySelector('[data-voortgang]');
        if (!voortgang || voortgang.hidden) { return; }

        var lijst = actieveSecties(form);
        if (huidigeStap > lijst.length - 1) { huidigeStap = Math.max(0, lijst.length - 1); }

        secties(form).forEach(function (sectie) {
            sectie.classList.toggle('buiten-stap', stapModus && lijst.indexOf(sectie) !== huidigeStap);
        });

        // De inleiding boven het formulier hoort bij stap 1; app.css verbergt hem daarna.
        form.parentNode.toggleAttribute('data-na-eerste-stap', stapModus && huidigeStap > 0);

        var laatste = huidigeStap >= lijst.length - 1;

        form.querySelector('[data-vorige]').hidden = !stapModus || huidigeStap === 0;
        form.querySelector('[data-volgende]').hidden = !stapModus || laatste;
        form.querySelector('[data-versturen]').hidden = stapModus && !laatste;
        form.querySelector('[data-afsluiting-hint]').hidden = stapModus && !laatste;
        form.querySelector('[data-toon-alles]').textContent = stapModus ? 'Toon alles' : 'Stap voor stap';

        form.querySelector('[data-voortgang-vulling]').value = Math.round(
            (stapModus && lijst.length ? (huidigeStap + 1) / lijst.length : 1) * 100
        );

        // Zolang de routevraag open staat, is het aantal stappen nog niet bekend.
        // Een totaal noemen zou dan liegen: dat springt zodra die vraag beantwoord is.
        var routeBekend = !form.dataset.route || !!leesAntwoorden(form)[form.dataset.route];
        var kop = lijst[huidigeStap] && lijst[huidigeStap].querySelector('h2');
        var titel = kop ? ': ' + kop.textContent.replace(/^\s*\d+\.\s*/, '').trim() : '';

        form.querySelector('[data-voortgang-tekst]').textContent = stapModus
            ? 'Stap ' + (huidigeStap + 1) + (routeBekend ? ' van ' + lijst.length : '') + titel
            : lijst.length + ' secties, alles zichtbaar';
    }

    // ------------------------------------------------------------ validatie

    /** Spiegelt cm_vraag_verplicht(). */
    function isVerplicht(blok, a) {
        return blok.hasAttribute('data-verplicht')
            || (!!blok.dataset.verplichtAls && matcht(blok, 'verplichtAls', function (k) { return a[k]; }))
            || (!!blok.dataset.verplichtAlsUitkomst
                && matcht(blok, 'verplichtAlsUitkomst', function (k) { return roep('uitkomst', k, a); }));
    }

    /** Spiegelt cm_valideer(). */
    function valideerSectie(form, sectie) {
        if (!sectie) { return null; }

        var a = leesAntwoorden(form);
        var eerste = null;

        elk(sectie.querySelectorAll('[data-vraag]'), function (blok) {
            var type = blok.dataset.type;
            var key = blok.dataset.vraag;
            if (type === 'afgeleid' || type === 'kop' || type === 'groep') { return; }

            toonVeldFout(blok, null);
            if (blok.closest('[hidden]')) { return; }

            var waarde = a[key];
            var leeg = type === 'maatregelen'
                ? !heeft(Object.keys(waarde || {}).map(function (m) { return waarde[m]; }), 'komt')
                : (Array.isArray(waarde) ? !waarde.length : !String(waarde || '').trim());
            var melding = null;

            if (leeg && isVerplicht(blok, a)) {
                melding = {
                    rijen: 'Vul minstens één rij in.',
                    maatregelen: 'Bij deze omvang is een nieuwe maatregel nodig. Kies bij minstens één maatregel '
                        + '‘Komt er’, of vul er zelf een in bij ‘Andere maatregelen’.',
                    radio: 'Maak hier een keuze.',
                    checkbox: 'Maak hier een keuze.',
                    select: 'Maak hier een keuze.'
                }[type] || 'Dit veld is nog leeg.';
            } else if (type === 'rijen') {
                melding = rijFout(blok);
            }

            if (melding) {
                toonVeldFout(blok, melding);
                eerste = eerste || blok;
            }
        });

        return eerste;
    }

    /** Spiegelt cm_rij_fout(): de eerste gevulde rij waar een verplicht veld leeg is. */
    function rijFout(blok) {
        var rijen = blok.querySelectorAll('[data-rij]');

        for (var i = 0; i < rijen.length; i++) {
            if (rijIsLeeg(rijen[i])) { continue; }

            var leeg = Array.prototype.filter.call(rijen[i].querySelectorAll('[data-verplicht]'), function (v) {
                return !v.value.trim();
            })[0];

            if (leeg) {
                return rijen[i].querySelector('legend').textContent + ' is nog niet compleet. Vul ook ‘'
                    + leeg.closest('label').firstChild.textContent.trim() + '’ in.';
            }
        }

        return null;
    }

    function valideerAlles(form) {
        var eerste = null;

        actieveSecties(form).forEach(function (sectie) {
            var mis = valideerSectie(form, sectie);
            if (mis && !eerste) { eerste = mis; }
        });

        return eerste;
    }

    function toonVeldFout(blok, tekst) {
        var melding = blok.querySelector('[data-fout]');

        // De rode rand om de vraag volgt in app.css uit aria-invalid.
        elk(blok.querySelectorAll('input, select, textarea'), function (veld) {
            if (tekst) { veld.setAttribute('aria-invalid', 'true'); } else { veld.removeAttribute('aria-invalid'); }
        });

        if (melding) {
            melding.textContent = tekst || '';
            melding.hidden = !tekst;
        }
    }

    function springNaarFout(form, blok) {
        var sectie = blok.closest('[data-sectie]');
        var lijst = actieveSecties(form);

        if (stapModus && sectie && lijst.indexOf(sectie) !== -1) {
            huidigeStap = lijst.indexOf(sectie);
            werkStappenBij(form);
        }

        // Een veld in een dichte regel of een dicht uitklapblok is niet te zien.
        for (var dicht = blok.closest('details'); dicht; dicht = dicht.parentElement.closest('details')) {
            dicht.open = true;
        }

        var veld = blok.querySelector('input, select, textarea');
        (veld || blok).focus({ preventScroll: true });
        blok.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }

    function regelFoutsamenvatting() {
        var melding = document.querySelector('[data-foutsamenvatting]');
        if (melding) { melding.focus(); }
    }

    // ---------------------------------------------------- tussentijds bewaren

    function bewaarStraks(form) {
        var werkplek = document.getElementById('cisomatic').hasAttribute('data-werkplek');
        clearTimeout(bewaarTimer);
        bewaarTimer = werkplek
            ? setTimeout(function () { bewaarOpServer(form); }, 2000)
            : setTimeout(function () { bewaarLokaal(form); }, 500);
    }

    /** Op een werkplek bewaart de server het concept, met dezelfde velden als Bewaren. */
    function bewaarOpServer(form) {
        if (!Object.keys(aangeraakt).length) { return; }

        var velden = new FormData(form);
        velden.set('bewaar', 'auto');

        fetch(form.action, { method: 'POST', body: velden, credentials: 'same-origin' })
            .then(function (antwoord) {
                // Een verlopen sessie stuurt door naar de login, en die geeft 200.
                if (antwoord.status !== 204) { throw new Error(String(antwoord.status)); }
            })
            .catch(function () { toast('Niet bewaard. Probeer het met de knop Bewaren.'); });
    }

    function bewaarLokaal(form) {
        // Pas bewaren als iemand iets heeft ingevuld. Anders overschrijft een
        // leeg formulier het concept waar iemand nog mee verder wil.
        if (!Object.keys(aangeraakt).length) { return; }

        try {
            localStorage.setItem(opslagsleutel, JSON.stringify({
                bewaard_op: new Date().toISOString(),
                antwoorden: leesAntwoorden(form, true)
            }));
        } catch (e) { /* vol of geblokkeerd: dan maar niet */ }
    }

    function laadLokaal() {
        try {
            var ruw = localStorage.getItem(opslagsleutel);
            return ruw ? JSON.parse(ruw) : null;
        } catch (e) {
            return null;
        }
    }

    function wisLokaal() {
        try { localStorage.removeItem(opslagsleutel); } catch (e) { /* niets aan te doen */ }
    }

    function regelEerderBegonnen(form) {
        var banner = document.querySelector('[data-eerder]');

        // Stuurde de server een fout terug, dan is iemand midden in het invullen.
        if (!banner || document.querySelector('[data-foutsamenvatting]')) { return; }

        var bewaard = laadLokaal();
        if (!bewaard || !bewaard.antwoorden) { return; }

        var moment = new Date(bewaard.bewaard_op);
        var tekst = 'Je was bezig met ' + (bewaard.antwoorden.projectnaam || 'een naamloos project') + '.';

        if (!isNaN(moment.getTime())) {
            tekst += ' Laatst bewaard op ' + moment.toLocaleDateString('nl-NL', { day: 'numeric', month: 'long' })
                + ' om ' + moment.toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' }) + '.';
        }

        banner.querySelector('[data-eerder-tekst]').textContent = tekst;
        banner.hidden = false;

        banner.querySelector('[data-eerder-verder]').addEventListener('click', function () {
            vulIn(form, bewaard.antwoorden, []);
            banner.hidden = true;
            ververs(form, true);
        });

        banner.querySelector('[data-eerder-opnieuw]').addEventListener('click', function () {
            wisLokaal();
            banner.hidden = true;
        });
    }

    // ----------------------------------------------------------- inladen

    /**
     * Een bestand met antwoorden inladen, van dit formulier of van een ander.
     * Dat gebeurt volledig in de browser: de tool is openbaar, dus een server
     * die opgeslagen antwoorden kan openen, zou iedereen bij andermans
     * antwoorden brengen.
     */
    function regelInladen(form) {
        var invoer = document.querySelector('[data-hervat]');
        if (!invoer) { return; }

        // Inladen kan alleen met script, dus pas nu tonen.
        invoer.closest('details').hidden = false;

        var melding = document.querySelector('[data-hervat-melding]');
        var eigen = document.getElementById('cisomatic').dataset.formulier;
        var overnemen = JSON.parse(form.dataset.overnemen || '{}');

        invoer.addEventListener('change', function () {
            var bestand = invoer.files && invoer.files[0];
            if (!bestand) { return; }

            var lezer = new FileReader();

            lezer.onload = function () {
                var data;
                try {
                    data = JSON.parse(lezer.result);
                } catch (e) {
                    toon(melding, 'Dit bestand is geen geldig bestand met antwoorden.', true);
                    return;
                }

                var antwoorden = data && data.antwoorden ? data.antwoorden : data;
                if (!antwoorden || typeof antwoorden !== 'object') {
                    toon(melding, 'Dit bestand bevat geen antwoorden.', true);
                    return;
                }

                // Een bestand van voor de cisomatic noemt zijn soort niet.
                // Alleen de quickscan schreef zulke bestanden.
                var soort = data.soort || 'quickscan';
                var bron = soort === eigen ? null : (overnemen[soort] || {});
                var ingevuld = vulIn(form, antwoorden, bron && bron.overslaan ? bron.overslaan : []);

                // Een antwoord dat hier onder een andere sleutel staat.
                Object.keys((bron && bron.als) || {}).forEach(function (van) {
                    var naar = bron.als[van];
                    if (antwoorden[van] && zetWaarde(form, naar, antwoorden[van])) {
                        aangeraakt[naar] = true;
                        ingevuld++;
                    }
                });

                Object.keys((bron && bron.zet) || {}).forEach(function (key) {
                    if (zetWaarde(form, key, bron.zet[key])) { aangeraakt[key] = true; }
                });

                ververs(form, true);

                toon(melding, bron && bron.naam
                    ? ingevuld + ' antwoorden overgenomen uit ' + bron.naam + '. Loop ze na, en vul de rest aan.'
                    : ingevuld + ' antwoorden teruggezet uit ' + (data.projectnaam || bestand.name) + '. Loop ze na, en vul aan.',
                false);
            };

            lezer.readAsText(bestand);
        });
    }

    /** Zet antwoorden in het formulier. Geeft terug hoeveel er pasten. */
    function vulIn(form, antwoorden, overslaan) {
        var aantalGevuld = 0;

        Object.keys(antwoorden).forEach(function (key) {
            var waarde = antwoorden[key];
            if (heeft(overslaan, key)) { return; }

            var rijen = Array.isArray(waarde) && waarde.length && typeof waarde[0] === 'object';
            if (rijen ? vulRijen(form, key, waarde) : zetWaarde(form, key, waarde)) {
                aangeraakt[key] = true;
                aantalGevuld++;
            }
        });

        return aantalGevuld;
    }

    function toon(element, tekst, isFout) {
        if (!element) { return; }
        if (isFout) { element.setAttribute('role', 'alert'); } else { element.removeAttribute('role'); }
        element.textContent = tekst;
        element.hidden = false;
    }

    // ------------------------------------------------------------- meldingen

    /** Een korte melding onderin beeld, die na een paar seconden weer weggaat. */
    function toast(tekst) {
        var melding = document.querySelector('[data-toast]');
        if (!melding) { return; }

        melding.textContent = tekst;
        melding.hidden = false;
        melding.classList.add('toast-zichtbaar');

        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            melding.classList.remove('toast-zichtbaar');
            setTimeout(function () { melding.hidden = true; }, 300);
        }, 6000);
    }

    // ------------------------------------------------------------ document

    function regelDocumentknoppen() {
        var tekstblok = document.querySelector('[data-document]');
        if (!tekstblok) { return; }

        // Zonder script doen deze knoppen niets, dus pas nu tonen.
        elk(document.querySelectorAll('[data-acties]'), function (blok) { blok.hidden = false; });

        var kopieer = document.querySelector('[data-kopieer]');
        if (kopieer && navigator.clipboard) {
            var opschrift = kopieer.textContent;
            kopieer.addEventListener('click', function () {
                // Als opgemaakte tekst, zodat plakken in een tekstverwerker de kopjes,
                // lijsten en tabellen houdt. Zonder ClipboardItem blijft platte tekst over.
                var klaar = window.ClipboardItem
                    ? navigator.clipboard.write([new ClipboardItem({
                        'text/html': new Blob([tekstblok.innerHTML], { type: 'text/html' }),
                        'text/plain': new Blob([tekstblok.innerText], { type: 'text/plain' })
                    })])
                    : navigator.clipboard.writeText(tekstblok.innerText);

                klaar.then(function () {
                    kopieer.textContent = 'Gekopieerd';
                    setTimeout(function () { kopieer.textContent = opschrift; }, 2000);
                });
            });
        } else if (kopieer) {
            kopieer.hidden = true;
        }

        var afdrukken = document.querySelector('[data-afdrukken]');
        if (afdrukken) {
            afdrukken.addEventListener('click', function () { window.print(); });
        }

        var antwoordenBlok = document.getElementById('antwoorden-json');
        var downloadJson = document.querySelector('[data-download-json]');
        if (antwoordenBlok && downloadJson) {
            downloadJson.addEventListener('click', function () {
                var link = document.createElement('a');
                link.href = URL.createObjectURL(new Blob([antwoordenBlok.textContent], { type: 'application/json;charset=utf-8' }));
                link.download = downloadJson.dataset.downloadJson;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(link.href);
            });
        }
    }
}());

/**
 * De regels van de AI scan, voor de motor in ../assets/app.js.
 *
 * - de beslishulp is een beslisboom: alleen de vragen op de route zijn te zien
 * - onder de vragen staat meteen of de AI-verordening geldt, en de risicogroep
 * - bij generatieve AI staat 'maakt tekst, beeld, audio of video' alvast aangevinkt
 *
 * Dit spiegelt ai_route(), ai_stap() en ai_klasse() in afleiding.php. De
 * rollen en de teksten komen via data-groepen mee, op het eerste
 * uitkomstblok. PHP rekent bij het versturen alles opnieuw uit.
 */
(function () {
    'use strict';

    var cm = window.cisomatic;
    var model = null;
    var vorige = { a: null, r: null };

    cm.regels({ uitkomst: uitkomst, voorstel: voorstel, afgeleid: afgeleid });

    function gegevens() {
        if (!model) {
            var blok = document.querySelector('[data-afgeleid="toepassing"]');
            model = blok ? JSON.parse(blok.dataset.groepen) : null;
        }
        return model;
    }

    function heeft(lijst, waarde) {
        return lijst.indexOf(waarde) !== -1;
    }

    /** Spiegelt ai_uitkomst(). */
    function uitkomst(naam, a) {
        var r = route(a);

        if (naam === 'risico') {
            return r.klaar && !heeft(r.pad, 'ai_toepassing') && !heeft(r.pad, 'ai_systeemrisico') ? 'nee' : 'ja';
        }
        return heeft(r.pad, naam) ? 'ja' : 'nee';
    }

    /** Wie generatieve AI gebruikt, maakt tekst, beeld, audio of video. */
    function voorstel(key, a) {
        if (key !== 'ai_toepassing') { return ''; }
        return a.ai_generatief === 'ja' ? ['transparantie_generatie'] : [];
    }

    /**
     * Spiegelt ai_route(). De motor vraagt per ronde voor elk blok de route op,
     * met dezelfde antwoorden; die rekenen we dus maar één keer uit.
     */
    function route(a) {
        if (vorige.a === a) { return vorige.r; }

        var r = {
            pad: [], klaar: false, rollen: [], soort: '', niet: '', risico: '', bijlage3: false, biometrie: false,
            derde: false, transparantie: false, opensource: false, systeemrisico: false
        };
        var key = 'ai_algoritme';

        while (key && gegevens()) {
            r.pad.push(key);
            var antwoord = a[key] || (key === 'ai_toepassing' ? [] : '');
            if (antwoord === '') { break; }

            var stap = volgende(key, antwoord, r);
            key = stap[0];
            Object.keys(stap[1]).forEach(function (k) { r[k] = stap[1][k]; });
        }

        r.klaar = !key;
        if (r.klaar && r.opensource && r.risico === 'laag' && !r.transparantie && r.soort !== 'model') {
            r.niet = 'opensource';
        }

        vorige = { a: a, r: r };
        return r;
    }

    /** Spiegelt ai_stap(): de volgende vraag, leeg aan het eind, en wat het antwoord toevoegt. */
    function volgende(key, w, r) {
        var markt = !heeft(r.rollen, 'aanbieder') && !heeft(r.rollen, 'gebruiksverantwoordelijke');
        var aanbieder = heeft(r.rollen, 'aanbieder');
        var transparantie = r.transparantie ? 'ai_transparantie_uitz' : '';
        var hoog = [markt ? '' : transparantie, { risico: 'hoog' }];
        var laag = [markt ? '' : 'ai_opensource', { risico: 'laag' }];
        var lijst = [].concat(w);
        var verboden = lijst.filter(function (k) { return k.indexOf('verboden_') === 0; });
        var alleenLive = verboden.length === 1 && verboden[0] === 'verboden_realtime';

        function niet(reden) { return ['', { niet: reden }]; }
        function begint(begin) { return lijst.some(function (k) { return k.indexOf(begin) === 0; }); }

        switch (key) {
            case 'ai_algoritme': return w === 'ja' ? ['ai_rol', {}] : niet('algoritme');
            case 'ai_rol': return model.rollen[w].length ? ['ai_status', { rollen: model.rollen[w] }] : niet('rol');
            case 'ai_status': return ['ai_soort', {}];
            case 'ai_soort':
                if (w === 'twijfel') { return ['ai_regels', {}]; }
                if (w === 'geen') { return niet('ai'); }
                return [w === 'model' && markt ? '' : 'ai_uitzondering', { soort: w }];
            case 'ai_regels': return w === 'nee' ? ['ai_inferentie', {}] : niet('ai');
            case 'ai_inferentie': return w === 'ja' ? ['ai_omgeving', {}] : niet('ai');
            case 'ai_omgeving': return w === 'ja' ? ['ai_uitzondering', { soort: 'systeem' }] : niet('ai');
            case 'ai_uitzondering':
                if (w !== 'nee') { return niet('uitzondering'); }
                return [r.soort === 'model' ? 'ai_systeemrisico' : 'ai_toepassing', {}];
            case 'ai_systeemrisico':
                return w === 'ja' ? ['', { systeemrisico: true }] : [aanbieder ? 'ai_opensource' : '', {}];
            case 'ai_toepassing':
                return [alleenLive ? 'ai_verboden_uitz' : (verboden.length ? '' : 'ai_bijlage1'), {
                    risico: verboden.length && !alleenLive ? 'verboden' : '',
                    bijlage3: begint('hoog_') || heeft(lijst, 'transparantie_emotie'),
                    biometrie: heeft(lijst, 'hoog_biometrie') || heeft(lijst, 'transparantie_emotie'),
                    transparantie: begint('transparantie_')
                }];
            case 'ai_verboden_uitz':
                if (w === 'nee') { return ['', { risico: 'verboden' }]; }
                return markt ? ['', { risico: 'hoog' }] : ['ai_bijlage1', { bijlage3: true, biometrie: true }];
            case 'ai_bijlage1':
                if (w === 'nee') { return r.bijlage3 ? ['ai_profilering', { derde: r.biometrie }] : laag; }
                return [markt ? '' : (w === 'a' && aanbieder ? 'ai_derde' : transparantie), { risico: 'hoog' }];
            case 'ai_derde': return [transparantie, { derde: w === 'ja' }];
            case 'ai_profilering': return w === 'ja' ? hoog : ['ai_taak', {}];
            case 'ai_taak': return w === 'nee' ? hoog : laag;
            case 'ai_opensource': return [r.soort === 'model' ? '' : transparantie, { opensource: w === 'ja' }];
            case 'ai_transparantie_uitz': return ['', { transparantie: w === 'nee' }];
        }
        return ['', {}];
    }

    /** Spiegelt ai_klasse(). */
    function klasse(r) {
        if (r.niet) { return 'niet'; }
        if (!r.klaar) { return ''; }
        if (r.risico === 'verboden' || r.risico === 'hoog') { return r.risico; }
        if (r.systeemrisico) { return 'systeemrisico'; }
        if (r.soort === 'model') { return 'model'; }
        return r.transparantie ? 'transparantie' : 'laag';
    }

    function afgeleid(bron, a) {
        if (!gegevens()) { return null; }

        var r = route(a);
        var open = { waarde: 'nog te bepalen', niveau: '', redenen: ['Beantwoord eerst de vragen hierboven.'] };

        if (bron === 'toepassing') {
            if (r.niet && !heeft(r.pad, 'ai_toepassing') && !heeft(r.pad, 'ai_systeemrisico')) {
                return { waarde: 'Nee', niveau: '', redenen: [model.niet[r.niet], model.klassen.niet.uitleg] };
            }
            if (!r.klaar && !heeft(r.pad, 'ai_toepassing') && !heeft(r.pad, 'ai_systeemrisico')) { return open; }

            return {
                waarde: 'Ja',
                niveau: '',
                redenen: [
                    'Jullie zijn ' + cm.opsomming(r.rollen) + '.',
                    'De toepassing is een ' + model.soorten[r.soort] + '.'
                ]
            };
        }

        var k = klasse(r);
        if (!k) { return open; }

        return {
            waarde: model.klassen[k].label,
            niveau: model.klassen[k].niveau,
            redenen: [r.niet ? model.niet[r.niet] : model.klassen[k].uitleg]
        };
    }
}());

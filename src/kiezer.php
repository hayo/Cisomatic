<?php

/**
 * De voordeur: de formulieren per fase, met een link naar elk. Daarboven de
 * dossiers. Die staan in de browser, dus assets/dossier.js tekent ze; zonder
 * script blijft alleen de lijst met formulieren over.
 *
 * Verwacht $formulieren, met in elk 'href' het adres, en $fasen: de
 * formulieren per fase.
 */

declare(strict_types=1);

/** @var list<array<string,mixed>> $formulieren */
/** @var array<string,list<array<string,mixed>>> $fasen */

$titels = array_column($formulieren, 'titel', 'id');
$kaderUitleg = ['bio2' => 'Met quickscan 2.0.', 'bio' => 'Met quickscan 1.0, voor wie nog met de BIO werkt.'];

?>
<article id="cisomatic" lang="nl" data-kiezer>
    <h1>CisoMatic</h1>
    <img src="<?= h(cm_asset('img/cisomatic.jpg')) ?>" alt="Cisomatic" width="2752" height="1536" decoding="async">
    <p>Kies hieronder een formulier. Je beantwoordt de vragen stap voor stap, en daarna maakt de tool er
       meteen een document van.</p>

    <section class="dossiers" data-dossiers hidden>
        <h2>Dossiers</h2>
        <p>Werk je aan een project? Bundel de formulieren dan in een dossier. Een antwoord dat in meer formulieren
           terugkomt, vul je één keer in. En je ziet per formulier wat er nog moet gebeuren.</p>

        <ul data-dossier-lijst hidden></ul>

        <form data-dossier-nieuw novalidate>
            <h3>Een nieuw dossier</h3>
            <div data-vraag="dossier_naam" data-type="text">
                <label for="dossier-naam">Hoe heet het project?</label>
                <input type="text" id="dossier-naam" name="dossier_naam" aria-describedby="fout-dossier-naam">
                <p id="fout-dossier-naam" data-fout hidden></p>
            </div>
            <fieldset data-vraag="dossier_normenkader" data-type="radio">
                <legend>Met welk normenkader werken jullie?</legend>
                <p>Dat bepaalt welke quickscan in het dossier komt. Je kunt het later nog wijzigen.</p>
                <?php foreach (CM_NORMENKADERS as $kader => $label): ?>
                    <label for="dossier-kader-<?= h($kader) ?>">
                        <input type="radio" name="dossier_normenkader" id="dossier-kader-<?= h($kader) ?>"
                            value="<?= h($kader) ?>"<?= $kader === 'bio2' ? ' checked' : '' ?>>
                        <span><?= h($label) ?></span>
                        <small><?= h($kaderUitleg[$kader] ?? '') ?></small>
                    </label>
                <?php endforeach; ?>
            </fieldset>
            <p><button type="submit">Begin een dossier</button></p>
        </form>

        <div class="inladen">
            <h3><label for="dossier-bestand">Een projectbestand inladen</label></h3>
            <p>Of het antwoordbestand van één formulier: dan begint er een dossier met dat formulier. Je browser
               leest het bestand zelf, dus er gaat niets naar de server.</p>
            <input type="file" id="dossier-bestand" accept="application/json,.json" data-dossier-inladen>
            <p data-dossier-melding hidden></p>
        </div>

        <p>Een dossier staat alleen in deze browser. Wil je het bewaren, op een andere computer verder, of
           doorgeven aan een collega? Download dan het projectbestand.</p>
    </section>

    <section class="dossier" data-dossier tabindex="-1" hidden></section>

    <div data-fasen>
        <?php foreach ($fasen as $fase => $lijst): ?>
            <section class="fase" id="fase-<?= h($fase === '' ? 'overig' : $fase) ?>">
                <h2><?= h(CM_FASEN[$fase]['titel']) ?></h2>
                <?php if (CM_FASEN[$fase]['intro'] !== ''): ?>
                    <p><?= h(CM_FASEN[$fase]['intro']) ?></p>
                <?php endif; ?>
                <ul>
                    <?php foreach ($lijst as $formulier): ?>
                        <li<?= isset($formulier['opvolger']) ? ' data-eerdere-versie' : '' ?>>
                            <?= cm_icoon($formulier['id']) ?>
                            <h3><a href="<?= h($formulier['href']) ?>"><?= h($formulier['titel']) ?></a></h3>
                            <p><?= h($formulier['intro']) ?></p>
                            <?php if (isset($formulier['opvolger'])): ?>
                                <p><small>Eerdere versie</small> Voor wie nog met de
                                   <?= h(CM_NORMENKADERS[$formulier['normenkader'] ?? ''] ?? 'vorige normen') ?> werkt.
                                   De nieuwe versie is
                                   <?= h($titels[$formulier['opvolger']] ?? $formulier['opvolger']) ?>.</p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endforeach; ?>
    </div>

    <p data-toast role="status" hidden></p>

    <footer>
        <p>Je antwoorden kun je als bestand downloaden. Een ander formulier leest dat bestand ook in, en vult dan
           alvast in wat het al weet. Een dossier bewaar je als projectbestand: één bestand met alle formulieren
           van het project.</p>
    </footer>
</article>
<script type="application/json" id="cisomatic-formulieren"><?= json_encode(
    ['formulieren' => cm_dossier_formulieren($formulieren), 'normenkaders' => CM_NORMENKADERS],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP
) ?></script>
<script src="<?= h(cm_asset('assets/dossier.js')) ?>" defer></script>

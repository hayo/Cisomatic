<?php

/**
 * De inhoud: het formulier, of het document na het versturen.
 *
 * Alleen een article, zonder html, head of body. Die levert de site eromheen,
 * of src/document.php als het formulier zelfstandig draait.
 *
 * Verwacht $formulier, $antwoorden, $fouten, $uitkomst, $blokken, $opgeslagen en $opslagFout.
 */

declare(strict_types=1);

/** @var array<string,mixed> $formulier */
/** @var array<string,mixed> $antwoorden */
/** @var array<string,string> $fouten */
/** @var list<array<int,mixed>>|null $blokken */

$klaar = $blokken !== null;
$map = CM_ROOT . '/' . $formulier['id'];
$uitvoer = (string) $formulier['uitvoer'];
$werkplek = cm_werkplek();

?>
<article id="cisomatic" lang="nl" data-formulier="<?= h($formulier['id']) ?>"<?= $klaar ? ' data-klaar' : '' ?><?= $werkplek !== null ? ' data-werkplek' : '' ?>>
    <h1><?= h($formulier['titel']) ?></h1>
    <p><?= h($formulier['intro']) ?></p>

<?php if ($klaar): ?>

    <?php require __DIR__ . '/resultaat.php'; ?>

<?php else: ?>

    <?php require $map . '/vooraf.php'; ?>

    <?php if ($werkplek === null): ?>
    <section data-eerder hidden>
        <h2>Je hebt een onafgemaakte <?= h($formulier['naam']) ?></h2>
        <p data-eerder-tekst></p>
        <p>
            <button type="button" data-eerder-verder>Verdergaan</button>
            <button type="button" data-eerder-opnieuw>Opnieuw beginnen</button>
        </p>
    </section>
    <?php endif; ?>

    <details hidden>
        <summary>Een bestand met antwoorden inladen</summary>
        <p><?= h($formulier['inladen']) ?></p>
        <input type="file" id="hervat-bestand" accept="application/json,.json" data-hervat>
        <p data-hervat-melding hidden></p>
    </details>

    <?php if ($fouten !== []): ?>
        <div class="melding" role="alert" tabindex="-1" data-foutsamenvatting>
            <p><strong>Er ontbreekt nog iets.</strong></p>
            <p><?= count($fouten) === 1
                ? 'Eén vraag is nog niet goed ingevuld.'
                : count($fouten) . ' vragen zijn nog niet goed ingevuld.' ?></p>
            <ul>
                <?php foreach ($fouten as $key => $melding): ?>
                    <li><a href="#vraag-<?= h($key) ?>"><?= h(cm_alle_vragen()[$key]['label'] ?? $key) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" novalidate data-route="<?= h($formulier['route']) ?>"<?= empty($formulier['overnemen'])
        ? '' : ' data-overnemen="' . h((string) json_encode($formulier['overnemen'])) . '"' ?>>
        <?= $werkplek['velden'] ?? '' ?>
        <div data-voortgang hidden>
            <progress max="100" value="0" aria-labelledby="voortgang-tekst" data-voortgang-vulling></progress>
            <p>
                <span id="voortgang-tekst" role="status" data-voortgang-tekst></span>
                <button type="button" data-toon-alles>Toon alles</button>
            </p>
        </div>

        <?php foreach (cm_secties() as $nummer => $sectie): ?>
            <?= cm_render_sectie($sectie, $antwoorden, $fouten, $nummer + 1) ?>
        <?php endforeach; ?>

        <p>
            <button type="button" data-vorige hidden>Vorige</button>
            <button type="button" data-volgende hidden>Volgende</button>
            <button type="submit" data-versturen>Maak het <?= h($uitvoer) ?></button>
            <?php if ($werkplek !== null): ?>
                <button type="submit" name="bewaar" value="1">Bewaren</button>
            <?php endif; ?>
        </p>
        <p data-afsluiting-hint>Het <?= h($uitvoer) ?> verschijnt op de volgende pagina. <?= match (true) {
            $werkplek !== null => 'Je antwoorden blijven bewaard, dus je kunt later verder.',
            (cm_config()['bewaar_map'] ?? null) === null => 'De server bewaart niets, dus download het als je het wilt houden.',
            default => 'De server bewaart er ook een kopie van.',
        } ?></p>
    </form>

<?php endif; ?>

    <p data-toast role="status" hidden></p>

    <footer>
        <p><?= h($formulier['voet']) ?></p>
    </footer>
</article>
<script src="<?= h(cm_asset('assets/app.js')) ?>" defer></script>
<script src="<?= h(cm_asset($formulier['id'] . '/regels.js')) ?>" defer></script>

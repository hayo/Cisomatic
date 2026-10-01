<?php

/**
 * De pagina na het versturen: eerst de samenvatting van het formulier zelf,
 * dan het volledige document om te downloaden, kopiëren of af te drukken.
 *
 * Verwacht $formulier, $antwoorden, $uitkomst, $blokken, $opgeslagen en $opslagFout.
 */

declare(strict_types=1);

/** @var array<string,mixed> $formulier */
/** @var array<string,mixed> $antwoorden */
/** @var list<array<int,mixed>> $blokken */
/** @var array{bestandsnaam:string,pad:string}|null $opgeslagen */
/** @var string|null $opslagFout */
/** @var string $uitvoer */

$opgemaakt = cm_blokken_naar_html($blokken);

?>
<?php if (cm_werkplek() !== null): ?>
    <p><a href="">&larr; Terug naar de antwoorden</a> &middot; <a href="<?= h(cm_werkplek()['lijst']) ?>">Alle documenten</a></p>
<?php else: ?>
    <p><a href="">&larr; Een nieuwe <?= h($formulier['naam']) ?> invullen</a></p>
<?php endif; ?>

<?php require CM_ROOT . '/' . $formulier['id'] . '/samenvatting.php'; ?>

<script type="application/json" id="antwoorden-json"><?= json_encode([
    'soort' => $formulier['id'],
    'projectnaam' => $antwoorden['projectnaam'] ?? '',
    'opgeslagen_op' => (new DateTimeImmutable('now'))->format(DATE_ATOM),
    'antwoorden' => $antwoorden,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>

<section id="volledig">
    <h3>Het volledige <?= h($uitvoer) ?></h3>

    <?php if ($opgeslagen !== null): ?>
        <p>Opgeslagen op de server als <code><?= h($opgeslagen['bestandsnaam']) ?></code>.</p>
    <?php elseif ($opslagFout !== null): ?>
        <div class="melding waarschuwing">
            <p><strong>Het <?= h($uitvoer) ?> is niet opgeslagen.</strong></p>
            <p><?= h($opslagFout) ?> Download of kopieer de tekst hieronder, dan gaat hij niet
               verloren.</p>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="uitvoer" value="odt">
        <input type="hidden" name="antwoorden" value="<?= h((string) json_encode($antwoorden, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE)) ?>">
        <p><button type="submit">Download het <?= h($uitvoer) ?></button></p>
    </form>
    <p><?= h($formulier['download_uitleg']) ?></p>

    <p data-acties hidden>
        <button type="button" data-kopieer>Kopieer het <?= h($uitvoer) ?></button>
        <button type="button" data-download-json="<?= h(cm_bestandsnaam((string) ($antwoorden['projectnaam'] ?? ''), 'json')) ?>">Download de antwoorden</button>
        <button type="button" data-afdrukken>Afdrukken</button>
    </p>
    <p data-acties hidden>Bewaar de antwoorden als je later verder wilt. Op het formulier laad je dat
       bestand weer in, dan hoef je niet alles opnieuw in te vullen.</p>

    <?php if ($opgemaakt['inhoud'] !== []): ?>
        <nav aria-label="Inhoud van het <?= h($uitvoer) ?>">
            <h4>Inhoud</h4>
            <ol>
                <?php foreach ($opgemaakt['inhoud'] as $deel): ?>
                    <li><a href="#<?= h($deel['id']) ?>"><?= h($deel['titel']) ?></a></li>
                <?php endforeach; ?>
            </ol>
        </nav>
    <?php endif; ?>

    <div data-document><?= $opgemaakt['html'] ?></div>
</section>

<?php

/**
 * De samenvatting boven het document: het risico, de toetsen, wat opvalt, en
 * wat er daarna gebeurt. Het volledige document zet de motor eronder, in
 * src/resultaat.php.
 *
 * Verwacht $antwoorden en $uitkomst.
 */

declare(strict_types=1);

/** @var array<string,mixed> $antwoorden */
/** @var array<string,mixed> $uitkomst */

?>
<h2><?= h((string) ($antwoorden['projectnaam'] ?: 'Naamloos project')) ?></h2>
<p>
    Versie <?= h($uitkomst['versie']) ?>.
    Opgesteld op <?= h(cm_datum_nl(new DateTimeImmutable('now'))) ?> door <?= h((string) $antwoorden['aanvrager']) ?>.
</p>
<p><?= h($uitkomst['risico']['advies']) ?></p>

<section class="facts">
    <h3>De vijf toetsen</h3>
    <dl>
        <dt>Risico</dt>
        <dd data-niveau="<?= h($uitkomst['risico']['niveau']) ?>"><?= h($uitkomst['risico']['label']) ?></dd>
        <?php foreach ($uitkomst['toetsen'] as $toets): ?>
            <dt>Toets <?= $toets['stap'] ?>: <?= h($toets['titel']) ?></dt>
            <dd data-niveau="<?= h($toets['niveau']) ?>"><?= h($toets['label']) ?></dd>
        <?php endforeach; ?>
        <dt>Inkoop</dt>
        <dd data-niveau="<?= h($uitkomst['inkoop']['niveau']) ?>"><?= h($uitkomst['inkoop']['label']) ?></dd>
    </dl>
</section>

<?php if ($uitkomst['aandachtspunten'] !== []): ?>
    <section>
        <h3>Aandachtspunten</h3>
        <ul>
            <?php foreach ($uitkomst['aandachtspunten'] as $punt): ?>
                <li><?= h($punt) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<section>
    <h3>Vervolgstappen</h3>
    <ol>
        <?php foreach ($uitkomst['vervolg'] as $stap): ?>
            <li><?= h($stap) ?></li>
        <?php endforeach; ?>
    </ol>
</section>

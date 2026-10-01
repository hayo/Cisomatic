<?php

/**
 * De samenvatting boven het rapport: het oordeel, het risico per gebied en
 * de voorwaarden. Het volledige rapport zet de motor eronder, in
 * src/resultaat.php.
 *
 * Verwacht $antwoorden en $uitkomst.
 */

declare(strict_types=1);

/** @var array<string,mixed> $antwoorden */
/** @var array<string,mixed> $uitkomst */

$oordeel = CO_OORDELEN[$uitkomst['oordeel']];

?>
<h2><?= h((string) ($antwoorden['projectnaam'] ?: 'Naamloos project')) ?></h2>
<p>
    CIO oordeel, opgesteld op <?= h(cm_datum_nl(new DateTimeImmutable('now'))) ?> door
    <?= h((string) $antwoorden['aanvrager']) ?>.
</p>

<?php if ($uitkomst['oordeel'] === 'negatief'): ?>
    <section class="melding" role="alert">
        <h3>Het oordeel is negatief</h3>
        <p><?= h($oordeel['betekenis']) ?></p>
    </section>
<?php endif; ?>

<section class="facts">
    <h3>Het oordeel in het kort</h3>
    <dl>
        <dt>Oordeel</dt>
        <dd data-niveau="<?= $uitkomst['oordeel'] === 'negatief' ? 'h' : '' ?>"><?= h($oordeel['label']) ?></dd>

        <?php if ($uitkomst['afwijking']): ?>
            <dt>Voorstel</dt>
            <dd><?= h(CO_OORDELEN[$uitkomst['voorstel']]['label']) ?></dd>
        <?php endif; ?>

        <dt>Voorwaarden</dt>
        <dd><?= count($uitkomst['voorwaarden']) ?></dd>

        <dt>Aanbevelingen</dt>
        <dd><?= count($uitkomst['aanbevelingen']) ?></dd>
    </dl>
</section>

<section class="facts">
    <h3>Risico per gebied</h3>
    <dl>
        <?php foreach ($uitkomst['gebieden'] as $id => $gebied): ?>
            <dt><?= h(CO_GEBIEDEN[$id]['naam']) ?></dt>
            <dd data-niveau="<?= h($gebied['niveau']) ?>"><?= h(cm_niveau_label($gebied['niveau'])) ?></dd>
        <?php endforeach; ?>
    </dl>
</section>

<?php if ($uitkomst['voorwaarden'] !== []): ?>
    <section>
        <h3>Voorwaarden</h3>
        <ol>
            <?php foreach ($uitkomst['voorwaarden'] as $voorwaarde): ?>
                <li><?= h($voorwaarde) ?></li>
            <?php endforeach; ?>
        </ol>
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

<?php

/**
 * De samenvatting boven het rapport: het label als een energielabel, of het
 * past bij de informatie, en per thema het label. Het volledige rapport zet de
 * motor eronder, in src/resultaat.php.
 *
 * Verwacht $antwoorden en $uitkomst.
 */

declare(strict_types=1);

/** @var array<string,mixed> $antwoorden */
/** @var array<string,mixed> $uitkomst */

$label = SV_LABELS[$uitkomst['label']];

?>
<h2><?= h((string) ($antwoorden['projectnaam'] ?: 'Naamloos project')) ?></h2>
<p>
    Scan van <?= h((string) $antwoorden['cloud_leverancier']) ?>, opgesteld op
    <?= h(cm_datum_nl(new DateTimeImmutable('now'))) ?> door <?= h((string) $antwoorden['aanvrager']) ?>.
</p>

<section>
    <h3>Label <?= h(sv_label($uitkomst['label'])) ?></h3>
    <ol data-labels aria-label="De labels, van A tot E">
        <?php foreach (array_reverse(SV_LABELS, true) as $niveau => $stap): ?>
            <li data-letter="<?= h($stap['letter']) ?>"<?= $niveau === $uitkomst['label'] ? ' aria-current="true"' : '' ?>>
                <strong><?= h($stap['letter']) ?></strong> <?= h(ucfirst($stap['naam'])) ?>
                <?php if ($niveau === $uitkomst['label']): ?><em>deze dienst</em><?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
    <p><?= h($label['betekenis']) ?></p>
</section>

<?php if (!$uitkomst['past']): ?>
    <section class="melding" role="alert">
        <h3>Dit past niet bij de informatie</h3>
        <p>Voor <?= h(mb_strtolower(cm_antwoord_tekst('sv_belang', $antwoorden), 'UTF-8')) ?> is het label te laag bij
           <?= h(sv_themanamen($uitkomst['te_laag'])) ?>. Hieronder staat wat jullie kunnen doen.</p>
    </section>
<?php endif; ?>

<section class="facts">
    <h3>De scan in het kort</h3>
    <dl>
        <dt>Score</dt>
        <dd><?= $uitkomst['score'] ?> van de 100</dd>

        <dt>Informatie</dt>
        <dd><?= h(cm_antwoord_tekst('sv_belang', $antwoorden)) ?></dd>

        <dt>Nodig</dt>
        <dd><?= h($uitkomst['eis']) ?></dd>

        <dt>Past het?</dt>
        <dd data-niveau="<?= $uitkomst['past'] ? '' : 'h' ?>"><?= h($uitkomst['oordeel']) ?></dd>
    </dl>
</section>

<section class="facts">
    <h3>Per thema</h3>
    <dl>
        <?php foreach ($uitkomst['themas'] as $id => $thema): ?>
            <dt><?= h(SV_THEMAS[$id]['naam']) ?></dt>
            <dd data-niveau="<?= $thema['te_laag'] ? 'h' : '' ?>"><?= h(sv_label($thema['niveau'])) ?><?= $thema['eis'] > 0
                ? h(' (nodig: ' . SV_LABELS[$thema['eis']]['letter'] . ')') : '' ?></dd>
        <?php endforeach; ?>
    </dl>
</section>

<?php if ($uitkomst['tips'] !== []): ?>
    <section>
        <h3>Wat kan beter?</h3>
        <ul>
            <?php foreach ($uitkomst['tips'] as $tip): ?>
                <li><?= h($tip) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?php if ($uitkomst['onbekend'] !== []): ?>
    <section>
        <h3>Nog uitzoeken</h3>
        <p>‘Weet ik niet’ telt als het laagste niveau. Het label kan dus hoger uitkomen als jullie dit uitzoeken.</p>
        <ul>
            <?php foreach ($uitkomst['onbekend'] as $punt): ?>
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

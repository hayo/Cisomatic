<?php

/**
 * De samenvatting boven het document: de uitkomst, de stand per deel, en wat
 * nog moet gebeuren. Beide fit/gap formulieren gebruiken dit bestand. Het
 * volledige document zet de motor eronder, in src/resultaat.php.
 *
 * Verwacht $antwoorden en $uitkomst.
 */

declare(strict_types=1);

/** @var array<string,mixed> $antwoorden */
/** @var array<string,mixed> $uitkomst */

$t = $uitkomst['telling'];

?>
<h2><?= h((string) ($antwoorden['projectnaam'] ?: 'Naamloze dienst')) ?></h2>
<p>
    Verklaring van <?= h((string) $antwoorden['fg_opdrachtnemer']) ?> aan <?= h((string) $antwoorden['fg_opdrachtgever']) ?>,
    opgesteld op <?= h(cm_datum_nl(new DateTimeImmutable('now'))) ?> door <?= h((string) $antwoorden['aanvrager']) ?>.
</p>

<?php if (!$uitkomst['voldoet']): ?>
    <section class="melding" role="alert">
        <h3>De dienst voldoet nog niet</h3>
        <p>Van de <?= h(fg_aantal($uitkomst['totaal'])) ?> die gelden, voldoet de dienst aan <?= $t['nee'] ?> nog niet.
           De dienstverlening kan pas starten als de dienst aan alle normen voldoet.</p>
    </section>
<?php endif; ?>

<section class="facts">
    <h3>De verklaring in het kort</h3>
    <dl>
        <dt>Uitkomst</dt>
        <dd data-niveau="<?= $uitkomst['voldoet'] ? '' : 'h' ?>"><?= h($uitkomst['oordeel']) ?></dd>

        <?php foreach ($uitkomst['kenmerken'] as [$label, $waarde]): ?>
            <dt><?= h($label) ?></dt>
            <dd><?= h($waarde) ?></dd>
        <?php endforeach; ?>

        <dt>Normen</dt>
        <dd><?= $uitkomst['totaal'] ?></dd>

        <dt>Voldoet</dt>
        <dd><?= $t['ja'] ?></dd>

        <dt>Voldoet nog niet</dt>
        <dd data-niveau="<?= $t['nee'] > 0 ? 'h' : '' ?>"><?= $t['nee'] ?></dd>

        <?php if ($t['nvt'] > 0): ?>
            <dt>Niet van toepassing</dt>
            <dd><?= $t['nvt'] ?></dd>
        <?php endif; ?>
    </dl>
</section>

<section>
    <h3>Per deel</h3>
    <ul>
        <?php foreach ($uitkomst['delen'] as $deel): ?>
            <li><?= h($deel['naam'] . ': ' . ($deel['telling']['nee'] > 0
                ? $deel['telling']['nee'] . ' van de ' . fg_aantal(count($deel['normen']))
                    . ($deel['telling']['nee'] === 1 ? ' voldoet' : ' voldoen') . ' nog niet.'
                : 'voldoet.')) ?></li>
        <?php endforeach; ?>
        <?php foreach (fg_afgedekt_zinnen($uitkomst) as $zin): ?>
            <li><?= h($zin) ?></li>
        <?php endforeach; ?>
    </ul>
</section>

<?php if ($uitkomst['gaten'] !== []): ?>
    <section>
        <h3>Wat nog moet gebeuren</h3>
        <ul>
            <?php foreach ($uitkomst['gaten'] as $norm): ?>
                <li><?= h($norm['nummer'] . ' ' . $norm['titel'] . ': ' . implode(' ', cm_regels($norm['maatregel']))
                    . ' Klaar op ' . rtrim($norm['datum'], '.') . '.') ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?php if ($uitkomst['bijlagen'] !== []): ?>
    <section>
        <h3>Stuur dit mee</h3>
        <ul>
            <?php foreach ($uitkomst['bijlagen'] as $bijlage): ?>
                <li><?= h($bijlage) ?></li>
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

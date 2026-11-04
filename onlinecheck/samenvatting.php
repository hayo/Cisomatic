<?php

/**
 * De samenvatting boven het document: de uitkomst, en waarom. Het volledige
 * document zet de motor eronder, in src/resultaat.php.
 *
 * Verwacht $antwoorden en $uitkomst.
 */

declare(strict_types=1);

/** @var array<string,mixed> $antwoorden */
/** @var array<string,mixed> $uitkomst */

?>
<h2><?= h((string) ($antwoorden['projectnaam'] ?: 'Naamloos kanaal')) ?></h2>
<p>
    Ingevuld op <?= h(cm_datum_nl(new DateTimeImmutable('now'))) ?> door <?= h((string) $antwoorden['aanvrager']) ?>.
</p>

<section class="facts">
    <h3>De uitkomst</h3>
    <dl>
        <dt>Platform</dt>
        <dd><?= h($uitkomst['kader']['naam']) ?></dd>
        <dt>Kader</dt>
        <dd data-niveau="<?= h($uitkomst['inzet']['niveau']) ?>"><?= h($uitkomst['inzet']['label']) ?></dd>
        <dt>Uitkomst</dt>
        <dd data-niveau="<?= h($uitkomst['niveau']) ?>"><?= h($uitkomst['label']) ?></dd>
        <dt>Herzien uiterlijk</dt>
        <dd><?= h(cm_datum_nl($uitkomst['herziening'])) ?></dd>
    </dl>
</section>

<section>
    <h3>Waarom</h3>
    <ul>
        <?php foreach ($uitkomst['redenen'] as $reden): ?>
            <li><?= h($reden) ?></li>
        <?php endforeach; ?>
    </ul>
</section>

<?php if ($uitkomst['let_op'] !== []): ?>
    <section>
        <h3>Let op</h3>
        <ul>
            <?php foreach ($uitkomst['let_op'] as $regel): ?>
                <li><?= h($regel) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

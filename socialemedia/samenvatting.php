<?php

/**
 * De samenvatting boven het document: de uitkomst, waarom, en wat nog te
 * regelen is. Het volledige document zet de motor eronder, in src/resultaat.php.
 *
 * Verwacht $antwoorden en $uitkomst.
 */

declare(strict_types=1);

/** @var array<string,mixed> $antwoorden */
/** @var array<string,mixed> $uitkomst */

$exit = $uitkomst['situatie'] === 'exit';

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
        <dt><?= $exit ? 'Vertrek' : 'Uitkomst' ?></dt>
        <dd data-niveau="<?= h($uitkomst['niveau']) ?>"><?= h($uitkomst['label']) ?></dd>
        <?php if (!$exit): ?>
            <dt>Herzien uiterlijk</dt>
            <dd><?= h(cm_datum_nl($uitkomst['herziening'])) ?></dd>
        <?php endif; ?>
    </dl>
</section>

<section>
    <h3><?= $exit ? 'Advies over het vertrek' : 'Waarom' ?></h3>
    <ul>
        <?php foreach ($uitkomst['redenen'] as $reden): ?>
            <li><?= h($reden) ?></li>
        <?php endforeach; ?>
    </ul>
</section>

<?php foreach (['regelen' => 'Nog te regelen', 'let_op' => 'Let op bij adverteren'] as $lijst => $kop): ?>
    <?php if ($uitkomst[$lijst] !== []): ?>
        <section>
            <h3><?= h($kop) ?></h3>
            <ul>
                <?php foreach ($uitkomst[$lijst] as $regel): ?>
                    <li><?= h($regel) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
<?php endforeach; ?>

<?php

/**
 * De samenvatting boven het document: de IAMA in het kort, wat het gebruik
 * tegenhoudt, en wat er nog moet gebeuren. Het volledige document zet de
 * motor eronder, in src/resultaat.php.
 *
 * Verwacht $antwoorden en $uitkomst.
 */

declare(strict_types=1);

/** @var array<string,mixed> $antwoorden */
/** @var array<string,mixed> $uitkomst */

$ernst = ['ernstig' => 'h', 'medium' => 'm', 'licht' => 'l'];
$advies = ['niet' => 'h', 'voorwaarden' => 'm', 'inzetten' => 'l'];

?>
<h2><?= h((string) ($antwoorden['projectnaam'] ?: 'Naamloos algoritme')) ?></h2>
<p>
    Versie <?= h($uitkomst['versie']) ?>.
    Opgesteld op <?= h(cm_datum_nl(new DateTimeImmutable('now'))) ?> door <?= h((string) $antwoorden['aanvrager']) ?>.
</p>

<?php if ($uitkomst['sessie'] < 3): ?>
    <section class="melding">
        <h3>Dit is sessie <?= $uitkomst['sessie'] ?></h3>
        <p>De volgende delen zijn nog leeg. Download hieronder je antwoorden. Laad ze in de volgende sessie weer
           in, en kies dan wat jullie gaan invullen.</p>
    </section>
<?php endif; ?>

<?php if ($uitkomst['blokkerend'] !== []): ?>
    <section class="melding" role="alert">
        <h3>Dit houdt het gebruik tegen</h3>
        <ul>
            <?php foreach ($uitkomst['blokkerend'] as $punt): ?>
                <li><?= h($punt) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<section class="facts">
    <h3>De IAMA in het kort</h3>
    <dl>
        <dt>Algoritme</dt>
        <dd><?= h(cm_antwoord_tekst('ia_type', $antwoorden)) ?></dd>

        <dt>AI verordening</dt>
        <dd data-niveau="<?= h($uitkomst['ai']['niveau']) ?>"><?= h($uitkomst['ai']['label']) ?></dd>

        <dt>Wettelijke taak</dt>
        <dd><?= h(cm_antwoord_tekst('ia_grondslag_taak', $antwoorden)) ?></dd>

        <?php if ($uitkomst['ernst'] !== ''): ?>
            <dt>Zwaarste inbreuk</dt>
            <dd data-niveau="<?= $ernst[$uitkomst['ernst']] ?>"><?= h(ucfirst($uitkomst['ernst'])) ?></dd>
        <?php endif; ?>

        <?php if (($antwoorden['ia_advies'] ?? '') !== ''): ?>
            <dt>Advies</dt>
            <dd data-niveau="<?= $advies[$antwoorden['ia_advies']] ?>"><?= h(cm_antwoord_tekst('ia_advies', $antwoorden)) ?></dd>
        <?php endif; ?>
    </dl>
</section>

<?php if ($uitkomst['actiepunten'] !== []): ?>
    <section>
        <h3>Actiepunten</h3>
        <ul>
            <?php foreach ($uitkomst['actiepunten'] as $punt): ?>
                <li><?= h($punt['actie']) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

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

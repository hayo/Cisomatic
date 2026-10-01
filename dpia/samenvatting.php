<?php

/**
 * De samenvatting boven het document: de DPIA in het kort, en wat eerst
 * geregeld moet zijn. Het volledige document zet de motor eronder, in
 * src/resultaat.php.
 *
 * Verwacht $antwoorden en $uitkomst.
 */

declare(strict_types=1);

/** @var array<string,mixed> $antwoorden */
/** @var array<string,mixed> $uitkomst */

$niveau = static fn (string $n): string => '<dd data-niveau="' . h($n) . '">' . h(cm_niveau_label($n)) . '</dd>';

?>
<h2><?= h((string) ($antwoorden['projectnaam'] ?: 'Naamloze verwerking')) ?></h2>
<p>
    Versie <?= h($uitkomst['versie']) ?>.
    Opgesteld op <?= h(cm_datum_nl(new DateTimeImmutable('now'))) ?> door <?= h((string) $antwoorden['aanvrager']) ?>.
</p>

<?php if (!$uitkomst['samen']): ?>
    <section class="melding">
        <h3>Dit is de beschrijving</h3>
        <p>Hoofdstuk 2 tot en met 4 zijn nog leeg. Download hieronder je antwoorden. Laad ze samen met
           de privacyfunctionaris weer in, en kies dan ‘Alles, samen met de privacyfunctionaris’.</p>
    </section>
<?php endif; ?>

<?php if ($uitkomst['blokkerend'] !== []): ?>
    <section class="melding" role="alert">
        <h3>Dit moet eerst geregeld zijn</h3>
        <ul>
            <?php foreach ($uitkomst['blokkerend'] as $punt): ?>
                <li><?= h($punt) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<section class="facts">
    <h3>De DPIA in het kort</h3>
    <dl>
        <dt>Betrokkenen</dt>
        <dd><?= h(cm_opsomming(array_column($uitkomst['groepen'], 'label'))) ?></dd>

        <dt>Persoonsgegevens</dt>
        <dd><?= h($uitkomst['pg']['label']) ?></dd>

        <?php if ($uitkomst['samen']): ?>
            <dt>Grondslag</dt>
            <dd><?= h(cm_antwoord_tekst('pg_grondslag', $antwoorden)) ?></dd>

            <dt>Grootste risico</dt>
            <?= $niveau($uitkomst['hoogste']) ?>

            <dt>Met de extra maatregelen</dt>
            <?= $niveau($uitkomst['hoogste_rest']) ?>

            <dt>Extra maatregelen</dt>
            <dd><?= count($uitkomst['maatregelen']) ?></dd>
        <?php endif; ?>
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

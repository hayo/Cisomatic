<?php

/**
 * De samenvatting boven het rapport: wat verboden is of niet mag, de scan in
 * het kort, en de vervolgstappen. Het volledige rapport zet de motor
 * eronder, in src/resultaat.php.
 *
 * Verwacht $antwoorden en $uitkomst.
 */

declare(strict_types=1);

/** @var array<string,mixed> $antwoorden */
/** @var array<string,mixed> $uitkomst */

$standpunt = $uitkomst['standpunt'];
$niveaus = [
    'Risicogroep' => AI_KLASSEN[$uitkomst['klasse']]['niveau'],
    'Generatieve AI' => $standpunt === null ? '' : GAI_OORDELEN[$standpunt['oordeel']]['niveau'],
];

?>
<h2><?= h((string) ($antwoorden['projectnaam'] ?: 'Naamloze toepassing')) ?></h2>
<p>
    Scan opgesteld op <?= h(cm_datum_nl(new DateTimeImmutable('now'))) ?> door <?= h((string) $antwoorden['aanvrager']) ?>.
</p>

<?php if ($uitkomst['klasse'] === 'verboden'): ?>
    <section class="melding" role="alert">
        <h3>Deze toepassing is verboden</h3>
        <p><?= h(AI_KLASSEN['verboden']['uitleg']) ?> Dat staat in artikel 5 van de AI verordening.</p>
    </section>
<?php elseif ($standpunt !== null && $standpunt['oordeel'] === 'nee'): ?>
    <section class="melding" role="alert">
        <h3>Dit mag niet volgens het standpunt</h3>
        <p>Een account onder voorwaarden voor consumenten is niet toegestaan. Dat geldt ook voor één medewerker.</p>
    </section>
<?php endif; ?>

<section class="facts">
    <h3>De scan in het kort</h3>
    <dl>
        <?php foreach (ai_kort($uitkomst) as $term => $tekst): ?>
            <dt><?= h($term) ?></dt>
            <dd data-niveau="<?= h($niveaus[$term] ?? '') ?>"><?= h($tekst) ?></dd>
        <?php endforeach; ?>
    </dl>
</section>

<?php if ($standpunt !== null && $standpunt['acties'] !== []): ?>
    <section>
        <h3>Wat nog moet voor generatieve AI</h3>
        <ul>
            <?php foreach ($standpunt['acties'] as $actie): ?>
                <li><?= h($actie) ?></li>
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

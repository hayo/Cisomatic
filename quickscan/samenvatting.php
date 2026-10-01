<?php

/**
 * De samenvatting boven het advies: de uitkomst in het kort, de analyses, de
 * wegingen en het gevolgde pad door de beslisboom. Het volledige advies zet de
 * motor eronder, in src/resultaat.php.
 *
 * Verwacht $antwoorden en $uitkomst.
 */

declare(strict_types=1);

/** @var array<string,mixed> $antwoorden */
/** @var array<string,mixed> $uitkomst */

$beoordeeld = ($antwoorden['rol'] ?? '') === 'beoordelaar';

?>
<h2><?= h((string) ($antwoorden['projectnaam'] ?: 'Naamloos project')) ?></h2>
<p>
    <?= $beoordeeld ? 'Stap 2 · beoordeeld' : 'Stap 1 · beschreven' ?>
    door <?= h((string) $antwoorden['aanvrager']) ?>
    op <?= h(cm_datum_nl(new DateTimeImmutable('now'), true)) ?>.
</p>

<?php if ($uitkomst['aannames'] !== []): ?>
    <section class="melding waarschuwing">
        <h3>Hier is iets aangenomen</h3>
        <p>Op <?= count($uitkomst['aannames']) === 1 ? 'één vraag' : count($uitkomst['aannames']) . ' vragen' ?>
           is "weet ik niet" geantwoord. De tool heeft daar een veilige aanname voor gedaan.
           Klopt die niet, dan verandert de uitkomst.</p>
        <ul>
            <?php foreach ($uitkomst['aannames'] as $aanname): ?>
                <li><?= h($aanname['vraag']) ?> → aangenomen: <strong><?= h($aanname['aanname']) ?></strong></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<?php if ($uitkomst['uitzondering']): ?>
    <section class="melding">
        <h3>Uitzonderingsgeval: géén te beschermen informatie</h3>
        <p>De volledige scan is niet ingevuld, want volgens de invuller valt er niets te beschermen.
           De beoordelaar toetst die motivatie. Houdt die geen stand, dan volgt alsnog de volledige
           scan.</p>
    </section>
<?php endif; ?>

<?php if (!$beoordeeld): ?>
    <section class="melding">
        <h3>Dit is stap 1 van twee</h3>
        <p>De wegingen hieronder zijn voorlopig. De beoordelaar stelt ze in stap 2 definitief vast
           en kan ze bijstellen. Gebruik dit als beeld van wat er waarschijnlijk nodig is, niet als
           eindoordeel.</p>
    </section>
<?php endif; ?>

<?php if ($uitkomst['blokkerend'] !== []): ?>
    <section class="melding" role="alert">
        <h3>Dit blokkeert het voorstel</h3>
        <ul>
            <?php foreach ($uitkomst['blokkerend'] as $punt): ?>
                <li><?= h($punt) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<section class="facts">
    <h3>De uitkomst in het kort</h3>
    <dl>
        <dt>Beveiligingsniveau</dt>
        <dd>BBN <?= (int) $uitkomst['bbn'] ?>, oftewel IB niveau <?= (int) $uitkomst['bbn'] ?></dd>

        <dt>Risiconiveau</dt>
        <dd data-risico="<?= h(str_replace(' ', '-', (string) $uitkomst['risiconiveau'])) ?>"><?= h(ucfirst((string) $uitkomst['risiconiveau'])) ?></dd>

        <dt>Beschikbaarheid</dt>
        <dd><?= h(cm_niveau_label($uitkomst['biv']['b'])) ?>, RTO <?= h((string) $uitkomst['rto']) ?></dd>

        <dt>Integriteit</dt>
        <dd><?= h(cm_niveau_label($uitkomst['biv']['i'])) ?></dd>

        <dt>Vertrouwelijkheid</dt>
        <dd><?= h(cm_niveau_label($uitkomst['biv']['v'])) ?></dd>

        <dt>Cloudbeleid</dt>
        <dd><?= h(ucfirst(qs_cloud_label($uitkomst['cloud_categorie']))) ?></dd>
    </dl>
</section>

<section id="analyses">
    <h3>Aanvullende analyses</h3>
    <?php if ($uitkomst['analyses'] === []): ?>
        <p>Geen. De standaardmaatregelen volstaan voor deze combinatie.</p>
    <?php else: ?>
        <p>Zolang deze ontbreken, kunnen er nog geen definitieve eisen aan de leverancier worden
           gesteld.</p>
        <ul>
            <?php foreach ($uitkomst['analyses'] as $analyse): ?>
                <li>
                    <strong><?= h($analyse['naam']) ?></strong>
                    <small>beoordeling door <?= h($analyse['beoordelaar']) ?></small>
                    <p><?= h($analyse['reden']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php if (!empty($uitkomst['wegingen'])): ?>
    <section id="wegingen">
        <h3>De wegingen en waarom</h3>
        <p>Per weging: het niveau, wat dat volgens de quickscan betekent, waarom het zo is
           ingeschaald en wie dat heeft bepaald.</p>
        <?php foreach ($uitkomst['wegingen'] as $weging): ?>
            <h4><?= h($weging['label']) ?> <mark><?= h($weging['niveau']) ?></mark></h4>
            <?php if ($weging['omschrijving'] !== ''): ?>
                <p><?= h($weging['omschrijving']) ?></p>
            <?php endif; ?>
            <?php if (trim($weging['motivatie']) !== ''): ?>
                <blockquote><?= h($weging['motivatie']) ?></blockquote>
            <?php endif; ?>
            <p><small><?= h($weging['herkomst']) ?></small></p>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<section id="pad">
    <h3>Het gevolgde pad door de beslisboom</h3>
    <ol>
        <?php foreach ($uitkomst['beslisboom'] as $stap): ?>
            <li<?= $stap['geraakt'] ? ' class="geraakt"' : '' ?>>
                <strong><?= h($stap['vraag']) ?></strong>
                <span><?= h($stap['antwoord']) ?></span>
                <span><?= h($stap['gevolg']) ?></span>
            </li>
        <?php endforeach; ?>
    </ol>
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
    <h3>Wie kijkt mee en wie stelt vast</h3>
    <p>Collegiaal advies wordt gevraagd aan
       <strong><?= h(implode(', ', $uitkomst['collegiaal_advies_rollen'])) ?></strong>.
       <?php if ($uitkomst['cio_reden'] !== ''): ?>
           De CIO wordt betrokken omdat <?= h((string) $uitkomst['cio_reden']) ?>.
       <?php endif; ?>
    </p>
    <p>De uitkomst wordt vastgesteld door
       <strong><?= h(implode(', ', $uitkomst['vaststelling'])) ?></strong>.</p>
</section>

<?php

/**
 * De pagina met wijzigingen: per versie wat er veranderd is, de nieuwste
 * bovenaan.
 *
 * Verwacht $versies en $voordeur.
 */

declare(strict_types=1);

/** @var list<array{versie:string,datum:string,wijzigingen:list<string>}> $versies */
/** @var string $voordeur */

?>
<article id="cisomatic" lang="nl" data-wijzigingen>
    <p><a href="<?= h($voordeur) ?>">&larr; Naar de formulieren</a></p>
    <h1>Wat is er veranderd</h1>
    <p>Dit is versie <?= h($versies[0]['versie']) ?> van de Cisomatic. De nieuwste wijzigingen staan bovenaan.</p>
    <p>Het eerste cijfer gaat omhoog als de Cisomatic helemaal opnieuw is gebouwd. Het tweede cijfer gaat omhoog
       bij een nieuw formulier, een formulier dat verdwijnt, of een groot nieuw onderdeel zoals de dossiers. Het
       derde cijfer gaat omhoog bij alle andere wijzigingen.</p>

    <?php foreach ($versies as $versie): ?>
        <section>
            <h2>Versie <?= h($versie['versie']) ?></h2>
            <p><time datetime="<?= h($versie['datum']) ?>"><?= h(cm_datum_nl(new DateTimeImmutable($versie['datum']))) ?></time></p>
            <ul>
                <?php foreach ($versie['wijzigingen'] as $wijziging): ?>
                    <li><?= h($wijziging) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>
</article>

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
    <p>Dit is versie <?= h($versies[0]['versie']) ?> van de Cisomatic. Hieronder staat per versie wat er veranderd
       is, de nieuwste bovenaan.</p>
    <p>Het eerste cijfer gaat omhoog als een antwoordbestand van een eerdere versie niet meer goed inlaadt. Het
       tweede cijfer bij elke andere grote wijziging: een nieuw formulier, nieuwe vragen, of een andere uitkomst
       bij dezelfde antwoorden.</p>

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

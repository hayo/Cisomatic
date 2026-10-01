<?php

/**
 * De voordeur: welke formulieren er zijn, met een link naar elk.
 *
 * Verwacht $formulieren, en in $basis en $staart het adres van deze pagina.
 */

declare(strict_types=1);

/** @var list<array<string,mixed>> $formulieren */
/** @var string $basis */
/** @var string $staart */

?>
<article id="cisomatic" lang="nl">
    <img src="<?= h(cm_asset('img/cisomatic.jpg')) ?>" alt="Cisomatic" width="2752" height="1536" decoding="async">
    <p>Kies hieronder een formulier. Je beantwoordt de vragen stap voor stap, en daarna maakt de tool er
       meteen een document van.</p>

    <ul>
        <?php foreach ($formulieren as $formulier): ?>
            <li>
                <h2><a href="<?= h($basis . '/' . $formulier['id'] . $staart) ?>"><?= h($formulier['titel']) ?></a></h2>
                <p><?= h($formulier['intro']) ?></p>
            </li>
        <?php endforeach; ?>
    </ul>

    <footer>
        <p>Je antwoorden kun je als bestand downloaden. Een ander formulier leest dat bestand ook in, en
           vult dan alvast in wat het al weet.</p>
    </footer>
</article>

# Cisomatic

## Geen verwijzingen naar Claude

Zet nergens een verwijzing naar Claude, Claude Code of Anthropic. Dat geldt voor
code, commentaar, documentatie, commitberichten en pull requests.

- Geen `Co-Authored-By`, geen `Claude-Session` en geen "Generated with Claude
  Code" in een commit of pull request.
- Claude is nooit de auteur of committer van een commit. Commit als
  `Hayo Bethlehem <mail@hayobethlehem.nl>`.

## Versie bijwerken

Zet na elke wijziging een nieuwe versie bovenaan `wijzigingen/versies.php`, in
dezelfde commit. Een complete herbouw van de motor of een compleet nieuw ontwerp
is een nieuwe hoofdversie (1.x naar 2.0). Een formulier erbij of eraf, of een
groot nieuw onderdeel zoals de dossiers, is een .x-release (1.2 naar 1.3). Al
het andere is een .x.x-release (1.2.2 naar 1.2.3). Meer staat in README.md onder
Versies.
Schrijf de wijzigingen kort, in B1-Nederlands, voor wie de formulieren gebruikt.

## Deze repo is de bron

Verander de Cisomatic alleen in deze repo, nooit in de map
`hb2025/content/forge/cisomatic` van `hayo/hayobethlehem.nl`. Die map is een
subtree van deze repo. Na elke push naar `main` zet de workflow
`naar-site.yml` hem daar neer, met het geheim `SITE_TOKEN`. De push start daar
ook de deploy.

Een wijziging in de map van de site laat de volgende update botsen. Is het toch
gebeurd, zet de wijziging dan eerst hier terug. Start daarna "Naar de site" met
de hand, met "eerste keer" aan. Dat vervangt de map in de site door deze repo.
Wat daar alleen stond, valt dan weg. Meer staat in README.md onder Binnen een
site.

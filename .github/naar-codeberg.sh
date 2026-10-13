#!/usr/bin/env bash
#
# Zet de cisomatic in de repo van de site, met git subtree. Deze repo is de
# bron; de site krijgt de map in PAD. De workflow naar-codeberg.yml draait dit
# na elke push naar main, maar het werkt ook lokaal.
#
# - SITE_URL: de repo van de site, met een token om te mogen pushen
# - BRON_URL: deze repo
# - PAD: de map van de cisomatic in de site
# - TAK: de branch van de site
# - EERSTE_KEER: true vervangt de map één keer door een subtree. Wat in die map
#   alleen op de site stond, valt dan weg.

set -euo pipefail

: "${SITE_URL:?Geef SITE_URL mee.}"
: "${BRON_URL:?Geef BRON_URL mee.}"
PAD="${PAD:-hb2025/content/forge/cisomatic}"
TAK="${TAK:-main}"
BRON_TAK="${BRON_TAK:-main}"
EERSTE_KEER="${EERSTE_KEER:-false}"

werkmap="$(mktemp -d)"
trap 'rm -rf "$werkmap"' EXIT

git clone -q --branch "$TAK" "$SITE_URL" "$werkmap/site"
cd "$werkmap/site"
git config user.name "Hayo Bethlehem"
git config user.email "mail@hayobethlehem.nl"

bericht="Cisomatic bijgewerkt vanaf GitHub"

if git log -1 --format=%H --grep="^git-subtree-dir: $PAD/*\$" | grep -q .; then
    git subtree pull -q --prefix="$PAD" "$BRON_URL" "$BRON_TAK" --squash -m "$bericht"
elif [ "$EERSTE_KEER" = "true" ]; then
    if [ -e "$PAD" ]; then
        git rm -r -q "$PAD"
        git commit -q -m "Cisomatic komt voortaan van GitHub"
    fi
    git subtree add -q --prefix="$PAD" "$BRON_URL" "$BRON_TAK" --squash -m "$bericht"
else
    echo "In $PAD staat nog geen subtree. Start de workflow één keer met 'eerste keer' aan." >&2
    exit 1
fi

if [ "$(git rev-parse HEAD)" = "$(git rev-parse "origin/$TAK")" ]; then
    echo "De site is al bij."
    exit 0
fi

git push -q origin "HEAD:$TAK"
echo "De site is bijgewerkt: $(git log -1 --format='%h %s')"

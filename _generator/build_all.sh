#!/bin/sh
# Kompletní sestavení: php build.php && python3 i18n.py apply && python3 relink.py
cd "$(dirname "$0")" && php build.php && python3 -I i18n.py apply && python3 -I relink.py

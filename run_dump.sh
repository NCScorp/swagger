#!/usr/bin/env bash

while ! pg_isready -U "$POSTGRES_USER" -h postgres;
    do sleep 1;
done

psql=( psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" )

set -e
for f in $1/*; do
    case "$f" in
        *.sh)     echo "$0: running $f"; . "$f" ;;
        *.sql)    echo "$0: running $f"; "${psql[@]}" -h postgres -f "$f"; echo ;;
        *.sql.gz) echo "$0: running $f"; gunzip -c "$f" | "${psql[@]}"; echo ;;
        *)        echo "$0: ignoring $f" ;;
    esac
    echo
done

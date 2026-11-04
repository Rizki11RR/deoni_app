#!/usr/bin/env bash
set -Eeuo pipefail

backup_path="${1:-storage/app/private/backup-$(date +%Y%m%d-%H%M%S).dump}"
read -r -a compose <<< "${COMPOSE_COMMAND:-docker compose}"
database="${POSTGRES_DB:-cake_store}"
user="${POSTGRES_USER:-cake_store}"
container="${POSTGRES_CONTAINER:-pgsql}"
restore_database="${database}_restore_check"

mkdir -p "$(dirname "$backup_path")"
cleanup() {
    "${compose[@]}" exec -T "$container" dropdb -U "$user" --if-exists "$restore_database" >/dev/null 2>&1 || true
}
trap cleanup EXIT

${compose[@]} exec -T "$container" pg_dump -U "$user" -Fc "$database" > "$backup_path"
${compose[@]} exec -T "$container" dropdb -U "$user" --if-exists "$restore_database"
${compose[@]} exec -T "$container" createdb -U "$user" "$restore_database"
${compose[@]} exec -T "$container" pg_restore -U "$user" -d "$restore_database" --exit-on-error < "$backup_path"

printf 'Backup and restore verification passed: %s\n' "$backup_path"

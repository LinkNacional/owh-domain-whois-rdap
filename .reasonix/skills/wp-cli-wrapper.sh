#!/usr/bin/env bash
# wp-cli-wrapper.sh — WP-CLI seguro com trava para ambiente Local WP
#
# Uso:
#   .reasonix/skills/wp-cli-wrapper.sh <comando wp>
#   .reasonix/skills/wp-cli-wrapper.sh --test db tables    # comando db no local_tests
#   .reasonix/skills/wp-cli-wrapper.sh --dry-run db drop   # preview destrutivo
#
# Modo --test:
#   - Comandos 'db': usa mysql CLI direto (socket Local WP) — sem bootstrap
#   - Outros comandos: cria wp-config.php temporário com symlinks pro core real
#
# Trava de segurança: comandos destrutivos exigem confirmação "SIM"

set -euo pipefail

RED='\033[0;31m'; YLW='\033[0;33m'; GRN='\033[0;32m'; BOLD='\033[1m'; NC='\033[0m'

# ── Binaries ──
WP_CLI="${WP_CLI:-/opt/Local/resources/extraResources/bin/wp-cli/posix/wp}"
[[ -x "$WP_CLI" ]] || WP_CLI="$(which wp 2>/dev/null || true)"
[[ -z "$WP_CLI" ]] && { echo -e "${RED}WP-CLI não encontrado.${NC}"; exit 1; }
MYSQL="$(which mysql 2>/dev/null || true)"

# ── MySQL socket do Local WP ──
MYSQL_SOCKET="$(php -r 'echo ini_get("mysqli.default_socket");' 2>/dev/null || true)"
[[ -n "$MYSQL_SOCKET" && -S "$MYSQL_SOCKET" ]] || MYSQL_SOCKET=""

# ── Argumentos ──
DRY_RUN=false; USE_TEST_DB=false; WP_PATH=""; ARGS=()
while [[ $# -gt 0 ]]; do
    case "$1" in
        --dry-run) DRY_RUN=true; shift ;;
        --test)    USE_TEST_DB=true; shift ;;
        --path=*)  WP_PATH="${1#--path=}"; shift ;;
        *)         ARGS+=("$1"); shift ;;
    esac
done

[[ ${#ARGS[@]} -eq 0 ]] && {
    echo "Uso: $0 [--dry-run] [--test] <comando wp>"
    echo "Ex:  $0 plugin list"
    echo "     $0 --test db tables"
    echo "     $0 --dry-run --test db check"
    exit 1
}

CMD_STR="${ARGS[*]}"

# ── WordPress path ──
if [[ -z "$WP_PATH" ]]; then
    PROJECT_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
    CANDIDATE="$PROJECT_ROOT/../../.."
    if [[ -f "$CANDIDATE/wp-load.php" ]]; then
        WP_PATH="$(cd "$CANDIDATE" && pwd)"
    elif [[ -f "$PROJECT_ROOT/wordpress/wp-load.php" ]]; then
        WP_PATH="$(cd "$PROJECT_ROOT/wordpress" && pwd)"
    else
        echo -e "${RED}WordPress não encontrado. Use --path=...${NC}"
        exit 1
    fi
fi

# ── Trava de segurança ──
DESTRUCTIVE=("db drop" "db reset" "db clean" "db truncate" "site delete" "site empty" "user delete" "plugin delete" "theme delete")
for dangerous in "${DESTRUCTIVE[@]}"; do
    if [[ "$CMD_STR" == "$dangerous"* ]]; then
        echo -e "\n${RED}${BOLD}╔══════════════════════════════════╗${NC}"
        echo -e "${RED}${BOLD}║  ⛔ TRAVA — comando destrutivo   ║${NC}"
        echo -e "${RED}${BOLD}╚══════════════════════════════════╝${NC}"
        echo -e "\nComando: ${BOLD}$CMD_STR${NC}"
        echo -e "Path:    ${YLW}$WP_PATH${NC}\n"
        $DRY_RUN && { echo -e "${GRN}[dry-run] Não executado.${NC}"; exit 0; }
        echo -ne "Digite ${BOLD}SIM${NC} para confirmar: "
        read -r CONFIRM
        [[ "$CONFIRM" != "SIM" ]] && { echo -e "${GRN}Abortado.${NC}"; exit 0; }
        echo -e "${YLW}Executando...${NC}"
        break
    fi
done

# ═══════════════════════════════════════════════
# MODO --test: comandos 'db' via mysql CLI direto
# ═══════════════════════════════════════════════
if $USE_TEST_DB; then
    echo -e "${YLW}[test db: local_tests | wptests_]${NC}"

    if [[ "$CMD_STR" == "db "* ]]; then
        [[ -z "$MYSQL" ]] && { echo -e "${RED}mysql CLI não encontrado.${NC}"; exit 1; }

        DB_SUB="${CMD_STR#db }"
        MYSQL_ARGS=(-uroot -proot local_tests)
        [[ -n "$MYSQL_SOCKET" ]] && MYSQL_ARGS=(--socket="$MYSQL_SOCKET" -uroot -proot local_tests)

        case "$DB_SUB" in
            tables|"tables --all"*)   MYSQL_QRY="SHOW TABLES" ;;
            check|"check --all")       MYSQL_QRY="SELECT 1" ;;
            query*)                    MYSQL_QRY="${DB_SUB#query }"
                                       MYSQL_QRY="${MYSQL_QRY#\"}"; MYSQL_QRY="${MYSQL_QRY%\"}"
                                       MYSQL_QRY="${MYSQL_QRY#\'}"; MYSQL_QRY="${MYSQL_QRY%\'}" ;;
            "export"*)                EXTRA="${DB_SUB#export }"
                                       [[ "$EXTRA" == "--tables=all" ]] && EXTRA=""
                                       $DRY_RUN && { echo -e "${GRN}[dry-run]${NC} mysqldump ${MYSQL_ARGS[*]} $EXTRA"; exit 0; }
                                       mysqldump "${MYSQL_ARGS[@]}" $EXTRA 2>&1; exit ${PIPESTATUS[0]} ;;
            "import"*)                echo -e "${RED}Use: mysql ${MYSQL_ARGS[*]} < arquivo.sql${NC}"; exit 1 ;;
            *)                        MYSQL_QRY="$DB_SUB" ;;
        esac

        $DRY_RUN && { echo -e "${GRN}[dry-run]${NC} mysql ${MYSQL_ARGS[*]} -e \"$MYSQL_QRY\""; exit 0; }

        set +e
        "$MYSQL" "${MYSQL_ARGS[@]}" -e "$MYSQL_QRY" 2>&1 | grep -v 'Using a password on the command line'
        EXIT=${PIPESTATUS[0]}
        set -e
        exit $EXIT
    fi

    # ── Outros comandos: bootstrap WP com banco de teste ──
    TEST_WP="$(mktemp -d /tmp/wp-test-XXXXXX)"
    trap 'rm -rf "$TEST_WP"' EXIT

    DB_HOST_VALUE="localhost"
    [[ -n "$MYSQL_SOCKET" ]] && DB_HOST_VALUE="localhost:${MYSQL_SOCKET}"

    for core in wp-settings.php wp-load.php wp-admin wp-includes; do
        ln -s "$WP_PATH/$core" "$TEST_WP/$core" 2>/dev/null || true
    done

    cat > "$TEST_WP/wp-config.php" <<WPCONF
<?php
define('DB_NAME','local_tests'); define('DB_USER','root'); define('DB_PASSWORD','root');
define('DB_HOST','$DB_HOST_VALUE'); define('DB_CHARSET','utf8'); define('DB_COLLATE','');
\$table_prefix='wptests_';
define('AUTH_KEY','t'); define('SECURE_AUTH_KEY','t'); define('LOGGED_IN_KEY','t');
define('NONCE_KEY','t'); define('AUTH_SALT','t'); define('SECURE_AUTH_SALT','t');
define('LOGGED_IN_SALT','t'); define('NONCE_SALT','t');
define('WP_DEBUG',true);
define('ABSPATH','$TEST_WP/');
require_once ABSPATH.'wp-settings.php';
WPCONF

    $DRY_RUN && { echo -e "${GRN}[dry-run]${NC} wp --path=$TEST_WP ${ARGS[*]}"; exit 0; }

    set +e
    "$WP_CLI" --path="$TEST_WP" "${ARGS[@]}" 2>&1 \
        | grep -v -E '(Constant ABSPATH already|PHP Warning:|URL redirect|Backtrace|#[0-9]+ |header information|wp_not_installed|wp_redirect_handler)' \
        || true
    EXIT=${PIPESTATUS[0]}
    set -e
    [[ $EXIT -ne 0 ]] && echo -e "${RED}WP-CLI falhou (exit $EXIT). Tabelas WP existem em local_tests? Rode PHPUnit primeiro.${NC}"
    exit $EXIT
fi

# ═══════════════════════════
# MODO NORMAL
# ═══════════════════════════
WP_CMD=("$WP_CLI" --path="$WP_PATH" "${ARGS[@]}")

$DRY_RUN && { echo -e "${GRN}[dry-run]${NC} ${WP_CMD[*]}"; exit 0; }

set +e
"${WP_CMD[@]}" 2>&1
EXIT=$?
set -e
[[ $EXIT -ne 0 ]] && echo -e "${RED}WP-CLI falhou (exit $EXIT)${NC}"
exit $EXIT

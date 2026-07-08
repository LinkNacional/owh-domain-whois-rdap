#!/usr/bin/env bash
# check-security.sh — Varredura de segurança para OWH Domain WHOIS RDAP
# Uso: .reasonix/skills/check-security.sh [--all|--diff]
set -euo pipefail

MODE="${1:-}"
TARGETS=("src" "includes" "admin" "public")
EXCLUDE="vendor|wordpress|node_modules|\.git|\.reasonix|specs"

if [[ "$MODE" == "--diff" ]]; then
    mapfile -t FILES < <(git diff --name-only HEAD -- '*.php' 2>/dev/null || true)
    if [[ ${#FILES[@]} -eq 0 ]]; then echo "Nenhum .php no diff."; exit 0; fi
    GREP_TARGETS=("${FILES[@]}")
elif [[ "$MODE" == "--all" ]]; then
    GREP_TARGETS=(".")
else
    GREP_TARGETS=("${TARGETS[@]}")
fi

ISSUES=0
GREEN="✓"
RED="✗"
YLW="⚠"

# ── 1. Superglobal crua sem sanitização ──
echo "── 1. Superglobais sem sanitização ──"
while IFS= read -r line; do
    file=$(echo "$line" | cut -d: -f1)
    lnum=$(echo "$line" | cut -d: -f2)
    # Pula vendor/wordpress
    [[ "$file" =~ ^($EXCLUDE) ]] && continue
    # Conta se NÃO tem sanitização na mesma linha
    if ! echo "$line" | grep -qP '(sanitize_text_field|sanitize_email|sanitize_key|sanitize_title|intval|absint|wp_unslash|esc_|wp_kses|map_deep|rest_sanitize|sanitize_meta)'; then
        echo "  $RED $file:$lnum  superglobal sem sanitização"
        ISSUES=$((ISSUES + 1))
    fi
done < <(grep -rn '\$_(POST|GET|REQUEST|COOKIE)\[' "${GREP_TARGETS[@]}" --include='*.php' 2>/dev/null || true)

# ── 2. Sanitização sem wp_unslash ──
echo "── 2. sanitize_text_field sem wp_unslash ──"
while IFS= read -r line; do
    file=$(echo "$line" | cut -d: -f1)
    lnum=$(echo "$line" | cut -d: -f2)
    [[ "$file" =~ ^($EXCLUDE) ]] && continue
    if ! echo "$line" | grep -q 'wp_unslash'; then
        echo "  $RED $file:$lnum  sanitize sem wp_unslash() antes"
        ISSUES=$((ISSUES + 1))
    fi
done < <(grep -rn 'sanitize_text_field(\s*\$_(POST|GET|REQUEST)\[' "${GREP_TARGETS[@]}" --include='*.php' 2>/dev/null || true)

# ── 3. AJAX sem check_ajax_referer ──
echo "── 3. wp_ajax_ sem nonce ──"
while IFS= read -r line; do
    file=$(echo "$line" | cut -d: -f1)
    lnum=$(echo "$line" | cut -d: -f2)
    [[ "$file" =~ ^($EXCLUDE) ]] && continue
    # Verifica se check_ajax_referer existe no mesmo arquivo
    if ! grep -q 'check_ajax_referer' "$file" 2>/dev/null; then
        echo "  $RED $file:$lnum  wp_ajax_ sem check_ajax_referer()"
        ISSUES=$((ISSUES + 1))
    fi
done < <(grep -rn "add_action(\s*'wp_ajax_" "${GREP_TARGETS[@]}" --include='*.php' 2>/dev/null || true)

# ── 4. REST sem permission_callback ──
echo "── 4. REST route sem permission_callback ──"
while IFS= read -r line; do
    file=$(echo "$line" | cut -d: -f1)
    lnum=$(echo "$line" | cut -d: -f2)
    [[ "$file" =~ ^($EXCLUDE) ]] && continue
    if ! grep -q "'permission_callback'\s*=>" "$file" 2>/dev/null; then
        echo "  $RED $file:$lnum  register_rest_route sem permission_callback"
        ISSUES=$((ISSUES + 1))
    fi
done < <(grep -rn 'register_rest_route' "${GREP_TARGETS[@]}" --include='*.php' 2>/dev/null || true)

# ── 5. echo sem escaping ──
echo "── 5. echo de variável sem escaping ──"
while IFS= read -r line; do
    file=$(echo "$line" | cut -d: -f1)
    lnum=$(echo "$line" | cut -d: -f2)
    [[ "$file" =~ ^($EXCLUDE) ]] && continue
    echo "  $RED $file:$lnum  echo \$var sem escaping"
    ISSUES=$((ISSUES + 1))
done < <(grep -rn 'echo\s\+\$' "${GREP_TARGETS[@]}" --include='*.php' 2>/dev/null \
    | grep -vP '(esc_html|esc_attr|esc_url|esc_js|esc_textarea|wp_kses|esc_html__|esc_attr__|esc_html_e|esc_attr_e|number_format|intval|absint|count\(|sprintf|implode|\|)' || true)

# ── 6. SQL sem prepare ──
echo "── 6. \$wpdb->query sem prepare ──"
while IFS= read -r line; do
    file=$(echo "$line" | cut -d: -f1)
    lnum=$(echo "$line" | cut -d: -f2)
    [[ "$file" =~ ^($EXCLUDE) ]] && continue
    # Ignora queries DDL (CREATE, ALTER, DROP, TRUNCATE, SHOW, DESCRIBE)
    if echo "$line" | grep -qP '(CREATE|ALTER|DROP|TRUNCATE|SHOW|DESCRIBE)\s+(TABLE|DATABASE|INDEX)'; then
        continue
    fi
    echo "  $RED $file:$lnum  \$wpdb query sem \$wpdb->prepare"
    ISSUES=$((ISSUES + 1))
done < <(grep -rn '\$wpdb->(query|get_var|get_row|get_results|get_col)\(' "${GREP_TARGETS[@]}" --include='*.php' 2>/dev/null \
    | grep -vP '(\$wpdb->prepare|%s|%d|%f)' || true)

# ── Sumário ──
echo ""
echo "── Resumo ──"
if [[ $ISSUES -eq 0 ]]; then
    echo "  $GREEN Nenhum problema de segurança encontrado"
else
    echo "  $RED $ISSUES problemas encontrados — corrigir antes do commit"
fi
exit $ISSUES

# 1.3.1 - 2025/07/14
* Fix: Fatal Error When WooCommerce not installed.
# 1.3.0 - 2025/07/08
* New: WooCommerce integration type is now disabled in settings when WooCommerce plugin is not active, with visual guidance.
* New: "Convert to Product" button in TLDs grid now requires WooCommerce integration — disabled with tooltip otherwise.
* New: WordPress Plugin Check (PCP) integration via `.reasonix/skills/plugin-check.sh`.
* New: Security scan script (`.reasonix/skills/check-security.sh`) — validates sanitization, nonces, escaping, and SQL prepare.
* New: WP-CLI wrapper (`.reasonix/skills/wp-cli-wrapper.sh`) with safety lock for destructive commands and Local WP socket sync.
* New: PHPUnit testing infrastructure — `phpunit.xml.dist`, `wp-tests-config.php`, `tests/bootstrap.php` with WooCommerce sibling loading.
* New: OpenAPI contracts (`specs/openapi/`) and BDD/Gherkin feature specs (`specs/features/`).
* New: `AGENTS.md` with architecture and security directives for AI agents.
* New: `.reasonix.toml` pipeline — Spec → RED → GREEN → Security → Plugin-Check → Refactor.
* Fix: Grid.js CSS/JS now loaded from `admin/` directory instead of missing `node_modules/` — resolves 500 error in production.
* Fix: Wrong product settings link `owh-rdap` → `owh-rdap-settings`.
* Fix: Removed missing `owh-domain-product-admin.css` enqueue — 404 on product edit page.
* Tweak: Server-side AJAX guards for TLD-to-product conversion when WooCommerce integration is not active.
* Tweak: Auto-reset integration type to "None" if WooCommerce is deactivated while selected.

# 1.2.10 - 01/06/26
* Ajuste: Versão mínima do PHP requerida.

# 1.2.9 - 29/05/26
* Ajuste: Cálculo de frete e taxas no checkout.

# 1.2.8 - 29/05/26
* Ajuste: Warning de produto + carregamento de script no Gutenberg.

# 1.2.7 - 27/05/26
* NOVO: Banners.

# 1.2.6 - 27/05/26
* NOVO: Ícones e banners.

# 1.2.5
* Alterando tags.

# 1.2.4
* Correção de erros do plugin checker.

# 1.2.3
* Correção de erros do plugin checker.

# 1.2.2
* Correção de erros do plugin checker.

# 1.2.1
* Adição de alerta informando que é preciso configurar a página de resultados;
* Correção de erros do wordpress.

# 1.2.0
* Adição de configuração para adicionar subdomnios;
* Adição de botão para atualizar TLDs.

# 1.1.1
* Corrigindo erros do plugin checker.

# 1.1.0
* Adição de blocos ao gutenberg.

# 1.0.0
* Lançamento de plugin.
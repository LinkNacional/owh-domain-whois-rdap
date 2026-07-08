# AGENTS.md — OWH Domain WHOIS RDAP

> Diretrizes absolutas para qualquer agente de IA atuando neste projeto.
> Desvie destas regras = introduza débito técnico.

---

## ⚡ Caveman Mode (Token Optimization)

**Respostas telegráficas.** Código primeiro, prosa mínima. O agente deve:

1. **Falar com código, não com texto.** Substituir parágrafos explicativos por diffs.
2. **Sem floreios.** Nada de "Aqui está o arquivo que você pediu" ou "Agora vou explicar". Ação direta.
3. **1 frase de contexto + diff.** Suficiente. Se precisar de mais de 3 linhas de prosa, está errado.
4. **Zero auto-elogios.** Nunca diga "Boa pergunta", "Ótima ideia", "Você está certo".
5. **Foco no diff.** O agente está aqui para editar código, não para conversar.

## 🦀 RTK — Rust Token Killer

**Compressão de logs de terminal.** Ao executar comandos:

1. **Nunca despejar output bruto de terminal** a menos que o usuário peça explicitamente.
2. **Resumir em 1 linha:** `✓ 12 testes passaram, 0 falhas` ou `✗ PHP Parse Error: line 47`.
3. **Filtrar stack traces** para mostrar apenas o erro + linha relevante do projeto.
4. **Omitir warnings irrelevantes** (deprecation notices de vendor, etc).
5. Se o output for >20 linhas → condensar. Só expandir se o usuário perguntar "qual foi o erro completo?".

---

## 🏛️ Arquitetura (NÃO NEGOCIÁVEL)

### Camada 1: Integração WordPress — `admin/`, `public/`, `includes/`

| Regra | Convenção |
| --- | --- |
| **Naming** | `snake_case` + `Studly_Caps` com underscore |
| **Classes** | `Owh_Domain_Whois_Rdap_Admin` → arquivo `class-owh-domain-whois-rdap-admin.php` |
| **Métodos** | `register_styles()`, `enqueue_scripts()` |
| **Funções globais** | `owh_domain_whois_rdap_function_run()` |
| **Variáveis** | `$slug_name_variable` |
| **Arquivos** | Prefixo `class-` para classes, `owh-domain-whois-rdap-*.{js,css}` para assets |

### Camada 2: Lógica de Negócio — `src/`

| Regra | Convenção |
| --- | --- |
| **Naming** | `PascalCase` (classes), `camelCase` (métodos) |
| **Namespace** | `OwhDomainWhoisRdap\` |
| **PSR-4** | `OwhDomainWhoisRdap\Services\RdapClient` → `src/Services/RdapClient.php` |
| **DI** | Injeção por construtor **obrigatória**. Proibido `new` dentro de serviço. |
| **WP-free** | Nada de `get_option()`, `get_post_meta()` diretamente. Usar `SettingsManager` como adapter. |

```php
// ✓ CORRETO — src/
namespace OwhDomainWhoisRdap\Services;

class RdapClient {
    public function queryDomain(string $domain, string $server): ?array { ... }
}

// ✗ ERRADO — não misture snake_case em src/
class rdap_client { ... }           // nome de classe errado
function query_domain() { ... }      // snake_case em src/
```

### Registro de Hooks (Regra de Ouro)

**NUNCA** `add_action` / `add_filter` em construtores de `Admin` ou `Public`.

```php
// ✓ CORRETO — via Loader
// includes/class-owh-domain-whois-rdap.php
private function define_admin_hooks() {
    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
}

// ✗ ERRADO
class Owh_Domain_Whois_Rdap_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']); // NUNCA
    }
}
```

### Estrutura de Diretórios

```
owh-domain-whois-rdap/
├── admin/           # WP Admin (snake_case)
│   ├── css/
│   ├── js/
│   ├── partials/
│   └── class-owh-domain-whois-rdap-admin.php
├── public/          # Frontend + Shortcodes (snake_case)
├── includes/        # Core WP (snake_case)
│   ├── class-owh-domain-whois-rdap.php          # Plugin principal
│   ├── class-owh-domain-whois-rdap-loader.php   # Registro de hooks
│   ├── class-owh-domain-whois-rdap-activator.php
│   └── class-owh-domain-whois-rdap-deactivator.php
├── src/             # Business Logic (PSR-4, PascalCase)
│   ├── Services/    # RdapClient, AvailabilityService, CacheManager, etc.
│   ├── Models/      # DomainResult (DTO)
│   ├── Exceptions/  # RdapConnectionException
│   └── Helpers/     # DomainValidator
└── tests/           # PHPUnit (PSR-4)
```

---

## 🔒 Segurança (NÃO NEGOCIÁVEL)

### Sanitização de Superglobais

```php
// ✓ CORRETO
$domain = sanitize_text_field($_POST['owh_domain']);
$years  = intval($_POST['owh_years']);

// ✗ ERRADO — nunca use $_POST/$_GET/$_REQUEST cru
$domain = $_POST['owh_domain'];
```

### Nonces e Capabilities

- Toda requisição AJAX: `check_ajax_referer()` + `current_user_can()`
- Todo formulário admin: `wp_nonce_field()` no render, `check_admin_referer()` no save
- Endpoints REST: `permission_callback` obrigatório, nunca `__return_true`

### Escapamento de Output

```php
// ✓ CORRETO
echo esc_html($domain);
echo esc_url($link);
echo wp_kses_post($html_content);

// ✗ ERRADO
echo $domain;       // XSS
echo $html_content; // XSS
```

---

## 🛒 Integração WooCommerce

### Tipo de Produto `domain`

- Classe: `WC_Product_Domain` em `includes/class-wc-product-domain.php`
- Registro: `woocommerce_product_class` + `woocommerce_product_type_loaded`
- Preço dinâmico no carrinho via `woocommerce_before_calculate_totals`

### LKN Invoice Payment (Assinatura)

Ao salvar produto `domain`, forçar meta:

```php
update_post_meta($post_id, '_lkn-wcip-subscription-product', 'yes');
update_post_meta($post_id, 'lkn_wcip_subscription_interval_number', '1');
update_post_meta($post_id, 'lkn_wcip_subscription_interval_type', 'year');
update_post_meta($post_id, 'lkn_wcip_subscription_limit', '0');
```

### Gatekeeper no Checkout

- Validar disponibilidade no `woocommerce_checkout_process`
- Timeout de 3s no `RdapClient` durante validação de checkout
- Fail closed com mensagem clara se RDAP não responder

### Dependência WooCommerce (Testes)

- **NÃO** instalar via Composer/wpackagist → erro `Cannot declare class`
- WooCommerce é vizinho no Local WP: `dirname(__DIR__, 2) . '/woocommerce/woocommerce.php'`
- Carregar em `muplugins_loaded` **antes** do plugin nos testes

---

## 🧪 Testes

### Ordem de Bootstrap

```
1. Composer autoload
2. tests_add_filter('muplugins_loaded') → WooCommerce vizinho
3. WP test bootstrap (vendor/wp-phpunit)
4. tests_add_filter('plugins_loaded') → Nosso plugin
```

### Banco de Testes

- Banco: `local_tests` (criado via Adminer no Local WP)
- Prefixo de tabelas: `wptests_`
- Host: `127.0.0.1` (TCP, não socket — evita erro de isolamento Linux)

---

## 🚫 Proibido

- `new` dentro de serviço `src/` (sem DI)
- `add_action`/`add_filter` em construtores
- Superglobais (`$_POST`, `$_GET`, `$_REQUEST`) sem sanitização
- `echo` sem escapamento (`esc_html`, `esc_url`, `wp_kses_post`)
- `snake_case` em `src/`
- `PascalCase` sem underscore em `includes/`/`admin/`/`public/`
- WooCommerce como dependência Composer
- API WHOIS legada (porta 43) — apenas RDAP (JSON)
- Lógica específica de provider (ResellerClub, etc) hardcoded nos Services genéricos

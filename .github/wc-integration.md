Tipo de Produto Dominio v2

Planejamento de Execução: Bloco 1 (Inteligência & Dados)
Objetivo: Estabelecer o núcleo de consulta RDAP focado em performance e segurança. O serviço deve responder rápido se o domínio existe e sinalizar se há indícios de ser Premium/Reservado, delegando a precificação para a etapa posterior.
Entregáveis deste Bloco:
DomainResult.php (Model): Um DTO (Data Transfer Object) padronizado que carrega o status, o slug técnico (para o frontend decidir qual botão mostrar) e os dados brutos.
AvailabilityService.php (Service): A lógica "Gatekeeper". Verifica Cache -> Verifica Formato -> Consulta RDAP -> Classifica a resposta (Disponível, Registrado, Premium/Reservado).

1. O Modelo de Dados (src/Models/DomainResult.php)
Este arquivo define como o serviço entrega a resposta. Adicionei o campo statusSlug que será vital para o seu Frontend saber se mostra o botão "Comprar" ou "Sob Consulta".
PHP
<?php

namespace OwhDomainWhoisRdap\Models;

/**
 * Class DomainResult
 * DTO para transportar o resultado da verificação RDAP.
 */
class DomainResult implements \JsonSerializable
{
    /** @var string O domínio pesquisado */
    private $domain;

    /** @var bool Se está tecnicamente disponível para registro "padrão" */
    private $isAvailable;

    /** @var string Slug de status para lógica de frontend (available, registered, premium_reserved, error) */
    private $statusSlug;

    /** @var string Mensagem legível para humanos */
    private $message;

    /** @var array|null Dados brutos do RDAP (para uso futuro/debug) */
    private $rawData;

    public function __construct(string $domain, bool $isAvailable, string $statusSlug, string $message, ?array $rawData = null)
    {
        $this->domain = $domain;
        $this->isAvailable = $isAvailable;
        $this->statusSlug = $statusSlug;
        $this->message = $message;
        $this->rawData = $rawData;
    }

    public function getDomain(): string { return $this->domain; }
    public function isAvailable(): bool { return $this->isAvailable; }
    public function getStatusSlug(): string { return $this->statusSlug; }
    public function getMessage(): string { return $this->message; }
    public function getRawData(): ?array { return $this->rawData; }

    /**
     * Helper para reconstruir objeto a partir do cache (array)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['domain'] ?? '',
            $data['isAvailable'] ?? false,
            $data['statusSlug'] ?? 'error',
            $data['message'] ?? '',
            $data['rawData'] ?? null
        );
    }

    /**
     * Serializa para salvar no Cache/JSON
     */
    public function jsonSerialize(): array
    {
        return [
            'domain' => $this->domain,
            'isAvailable' => $this->isAvailable,
            'statusSlug' => $this->statusSlug,
            'message' => $this->message,
            'rawData' => $this->rawData,
        ];
    }
    
    // Alias para compatibilidade com código legado que usa toArray
    public function toArray(): array { return $this->jsonSerialize(); }
}

2. O Serviço Magro (src/Services/AvailabilityService.php)
Refatorado para remover responsabilidades de preço. Ele foca em detecção rápida.
PHP
<?php

namespace OwhDomainWhoisRdap\Services;

use OwhDomainWhoisRdap\Models\DomainResult;
use OwhDomainWhoisRdap\Helpers\DomainValidator;

/**
 * Availability Service
 * Focado estritamente em Status RDAP e Detecção de Premium.
 */
class AvailabilityService
{
    private $rdapClient;
    private $bootstrapHandler;
    private $cacheManager;
    private $settingsManager;

    public function __construct(
        RdapClient $rdapClient,
        BootstrapFileHandler $bootstrapHandler,
        CacheManager $cacheManager,
        SettingsManager $settingsManager
    ) {
        $this->rdapClient = $rdapClient;
        $this->bootstrapHandler = $bootstrapHandler;
        $this->cacheManager = $cacheManager;
        $this->settingsManager = $settingsManager;
    }

    /**
     * Verifica disponibilidade com verificação de cache e detecção de premium.
     */
    public function checkAvailability(string $domain): DomainResult
    {
        // 1. Validação Básica
        if (!DomainValidator::isValidDomain($domain)) {
            return new DomainResult($domain, false, 'invalid_format', 'Formato de domínio inválido');
        }

        $domain = strtolower(trim($domain));

        // 2. Cache First (Performance Extrema)
        $cachedData = $this->cacheManager->get($domain);
        if ($cachedData && is_array($cachedData)) {
            return DomainResult::fromArray($cachedData);
        }

        // 3. Resolução de TLD e Servidor
        $tld = $this->extractTld($domain);
        if (!$tld) {
            return new DomainResult($domain, false, 'tld_error', 'TLD não identificado');
        }

        // Determina URL (Custom ou IANA)
        $rdapServer = 'https://rdap.org/domain/'; // Default universal
        
        // Verifica configurações customizadas
        $customUrl = $this->settingsManager->getCustomRdapUrl($tld);
        if ($customUrl) {
            $rdapServer = $customUrl;
        } elseif (!$this->bootstrapHandler->isValidTld($tld)) {
             return new DomainResult($domain, false, 'unsupported_tld', "Extensão .{$tld} não suportada");
        }

        // 4. Consulta Externa (O Gargalo de I/O)
        $rdapResponse = $this->rdapClient->queryDomain($domain, $rdapServer);

        if (!$rdapResponse) {
            return new DomainResult($domain, false, 'api_error', 'Falha de comunicação com servidor RDAP');
        }

        // 5. Análise de Resposta
        $result = $this->analyzeResponse($domain, $rdapResponse);

        // 6. Salva no Cache
        $this->cacheResult($result);

        return $result;
    }

    /**
     * Analisa o HTTP Code e o JSON para determinar o status real.
     */
    private function analyzeResponse(string $domain, array $response): DomainResult
    {
        $code = $response['status_code'];
        $data = $response['data'] ?? [];

        // CENÁRIO 1: Disponível (Padrão 404)
        if ($code === 404 || isset($response['curl_error'])) {
            return new DomainResult(
                $domain,
                true,
                'available',
                'Disponível',
                $data
            );
        }

        // CENÁRIO 2: Ocupado/Registrado, mas checando se é Premium/Reservado
        if ($code === 200) {
            // Antes de dizer que está "Ocupado", verificamos se é um Premium à venda
            // ou Reservado pela Registry.
            if ($this->detectPremiumHints($data)) {
                return new DomainResult(
                    $domain,
                    false, // Não disponível para registro comum
                    'premium_reserved', // Slug especial para o botão "Sob Consulta"
                    'Domínio Premium ou Reservado',
                    $data
                );
            }

            return new DomainResult(
                $domain,
                false,
                'registered',
                'Registrado',
                $data
            );
        }

        // Fallback
        return new DomainResult($domain, false, 'unknown', "Erro desconhecido ($code)", $data);
    }

    /**
     * Escaneia o JSON em busca de palavras-chave que indiquem Premium/Tier
     */
    private function detectPremiumHints(array $data): bool
    {
        // Palavras-chave comuns em respostas RDAP de domínios premium/reservados
        $keywords = ['premium', 'reserved', 'tier', 'price', 'unavailable', 'buy now'];
        
        // Converte JSON para string para busca rápida (Heurística)
        // Isso é mais rápido que iterar recursivamente em arrays profundos
        $jsonString = json_encode($data);
        
        foreach ($keywords as $word) {
            if (stripos($jsonString, $word) !== false) {
                return true;
            }
        }
        
        // Verificação específica de status ICANN
        if (isset($data['status'])) {
            $statuses = (array)$data['status'];
            foreach ($statuses as $status) {
                if (stripos($status, 'reserved') !== false) return true;
            }
        }

        return false;
    }

    /**
     * Cache inteligente baseado no resultado
     */
    private function cacheResult(DomainResult $result): void
    {
        // Se disponível, cache curto (ex: 1h) para evitar race condition.
        // Se indisponível/premium, cache longo (ex: 24h).
        $ttl = $result->isAvailable() 
            ? $this->settingsManager->getAvailableCacheTime() 
            : $this->settingsManager->getUnavailableCacheTime();

        $this->cacheManager->set($result->getDomain(), $result->toArray(), $ttl);
    }

    private function extractTld(string $domain): ?string
    {
        $parts = explode('.', $domain);
        return count($parts) > 1 ? end($parts) : null;
    }
}



🔎 Análise Crítica do Bloco 2: Precificação e Produto
Neste bloco, precisamos resolver a Dissonância Cognitiva entre o WooCommerce (que gosta de produtos estáticos) e o Mercado de Domínios (que é dinâmico).
O Desafio da Integração LKN (Invoice Payment)
Analisei o código do plugin woocommerce-invoice-payment que você enviou. Ele tem um comportamento rígido:
Ele verifica _lkn-wcip-subscription-product via get_post_meta.
Ele busca o intervalo (interval_number, interval_type) também no meta do produto pai.
O Risco: Se tentarmos vender domínios usando um único "Produto Genérico" e alterarmos o preço dinamicamente no carrinho, precisamos garantir que a Recorrência (o valor da fatura do ano que vem) respeite a Matriz 3x10 ou o preço original contratado.
Se o plugin LKN gerar a fatura futura baseada no "Preço do Produto Pai" (que pode ser R$ 0,00 ou um valor base), você terá prejuízo na renovação. Nossa arquitetura precisa garantir que o preço da renovação seja persistido no Pedido (Order), não apenas no Produto.

📋 Planejamento de Execução: Bloco 2
Entregáveis:
PricingService.php: O cérebro que decide o preço final (cruza Status do Bloco 1 + Matriz 3x10).
WC_Product_Domain.php: A classe do produto que "engana" o LKN Invoice para forçar a assinatura sem configuração manual.
Hooks de Integração LKN: Garantia de que a metadata necessária para a recorrência seja injetada automaticamente.

1. PricingService (O Cérebro Financeiro)
Este serviço isola a regra de negócio. Ele será usado tanto pelo frontend (para mostrar o preço no card) quanto pelo backend (para validar o carrinho).
Destaque Intelectual: Ele retorna isPurchasable = false se o Bloco 1 disse que é premium_reserved, protegendo seu caixa.
PHP
<?php
namespace OwhDomainWhoisRdap\Services;

use OwhDomainWhoisRdap\Models\DomainResult;

class PricingService {
    private $pricingMatrix; // Injetado via construtor (vindo do get_post_meta do produto)

    public function __construct(array $pricingMatrix) {
        $this->pricingMatrix = $pricingMatrix;
    }

    public function getPricingData(DomainResult $result, int $years = 1): array {
        // 1. TRAVA DE SEGURANÇA: Domínios Premium/Reservados
        if ($result->getStatusSlug() === 'premium_reserved') {
            return [
                'is_purchasable' => false,
                'price'          => null,
                'label'          => 'Sob Consulta',
                'action'         => 'contact_us',
                'message'        => 'Este é um domínio premium. Entre em contato para cotação.'
            ];
        }

        // 2. TRAVA DE DISPONIBILIDADE: Domínios Registrados
        if (!$result->isAvailable()) {
            return [
                'is_purchasable' => false,
                'price'          => null,
                'label'          => 'Indisponível',
                'action'         => 'none',
                'message'        => 'Domínio já registrado.'
            ];
        }

        // 3. CÁLCULO DE PREÇO: Matriz 3x10
        // Tenta buscar o preço para o ano solicitado.
        $price = $this->pricingMatrix[$years]['register'] ?? null;

        if ($price === null || $price === '') {
            return [
                'is_purchasable' => false,
                'price'          => null,
                'label'          => 'Erro de Configuração',
                'action'         => 'error',
                'message'        => 'Preço não configurado para este período.'
            ];
        }

        return [
            'is_purchasable' => true,
            'price'          => (float) $price,
            'label'          => 'Adicionar ao Carrinho',
            'action'         => 'add_to_cart',
            'message'        => null
        ];
    }
}

2. WC_Product_Domain & Automação LKN
Aqui fazemos a mágica. Ao salvar um produto do tipo domain, forçamos as configurações que o plugin woocommerce-invoice-payment exige.
Arquivo: includes/class-wc-product-domain.php
PHP
<?php

defined( 'ABSPATH' ) || exit;

class WC_Product_Domain extends WC_Product {

    public function get_type() {
        return 'domain';
    }

    /**
     * Sobrescreve o preço base.
     * Importante: Isso é visual (catálogo). O preço real é calculado no Carrinho.
     */
    public function get_price( $context = 'view' ) {
        // Retorna o preço de 1 ano de registro como base "A partir de..."
        return $this->get_meta( '_regular_price' ); 
    }
    
    // ... (Outros métodos obrigatórios da classe abstrata)
}
Arquivo: admin/class-owh-domain-admin-product.php (Trecho de salvamento)
Aqui está o "pulo do gato" para a integração com LKN Invoice sem dor de cabeça manual.
PHP
/**
 * Hook: woocommerce_process_product_meta_domain
 * Executado quando o admin salva o produto "Domínio".
 */
public function save_domain_product_defaults($post_id) {
    // 1. Salva configurações padrão do Plugin de Domínio
    // ... (Lógica de salvar a matriz de preços 3x10 que já desenhamos) ...

    // 2. FORÇA a integração com LKN Invoice Payment (Assinatura)
    // Isso garante que o produto sempre seja tratado como recorrente.
    
    // Ativa a flag de assinatura do plugin LKN
    update_post_meta($post_id, '_lkn-wcip-subscription-product', 'yes'); // ou 'on', verificar no plugin
    
    // Define o intervalo padrão para 1 Ano (Domínios são anuais)
    update_post_meta($post_id, 'lkn_wcip_subscription_interval_number', '1');
    update_post_meta($post_id, 'lkn_wcip_subscription_interval_type', 'year');
    
    // Sem limite de renovações (domínio é "eterno" enquanto pagar)
    update_post_meta($post_id, 'lkn_wcip_subscription_limit', '0');
}

3. Integração no Carrinho (A Garantia do Preço)
Como o LKN gera a fatura baseada no pedido, precisamos garantir que, quando o usuário adiciona o domínio ao carrinho, o preço dinâmico (calculado pelo PricingService) substitua o preço base do produto.
Arquivo: includes/class-owh-cart-handler.php
PHP
public function override_cart_price( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

    foreach ( $cart->get_cart() as $key => $item ) {
        if ( $item['data']->get_type() !== 'domain' ) continue;

        // Recupera dados personalizados inseridos no AddToCart
        // (Assumimos que o controller do frontend enviou 'custom_price' validado pelo PricingService)
        if ( isset( $item['owh_custom_price'] ) ) {
            $price = floatval( $item['owh_custom_price'] );
            $item['data']->set_price( $price );
            
            // CRÍTICO PARA LKN: 
            // Precisamos garantir que este preço seja persistido como o valor da assinatura.
            // O plugin LKN geralmente pega o total do item do pedido. 
            // Ao setar set_price aqui, o WooCommerce salva isso na Order Line Item, 
            // o que deve ser suficiente para o LKN gerar a fatura correta.
        }
    }
}

Validação do Bloco 2
Este bloco resolve:
Segurança Financeira: O PricingService bloqueia a venda de premium_reserved retornando is_purchasable = false e label "Sob Consulta".
Automação: O admin não precisa configurar manualmente os campos do wc-invoice-payment para cada produto de domínio. Forçamos 1 year e subscription = yes via código.
Flexibilidade: Prepara o terreno para a Página de Resultados (Frontend) consumir o PricingService via API/AJAX para desenhar os cartões de produto.


🏗️ Especificação Técnica: Bloco 2 (Produto & Integração LKN)
Status: Aprovado para Implementação.
2.1. O Cérebro Financeiro (src/Services/PricingService.php)
Responsável por converter o status técnico em regras comerciais.
PHP
<?php
namespace OwhDomainWhoisRdap\Services;

use OwhDomainWhoisRdap\Models\DomainResult;

class PricingService {
    private $pricingMatrix; // Injetado via construtor (vindo do get_post_meta do produto)

    public function __construct(array $pricingMatrix) {
        $this->pricingMatrix = $pricingMatrix;
    }

    public function getPricingData(DomainResult $result, int $years = 1): array {
        // 1. TRAVA DE SEGURANÇA: Domínios Premium/Reservados
        if ($result->getStatusSlug() === 'premium_reserved') {
            return [
                'is_purchasable' => false,
                'price'          => null,
                'label'          => 'Sob Consulta',
                'action'         => 'contact_us',
                'message'        => 'Este é um domínio premium. Entre em contato para cotação.'
            ];
        }

        // 2. TRAVA DE DISPONIBILIDADE: Domínios Registrados
        if (!$result->isAvailable()) {
            return [
                'is_purchasable' => false,
                'price'          => null,
                'label'          => 'Indisponível',
                'action'         => 'none',
                'message'        => 'Domínio já registrado.'
            ];
        }

        // 3. CÁLCULO DE PREÇO: Matriz 3x10
        // Tenta buscar o preço para o ano solicitado.
        $price = $this->pricingMatrix[$years]['register'] ?? null;

        if ($price === null || $price === '') {
            return [
                'is_purchasable' => false,
                'price'          => null,
                'label'          => 'Erro de Configuração',
                'action'         => 'error',
                'message'        => 'Preço não configurado para este período.'
            ];
        }

        return [
            'is_purchasable' => true,
            'price'          => (float) $price,
            'label'          => 'Adicionar ao Carrinho',
            'action'         => 'add_to_cart',
            'message'        => null
        ];
    }
}
2.2. A Automação LKN (admin/class-owh-domain-admin-product.php)
O "Hook Mágico" que garante a recorrência sem intervenção humana.
PHP
// Hook: woocommerce_process_product_meta_domain
public function save_domain_integrity($post_id) {
    // ... (salvamento da matriz de preços 3x10 aqui) ...

    // AUTOMAÇÃO LKN INVOICE PAYMENT
    // Força este produto a ser uma assinatura anual recorrente
    update_post_meta($post_id, '_lkn-wcip-subscription-product', 'yes'); 
    update_post_meta($post_id, 'lkn_wcip_subscription_interval_number', '1');
    update_post_meta($post_id, 'lkn_wcip_subscription_interval_type', 'year');
    update_post_meta($post_id, 'lkn_wcip_subscription_limit', '0'); // 0 = Infinito
}



🚨 Ponto Crítico: A Armadilha da Renovação LKN (Bloco 2)
Você resolveu brilhantemente a venda inicial ($price no carrinho), mas existe um risco alto na renovação automática.
O Problema: A maioria dos plugins de fatura/assinatura (como o LKN Invoice Payment) funciona copiando os dados do pedido original ("Order Item") para gerar a nova fatura.
Cenário: O cliente registra o domínio por R$ 10,00 (Promoção de 1º ano).
Expectativa: A fatura do ano que vem deve ser R$ 100,00 (Preço de Renovação da Matriz 3x10).
Risco Real: Se o plugin LKN apenas duplicar o item do pedido original, ele vai gerar uma fatura de R$ 10,00 para sempre.
A Solução (Ajuste no Bloco 2): Você precisa verificar se o plugin LKN oferece um hook para recalcular o preço na hora de gerar a fatura de renovação. Se não oferecer, você precisará de uma "Gambiarra Controlada" no momento do checkout (woocommerce_checkout_order_processed).
Sugestão de Código (Conceitual): Ao processar o pedido inicial, você deve salvar nos metadados do Item do Pedido qual será o valor da renovação.
PHP
// No Bloco 2 ou 3 (Checkout/Cart Handler)
$renewal_price = $pricingService->getRenewalPrice($domain, $years); // Pega da Coluna 'renew'
$item->add_meta_data('_lkn_recurring_amount', $renewal_price); // *Hipótese: Verificar se o LKN lê isso
Se o LKN não suportar preços variáveis por ciclo, você terá que criar um script cron que intercepta as faturas geradas (status 'pending') e atualiza o valor antes de enviar o e-mail para o cliente.













🏗️ Especificação Técnica: Bloco 3 (Checkout & Guardião)
Este bloco conecta o Carrinho ao Pagamento, garantindo integridade de dados e disponibilidade.
3.1. Configuração do Produto (Reflexo no Bloco 2)
Primeiro, precisamos garantir que o sistema saiba quais produtos exigem documento. No admin do produto (que definimos no Bloco 2), teremos um checkbox simples:
_owh_require_tax_id (Exigir CPF/CNPJ/Tax ID?)
3.2. O Manipulador de Carrinho (includes/class-owh-cart-handler.php)
Quando o produto entra no carrinho, precisamos "carimbar" nele se ele exige documento ou não. Isso evita queries pesadas no checkout.
PHP
/**
 * Hook: woocommerce_add_cart_item_data
 * Armazena metadados vitais na sessão do item do carrinho.
 */
public function add_cart_item_data($cart_item_data, $product_id, $variation_id) {
    // 1. Verifica se o produto exige documento (configurado no Admin)
    $requires_tax_id = get_post_meta($product_id, '_owh_require_tax_id', true);
    
    // 2. Verifica dados vindos do Frontend (Nome do domínio, Anos)
    // (Sanitização omitida para brevidade)
    if (isset($_POST['owh_domain'])) {
        $cart_item_data['owh_domain'] = sanitize_text_field($_POST['owh_domain']);
        $cart_item_data['owh_years']  = intval($_POST['owh_years']);
        $cart_item_data['owh_requires_tax_id'] = ($requires_tax_id === 'yes');
    }
    
    return $cart_item_data;
}
3.3. O Checkout Inteligente (includes/class-owh-checkout-manager.php)
Aqui implementamos a lógica do Checkbox "Titular Diferente".
Lógica Visual (Frontend):
Renderiza checkbox: "Registrar domínio em nome de outra pessoa/empresa?"
Se marcado: Abre uma div com campos: Nome, Sobrenome, Email, CPF/CNPJ (Tax ID).
Se desmarcado: Oculta a div.
Lógica de Validação (Backend): O sistema verifica se há algum domínio no carrinho com owh_requires_tax_id === true.
Se SIM e Checkbox Marcado: Valida o campo owh_registrant_tax_id.
Se SIM e Checkbox Desmarcado: Tenta encontrar o CPF nos campos de Billing do WooCommerce (ex: billing_cpf ou billing_company_id). Se não achar, bloqueia a compra pedindo o dado.
Se NÃO: Ignora validação de documento.
PHP
/**
 * Renderiza campos extras no Checkout
 * Hook: woocommerce_after_checkout_billing_form
 */
public function render_domain_registrant_fields($checkout) {
    // Só exibe se houver domínios no carrinho
    if (!$this->cart_has_domains()) return;

    echo '<div id="owh_domain_registrant_section">';
    
    // 1. O Checkbox de Toggle
    woocommerce_form_field('owh_toggle_registrant', [
        'type'  => 'checkbox',
        'class' => ['form-row-wide'],
        'label' => 'O titular do domínio é diferente do pagador?',
    ], $checkout->get_value('owh_toggle_registrant'));

    // 2. Os Campos (Escondidos via CSS/JS inicialmente)
    echo '<div id="owh_registrant_fields" style="display:none;">';
    
    // Nome, Sobrenome, Email...
    
    // O Campo Crítico: Tax ID
    // Note que não marcamos 'required' => true aqui no array, 
    // pois a validação será condicional via PHP/JS.
    woocommerce_form_field('owh_registrant_tax_id', [
        'type'        => 'text',
        'label'       => 'CPF/CNPJ do Titular',
        'placeholder' => 'Apenas números',
    ], $checkout->get_value('owh_registrant_tax_id'));

    echo '</div></div>';
    
    // Script JS inline simples para o toggle (Show/Hide)
    // ... (Javascript para ouvir o change do checkbox) ...
}
3.4. O Guardião (includes/class-owh-gatekeeper.php)
A barreira final antes de cobrar o cartão.
PHP
/**
 * Hook: woocommerce_checkout_process
 */
public function validate_checkout() {
    $cart = WC()->cart->get_cart();
    $needs_tax_id_check = false;

    // 1. VALIDAÇÃO DE DISPONIBILIDADE (Gatekeeper Síncrono)
    foreach ($cart as $item) {
        if (!isset($item['owh_domain'])) continue;

        if ($item['owh_requires_tax_id']) {
            $needs_tax_id_check = true;
        }

        // Chama o Serviço do Bloco 1 (AvailabilityService)
        // Check Availability FORCE (Bypass Cache se crítico)
        $result = $this->availabilityService->checkAvailability($item['owh_domain']);
        
        // Se mudou para Registrado ou Premium (e não suportamos premium automático)
        if (!$result->isAvailable() || $result->getStatusSlug() === 'premium_reserved') {
            wc_add_notice(sprintf(
                "Atenção: O domínio %s não está mais disponível para registro imediato. Por favor, remova-o do carrinho.",
                $item['owh_domain']
            ), 'error');
        }
    }

    // 2. VALIDAÇÃO DE DADOS (Tax ID)
    if ($needs_tax_id_check) {
        $is_custom_registrant = !empty($_POST['owh_toggle_registrant']);
        
        if ($is_custom_registrant) {
            // Usuário disse que é titular diferente, TEM que preencher o campo extra
            if (empty($_POST['owh_registrant_tax_id'])) {
                wc_add_notice("O CPF/CNPJ do Titular do domínio é obrigatório.", 'error');
            }
        } else {
            // Usuário disse que é o mesmo do Billing.
            // Precisamos garantir que o Billing tem CPF.
            // Tenta pegar de campos padrões de plugins brasileiros comuns (ex: billing_cpf, billing_cnpj)
            $billing_doc = $_POST['billing_cpf'] ?? $_POST['billing_cnpj'] ?? '';
            
            if (empty($billing_doc)) {
                wc_add_notice("Para registrar este domínio, precisamos do seu CPF/CNPJ nos dados de Faturamento ou marque a opção de Titular Diferente.", 'error');
            }
        }
    }
}
⚠️ Ponto de Atenção 1: O "Gatekeeper" Síncrono (Bloco 3)
No arquivo class-owh-gatekeeper.php, você faz isso: $this->availabilityService->checkAvailability($item['owh_domain']);
O Risco: Você está chamando uma API externa (RDAP) durante o processamento do POST do Checkout.
Timeout: Se o servidor RDAP demorar 10 segundos, o checkout do cliente trava girando a bolinha e pode dar "504 Gateway Time-out".
False Negative: Se a API RDAP cair, ninguém compra nada na loja.
A Melhoria: Mantenha a validação, mas defina um Timeout curto (ex: 3 segundos) no RdapClient.
Se a API responder a tempo: Ótimo, valida.
Se der Timeout/Erro: Fail Open ou Fail Closed?
Recomendação: Fail Closed (Bloqueia) com mensagem: "Não foi possível verificar a disponibilidade final deste domínio no momento. Tente novamente em 1 minuto.". É melhor perder a venda momentânea do que vender um domínio ocupado e ter que estornar cartão de crédito (o que gera taxas e desconfiança).







Execução: Bloco 4 (Provisionamento & Integração API)
Objetivo: Transformar um pedido pago em um comando técnico de registro (Domain Register Command), mapeando os dados sanitizados do Bloco 3 para o driver da API correta (ex: ResellerClub, Enom ou Manual).
Arquitetura do Bloco 4:
Drivers (Camada de Abstração): Interfaces para que o plugin não dependa de um único fornecedor.
Mapeador de Dados: A lógica que decide: "Uso o CPF do Billing ou o CPF do Titular Alternativo?"
Trigger de Pagamento: O gatilho que dispara a ação.
Painel de Falhas: Interface para o Admin reenviar o comando se a API falhar.

4.1. A Interface do Registrante (src/Interfaces/RegistrarInterface.php)
Para o MVP, precisamos garantir que o sistema seja agnóstico. Hoje você usa ResellerClub, amanhã pode usar outro.
PHP
<?php
namespace OwhDomainWhoisRdap\Interfaces;

interface RegistrarInterface {
    /**
     * Tenta registrar o domínio.
     * @param array $domainData Dados normalizados (domain, years, contacts[], ns[])
     * @return array ['success' => bool, 'message' => string, 'raw_response' => array]
     * @throws \Exception Se houver erro crítico de comunicação.
     */
    public function registerDomain(array $domainData): array;
    
    /**
     * Valida se o driver tem saldo ou conexão antes de tentar.
     */
    public function checkConnection(): bool;
}
4.2. O Orchestrator (src/Services/DomainProvisioningService.php)
Este é o componente que conecta os pontos. Ele pega o pedido do WooCommerce, extrai os dados do Bloco 3 e envia para o Driver.
Ponto Crítico de Inteligência: Note a lógica getContactData. Ela resolve o dilema "Titular Diferente" aprovado no Bloco 3.
PHP
class DomainProvisioningService {
    private $registrarFactory;

    // ... construtor ...

    public function processOrder(int $order_id) {
        $order = wc_get_order($order_id);
        
        foreach ($order->get_items() as $item) {
            if ($item->get_product()->get_type() !== 'domain') continue;

            // 1. Evita Duplicidade (Idempotência)
            if ($item->get_meta('_owh_provisioning_status') === 'success') continue;

            try {
                // 2. Prepara Dados
                $domainName = $item->get_meta('owh_domain'); // Salvo no Bloco 3
                $years      = $item->get_meta('owh_years');
                
                // Decide qual Registrar usar (Configurado no Produto - Bloco 2)
                $registrarSlug = get_post_meta($item->get_product_id(), '_owh_registrar_slug', true);
                $driver = $this->registrarFactory->get($registrarSlug);

                // 3. Resolução de Contato (A Lógica Híbrida do Bloco 3)
                $contactData = $this->getContactData($order, $item);

                // 4. Execução
                $result = $driver->registerDomain([
                    'domain'   => $domainName,
                    'years'    => $years,
                    'contacts' => $contactData,
                    'ns'       => $this->getDefaultNameservers() // Config global
                ]);

                // 5. Sucesso
                if ($result['success']) {
                    $item->update_meta_data('_owh_provisioning_status', 'success');
                    $item->update_meta_data('_owh_provisioning_log', 'Registrado com sucesso.');
                    $item->save();
                    $order->add_order_note("Domínio $domainName registrado com sucesso via $registrarSlug.");
                } else {
                    throw new \Exception($result['message']);
                }

            } catch (\Exception $e) {
                // 6. Tratamento de Falha (Não cancela o pedido, põe em On-Hold)
                $this->handleFailure($order, $item, $e->getMessage());
            }
        }
    }

    private function getContactData($order, $item) {
        // Verifica se o usuário marcou "Titular Diferente" no checkout (Bloco 3)
        $is_custom = $order->get_meta('_owh_toggle_registrant'); 
        
        if ($is_custom) {
            return [
                'first_name' => $order->get_meta('_owh_registrant_first_name'),
                'tax_id'     => $order->get_meta('_owh_registrant_tax_id'), // CPF Específico
                'email'      => $order->get_meta('_owh_registrant_email'),
                // ... outros campos mapeados
            ];
        } else {
            // Usa dados de Billing do WooCommerce
            return [
                'first_name' => $order->get_billing_first_name(),
                'tax_id'     => $order->get_meta('_billing_cpf'), // Ou o campo padrão usado na loja
                'email'      => $order->get_billing_email(),
                // ... outros campos mapeados
            ];
        }
    }

    private function handleFailure($order, $item, $reason) {
        $item->update_meta_data('_owh_provisioning_status', 'failed');
        $item->update_meta_data('_owh_provisioning_log', $reason);
        $item->save();

        // Altera status do pedido para "On Hold" para chamar atenção do Admin
        $order->update_status('on-hold', "[ERRO DOMÍNIO] Falha ao registrar {$item->get_name()}: $reason");
    }
}
4.3. O Trigger (includes/class-owh-order-handler.php)
Conectamos ao momento exato em que o dinheiro entra.
PHP
/**
 * Hook: woocommerce_payment_complete
 * Também compatível com LKN Invoice se ele disparar payment_complete.
 */
public function on_payment_complete($order_id) {
    // Delega imediatamente para o serviço, sem lógica complexa aqui.
    $this->provisioningService->processOrder($order_id);
}
4.4. A Rede de Segurança (Admin Retry)
APIs falham. Timeout, saldo insuficiente, erro de caractere inválido. Precisamos de uma Meta Box no Pedido (Admin) que mostre o status e um botão "Tentar Novamente".
Visualização na tela de edição do Pedido:
Domínio: meusite.com Status: 🔴 Falha Erro: "Invalid Postal Code format" [ Botão: Corrigir Dados & Tentar Novamente ]
Isso permite que você corrija o CEP do cliente no pedido e clique em reprocessar sem precisar pedir para ele comprar de novo.


⚠️ Ponto de Atenção 2: Mapeamento de Dados no Provisionamento (Bloco 4)
No método getContactData, você retorna um array genérico: ['tax_id' => $cpf, ...]
O Detalhe: Cada Registrante (ResellerClub, Registro.br, Enom) chama esse campo de um jeito diferente na API deles.
ResellerClub pede attr-name="taxid".
Outros podem pedir vat_number ou document.
A Melhoria: A responsabilidade de "traduzir" o array genérico para o formato da API deve ser da classe do Driver (ResellerClubDriver), não do DomainProvisioningService. O Service manda o dado padronizado, e o Driver se vira para formatar. Sua estrutura atual permite isso, apenas certifique-se de não colocar lógica de "if ResellerClub" dentro do Service principal.



✅ Veredito Final
A arquitetura está Aprovada. Ela é sofisticada, modular e resolve o problema principal da migração WHMCS -> Woo.
Resumo das Ações Corretivas sugeridas antes de codar:
Auditoria LKN: Instale o plugin de pagamentos e teste manualmente: crie um pedido de assinatura, force a renovação e veja qual preço ele puxa. Se puxar o preço antigo, planeje o hook de interceptação de preço.
Cache do Gatekeeper: Certifique-se de que o AvailabilityService usado no checkout ignore o cache apenas se ele for muito antigo (> 1 hora), caso contrário, confie no cache de 10 minutos atrás para evitar latência no checkout.
UI Feedback: No Bloco 3 (Frontend), garanta que, se o usuário marcar "Titular Diferente", o formulário valide o CPF/CNPJ via JS antes de enviar o submit, para evitar o reload da página.


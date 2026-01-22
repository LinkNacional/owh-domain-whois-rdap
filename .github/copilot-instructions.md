Diretrizes de Arquitetura e Design: OWH Domain WHOIS RDAP
Versão: 1.0.0 Contexto: Plugin WordPress para verificação de disponibilidade de domínios via protocolo RDAP. Slug do Plugin: owh-domain-whois-rdap
1. Visão Geral e Escopo
Este projeto segue uma Arquitetura Híbrida. O objetivo é criar um verificador de domínios robusto, seguro e escalável, utilizando exclusivamente o protocolo RDAP (Registration Data Access Protocol) e abandonando o protocolo WHOIS legado.
Restrições Funcionais Iniciais
Protocolo: Apenas RDAP (JSON). Não implementar conexões via porta 43 (WHOIS legado).
Vendas: Nesta versão (MVP), o botão de compra deve ser um Link Externo configurável para redirecionamento. A conversão interna para produtos WooCommerce ("Convert to Products") está prevista na arquitetura, mas deve ser tratada como feature futura.
Fonte de Dados: O plugin deve manter e atualizar localmente o arquivo dns.json da IANA.

2. Padrões de Codificação (Arquitetura Híbrida)
O projeto é dividido em duas camadas com regras de sintaxe distintas. O não cumprimento destas regras resultará em débito técnico.
2.1. Camada de Integração WordPress (admin/, public/, includes/)
Esta camada comunica-se com o WordPress. Deve seguir estritamente os WordPress Coding Standards.




Padrão: Snake Case e Studly Caps com underscores.
Classes: Owh_Domain_Whois_Rdap_Admin (Prefixo de arquivo: class-owh-domain-whois-rdap-admin.php).
Métodos/Funções: register_styles(), enqueue_scripts().
Hooks: Registrados via classe Loader do Wordpress.
Regras Específicas: 
Condição comum: if ( $usuario == 'admin' )
class Class_name()
function function_name()
{
}
function owh_domain_whois_rdap_function_run() (global)
{
}
$slug_name_variable; (global for templates/partials)
Nome dos arquivos:
owh-domain-whois-rdap.php (main)
owh-domain-whois-rdap-file.php (versão IONCUBE ou PRO)
class-owh-domain-whois-rdap.php
owh-domain-whois-rdap-script.js
owh-domain-whois-rdap-style.css		
2.2. Camada de Lógica de Negócio (src/)
Esta camada contém a inteligência do domínio, desacoplada do WordPress. Deve seguir PSR-4 e PSR-12.
Padrão: Camel Case e PascalCase (Sem underscores em nomes de classes).
Namespace Raiz: OwhDomainWhoisRdap\
Classes: RdapClient, SettingsManager (Nome do arquivo igual ao da classe: RdapClient.php).
Métodos: checkAvailability(), updateLocalDnsList().
Injeção de Dependência: Obrigatória via construtor. Não usar funções globais do WP (como get_option) diretamente no meio da lógica; injetar valores ou usar adaptadores.


3. Estrutura de Diretórios e Arquivos
A estrutura abaixo substitui os placeholders do documento original pelo slug do projeto atual.
Plaintext
/owh-domain-whois-rdap/
|-- admin/
|   |-- css/
|   |-- js/
|   |-- partials/ (Templates de configurações e metaboxes)
|   |   |-- owh-domain-whois-rdap-admin-display.php
|   |-- class-owh-domain-whois-rdap-admin.php (Controlador das telas de admin [cite: 141, 236])
|
|-- public/
|   |-- css/
|   |-- js/
|   |-- partials/
|   |   |-- owh-domain-whois-rdap-public-search.php (Template do shortcode de busca)
|   |-- class-owh-domain-whois-rdap-public.php (Controlador do frontend e shortcodes)
|
|-- includes/
|   |-- class-owh-domain-whois-rdap.php (Classe Principal - Boot do ServiceContainer)
|   |-- class-owh-domain-whois-rdap-loader.php (Registrador de Hooks)
|   |-- class-owh-domain-whois-rdap-activator.php
|   |-- class-owh-domain-whois-rdap-deactivator.php
|   |-- class-owh-domain-whois-rdap-i18n.php
|
|-- src/ (Lógica de Negócio - Namespace: OwhDomainWhoisRdap)
|   |-- Services/
|   |   |-- ServiceContainer.php (Injeção de dependências)
|   |   |-- RdapClient.php (Consumo da API RDAP JSON)
|   |   |-- BootstrapFileHandler.php (Gerencia o download/parse do dns.json da IANA [cite: 102])
|   |   |-- AvailabilityService.php (Analisa resposta RDAP e define status)
|   |   |-- SettingsManager.php (Abstração das configurações do plugin)
|   |   |-- CacheManager.php (Gerencia transientes de resposta RDAP )
|   |-- Models/
|   |   |-- DomainResult.php (DTO para o resultado da busca)
|   |-- Exceptions/
|   |   |-- RdapConnectionException.php
|   |-- Helpers/
|       |-- DomainValidator.php

4. Definição dos Serviços (src/)
Os seguintes serviços são mandatórios:
4.1. BootstrapFileHandler
Responsável por gerenciar a lista oficial de TLDs e servidores RDAP.
Responsabilidade: Baixar, armazenar localmente e ler o arquivo JSON da IANA (data.iana.org/rdap/dns.json).
Funcionalidade: Deve permitir atualização manual via botão no admin.
4.2. SettingsManager
Centraliza o acesso às opções salvas na tabela wp_options. Deve mapear os seguintes campos vistos na interface:
General: Ativação da pesquisa, definição da página de resultados.
Visual/Texto: Custom Available Text, Unavailable Text, Placeholder Input, Loading Image.
Buy Button: Text, Icon class, Link URL, "Open in new tab" (boolean).
Cache: available_domain_cache_time e unavailable_domain_cache_time.
4.3. RdapClient & AvailabilityService
O núcleo da lógica.
RdapClient: Recebe um domínio e o servidor RDAP correto (vindo do BootstrapFileHandler), faz a requisição HTTP GET e retorna o JSON cru.
AvailabilityService: Interpreta o JSON.
Lógica: Se o status HTTP for 404, geralmente o domínio está Disponível. Se retornar 200 com payload JSON válido, está Registrado (Indisponível).
Cache: Deve consultar o CacheManager antes de fazer a requisição externa para evitar rate-limiting.

5. Regras de Implementação Estritas
5.1. Registro de Hooks (Loader)
NENHUM add_action ou add_filter deve ser colocado nos construtores das classes Admin ou Public.
Correto: Registrar tudo no método define_admin_hooks() ou define_public_hooks()  da classe principal includes/class-owh-domain-whois-rdap.php, delegando a execução para a instância de Admin/Public.
5.2. Manipulação de JSON e TLDs
O sistema deve suportar a atualização da lista de extensões (TLDs).
A busca deve identificar a extensão do domínio digitado (ex: .com, .br) e buscar o endpoint RDAP correspondente no arquivo dns.json local.
Se a extensão não for suportada, retornar mensagem de erro configurável.
5.3. Frontend e Shortcodes
Utilizar shortcodes para renderizar o formulário de busca.
Se o domínio estiver disponível, exibir o "Buy Button" que aponta para o link externo definido por uma variável.
5.4. Segurança e Performance
Sanitização: Todos os inputs de domínio devem ser sanitizados antes da requisição.
5.5. Actions de lançamento
Workflows: Na pasta .github, deve existir uma pasta chamada: workflows, nela deve existir o arquivo main.yml no qual terá a responsabilidade de executar a verificação e geração do .zip do plugin.

6. Exemplo de Fluxo de Trabalho (Copilot Prompting)
Para implementar a funcionalidade de "Cache de Resultados", utilize este raciocínio:
Settings (Admin): "Preciso criar campos no admin para definir o tempo de cache (Available/Unavailable). Vou editar admin/partials/...display.php e src/Services/SettingsManager.php."
Lógica (Src): "Vou criar o serviço CacheManager em src/Services/. Ele terá métodos get($domain) e set($domain, $status, $ttl) usando set_transient."
Integração: "No AvailabilityService, antes de chamar o RdapClient, chamo CacheManager->get(). Se retornar false, faço a requisição e depois salvo com CacheManager->set() usando o tempo definido no SettingsManager."

Nota Final: Siga a estrutura de pastas descrita acima rigorosamente. Não misture convenções de nomenclatura snake_case dentro do diretório src/.


# OWH Domain WHOIS RDAP - Verificador de Domínios Avançado

* Contribuidores: LinkNacional, OWH Group
* Link para doações: [LinkNacional](https://www.linknacional.com.br/)
* Tags: domínios, WHOIS, RDAP, verificação, disponibilidade, DNS
* Testado até: 6.8
* Requer PHP: 7.4
* Tag estável: 1.2.3
* Licença: GPLv2 ou posterior
* URI da licença: [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

## Descrição

O **OWH Domain WHOIS RDAP** é um plugin poderoso e moderno para WordPress que permite verificar a disponibilidade de domínios utilizando exclusivamente o protocolo RDAP (Registration Data Access Protocol), abandonando o protocolo WHOIS legado. 

O plugin oferece uma experiência completa de verificação de domínios com interface intuitiva, blocos Gutenberg avançados e informações detalhadas de WHOIS/RDAP.

## ✅ Principais Funcionalidades

- **Protocolo RDAP Moderno** - Utiliza apenas RDAP (JSON) para consultas mais rápidas e precisas
- **Validação TLD Oficial** - Valida extensões de domínio usando a lista oficial da IANA (data.iana.org/rdap/dns.json)
- **Validação Automática** - Validação de domínios em tempo real com verificação prévia de TLD
- **Suporte a Múltiplas TLDs** - Compatível com centenas de extensões de domínio oficiais
- **Cache Inteligente** - Sistema de cache otimizado para melhor performance
- **Segurança Aprimorada** - Rejeita domínios com TLDs inválidas antes mesmo da consulta RDAP

## Como instalar?

### Instalação via Painel WordPress
1. Acesse o painel de administração do WordPress e vá para **Plugins > Adicionar Novo**.
2. Pesquise por "OWH Domain WHOIS RDAP".
3. Encontre o plugin, clique em **Instalar Agora** e depois em **Ativar**.
4. Configure o plugin em **OWH RDAP Settings**.

### Instalação Manual
1. Faça o download do arquivo `owh-domain-whois-rdap.zip`.
2. No painel do WordPress, vá para **Plugins > Adicionar Novo > Enviar Plugin**.
3. Selecione o arquivo ZIP e clique em **Instalar Agora**.
4. Ative o plugin e configure as opções.

## Configuração

### Configurações Básicas
1. Acesse **OWH RDAP Settings** no menu do WordPress.
2. **Ative a pesquisa** de domínios.
3. Selecione a **página de resultados**.
4. Selecione a **página de detalhes WHOIS**.
5. Configure o **tipo de integração** (Custom URL ou WHMCS).

### Armazenamento de Dados
O plugin armazena dados de configuração TLD no diretório de uploads do WordPress (`wp-content/uploads/owh-domain-whois-rdap/`) em vez da pasta do plugin, garantindo que os dados sejam preservados durante atualizações. Durante a ativação, o plugin migra automaticamente quaisquer dados existentes para o local apropriado.

## Uso

### Shortcodes Disponíveis

#### Formulário de Busca
```php
[owhdwhoisrdap-rdap-whois-search]
```

#### Página de Resultados
```php
[owhdwhoisrdap-rdap-whois-results]
```

#### Detalhes WHOIS Completos
```php
[owhdwhoisrdap-rdap-whois-details]
```


### Blocos Gutenberg

#### Bloco Enhanced
1. No editor Gutenberg, adicione um novo bloco.
2. Procure por **"RDAP Domain Search Enhanced"**.
3. Configure as opções na barra lateral:
   - **Cores**: Texto, fundo, botão
   - **Tipografia**: Tamanho da fonte
   - **Bordas**: Raio, largura, cor
   - **Layout**: Padrão ou inline
   - **CSS**: Código CSS personalizado

## Serviços Externos

Este plugin conecta-se a serviços externos para obter informações de domínio e dados de validação TLD. Esses serviços são essenciais para fornecer informações precisas sobre disponibilidade de domínios.

### IANA RDAP DNS Bootstrap Service
- **Serviço**: Serviço oficial da IANA para descoberta de servidores RDAP
- **Uso**: Obter lista oficial de TLDs e seus servidores RDAP correspondentes
- **Dados enviados**: Nenhum dado pessoal. Download do arquivo público DNS bootstrap
- **URL**: https://data.iana.org/rdap/dns.json
- **Termos de Uso**: https://www.iana.org/help/terms-of-service
- **Política de Privacidade**: https://www.iana.org/privacy-policy

### RDAP.org Universal Bootstrap Server
- **Serviço**: Servidor de bootstrap RDAP universal que agrega informações de todos os servidores RDAP conhecidos
- **Uso**: Endpoint único para consultas RDAP que redireciona automaticamente para o servidor RDAP apropriado para cada TLD
- **Dados enviados**: Apenas o nome do domínio consultado (ex: "example.com"). Nenhum dado pessoal é transmitido
- **Quando é usado**: Como fallback para TLDs padrão quando não há configuração personalizada de servidor RDAP
- **URL**: https://rdap.org/domain/
- **Termos de Uso**: https://rdap.org/ (informações sobre uso responsável)
- **Política de Privacidade**: Consulte https://rdap.org/ para detalhes sobre tratamento de dados

### Servidores RDAP (Vários Operadores de Registro)
- **Serviço**: Servidores RDAP operados por registros de domínio mundiais
- **Uso**: Consulta de informações de registro e status de disponibilidade de domínios
- **Dados enviados**: Apenas o nome do domínio consultado (ex: "example.com")
- **Principais servidores**:
  - Verisign (.com/.net): https://rdap.verisign.com/
  - PIR (.org): https://rdap.publicinterestregistry.org/
  - Afilias (.info/.biz): https://rdap.afilias.info/

**Proteção de Dados**:
- Nenhum dado pessoal é coletado ou transmitido
- Apenas nomes de domínio são enviados para consulta legítima
- Todas as comunicações usam conexões HTTPS seguras
- Cache local reduz solicitações a serviços externos
- O plugin respeita os limites de taxa (rate limiting) dos servidores RDAP

### Suporte Técnico
- [Atendimento LinkNacional](https://www.linknacional.com.br/atendimento/)
- [Issues GitHub](https://github.com/LinkNacional/owh-domain-whois-rdap/issues)
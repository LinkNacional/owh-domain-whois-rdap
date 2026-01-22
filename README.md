# OWH Domain WHOIS RDAP - Verificador de Domínios Avançado

* Contribuidores: LinkNacional, OWH Group
* Link para doações: [LinkNacional](https://www.linknacional.com.br/)
* Tags: domínios, WHOIS, RDAP, verificação, disponibilidade, DNS
* Testado até: 6.8
* Requer PHP: 7.4
* Tag estável: 1.1.1
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

## Uso

### Shortcodes Disponíveis

#### Formulário de Busca
```php
[owh-rdap-whois-search]
```

#### Página de Resultados
```php
[owh-rdap-whois-results]
```

#### Detalhes WHOIS Completos
```php
[owh-rdap-whois-details]
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

### Suporte Técnico
- [Atendimento LinkNacional](https://www.linknacional.com.br/atendimento/)
- [Issues GitHub](https://github.com/LinkNacional/owh-domain-whois-rdap/issues)
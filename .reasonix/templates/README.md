# Calculadora de Frete e Campos Checkout para o Brasil PRO

**Plugin WordPress para WooCommerce** — versão profissional com funcionalidades estendidas.

[![Versão](https://img.shields.io/badge/versão-1.0.0-blue.svg)](https://www.linknacional.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4.svg)](https://php.net)
[![WordPress](https://img.shields.io/badge/WordPress-6.5+-21759B.svg)](https://wordpress.org)
[![Licença](https://img.shields.io/badge/licença-GPL--2.0+-green.svg)](http://www.gnu.org/licenses/gpl-2.0.txt)

---

## 📋 Descrição

A **Calculadora de Frete e Campos Checkout para o Brasil PRO** é a evolução profissional do plugin gratuito, projetada especificamente para lojas de e-commerce brasileiras que utilizam WooCommerce. Esta versão PRO **estende** as funcionalidades da versão gratuita, oferecendo recursos avançados para otimizar a experiência de cálculo de frete e o preenchimento de dados essenciais (CEP, CPF/CNPJ) nas páginas do carrinho e finalização de compra.

> ⚠️ **Este plugin é uma extensão PRO.** Requer que a versão base gratuita esteja instalada e ativa.

### ✨ Funcionalidades da Versão PRO

- 🔄 **Atualizações automáticas** diretamente do painel WordPress, sem necessidade de upload manual
- 📦 **Integração avançada** com múltiplas transportadoras brasileiras
- 🎯 **Máscaras inteligentes** para CEP, CPF e CNPJ nos campos do checkout
- ⚡ **Cálculo de frete otimizado** com cache para tempos de resposta reduzidos
- 📊 **Logs de diagnóstico** via Monolog para facilitar a depuração e o suporte
- 🌐 **Internacionalização completa** (i18n) com suporte a traduções via `.po`/`.mo`
- 🎨 **Personalização visual** estendida dos campos de frete no frontend
- 🔒 **Segurança reforçada** com validações e sanitizações adicionais
- 🧩 **Arquitetura modular PSR-4** — fácil de manter, estender e integrar

---

## 🚀 Requisitos

| Requisito         | Mínimo        |
| ----------------- | ------------- |
| **PHP**           | 8.2 ou superior |
| **WordPress**     | 6.5 ou superior |
| **WooCommerce**   | 9.0 ou superior  |
| **Plugin Base**   | Versão gratuita instalada e ativa |

---

## 📦 Instalação

### Via painel WordPress (recomendado para clientes PRO)

1. Acesse **Plugins → Adicionar Novo → Enviar Plugin**
2. Faça o upload do arquivo `.zip` do plugin PRO
3. Ative o plugin em **Plugins → Plugins Instalados**
4. Certifique-se de que a **versão gratuita** está ativa

### Via FTP

1. Extraia o conteúdo do `.zip` na pasta `/wp-content/plugins/`
2. Ative o plugin pelo painel administrativo
3. Verifique que o plugin base gratuito está instalado

### Via Composer (desenvolvimento)

```bash
composer require lkn/class-woo-better-shipping-calculator-for-brazil-pro
```

---

## ⚙️ Configuração

Após a ativação, acesse **WooCommerce → Configurações → Frete PRO** para ajustar as novas opções disponíveis exclusivamente nesta versão.

---

## 🏗️ Estrutura do Plugin

```
woo-better-shipping-calculator-for-brazil-pro/
├── Admin/                    # Área administrativa
│   ├── css/                  # Estilos do admin
│   ├── js/                   # Scripts do admin
│   └── partials/             # Templates do admin
├── Includes/                 # Núcleo do plugin
│   ├── plugin-updater/       # Sistema de atualização automática (PUC)
│   │   └── Puc/              # Biblioteca Plugin Update Checker
│   └── ...                   # Loader, i18n, Activator, Deactivator, etc.
├── Languages/                # Arquivos de tradução (.pot)
├── Public/                   # Área pública (frontend)
│   ├── css/                  # Estilos do frontend
│   ├── js/                   # Scripts do frontend
│   └── partials/             # Templates do frontend
├── vendor/                   # Dependências Composer
├── composer.json             # Configuração PSR-4 e dependências
└── woo-better-shipping-calculator-for-brazil-pro.php  # Bootstrap
```

---

## 🔧 Stack Técnica

| Tecnologia          | Uso                                   |
| ------------------- | ------------------------------------- |
| **PHP 8.2+**        | Linguagem principal                   |
| **WordPress 6.5+**  | CMS base                              |
| **WooCommerce**     | Plataforma de e-commerce              |
| **Composer**        | Gerenciador de dependências e autoload |
| **Monolog 2.9**     | Sistema de logging avançado           |
| **Phan 5.4**        | Análise estática de código            |
| **PSR-4**           | Autoloading padronizado               |
| **PUC**             | Plugin Update Checker customizado     |

---

## 🔄 Atualizações

A versão PRO conta com um **sistema de atualização automática** integrado, conectado diretamente à API da Link Nacional. As atualizações aparecerão diretamente no painel do WordPress, como qualquer plugin do repositório oficial.

```
https://api.linknacional.com/v2/u/?slug=woo-better-shipping-calculator-for-brazil-pro
```

---

## 🌐 Internacionalização

O plugin é totalmente preparado para tradução. O arquivo base `.pot` está localizado em:

```
Languages/woo-better-shipping-calculator-for-brazil-pro.pot
```

Basta utilizar ferramentas como [Poedit](https://poedit.net/) ou [Loco Translate](https://wordpress.org/plugins/loco-translate/) para gerar os arquivos `.po`/`.mo` no seu idioma.

---

## 🧑‍💻 Para Desenvolvedores

### Autoloading PSR-4

O plugin utiliza namespaces padronizados:

```php
use Lkn\WooBetterShippingCalculatorForBrazilPro\Includes\WooBetterShippingCalculatorForBrazilPro;
use Lkn\WooBetterShippingCalculatorForBrazilPro\Admin\WooBetterShippingCalculatorForBrazilProAdmin;
use Lkn\WooBetterShippingCalculatorForBrazilPro\PublicView\WooBetterShippingCalculatorForBrazilProPublic;
```

### Hooks e Extensibilidade

A arquitetura baseada no WordPress Plugin Boilerplate garante total compatibilidade com hooks e filters do WordPress, permitindo que outros desenvolvedores estendam o plugin facilmente.

### Análise Estática

Para executar a análise estática com Phan durante o desenvolvimento:

```bash
composer require-dev phan/phan
./vendor/bin/phan
```

---

## 📝 Changelog

Veja o histórico completo de alterações em [CHANGELOG.md](CHANGELOG.md).

### v1.0.0 — 15/06/2026
- 🎉 Lançamento inicial da versão PRO
- Sistema de atualização automática integrado
- Arquitetura modular PSR-4
- Logging com Monolog
- Suporte completo a i18n
- Estrutura pronta para extensão de funcionalidades PRO

---

## 👥 Créditos

- **Desenvolvido por:** [Link Nacional](https://www.linknacional.com)
- **Autor:** Link Nacional <contato@linknacional.com>
- **Licença:** [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.txt)

---

## 📞 Suporte

Para clientes da versão PRO, o suporte técnico é oferecido através dos canais oficiais da Link Nacional:

- 🌐 **Site:** [https://www.linknacional.com](https://www.linknacional.com)
- 📧 **E-mail:** contato@linknacional.com
- 🎫 **Ticket:** ticket@linknacional.com

---

> Feito com ❤️ no Brasil para lojistas brasileiros.
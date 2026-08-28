# Proodonto — tema WordPress

Tema mobile-first orientado a performance e conversão, pronto para
desenvolvimento manual com custom fields (ACF Pro) e blocos nativos. Sem
page builder de plugin — edite os arquivos diretamente. O único passo de
build é o CSS utilitário (Tailwind CSS), compilado via npm.

## Estrutura

```
proodonto/
├── style.css                  Cabeçalho do tema + reset + design tokens (CSS vars)
├── functions.php               Bootstrap — só faz require dos arquivos de inc/
├── package.json                 Scripts npm (dev/build) do Tailwind CSS
├── header.php / footer.php     Estrutura mobile-first (menu hambúrguer sem JS bloqueante)
├── index.php / page.php / single.php / archive.php / search.php / 404.php
├── front-page.php              Home: imagem de destaque nativa opcional + conteúdo editável
├── comments.php / sidebar.php
├── page-{slug}.php              Gerado automaticamente por página (ver abaixo)
├── acf-json/                    Field groups do ACF Pro (local JSON, sincroniza sozinho)
├── inc/
│   ├── setup.php                Theme supports, menus, sidebars, tamanhos de imagem
│   ├── performance.php           Limpeza de <head>, defer/async, lazy-load, WebP, heartbeat...
│   ├── enqueue.php               Enfileiramento de CSS/JS + CSS crítico inline + CSS por página/bloco
│   ├── seo.php                   Meta description, canonical, Open Graph, Twitter Card, JSON-LD
│   ├── template-functions.php    Helpers (paginação, breadcrumbs, títulos de arquivo)
│   ├── page-generator.php        Gerador automático de template + CSS por página
│   ├── blocks.php                Registro dos blocos nativos (page-builder via Gutenberg)
│   └── contact-form.php          Handler do formulário do bloco Contato (nonce + honeypot + wp_mail)
├── blocks/
│   ├── cta/                      block.json + render.php — Chamada para ação
│   ├── hero/                     block.json + render.php — Seção de destaque com imagem
│   ├── testimonials/              block.json + render.php — Grade de depoimentos (contêiner)
│   ├── testimonial-item/          block.json + render.php — Um depoimento (filho de testimonials)
│   ├── faq/                      block.json + render.php — Perguntas frequentes (contêiner)
│   ├── faq-item/                  block.json + render.php — Uma pergunta/resposta (filho de faq)
│   ├── services/                  block.json + render.php — Grade de serviços/especialidades (contêiner)
│   ├── service-item/               block.json + render.php — Um serviço (filho de services)
│   └── contact/                  block.json + render.php — Contato/agendamento com formulário nativo
├── template-parts/
│   └── content.php, content-single.php, content-page.php, content-none.php, content-search.php
└── assets/
    ├── tailwind/input.css        Fonte do Tailwind (@import, @source, @theme) — edite este, não o compilado
    ├── css/tailwind.css          Saída compilada do Tailwind (gerado por npm run dev/build, não editar à mão)
    ├── css/main.css              Layout e componentes globais (header, footer, cards...)
    ├── css/critical.css          CSS acima da dobra, injetado inline no <head>
    ├── css/pages/{slug}.css      Um arquivo por página, gerado automaticamente
    ├── css/blocks/{slug}.css     Um arquivo por bloco, carregado só onde o bloco é usado
    ├── css/fonts.css              @font-face (Poppins + Coolvetica) — ver seção "Fontes"
    ├── fonts/poppins/             Arquivos .woff2 self-hosted (incluídos)
    ├── fonts/coolvetica/           Vazia — arquivo licenciado a ser adicionado (ver "Fontes")
    ├── js/main.js                JS único do front-end, vanilla, sem jQuery, carregado com defer
    └── js/blocks-editor.js       Registro dos blocos no Editor (só carrega no wp-admin)
```

## O tema como "page builder" (blocos nativos do Gutenberg)

Em vez de um page builder de plugin ou de template-parts fixos por página,
o conteúdo de cada página é montado **no próprio editor**, inserindo os
blocos abaixo em qualquer ordem, quantas vezes quiser:

| Bloco | O que faz |
|---|---|
| **CTA de Conversão** (`proodonto/cta`) | Título, texto de apoio e botão. |
| **Hero** (`proodonto/hero`) | Imagem + título + subtítulo + botão. |
| **Depoimentos** (`proodonto/testimonials`) | Contêiner — insira quantos blocos **Depoimento** quiser dentro dele (foto, texto, nome, cargo). |
| **Perguntas Frequentes** (`proodonto/faq`) | Contêiner — insira quantos blocos **Pergunta** quiser (accordion nativo via `<details>/<summary>`, zero JS, e gera automaticamente o Schema.org `FAQPage` no `<head>`). |
| **Serviços / Especialidades** (`proodonto/services`) | Contêiner — insira quantos blocos **Serviço** quiser (ícone, título, descrição, link opcional). |
| **Contato / Agendamento** (`proodonto/contact`) | Telefone, WhatsApp, e-mail, endereço, horário, mapa incorporado (opcional) + formulário nativo (nome, telefone, e-mail, mensagem) que envia por `wp_mail()` — sem plugin, sem AJAX. Protegido por nonce + honeypot. |

Todos são **renderizados no servidor** (`render_callback` em PHP, ver
`render.php` de cada bloco) — o JS do editor (`assets/js/blocks-editor.js`)
só cuida da experiência de edição (campos, upload de imagem). Isso
mantém 100% de controle sobre o HTML/CSS gerado, sem o peso de um page
builder de terceiros.

**Testando o formulário de Contato localmente:** no Local (Flywheel), o
site já roda com o Mailpit ativo — qualquer e-mail enviado por
`wp_mail()` fica visível em "Adminer"/"View mail" no próprio app do
Local, sem precisar configurar SMTP real. Em produção, se o host não
entregar e-mails de forma confiável (comum em hospedagem compartilhada),
instale um plugin de SMTP (ex.: WP Mail SMTP) — o formulário continua
funcionando igual, pois só chama `wp_mail()`.

**Performance:** o CSS de cada bloco (`assets/css/blocks/{slug}.css`)
só é carregado nas páginas onde o bloco realmente aparece, detectado
via `has_block()` em `inc/enqueue.php` — não importa em qual página o
editor usou o bloco.

### Criando um novo bloco

1. Duplique uma pasta em `/blocks` (uma sem filhos como `cta/`, ou um
   par contêiner+filho como `testimonials/` + `testimonial-item/`).
2. Ajuste `block.json` (nome, atributos) e `render.php` (função
   `proodonto_render_block_{slug}( $attributes, $content )`).
3. Registre o slug no array `$blocks` em `inc/blocks.php`.
4. Adicione o `registerBlockType` correspondente em
   `assets/js/blocks-editor.js` (copie um bloco existente como
   referência — CTA para blocos simples, Depoimentos/FAQ para
   contêiner + filho repetível).
5. Crie `assets/css/blocks/{slug}.css` — carrega automaticamente
   quando o bloco estiver na página.

Nenhum passo exige build (webpack/JSX): o JS usa
`wp.element.createElement` diretamente, do mesmo jeito que o próprio
Editor faz internamente.

## Geração automática de página + CSS

Ao publicar uma página nova no wp-admin, o tema cria automaticamente:

- `page-{slug}.php` na raiz do tema
- `assets/css/pages/{slug}.css`

Isso funciona porque `page-{slug}.php` já faz parte da **Template
Hierarchy nativa do WordPress** — não precisa de cabeçalho "Template
Name" nem de limpar cache. O CSS correspondente é carregado
automaticamente **só naquela página** por `inc/enqueue.php`.

Regras importantes:

- **Nunca sobrescreve** um arquivo já existente. Uma vez gerado, o
  arquivo é seu para editar como quiser.
- Se apagar o template ou o CSS de uma página manualmente, use o botão
  **"Criar arquivos que faltam"** na caixa lateral **"Proodonto —
  Arquivos da página"**, dentro do editor daquela página.
- Páginas que já existiam antes de instalar o tema são cobertas pelo
  fallback em `save_post_page` (gera na próxima vez que forem salvas).

Para customizar o boilerplate gerado, use os filtros:

```php
add_filter( 'proodonto_page_template_boilerplate', function ( $php, $post, $slug ) {
    // retorne uma string PHP diferente
    return $php;
}, 10, 3 );

add_filter( 'proodonto_page_css_boilerplate', function ( $css, $post, $slug ) {
    return $css;
}, 10, 3 );
```

## Performance

- CSS crítico (`assets/css/critical.css`) é injetado inline no `<head>`
  — mantenha esse arquivo pequeno (só o que aparece sem rolar no
  mobile).
- CSS/JS versionados por `filemtime()`, não por número de versão fixo:
  o cache do navegador quebra automaticamente a cada alteração.
- CSS por página e por bloco: nada carrega em uma URL a menos que seja
  usado nela (ver seções acima).
- `main.js` (front-end) carrega com `defer`, sem jQuery.
- Emojis nativos, embeds, XML-RPC, jQuery Migrate, dashicons no front e
  tags irrelevantes do `<head>` (RSD, wlwmanifest, generator, shortlink)
  são removidos por padrão em `inc/performance.php`.
- Upload de imagens gera automaticamente subtamanhos em WebP.
- Imagem de destaque/hero é pré-carregada (`<link rel="preload">`) e
  recebe `fetchpriority="high"` quando funciona como LCP; demais
  imagens usam `loading="lazy"` nativo do WordPress.
- Recomendado complementar no `wp-config.php`:
  ```php
  define( 'WP_POST_REVISIONS', 5 );
  define( 'AUTOSAVE_INTERVAL', 120 );
  define( 'WP_CACHE', true ); // se usar plugin de cache de página
  ```

## Tailwind CSS

O tema usa **Tailwind CSS 4.x** (via `@tailwindcss/cli`, sem Vite/webpack)
para classes utilitárias. É o único passo de build do projeto — o
restante (JS dos blocos, CSS por página/bloco) continua sem build.

### Instalação (uma vez, por máquina)

```bash
cd wp-content/themes/proodonto
npm install
```

### Uso no dia a dia

```bash
npm run dev     # compila e observa mudanças (roda enquanto você desenvolve)
npm run build   # compila uma vez, minificado (rodar antes de commitar/publicar)
```

- **Edite** `assets/tailwind/input.css` (fonte: `@import`, `@source`,
  `@theme`) — nunca `assets/css/tailwind.css` diretamente, ele é
  sobrescrito a cada `dev`/`build`.
- O Tailwind varre automaticamente os `.php` do tema (raiz, `inc/`,
  `blocks/`, `template-parts/`, incluindo os `page-{slug}.php` gerados
  automaticamente) e os `.js` de `assets/js/`, via `@source` em
  `input.css` — nenhuma classe usada nesses arquivos precisa de
  configuração extra para ser gerada.
- `assets/css/tailwind.css` (o arquivo compilado) **é commitado/enviado
  ao servidor** — não há build step em produção, então rode
  `npm run build` antes de publicar qualquer alteração de classes.
- Os design tokens do tema (`--color-primary`, `--font-base`, etc.) são
  espelhados no bloco `@theme` de `input.css`, gerando utilitários
  (`bg-primary`, `text-primary`...) equivalentes às variáveis já usadas
  em `style.css`/`main.css`.
- Os breakpoints (`sm`/`md`/`lg`/`xl`/`2xl`) foram mantidos no padrão do
  Tailwind (1024px para `lg`, etc.) de propósito — código já existente
  no tema assume esse padrão. Isso roda **em paralelo** ao CSS
  hand-written em `assets/css/*.css`, que usa seus próprios breakpoints
  (600/900/1200px); os dois sistemas não se misturam nem conflitam.
- Ordem de carregamento (`inc/enqueue.php`): `tailwind.css` primeiro
  (inclui o preflight/reset do Tailwind), depois `style.css` e
  `main.css` — então qualquer reset/tipografia definida à mão continua
  vencendo a cascata onde for necessário.

## Fontes

Fontes padrão do projeto: **Poppins** (`--font-base`, corpo do texto) e
**Coolvetica** (`--font-heading`, títulos/destaques). Ambas self-hosted
(`assets/fonts/`, `@font-face` em `assets/css/fonts.css`) — sem
dependência do Google Fonts nem de nenhum CDN.

- **Poppins**: já incluída (pesos 400/500/600/700, subset latin,
  `.woff2`), extraída do pacote oficial `@fontsource/poppins`
  (Google Fonts, licença SIL Open Font License — redistribuição livre).
- **Coolvetica**: fonte comercial da Typodermic Fonts, **não incluída**.
  Existe um pacote `coolvetica` no npm, mas é de um publicador terceiro
  não-oficial reivindicando licença ISC — quase certamente uma
  redistribuição não autorizada de uma fonte comercial, então não foi
  usado. Para ativar:
  1. Obtenha o arquivo com uma licença válida (typodermic.com ou onde
     vocês licenciaram a fonte).
  2. Converta para `.woff2` se necessário e salve como
     `assets/fonts/coolvetica/coolvetica.woff2`.
  3. Pronto — o `@font-face` em `assets/css/fonts.css` já aponta para
     esse caminho; nenhuma outra alteração é necessária.
  4. Até lá, tudo que usa `--font-heading` cai no fallback (Poppins)
     sem erro nenhum.
- O peso 400 do Poppins é pré-carregado (`<link rel="preload">`) via
  `inc/enqueue.php`, já que é o mais usado (corpo do texto) — evita
  FOIT na primeira pintura.

## SEO

`inc/seo.php` adiciona, sem plugin:

- Meta box **SEO** (meta description, canonical customizado, noindex)
  em posts e páginas.
- `<meta name="description">`, `<meta name="robots">`, `<link rel="canonical">`.
- Open Graph e Twitter Card completos (título, descrição, imagem,
  tipo).
- JSON-LD: `Organization` + `WebSite` (com `SearchAction`) na home,
  `BreadcrumbList` nas demais páginas, e `FAQPage` automático quando a
  página usa o bloco de Perguntas Frequentes (ver `inc/blocks.php`).
- Função `proodonto_breadcrumbs()` para usar em qualquer template.
- Filtro `proodonto_json_ld_graphs` para adicionar seus próprios
  schemas (ex.: `Product`, `LocalBusiness`) sem editar `inc/seo.php`.

Se o projeto adotar Yoast SEO ou Rank Math futuramente, remova a linha
`'/inc/seo.php'` do array em `functions.php` — os plugins assumem as
mesmas tags sem duplicar.

## Custom fields (base para o desenvolvimento manual)

Para campos que fazem sentido **por página inteira** (não repetíveis,
não misturados ao fluxo do conteúdo) — como a meta box de SEO em
`inc/seo.php` — o padrão é: `add_meta_box()` + função de render com
`wp_nonce_field()` + hook em `save_post` validando nonce/capability e
sanitizando cada campo antes de `update_post_meta()`. Use `inc/seo.php`
como referência para replicar esse padrão.

Para conteúdo **repetível e posicionável dentro da página** (CTA,
depoimentos, FAQ, e qualquer seção nova que precisar) — o padrão é
**bloco nativo**, não meta box: veja a seção "O tema como page builder"
acima. Se preferir Advanced Custom Fields para algum caso específico,
basta adicionar seu próprio arquivo em `inc/` e o requer em
`functions.php` — nada no restante do tema depende de um jeito só.

## Convenções

- Mobile-first: todo CSS parte do estado mobile; breakpoints usam
  `min-width` (600px tablet, 900px desktop, 1200px desktop grande).
- Design tokens centralizados em `style.css` (`:root { --cor-x: ... }`);
  evite valores soltos nos componentes.
- Único build step do projeto: Tailwind CSS (ver seção acima). JS
  continua sem build (vanilla, servido como está). Se o projeto crescer
  e precisar de mais Sass/bundling, isso é uma decisão consciente a
  tomar depois — não uma dependência padrão do tema.

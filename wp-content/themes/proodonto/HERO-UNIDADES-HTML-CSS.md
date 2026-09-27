# Hero das unidades — gerar HTML e CSS responsivo (Direção A)

> Spec de geração para um agente Claude no VS Code.
> Complementa `HERO-UNIDADES-DIRECAO-A.md` — este documento cobre **só** a produção do markup e do CSS.
> O grupo de campos ACF será criado **manualmente no painel** pelo usuário; §2 lista exatamente o que ele vai criar, e o HTML deve casar com esses nomes.

---

## 1. O que gerar

Dois arquivos, nada além disso:

| Arquivo | Caminho | Papel |
|---|---|---|
| `hero-unidade.html` | `wp-content/themes/proodonto/preview/hero-unidade.html` | Preview estático, abre direto no navegador. Não é carregado pelo WordPress. |
| `hero-unidade.css` | `wp-content/themes/proodonto/assets/css/hero-unidade.css` | CSS de produção. O mesmo arquivo serve o preview e, depois, o tema. |

Crie a pasta `preview/` se não existir e adicione uma linha `preview/` ao `.gitignore` do tema **apenas se** o usuário confirmar que não quer o preview versionado — na dúvida, versione.

O preview linka o CSS por caminho relativo:

```html
<link rel="stylesheet" href="../assets/css/hero-unidade.css">
```

Ele também precisa de Poppins. Como o tema serve a fonte local (`assets/fonts/`, `@font-face` em `assets/css/fonts.css`), o preview deve linkar **esse mesmo arquivo** antes do CSS do hero:

```html
<link rel="stylesheet" href="../assets/css/fonts.css">
```

Não use Google Fonts nem CDN — o tema é self-hosted por decisão registrada no `README.md`.

> **Coolvetica:** `--font-heading` existe mas o arquivo `assets/fonts/coolvetica/coolvetica.woff2` não está instalado, então tudo cai em Poppins hoje. Use `font-family: var(--font-heading, var(--font-base))` nos títulos: quando a fonte for licenciada, o hero acompanha sem nenhuma alteração.

---

## 2. Campos ACF (criados à mão no painel)

O HTML precisa referenciar exatamente estes `name`. Não invente nomes nem abrevie.

Grupo: **Unidade — Hero**, exibido quando `Template da página` for `page-aracaju.php`, `page-lagarto.php` ou `page-simao-dias.php` (três regras em OU).

| name | Label no painel | Tipo | Obrigatório |
|---|---|---|---|
| `hero_eyebrow` | Linha de contexto | Texto | sim |
| `hero_titulo` | Título principal (H1) | Texto | sim |
| `hero_texto` | Texto de apoio | Área de texto | sim |
| `hero_imagem` | Imagem (desktop) | Imagem · retorno **Array** | não |
| `hero_imagem_mobile` | Imagem (mobile) | Imagem · retorno **Array** | não |
| `hero_depoimento_texto` | Depoimento do Google | Área de texto | não |
| `hero_depoimento_autor` | Nome de quem avaliou | Texto | não |
| `hero_depoimento_data` | Mês e ano | Texto | não |

Endereço, link do Maps e link do WhatsApp **não são campos ACF** — saem de `proodonto_get_unit_by_slug()` em `inc/units-map.php`. No preview eles aparecem como placeholder igual aos demais.

---

## 3. Convenção de placeholders

Regra única, que torna a troca por PHP mecânica depois:

- Todo nó que receberá conteúdo dinâmico carrega `data-acf="<name>"` (campo ACF) ou `data-unit="<chave>"` (dado de `proodonto_get_units()`).
- O texto visível é o rótulo entre colchetes, em maiúsculas, começando por `[`.
- Nada de lorem ipsum e nada de conteúdo fictício — nem em comentário.

```html
<h1 class="hero-unidade__titulo" data-acf="hero_titulo">[TÍTULO PRINCIPAL]</h1>
<a class="hero-unidade__cta" data-unit="whatsapp_url" href="#">AGENDAR MINHA AVALIAÇÃO</a>
```

Chaves válidas em `data-unit`: `name`, `address`, `maps_url`, `whatsapp_url`.

Placeholders visíveis recebem a classe utilitária `.is-placeholder` (§5.9), para ficarem óbvios no preview e sumirem sozinhos quando o conteúdo real entrar.

**Conteúdo que NÃO é placeholder** — é literal, escreva no markup:

- Rótulos dos CTAs: `AGENDAR MINHA AVALIAÇÃO`, `Como chegar`, `AGENDAR AVALIAÇÃO` (mobile).
- Linha de prova: `5,0` de nota no Google · `+22 mil` atendimentos · `13 profissionais`, todas as áreas · Pagamento `parcelado`.
- Chips do mobile: `5,0 no Google`, `+22 mil atendimentos`, `13 profissionais`, `Parcelado`.
- A palavra "no Google" ao lado das estrelas do card.

> É **13 profissionais**, nunca "13 especialistas".

---

## 4. Estrutura do HTML

Bloco único, classe raiz `hero-unidade`. BEM com `__` para elemento e `--` para modificador, como o resto do tema.

```
section.hero-unidade
├── div.hero-unidade__inner
│   ├── div.hero-unidade__conteudo
│   │   ├── p.hero-unidade__eyebrow            [ícone pin + data-acf="hero_eyebrow"]
│   │   ├── h1.hero-unidade__titulo            [data-acf="hero_titulo"]
│   │   ├── p.hero-unidade__texto              [data-acf="hero_texto"]
│   │   ├── div.hero-unidade__acoes
│   │   │   ├── a.hero-unidade__cta            [data-unit="whatsapp_url"]
│   │   │   └── a.hero-unidade__cta-secundario [data-unit="maps_url"]
│   │   ├── ul.hero-unidade__chips             [só mobile]
│   │   ├── hr.hero-unidade__divisor           [só desktop]
│   │   └── ul.hero-unidade__prova             [só desktop]
│   └── div.hero-unidade__midia
│       ├── picture > img.hero-unidade__imagem [data-acf="hero_imagem"]
│       └── figure.hero-unidade__depoimento    [data-acf="hero_depoimento_texto"]
│           ├── div.hero-unidade__estrelas
│           ├── blockquote
│           └── figcaption
└── (fora do section) div.hero-unidade-barra   [barra fixa, só mobile]
```

Regras de markup:

- Exatamente **um `<h1>`** no documento.
- A imagem usa `<picture>` com `<source media="(min-width: 900px)">` para a versão desktop e `<img>` para a mobile. Sempre com `width`, `height`, `alt`, `loading="eager"` e `fetchpriority="high"`.
- `alt` no preview: `[TEXTO ALTERNATIVO DA IMAGEM]`.
- Os CTAs levam `target="_blank"` e `rel="noopener"`. **Nunca `noreferrer`** — ele apaga o `Referer` e quebra a atribuição do CRM.
- Ícones: SVG inline, traço, grid de 16/20/24px, `aria-hidden="true"`. Sem emoji, sem biblioteca de ícones.
- Botão de rota da barra fixa e botão de menu: `aria-label` obrigatório.
- No preview, os `href` ficam `#` e o atributo `data-unit` indica de onde virá a URL.

### Modificadores de degradação

| Modificador na raiz | Quando | Efeito |
|---|---|---|
| `hero-unidade--sem-imagem` | `hero_imagem` vazio | coluna única centralizada, `max-width: 70ch`; `.hero-unidade__midia` não renderiza |
| `hero-unidade--sem-depoimento` | `hero_depoimento_texto` vazio | `.hero-unidade__depoimento` não renderiza |

Os elementos são **omitidos do DOM**, não escondidos com `display:none`. No preview, gere o bloco três vezes na mesma página para conferir os três estados: completo, sem imagem, sem depoimento.

---

## 5. CSS

Arquivo próprio, escrito à mão, **mobile-first**, com os breakpoints do tema (`README.md` → Convenções): `min-width` em **600px**, **900px** e **1200px**. Não use `max-width` em media query. Não use Tailwind aqui — este CSS precisa funcionar no preview sem build.

### 5.1 Tokens no topo do arquivo

Declare em `.hero-unidade` (escopo local, sem poluir o `:root` do tema):

```css
.hero-unidade {
  --hu-teal: #049DA5;
  --hu-teal-escuro: #037880;
  --hu-petrol: #063E42;
  --hu-creme: #FAF7F2;
  --hu-tinta: #1A1A1A;
  --hu-texto: #45605E;
  --hu-texto-2: #33514F;
  --hu-texto-3: #8C9D9B;
  --hu-borda: #ECEFEE;
  --hu-borda-btn: #CFE0DF;
  --hu-chip: #F4F8F7;
  --hu-estrela: #F2B705;
  --hu-branco: #FFFFFF;
  --hu-raio: 8px;
}
```

Use sempre as variáveis no resto do arquivo.

### 5.2 Base (mobile, até 599px)

| Elemento | Valores |
|---|---|
| `__inner` | `display:flex; flex-direction:column; gap:16px; padding:22px 20px 0` |
| `__eyebrow` | `font-size:12px; font-weight:600; letter-spacing:.1em; text-transform:uppercase; color:var(--hu-teal)`; ícone 14px, `gap:7px` |
| `__titulo` | `font-size:31px; line-height:1.08; font-weight:600; letter-spacing:-.025em; color:var(--hu-petrol); text-wrap:balance; margin:0` |
| `__texto` | `font-size:15.5px; line-height:1.5; color:var(--hu-texto); margin:0` — `<strong>` em `var(--hu-tinta)` peso 600 |
| `__acoes` | `display:flex; flex-direction:column; gap:10px` |
| `__cta` | largura total, `height:56px; background:var(--hu-teal); color:#fff; border-radius:var(--hu-raio); font-size:16px; font-weight:600; letter-spacing:.02em; text-transform:uppercase`; centralizado com flex, `gap:10px`, ícone 19px |
| `__cta-secundario` | **oculto** no mobile (a rota fica na barra fixa) |
| `__chips` | `display:flex; flex-wrap:wrap; gap:8px; list-style:none; margin:0; padding:0`. Item: `background:var(--hu-chip); border-radius:999px; padding:8px 13px; font-size:12.5px; font-weight:500; color:var(--hu-texto-2)` |
| `__prova` e `__divisor` | `display:none` |
| `__imagem` | `height:246px; width:100%; object-fit:cover; border-radius:14px; display:block` |
| `__depoimento` | `display:none` |

**Ordem visual no mobile:** eyebrow → título → texto → CTA → chips → imagem. Use `order` no flex ou escreva o markup nessa ordem e reordene no desktop — prefira a segunda opção, é mais robusta para leitores de tela.

### 5.3 ≥600px (tablet)

Ainda coluna única. Só respiro e tipografia:

- `__inner`: `padding:32px 32px 0; gap:18px`
- `__titulo`: `font-size:38px`
- `__texto`: `font-size:17px; max-width:56ch`
- `__acoes`: `flex-direction:row; gap:12px`; `__cta` perde a largura total (`width:auto; padding:0 28px`)
- `__imagem`: `height:320px`

### 5.4 ≥900px (desktop)

Vira duas colunas:

- `__inner`: `display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:40px; padding:44px 40px 0; align-items:start`
- `__conteudo`: `display:flex; flex-direction:column; gap:20px`
- `__titulo`: `font-size:44px; line-height:1.05`
- `__texto`: `font-size:18px; max-width:46ch`
- `__cta`: `padding:18px 30px; height:auto`
- `__cta-secundario`: visível — `border:1.5px solid var(--hu-borda-btn); color:var(--hu-petrol); padding:17px 24px; border-radius:var(--hu-raio); font-size:15px; font-weight:600`
- `__chips`: `display:none`
- `__divisor`: visível — `height:1px; background:var(--hu-borda); border:0; margin:12px 0 0`
- `__prova`: visível — `display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:16px 28px; list-style:none; margin:0; padding:0`. Item: `font-size:14.5px; color:var(--hu-texto-2)`, ícone 18px, `gap:10px`; número em `<strong>` `var(--hu-tinta)` peso 600
- `__midia`: `position:relative`
- `__imagem`: `height:480px; border-radius:18px`
- `__depoimento`: visível — `position:absolute; left:-24px; bottom:28px; width:300px; background:var(--hu-branco); border-radius:14px; padding:20px 22px; box-shadow:0 18px 40px -18px rgba(6,62,66,.38), 0 2px 6px rgba(6,62,66,.06); margin:0`
  - estrelas 16px `var(--hu-estrela)`, `gap:3px`; "no Google" 13px/600 `var(--hu-texto-2)`
  - `blockquote`: 14.5px/1.55 `var(--hu-texto)`, `margin:10px 0 0`
  - `figcaption`: 12.5px `var(--hu-texto-3)`, `margin-top:8px`
- `.hero-unidade-barra`: `display:none`

### 5.5 ≥1200px (desktop grande)

- `__inner`: `gap:56px; padding:54px 64px 0; max-width:1440px; margin-inline:auto`
- `__titulo`: `font-size:52px; line-height:1.04`
- `__texto`: `font-size:18.5px`
- `__imagem`: `height:566px`
- `__depoimento`: `left:-34px; bottom:34px; width:330px`

### 5.6 Barra fixa (só abaixo de 900px)

```
position:fixed; inset:auto 0 0 0; z-index:40; height:82px;
background:var(--hu-branco); border-top:1px solid #E7ECEB;
box-shadow:0 -8px 24px -14px rgba(6,62,66,.3);
display:flex; align-items:center; gap:10px; padding:0 16px;
```

- Botão de rota: `flex:0 0 56px; height:52px; border:1.5px solid var(--hu-borda-btn); border-radius:var(--hu-raio)`, só ícone 24px.
- CTA principal: `flex:1; height:52px; background:var(--hu-teal); border-radius:var(--hu-raio)`, ícone + `AGENDAR AVALIAÇÃO` 15.5px/600.
- Estado inicial `transform:translateY(100%)`; com a classe `is-visivel`, `translateY(0)`; `transition:transform .2s ease`.
- Reserve espaço: `body { padding-bottom:82px }` dentro do mesmo bloco `max-width:899px`. No preview, aplique no `<body>` do próprio arquivo.

### 5.7 Acessibilidade

- Alvo de toque mínimo **44×44px** em tudo clicável.
- Nenhuma fonte abaixo de **12px**.
- `:focus-visible` com contorno visível em todos os links e botões: `outline:2px solid var(--hu-teal); outline-offset:3px; border-radius:2px`.
- `@media (prefers-reduced-motion: reduce)` zerando a transição da barra fixa.

### 5.8 Estados

`:hover` e `:focus-visible` do CTA primário escurecem para `var(--hu-teal-escuro)`. O secundário ganha `border-color:var(--hu-teal); color:var(--hu-teal)`. Sem deslocamento de layout em nenhum estado.

### 5.9 Placeholder

```css
.is-placeholder { color: var(--hu-texto-3); font-style: italic; }
.hero-unidade__imagem.is-placeholder,
picture.is-placeholder img {
  background: var(--hu-creme);
  border: 1.5px dashed #D6CFC2;
  object-fit: contain;
}
```

Esta regra fica no fim do arquivo, sob um comentário `/* Placeholders — remover quando o conteúdo real entrar */`, para ser apagada em um passo só.

---

## 6. JavaScript

Um único trecho, inline no `<script>` do preview e, depois, em `assets/js/pages/` no tema. Sem dependência.

`IntersectionObserver` sobre `.hero-unidade__cta`: quando ele sai da viewport, adiciona `is-visivel` na barra fixa; quando volta, remove. Só instancie abaixo de 900px (`window.matchMedia('(max-width: 899px)')`) e reavalie no `resize`. Se `IntersectionObserver` não existir, deixe a barra sempre visível.

Nada além disso. O carrossel do hero da home **não** se aplica aqui — a Direção A tem imagem única, não slider.

---

## 7. Preview

O `hero-unidade.html` é um documento completo (`<!doctype html>`, `lang="pt-BR"`, `<meta name="viewport">`) contendo, na ordem:

1. Um cabeçalho simples do preview, fora do hero, com o nome do arquivo e as três larguras de teste — texto puro, sem estilizar demais.
2. `<section class="hero-unidade">` no estado **completo**.
3. `<section class="hero-unidade hero-unidade--sem-imagem">`.
4. `<section class="hero-unidade hero-unidade--sem-depoimento">`.
5. A barra fixa (uma só, no fim do `<body>`).

Separe os três blocos com uma faixa fina identificando o estado.

---

## 8. Critérios de aceite

Abra o preview em **390px**, **768px**, **1024px** e **1440px**.

- [ ] Em 390px: eyebrow, título, texto e CTA cabem na primeira tela, sem rolagem. A imagem vem depois do botão.
- [ ] Em 390px e 768px: a barra fixa aparece ao rolar e some ao voltar ao topo.
- [ ] Em 1024px e 1440px: duas colunas, card de depoimento sobreposto sem cobrir o rosto da foto nem vazar do `__midia`.
- [ ] O bloco `--sem-imagem` fica em coluna única centralizada e legível, sem moldura vazia sobrando.
- [ ] O bloco `--sem-depoimento` não deixa buraco na coluna da direita.
- [ ] Um único `<h1>` no documento inteiro (os outros dois blocos usam `<p>` com a mesma classe visual, ou `aria-hidden="true"` — escolha e comente a decisão no HTML).
- [ ] Nenhum `rel` contendo `noreferrer`.
- [ ] Nenhuma fonte abaixo de 12px; nenhum alvo de toque abaixo de 44px.
- [ ] Sem rolagem horizontal em nenhuma das quatro larguras.
- [ ] Zero erro no console.
- [ ] `hero-unidade.css` não referencia nenhuma classe do Tailwind e não precisa de `npm run build` para funcionar.

---

## 9. Passo seguinte (não fazer agora)

Quando o preview for aprovado, a conversão para o tema é mecânica:

1. Copiar o markup do bloco completo para `template-parts/hero-unidade.php`.
2. Trocar cada `data-acf="x"` pelo `get_field('x')` correspondente, e cada `data-unit="y"` por `$unit['y']`, com `esc_html()` / `esc_url()` / `esc_attr()`.
3. Aplicar os modificadores `--sem-imagem` e `--sem-depoimento` condicionalmente.
4. Enfileirar `hero-unidade.css` em `inc/enqueue.php` apenas nas páginas de unidade.
5. Remover o `<h1 class="sr-only">` dos templates de unidade.
6. Chamar o partial no topo de cada `page-{slug}.php`, acima de `section.marquee`, e apagar o `section.hero` vazio.

Os detalhes de integração, ACF por código, repasse de UTM e critérios de aceite em produção estão em `HERO-UNIDADES-DIRECAO-A.md`.

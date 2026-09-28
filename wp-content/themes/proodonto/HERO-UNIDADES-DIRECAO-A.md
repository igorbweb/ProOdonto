# Hero das páginas de unidade — Direção A

> Spec de implementação para um agente Claude no VS Code.
> Tema: `wp-content/themes/proodonto` (WordPress + ACF + Tailwind 4).
> Páginas-alvo: `/aracaju/`, `/lagarto/`, `/simao-dias/`.
> Autor do spec: auditoria de conversão de 10–16/09/2026.

---

## 0. Como usar este documento

Execute na ordem: **§1 descoberta → §2 escopo → §3–§9 implementação → §10 build → §11 aceite**.

Regras de execução:

- **Não invente conteúdo.** Toda a copy está literal em §6. Onde há `[COLCHETES]`, o valor é do cliente e deve ficar vazio no código (campo ACF vazio), nunca preenchido com texto fictício.
- **Não altere `page-home.php`, `page-vendas.php` nem `page-sobre.php`.** Eles compartilham classes CSS com as páginas de unidade; qualquer mudança neles é regressão fora de escopo.
- **Pare e pergunte** nos dois pontos marcados com **⚠️ PARE** em §1 e §3.
- Comentários de código em português, como no resto do tema.

---

## 1. Descoberta obrigatória (antes de escrever qualquer código)

O repositório local pode estar atrás da produção. Rode isto primeiro:

```bash
cd wp-content/themes/proodonto
ls page-*.php
grep -rn "section class=\"hero" --include=*.php .
grep -rn "proodonto_get_current_unit_slug\|proodonto_get_unit_by_slug" --include=*.php .
```

Você precisa identificar **qual template renderiza `/aracaju/`**. Três cenários:

| Cenário | O que você vai ver | O que fazer |
|---|---|---|
| **A** | Existem `page-aracaju.php`, `page-lagarto.php`, `page-simao-dias.php` | Edite os três. É o caminho esperado. |
| **B** | Existe só `page-simao-dias.php` (stub de 619 bytes) e nenhum dos outros | ⚠️ **PARE.** O local está atrás da produção. Avise o usuário e peça os arquivos de produção antes de continuar — não crie templates do zero por cima de algo que já existe no servidor. |
| **C** | As seções vêm de blocos Gutenberg em `blocks/` ou de `template-parts/content-page.php` | Adapte §7 para o local certo, mantendo todo o resto do spec. |

### Fatos já confirmados em produção (não precisa reverificar)

- `<section class="hero mb-0">` existe nas três páginas de unidade, mas renderiza com **0 slides e 0px de altura**.
- O `<h1>` dessas páginas é `sr-only` (`position:absolute; clip-path:inset(50%)`, 1×1px) — invisível.
- O hero usa o repeater ACF `banner`, e **o grupo `group_home_banner` está preso a `page_template == page-home.php`** (`inc/acf-fields.php`, linha ~474). Nenhum grupo ACF está ligado às páginas de unidade. **Esta é a causa raiz do hero vazio.**
- O primeiro CTA do corpo aparece a **878px** de rolagem no desktop.
- A fonte `Coolvetica` (`--font-heading`) **não está instalada** (`assets/fonts/coolvetica/coolvetica.woff2` ausente) — tudo cai no fallback Poppins. O spec assume Poppins; se a Coolvetica for licenciada depois, o `@font-face` já existe e nada aqui muda.

---

## 2. Escopo

### Fazer

1. Hero novo e visível nas três páginas de unidade (desktop + mobile).
2. Novo grupo ACF ligado aos templates de unidade.
3. `<h1>` visível no hero, substituindo o `sr-only`.
4. Barra fixa de CTA no mobile.
5. Repasse de parâmetros de campanha nos links do UpView + troca de `rel`.

### Não fazer (fora deste spec)

- Reordenar as demais seções da página.
- Preencher os blocos vazios de Avaliações e Unidades.
- Mexer no Pixel da Meta, GTM ou GA4.
- Corrigir os CTAs da home que apontam para `wa.me/5511300000000`.
- Recriar `/vendas/` (404 em produção).

---

## 3. Design tokens

Valores extraídos do CSS em produção. Use **exatamente** estes — não arredonde.

```
Teal principal (CTA, tarja)     #049DA5
Teal profundo                   #037880
Petrol (títulos)                #063E42
Creme                           #FAF7F2
Texto corpo                     #1A1A1A
Texto secundário                #45605E
Texto terciário                 #33514F
Texto de apoio                  #8C9D9B
Borda clara                     #ECEFEE
Borda de botão fantasma         #CFE0DF
Fundo de chip (mobile)          #F4F8F7
Amarelo da estrela              #F2B705
Branco                          #FFFFFF
```

Tipografia: `--font-base` (Poppins) para tudo. Títulos usam `--font-heading`, que hoje cai em Poppins.

Botão primário (padrão já existente no tema): raio **8px**, `text-transform: uppercase`, peso **600**, `letter-spacing: 0.02em`.

⚠️ **PARE se** `assets/tailwind/input.css` não expuser utilitários para estas cores. Nesse caso use `style="..."` inline com os hex acima — **não** adicione cores novas ao `@theme` sem confirmar com o usuário.

---

## 4. Estrutura do hero — desktop (≥1024px)

Grid de 2 colunas iguais, `gap: 56px`, `padding: 54px 64px 0`.

### Coluna esquerda (na ordem)

| # | Elemento | Especificação |
|---|---|---|
| 1 | Eyebrow | ícone de pin 16px `#049DA5` + texto **12.5px / 600 / letter-spacing .14em / #049DA5** |
| 2 | `<h1>` | **52px / line-height 1.04 / peso 600 / letter-spacing -0.025em / #063E42**, `text-wrap: balance` |
| 3 | Lead `<p>` | **18.5px / 1.55 / #45605E**, `max-width: 46ch`. O trecho "plano escrito e o valor fechado" em `<strong>` com `#1A1A1A` e peso 600 |
| 4 | Linha de CTAs | `display:flex; gap:14px` |
| 4a | CTA primário | ícone WhatsApp 19px + label. `background:#049DA5; color:#fff; padding:18px 30px; border-radius:8px; font-size:16px; font-weight:600; letter-spacing:.02em; text-transform:uppercase` |
| 4b | CTA secundário | `border:1.5px solid #CFE0DF; color:#063E42; padding:17px 24px; border-radius:8px; font-size:15px; font-weight:600` |
| 5 | Divisor | `height:1px; background:#ECEFEE; margin-top:12px` |
| 6 | Linha de prova | grid 2 colunas, `gap:16px 28px`. Cada item: ícone 18px + texto **14.5px / #33514F**, com o número em `<strong>` `#1A1A1A` peso 600 |

### Coluna direita

- Imagem do hero: `border-radius: 18px`, altura **566px**, `object-fit: cover`.
- Card de depoimento sobreposto: `position:absolute; left:-34px; bottom:34px; width:330px`, fundo branco, `border-radius:14px`, `padding:20px 22px`, `box-shadow: 0 18px 40px -18px rgba(6,62,66,.38), 0 2px 6px rgba(6,62,66,.06)`.
  - Linha de 5 estrelas 16px `#F2B705` + "no Google" (13px / 600 / `#33514F`).
  - Texto do depoimento: 14.5px / 1.55 / `#45605E`.
  - Assinatura: 12.5px / `#8C9D9B`.

### Abaixo do hero

A tarja teal existente (`section.marquee`) permanece como está, logo depois do hero.

### Degradação

- **Sem imagem cadastrada:** o hero vira coluna única, centralizado, `max-width: 70ch`. Não renderize moldura vazia nem placeholder.
- **Sem depoimento cadastrado:** oculte o card inteiro. Não renderize card com texto de exemplo.

---

## 5. Estrutura do hero — mobile (<1024px)

`padding: 22px 20px`. Ordem **obrigatória** — a imagem vem **depois** do CTA para o botão não sair da primeira tela:

1. Eyebrow — **12px / 600 / letter-spacing .1em** (não desça abaixo de 12px)
2. `<h1>` — **31px / 1.08 / 600 / -0.025em**
3. Lead — **15.5px / 1.5**
4. CTA primário — **largura total, altura 56px**, 16px
5. Chips de prova — `flex-wrap`, `gap: 8px`. Cada chip: `background:#F4F8F7; border-radius:999px; padding:8px 13px; font-size:12.5px; font-weight:500; color:#33514F`
6. Imagem — altura **246px**, `border-radius:14px`, `object-fit:cover`

O card de depoimento **não aparece** no mobile (vai para a seção de avaliações).

### Barra fixa de CTA

`position: fixed; bottom: 0; left: 0; right: 0; z-index: 40`, só abaixo de 1024px.

- Altura **82px**, fundo branco, `border-top: 1px solid #E7ECEB`, `box-shadow: 0 -8px 24px -14px rgba(6,62,66,.3)`, `padding: 0 16px`, `gap: 10px`.
- Botão de rota: `flex: 0 0 56px; height: 52px; border: 1.5px solid #CFE0DF; border-radius: 8px`, só o ícone de pin 24px, com `aria-label="Como chegar"`.
- CTA principal: `flex: 1; height: 52px; background: #049DA5; border-radius: 8px`, ícone + "AGENDAR AVALIAÇÃO" 15.5px/600.
- Aparece só depois que o CTA do hero sai da tela (`IntersectionObserver`), com transição de 200ms. Respeite `prefers-reduced-motion`.
- Adicione `padding-bottom: 82px` ao `<body>` nessa faixa para a barra não cobrir o rodapé.

**Alvos de toque mínimos de 44px** em tudo que é clicável, inclusive o botão de menu do header.

---

## 6. Copy exata

### Igual nas três unidades

**Lead:**
> Você faz o exame de imagem aqui, é avaliado pela equipe e sai com o **plano escrito e o valor fechado** na mesma visita. Sem custo e sem compromisso de fechar.

**CTA primário:** `AGENDAR MINHA AVALIAÇÃO` (mobile e barra fixa: `AGENDAR AVALIAÇÃO`)
**CTA secundário:** `Como chegar`

**Linha de prova** (desktop, nesta ordem):

| Ícone | Texto |
|---|---|
| estrela | **5,0** de nota no Google |
| check | **+22 mil** atendimentos |
| pessoas | **13 profissionais**, todas as áreas |
| cartão | Pagamento **parcelado** |

**Chips (mobile):** `5,0 no Google` · `+22 mil atendimentos` · `13 profissionais` · `Parcelado`

> **Atenção:** é "13 profissionais", nunca "13 especialistas" — o dado real é 13 profissionais cobrindo todas as especialidades.

### Por unidade

| Slug | Eyebrow | `<h1>` |
|---|---|---|
| `aracaju` | `UNIDADE ARACAJU · BAIRRO JARDINS` | Implante, prótese e ortodontia no mesmo lugar, em Aracaju |
| `lagarto` | `UNIDADE LAGARTO · CENTRO` | Implante, prótese e ortodontia no mesmo lugar, em Lagarto |
| `simao-dias` | `UNIDADE SIMÃO DIAS · CENTRO` | Implante, prótese e ortodontia no mesmo lugar, em Simão Dias |

### Depoimento

Fica **vazio** no código. O cliente cola uma avaliação real do Google no ACF. Sem valor, o card não renderiza. Não escreva depoimento de exemplo em lugar nenhum, nem comentado.

---

## 7. De onde vem cada dado

**Nada de hardcode de endereço, link de mapa ou link de WhatsApp.** O tema já tem fonte única em `inc/units-map.php`:

```php
$slug = proodonto_get_current_unit_slug();          // 'aracaju' | 'lagarto' | 'simao-dias'
$unit = proodonto_get_unit_by_slug( $slug );        // null se não for página de unidade

// $unit['name']         => 'Aracaju'
// $unit['address']      => 'Av. Pres. Tancredo Neves, 1028 - Jardins, Aracaju - SE, 49025-620'
// $unit['maps_url']     => 'https://maps.app.goo.gl/wvRKAiDYqjFTaJbd9'
// $unit['whatsapp_url'] => 'https://api.upviewcrm.com/go/proodonto-aracaju/lp-aracaju'
```

| Dado no hero | Origem |
|---|---|
| CTA primário (href) | `$unit['whatsapp_url']` |
| CTA secundário / botão de rota | `$unit['maps_url']` |
| Nome da cidade | `$unit['name']` |
| Eyebrow, `<h1>`, imagens, depoimento | novo grupo ACF (§8) |
| Números da linha de prova | literais no template (já aparecem em outras seções) |

Se `$unit` vier `null`, **não renderize o hero** — proteja com early return.

---

## 8. Novo grupo ACF

Em `inc/acf-fields.php`, no mesmo padrão dos grupos existentes:

- **key:** `group_unidade_hero`
- **title:** `Unidade — Hero`
- **location:** `page_template == page-aracaju.php` **OU** `page-lagarto.php` **OU** `page-simao-dias.php` (três regras em OR, uma por array externo). No cenário C de §1, ligue por `page_type`/`post` conforme o que existir.

| name | label | type | required | notas |
|---|---|---|---|---|
| `hero_eyebrow` | Linha de contexto | text | 1 | valores em §6 |
| `hero_titulo` | Título principal (H1) | text | 1 | valores em §6 |
| `hero_texto` | Texto de apoio | textarea | 1 | lead de §6; permita `<strong>` via `wp_kses_post` |
| `hero_imagem` | Imagem (desktop) | image | 0 | `return_format => 'array'`, vertical, ≥ 900×1200 |
| `hero_imagem_mobile` | Imagem (mobile) | image | 0 | horizontal, ≥ 1200×760. Vazio → usa a de desktop |
| `hero_depoimento_texto` | Depoimento do Google | textarea | 0 | vazio → card oculto |
| `hero_depoimento_autor` | Nome de quem avaliou | text | 0 | |
| `hero_depoimento_data` | Mês e ano | text | 0 | ex.: `março de 2026` |

Depois de registrar, exporte o JSON para `acf-json/` seguindo o que já existe na pasta.

Popule os valores de §6 nas três páginas via seed ou manualmente — mas **não** crie defaults que mascarem campo vazio de imagem/depoimento: a degradação de §4 precisa funcionar.

---

## 9. Links do UpView — repasse de campanha

Dois ajustes. Ambos são necessários: hoje o CRM recebe o clique sem origem nenhuma e classifica todo lead do site como orgânico.

### 9.1 `rel`

Em todo link para `api.upviewcrm.com`, troque `rel="noopener noreferrer"` por `rel="noopener"`. O `noopener` é o que dá a segurança; o `noreferrer` apaga o `Referer` e destrói a atribuição. Mantenha `target="_blank"`.

### 9.2 Repasse de parâmetros

Novo arquivo `assets/js/utm-passthrough.js`, enfileirado em todas as páginas por `inc/enqueue.php` (no rodapé, sem dependência).

Comportamento:

1. No carregamento, leia de `location.search`: `utm_source`, `utm_medium`, `utm_campaign`, `utm_content`, `utm_term`, `fbclid`, `gclid`.
2. Se houver algum, grave em `sessionStorage` sob `proodonto_attrib` (JSON). Se não houver, leia o que já estiver gravado — assim a origem sobrevive à navegação interna.
3. Acrescente os parâmetros ao `href` de todo `a[href*="api.upviewcrm.com"]`, preservando qualquer query que o link já tenha.
4. Reaplique depois de qualquer inserção dinâmica de links (use `MutationObserver` ou reexecute no `load`).
5. Envolva todo acesso a `sessionStorage` em `try/catch` — navegação anônima pode lançar.

Validado em produção: o link do UpView **aceita parâmetro sem quebrar** e só então injeta o código de clique (`#c_xxxxxxxx`) na mensagem do WhatsApp. Sem parâmetro, não há código e o lead cai como orgânico.

---

## 10. SEO, acessibilidade e build

### H1

- O `<h1>` do hero é o **único** `<h1>` da página.
- **Remova** o `<h1 class="sr-only">` dos templates de unidade. Não deixe os dois.
- O texto do `<h1>` é o de §6 — mantém "Dentista"/cidade fora do título, mas o `<title>` e a meta description do Yoast ficam como estão. Não mexa em `inc/seo.php`.

### Acessibilidade

- `alt` descritivo nas imagens do hero (ex.: `Equipe da PróOdonto atendendo na unidade de Aracaju`). Se o cliente não preencher, use `alt=""` e `role="presentation"` — nunca um alt genérico.
- Estado de foco visível em todos os CTAs.
- Contraste mínimo AA. `#049DA5` com texto branco passa em 16px/600; não reduza esse par abaixo disso.
- `aria-label` no botão de rota da barra fixa e no botão de menu.

### Performance

- Imagem do hero com `loading="eager"` e `fetchpriority="high"` (é o LCP). Todas as outras continuam `lazy`.
- Sirva WebP. A página de Aracaju hoje carrega 1,3 MB de imagem — não aumente esse número.
- Use `<picture>` com `media="(min-width: 1024px)"` para alternar desktop/mobile, com `width`/`height` explícitos para não causar CLS.

### Build e entrega

```bash
cd wp-content/themes/proodonto
npm run build          # obrigatório: tailwind.css é commitado, não há build em produção
git checkout -b feat/hero-unidades
git add -A
git commit -m "feat(unidades): hero com promessa, CTA e prova nas paginas de unidade"
```

Não faça deploy. O ambiente é Local by Flywheel; a subida para a HostGator é passo manual do usuário.

---

## 11. Critérios de aceite

Rode nas três páginas, em 1440px e em 390px. Cole os resultados no fim da execução.

```js
// 1. Um único H1, visível, com altura real
document.querySelectorAll('h1').length === 1
document.querySelector('h1').getBoundingClientRect().height > 20

// 2. Hero com altura real
document.querySelector('section.hero').getBoundingClientRect().height > 400   // desktop
document.querySelector('section.hero').getBoundingClientRect().height > 300   // mobile

// 3. CTA na primeira tela, sem rolagem
const cta = document.querySelector('section.hero a[href*="upviewcrm"]');
cta.getBoundingClientRect().bottom < window.innerHeight

// 4. Nenhum link com noreferrer
[...document.querySelectorAll('a[href*="upviewcrm"]')]
  .every(a => !(a.rel || '').includes('noreferrer'))

// 5. Repasse de campanha — abra a página com ?utm_source=facebook&utm_medium=paid
[...document.querySelectorAll('a[href*="upviewcrm"]')]
  .every(a => a.href.includes('utm_source=facebook'))

// 6. Cada página aponta para a sua própria unidade
document.querySelector('section.hero a[href*="upviewcrm"]').href
// /aracaju/    -> .../go/proodonto-aracaju/lp-aracaju
// /lagarto/    -> .../go/proodonto-lagarto/lp-lagarto
// /simao-dias/ -> .../go/proodonto-simao-dias/lp-simao-dias

// 7. Barra fixa só no mobile e só depois do hero
// 8. Zero erro no console
```

Checklist manual:

- [ ] As três páginas mostram a cidade certa no eyebrow e no H1.
- [ ] Sem imagem cadastrada, o hero fica em coluna única e legível — sem moldura vazia.
- [ ] Sem depoimento cadastrado, o card não aparece — e não há texto de exemplo em lugar nenhum.
- [ ] Nenhuma fonte abaixo de 12px.
- [ ] Nenhum alvo de toque abaixo de 44px no mobile.
- [ ] A home, `/sobre/` e `/vendas/` continuam idênticas ao que eram.
- [ ] `npm run build` rodado e `assets/css/tailwind.css` incluído no commit.

---

## 12. Referência visual

O mockup aprovado da Direção A (desktop, mobile e ordem das seções) está em:

`https://claude.ai/artifact/F1DWY6Az1zRjkxKsapgAQG`

Em caso de conflito entre o mockup e este documento, **este documento vence** — ele carrega os valores medidos do tema em produção.

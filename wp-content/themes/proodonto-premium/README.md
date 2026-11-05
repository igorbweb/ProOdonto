# PróOdonto Premium — tema WordPress clássico

Implementação da **Proposta 2 · atendimento premium** do canvas
"Reformulação PróOdonto — conversão e orgânico" (artboards P2*, 390 e 1440).
PHP clássico + ACF PRO, sem page builder, sem jQuery, sem framework CSS.

## Como ativar (Local)

1. Copie a pasta `proodonto-premium` para `wp-content/themes/` (fica ao lado do tema `proodonto`, que continua intacto).
2. Com o ACF PRO ativo, ative o tema em Aparência → Temas.
3. Abra qualquer tela do painel uma vez (como administrador): o `inc/content-seed.php` grava o conteúdo aprovado **só nos campos vazios** (unidades, números, passos, textos da Home, das 3 unidades e do hub).
4. Configurações → Links permanentes → Salvar (por garantia).
5. Opcional: Aparência → Menus → atribua menus a "Menu principal", "Rodapé — Tratamentos" e "Rodapé — A clínica". Sem menu, o tema monta os links com as páginas que existem.

Voltar ao tema antigo = reativar `proodonto`. Nada do tema antigo é apagado; os campos novos têm nomes próprios.

## Mapa canvas → template

| Canvas (P2) | Template | Campos |
|---|---|---|
| P2Home / P2HomeDesktop (hero opção A) | `page-home.php` (Template Name: Home) | grupo **Home** (`home_*`) |
| P2Vendas / P2VendasDesktop | `page-vendas.php` (Unidade) — /aracaju/, /lagarto/, /simao-dias/ | grupo **Página de unidade** (`uni_*`) |
| P2Hub / P2HubDesktop | `page-servicos.php` (Hub) — /servicos/ | grupo **Hub** (`hub_*`) |
| P2Servico / P2ServicoDesktop | `single-servico.php` — /servicos/<x>/ | grupo **Tratamento** (chaves do tema antigo + `srv_*`) |
| P2Post / P2PostDesktop | `single.php` | grupo **Post** (`post_*`, lateral) |
| FooterDesktop / FooterMobile (alinhado à P2) | `footer.php` | Opções → Rodapé |
| — (adaptação própria) | `home.php`, `archive.php`, `search.php`, `page.php`, `404.php` | editor do WP |

Os mesmos nomes de template do tema antigo foram mantidos de propósito: as páginas que já existem no banco caem no template certo sem reatribuir nada.

## Onde fica cada conteúdo

- **PróOdonto (Opções do Tema)** — fonte única do que se repete: logo, nota resumo, **Unidades** (endereço, horário, UpView, Maps, nota, foto, página), Números, Primeira avaliação, Resultados, Avaliações, Fechamento, Rodapé, Rastreamento.
- **Cada página** — só o que é dela. Títulos aceitam `*destaque*` (vira teal, sem itálico) e `{cidade}` / `{bairro}` nas páginas de unidade.
- **Regra do tema:** campo vazio = elemento fora do HTML. Nenhum texto de conteúdo está no PHP; só microcopy de interface (`__()`: "WhatsApp", "Rota", "Menu"...).

## Interações (assets/js/main.js, ~8 KB)

- Menu do celular em `<dialog>` (Esc, foco preso, fecha no fundo).
- Barra fixa no celular: aparece quando o seletor/topo sai da tela, some no rodapé.
- Teste A/B do topo da unidade (campo "Versão do topo": A, B ou 50/50). No 50/50 a variante é decidida no `<head>` antes da pintura (sem pulo de layout, funciona com cache), fica num cookie de 30 dias e vai ao GTM como `ab_view`.
- CTAs: repassa `utm_*`, `fbclid`, `gclid` para o UpView no clique; `utm_source=blog` nos posts; evento `cta_click` (lugar, unidade, variante) no dataLayer e `Contact` no Pixel. Nada abre em nova aba.
- Post: barra de progresso, sumário com item ativo (sanfona no celular, fixo à esquerda no desktop), copiar link, CTA inserido antes do H2 nº N.
- Resultados abrem num visualizador (`<dialog>`); sem JS, o link abre a imagem.
- Pixel da Meta (se o ID estiver nas Opções) só carrega quando a página fica ociosa.

## Performance

- Celular: o topo não tem imagem — o LCP é o H1. A foto do topo (Home ≥768px, unidade/tratamento ≥1024px) usa `<picture>` com GIF de 1px abaixo do corte (o celular não baixa) + `preload` com `media`.
- Poppins self-hosted 100/400/700 (subset latin, OFL), preload de 400/700. CSS e JS com versão por `filemtime`.
- Sem `wp-block-library` nas páginas que não usam blocos; sem emojis/embeds.

## Fontes — atenção à licença

O tema só aponta para `assets/fonts/coolvetica/coolvetica.woff2` e `assets/fonts/acre/acre-medium.woff2` **se os arquivos existirem**. O zip da Coolvetica recebido em 30/10/2026 traz a EULA Desktop da Typodermic, que proíbe uso como webfont (seção 4.3). Até a licença web: `local()` — quem tem a fonte instalada vê, o resto cai na Poppins. Verificar também a licença da Acre Medium.

## SEO / schema

Tudo entra no grafo do Yoast (`wpseo_schema_graph`), sem `<script>` solto: Organization (+ MedicalOrganization) com subOrganization, Dentist por unidade (Home) ou da unidade (página dela), MedicalProcedure no tratamento, ItemList no hub, FAQPage das perguntas visíveis, `reviewedBy` no post. Sem `aggregateRating`. Sem Yoast, o tema imprime um grafo mínimo próprio.
Copiados sem mudança do tema antigo: `inc/author-credentials.php` (CRO e revisor técnico) e `inc/indexacao.php` (noindex de arquivos, 301 de barra final e posts mesclados).

## Compatibilidade com o conteúdo antigo

- CPT `servico` com o mesmo slug `/servicos/`. Campos do tratamento que já existiam mantêm **nome e chave** (`hero_titulo`, `etapas`, `avaliacoes`, `cta_url_*`, `posts_relacionados`...), então repeaters já preenchidos continuam aparecendo.
- Blocos `proodonto/faq`, `proodonto/faq-item` e `proodonto/cta` continuam registrados (visual novo). Os blocos `hero`, `testimonials`, `services` e `contact` **não** foram portados — não havia uso deles em posts no banco local (verificado em 30/10/2026). Conferir em produção antes de trocar o tema.
- Links antigos no conteúdo (wa.me, número de exemplo, `{{URL_AGENDAMENTO}}`) são trocados pelo UpView no `the_content`.

## acf-export/

JSON dos 6 grupos no formato de importação do ACF, gerado a partir do código. É **referência**: os campos já são registrados por `acf_add_local_field_group()` — não importe junto, ou o painel mostra os grupos em dobro.

## O que ficou para depois / decisões

- Fotos reais: sem foto, os blocos de foto viram o marinho listrado do canvas (decorativo). Recomendada sessão profissional nas 3 unidades.
- Conteúdo clínico dos tratamentos e as respostas que dependem do Dr. Alisson ficam vazios → as seções não aparecem até serem preenchidas.
- Avaliações e resultados: só reais, cadastrados nas Opções. Sem cadastro, as seções somem.
- Breakpoint de tablet (768–1023) é adaptação própria — o canvas só desenha 390 e 1440.
- Verificado em WordPress 6.4 local com um shim do ACF (não incluso): todas as páginas 200, sem notice/warning, sem rolagem horizontal em 390 e 1440, sem erro de JS.

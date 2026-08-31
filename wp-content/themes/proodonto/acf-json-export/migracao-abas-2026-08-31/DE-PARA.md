# De/Para — migração para "1 grupo por página, organizado em abas"

Gerado em 2026-08-31. Nenhuma key ou name de campo mudou — só a
apresentação (de N grupos separados por seção, para 1 grupo por página
com 1 aba por seção antiga).


## Home -> `group_home`

| Grupo antigo (key) | Título antigo | Campos | Aba nova | Key da aba |
| --- | --- | --- | --- | --- |
| `group_home_banner` | Home — Banner (Hero) | 1 | Banner (Hero) | `field_home_tab_banner` |
| `group_home_about` | Home — Sobre | 6 | Sobre | `field_home_tab_about` |
| `group_home_marquee` | Home — Letreiro de diferenciais | 1 | Letreiro de diferenciais | `field_home_tab_marquee` |
| `group_home_results` | Home — Resultados (Antes e Depois) | 5 | Resultados (Antes e Depois) | `field_home_tab_results` |
| `group_home_treatments` | Home — Tratamentos | 4 | Tratamentos | `field_home_tab_treatments` |
| `group_home_shorts` | Home — Shorts (YouTube) | 5 | Shorts (YouTube) | `field_home_tab_shorts` |
| `group_home_steps` | Home — Passo a passo | 3 | Passo a passo | `field_home_tab_steps` |
| `group_home_reviews` | Home — Avaliações | 3 | Avaliações | `field_home_tab_reviews` |
| `group_home_units` | Home — Unidades (cabeçalho) | 4 | Unidades (cabeçalho) | `field_home_tab_units` |
| `group_home_blog` | Home — Blog | 5 | Blog | `field_home_tab_blog` |
| `group_home_closing_cta` | Home — CTA final | 3 | CTA final | `field_home_tab_closing_cta` |

## Sobre / Quem Somos -> `group_sobre`

| Grupo antigo (key) | Título antigo | Campos | Aba nova | Key da aba |
| --- | --- | --- | --- | --- |
| `group_sobre_hero` | Sobre — Hero (topo) | 5 | Hero (topo) | `field_sobre_tab_hero` |
| `group_sobre_historia` | Sobre — Nossa História | 4 | Nossa História | `field_sobre_tab_historia` |
| `group_sobre_valores` | Sobre — Missão, Visão e Valores | 7 | Missão, Visão e Valores | `field_sobre_tab_valores` |
| `group_sobre_numeros` | Sobre — Números | 3 | Números | `field_sobre_tab_numeros` |
| `group_sobre_equipe` | Sobre — Corpo Clínico / Equipe | 5 | Corpo Clínico / Equipe | `field_sobre_tab_equipe` |
| `group_sobre_seguranca` | Sobre — Biossegurança | 4 | Biossegurança | `field_sobre_tab_seguranca` |
| `group_sobre_units` | Sobre — Unidades (cabeçalho) | 4 | Unidades (cabeçalho) | `field_sobre_tab_units` |
| `group_sobre_faq` | Sobre — Perguntas Frequentes | 3 | Perguntas Frequentes | `field_sobre_tab_faq` |
| `group_sobre_cta` | Sobre — CTA final | 3 | CTA final | `field_sobre_tab_cta` |

## Página de Vendas -> `group_vendas`

| Grupo antigo (key) | Título antigo | Campos | Aba nova | Key da aba |
| --- | --- | --- | --- | --- |
| `group_vendas_cta` | Página de Vendas — CTA | 1 | CTA | `field_vendas_tab_cta` |
| `group_vendas_marquee` | Página de Vendas — Letreiro de diferenciais | 1 | Letreiro de diferenciais | `field_vendas_tab_marquee` |
| `group_vendas_about` | Página de Vendas — Sobre (texto) | 5 | Sobre (texto) | `field_vendas_tab_about` |
| `group_vendas_about_gallery` | Página de Vendas — Galeria "Sobre" | 1 | Galeria "Sobre" | `field_vendas_tab_about_gallery` |
| `group_vendas_results` | Página de Vendas — Resultados (Antes e Depois) | 5 | Resultados (Antes e Depois) | `field_vendas_tab_results` |
| `group_vendas_treatments` | Página de Vendas — Tratamentos | 5 | Tratamentos | `field_vendas_tab_treatments` |
| `group_vendas_shorts` | Página de Vendas — Shorts (YouTube) | 6 | Shorts (YouTube) | `field_vendas_tab_shorts` |
| `group_vendas_steps` | Página de Vendas — Passo a passo | 4 | Passo a passo | `field_vendas_tab_steps` |
| `group_vendas_reviews` | Página de Vendas — Avaliações | 4 | Avaliações | `field_vendas_tab_reviews` |
| `group_vendas_units` | Página de Vendas — Unidades (cabeçalho) | 5 | Unidades (cabeçalho) | `field_vendas_tab_units` |
| `group_vendas_closing_cta` | Página de Vendas — CTA final | 3 | CTA final | `field_vendas_tab_closing_cta` |

## O que NÃO mudou
- Todas as `key` e `name` de cada campo (inclusive sub_fields de repeaters/galleries).
- O `location` (mesmo `page_template` de cada página).
- Os dados já salvos no banco (postmeta) — o valor é lido pela `name`, não pelo grupo.

## O que mudou
- Os N grupos `group_<pagina>_<secao>` viram 1 grupo `group_<pagina>` só.
- Cada seção antiga virou uma aba (`type: tab`) dentro desse grupo único.
- `group_theme_options`, `group_theme_options_agregador` e `group_theme_options_footer` NÃO foram tocados (não são campos de página).

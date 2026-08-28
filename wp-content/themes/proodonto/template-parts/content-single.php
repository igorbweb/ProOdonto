<?php
/**
 * Conteúdo de post singular — hierarquia pensada para leitura, retenção
 * e rastreio (SEO/GEO): breadcrumb → categoria → título → meta (data de
 * publicação/atualização + tempo de leitura) → resumo (quando o autor
 * escreveu um excerpt manual — dá uma resposta direta e citável logo no
 * topo, útil tanto pra quem só quer o essencial quanto pra IAs que
 * resumem o conteúdo) → imagem grande → conteúdo com tipografia rica →
 * CTA de agendamento → autor com credenciais (cargo/CRO, quando
 * preenchidos no perfil — inc/author-credentials.php), marcado como
 * <address>, semântica correta para dados de autoria → aviso de
 * conformidade (responsável técnico + "conteúdo informativo" + data de
 * revisão), exigido pelas normas de publicidade do CFO em página de saúde.
 *
 * Os dados estruturados (JSON-LD BlogPosting) e as tags article:* no
 * <head> ficam em inc/seo.php — aqui só o que é visível.
 */

defined( 'ABSPATH' ) || exit;

$proodonto_categories     = get_the_category();
$proodonto_published_date = get_the_date();
$proodonto_modified_date  = get_the_modified_date();
$proodonto_author_id      = (int) get_the_author_meta( 'ID' );
$proodonto_author_url     = get_author_posts_url( $proodonto_author_id );
$proodonto_credentials    = proodonto_get_author_credentials( $proodonto_author_id );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>

	<header class="entry-header">

		<?php proodonto_breadcrumbs(); ?>

		<?php if ( $proodonto_categories ) : ?>
			<div class="entry-categories">
				<?php foreach ( $proodonto_categories as $proodonto_category ) : ?>
					<a href="<?php echo esc_url( get_category_link( $proodonto_category ) ); ?>" class="entry-category-pill">
						<?php echo esc_html( $proodonto_category->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

		<div class="entry-meta">
			<span>
				<?php esc_html_e( 'Por', 'proodonto' ); ?>
				<?php if ( $proodonto_author_url ) : ?>
					<a href="<?php echo esc_url( $proodonto_author_url ); ?>" rel="author"><?php the_author(); ?></a>
				<?php else : ?>
					<?php the_author(); ?>
				<?php endif; ?>
			</span>
			<span class="entry-meta__dot" aria-hidden="true">·</span>
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( $proodonto_published_date ); ?></time>
			<?php if ( $proodonto_modified_date !== $proodonto_published_date ) : ?>
				<span class="entry-meta__dot" aria-hidden="true">·</span>
				<span>
					<?php esc_html_e( 'Atualizado em', 'proodonto' ); ?>
					<time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php echo esc_html( $proodonto_modified_date ); ?></time>
				</span>
			<?php endif; ?>
			<span class="entry-meta__dot" aria-hidden="true">·</span>
			<span>
				<?php
				printf(
					/* translators: %d: minutos de leitura */
					esc_html( _n( '%d min de leitura', '%d min de leitura', proodonto_reading_time(), 'proodonto' ) ),
					proodonto_reading_time()
				);
				?>
			</span>
		</div>

	</header>

	<?php if ( has_excerpt() ) : ?>
		<p class="entry-summary">
			<?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?>
		</p>
	<?php endif; ?>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="entry-thumb">
			<?php the_post_thumbnail( 'proodonto-hero', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
		</div>
	<?php endif; ?>

	<div class="entry-content">
		<?php
		the_content();

		wp_link_pages(
			array(
				'before' => '<nav class="page-links">' . esc_html__( 'Páginas:', 'proodonto' ),
				'after'  => '</nav>',
			)
		);
		?>
	</div>

	<div class="entry-cta">
		<h2 class="entry-cta__title"><?php esc_html_e( 'Ficou com alguma dúvida?', 'proodonto' ); ?></h2>
		<p class="entry-cta__text"><?php esc_html_e( 'Fale com a nossa equipe pelo WhatsApp e agende uma avaliação gratuita.', 'proodonto' ); ?></p>
		<a
			href="https://wa.me/<?php echo esc_attr( function_exists( 'proodonto_get_whatsapp' ) ? proodonto_get_whatsapp() : '' ); ?>?text=<?php echo esc_attr( rawurlencode( sprintf( __( 'Olá! Vim do blog e gostaria de agendar uma avaliação na %s.', 'proodonto' ), wp_strip_all_tags( get_bloginfo( 'name' ) ) ) ) ); ?>"
			target="_blank"
			rel="noopener noreferrer"
			class="cta cta--whatsapp"
		>
			<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true">
				<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .105 5.36.102 11.943c0 2.105.549 4.16 1.595 5.976L0 24l6.335-1.652a11.882 11.882 0 0 0 5.71 1.447h.006c6.585 0 11.941-5.36 11.944-11.943a11.87 11.87 0 0 0-3.475-8.403"/>
			</svg>
			<?php esc_html_e( 'Falar no WhatsApp', 'proodonto' ); ?>
		</a>
	</div>

	<address class="entry-author">
		<div class="entry-author__avatar">
			<?php echo get_avatar( get_the_author_meta( 'ID' ), 56 ); ?>
		</div>
		<div class="entry-author__body">
			<p class="entry-author__name">
				<?php if ( $proodonto_author_url ) : ?>
					<a href="<?php echo esc_url( $proodonto_author_url ); ?>" rel="author"><?php the_author(); ?></a>
				<?php else : ?>
					<?php the_author(); ?>
				<?php endif; ?>
			</p>
			<?php if ( $proodonto_credentials['job_title'] || $proodonto_credentials['cro'] ) : ?>
				<p class="entry-author__credentials">
					<?php
					echo esc_html(
						implode(
							' · ',
							array_filter(
								array(
									$proodonto_credentials['job_title'],
									$proodonto_credentials['cro'] ? sprintf( /* translators: %s: número do CRO */ __( 'CRO %s', 'proodonto' ), $proodonto_credentials['cro'] ) : '',
								)
							)
						)
					);
					?>
				</p>
			<?php endif; ?>
			<p class="entry-author__bio">
				<?php
				$proodonto_author_bio = get_the_author_meta( 'description' );
				echo esc_html( $proodonto_author_bio ? $proodonto_author_bio : __( 'Conteúdo revisado pela nossa equipe de especialistas.', 'proodonto' ) );
				?>
			</p>
		</div>
	</address>

	<?php if ( is_singular( 'post' ) ) : ?>
		<p class="entry-disclaimer">
			<?php
			printf(
				/* translators: 1: nome do responsável técnico, 2: CRO, 3: data da última revisão */
				esc_html__( 'Responsável técnico: %1$s%2$s. Este conteúdo é informativo e não substitui uma consulta odontológica. Última revisão em %3$s.', 'proodonto' ),
				esc_html( get_the_author() ),
				$proodonto_credentials['cro'] ? esc_html( sprintf( ' (CRO %s)', $proodonto_credentials['cro'] ) ) : '',
				esc_html( $proodonto_modified_date )
			);
			?>
		</p>
	<?php endif; ?>

</article>

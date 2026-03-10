<?php
/**
 * content-list.php — Card de notícia em modo "Portal Lista" (artigo completo inline)
 */
$cats       = get_the_category();
$cat        = $cats ? $cats[0] : null;
$thumb_url  = get_the_post_thumbnail_url( get_the_ID(), 'large' );
$views      = (int) get_post_meta( get_the_ID(), 'post_views_count', true );
$author_id  = get_the_author_meta( 'ID' );
$author_av  = get_avatar_url( $author_id, [ 'size' => 40 ] );
?>
<article class="list-article" id="post-<?php the_ID(); ?>" <?php post_class(''); ?> itemscope itemtype="https://schema.org/NewsArticle">

    <?php if ( $cat ) : ?>
    <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" class="news-card-cat" style="margin-bottom: 1rem; display:inline-block;">
        <?php echo esc_html( $cat->name ); ?>
    </a>
    <?php endif; ?>

    <h2 class="list-article__title" itemprop="headline">
        <a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
    </h2>

    <!-- Meta info -->
    <div class="news-card-meta" style="margin-bottom: 1.5rem;">
        <img src="<?php echo esc_url( $author_av ); ?>" alt="<?php the_author(); ?>" style="width:28px; height:28px; border-radius:50%; object-fit:cover; vertical-align:middle; margin-right:6px;">
        <span><?php the_author(); ?></span>
        <span>·</span>
        <time datetime="<?php echo get_the_date( 'c' ); ?>" itemprop="datePublished">
            <?php echo get_the_date( 'd/m/Y \à\s H\hi' ); ?>
        </time>
        <?php if ( $views ) : ?>
        <span>·</span>
        <span>
            <span class="material-symbols-outlined" style="font-size:0.9rem; vertical-align:middle;">visibility</span>
            <?php echo number_format( $views, 0, ',', '.' ); ?> views
        </span>
        <?php endif; ?>
        <span>·</span>
        <span><?php echo cdn_reading_time(); ?></span>
    </div>

    <!-- Imagem de destaque -->
    <?php if ( $thumb_url ) : ?>
    <a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
        <figure class="list-article__cover" style="margin: 0 0 2rem; border-radius: var(--radius-md); overflow:hidden; max-height: 480px;">
            <img src="<?php echo esc_url( $thumb_url ); ?>"
                 alt="<?php the_title_attribute(); ?>"
                 itemprop="image"
                 style="width:100%; height:100%; object-fit:cover; display:block; transition: transform 0.4s ease;"
                 onmouseover="this.style.transform='scale(1.02)'"
                 onmouseout="this.style.transform='scale(1)'">
        </figure>
    </a>
    <?php endif; ?>

    <!-- Conteúdo completo do post -->
    <div class="list-article__content entry-content" itemprop="articleBody" style="font-size: 1.1rem; line-height: 1.85; color: var(--color-text); max-width: 820px;">
        <?php the_content(); ?>
    </div>

    <!-- Rodapé do artigo com link para ver no single -->
    <div style="margin-top: 2rem; display:flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <a href="<?php the_permalink(); ?>" class="btn" style="display:inline-flex; align-items:center; gap:0.4rem; font-size:0.9rem; padding: 0.6rem 1.2rem; border-radius: 50px;">
            <span class="material-symbols-outlined" style="font-size:1rem;">open_in_new</span>
            Ver página completa
        </a>
        <?php if ( $cat ) : ?>
        <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" style="font-size:0.85rem; color:var(--color-text-muted); text-decoration:none;">
            Mais em <strong><?php echo esc_html( $cat->name ); ?></strong> →
        </a>
        <?php endif; ?>
    </div>

</article>

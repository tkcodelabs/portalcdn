<?php
/**
 * search.php — Resultados de busca
 */
get_header();
?>
<!-- Hero Banner (Busca) -->
<section class="category-hero" aria-labelledby="cat-headline" style="border-radius:var(--radius-lg);margin:2rem auto;max-width:var(--max-width)">
    <div class="hero-bg search-bg"></div>
    <div class="hero-particles" aria-hidden="true" id="hero-particles"></div>
    
    <div class="hero-content">
        <h1 class="hero-title" id="cat-headline">Você buscou por "<span class="search-term"><?php echo esc_html( get_search_query() ); ?></span>"</h1>
        <p class="hero-desc">
            <?php
            global $wp_query;
            $count = $wp_query->found_posts;
            echo $count === 1 ? 'Encontramos apenas 1 resultado correspondente.' : 'Foram encontrados ' . $count . ' resultados para sua busca no portal.';
            ?>
        </p>
    </div>
</section>

<main id="main-content" class="container">
    <div class="archive-layout">
        <div>

            <?php if ( have_posts() ) : ?>
            <div class="archive-list">
                <?php while ( have_posts() ) : the_post();
                    $cats = get_the_category();
                    $cat  = $cats ? $cats[0] : null;
                ?>
                <article class="archive-item">
                    <?php if ( has_post_thumbnail() ) : ?>
                    <a href="<?php the_permalink(); ?>" class="archive-item-thumb" aria-hidden="true" tabindex="-1">
                        <?php the_post_thumbnail( 'cdn-archive', [ 'loading' => 'lazy' ] ); ?>
                    </a>
                    <?php endif; ?>
                    <div>
                        <?php if ( $cat ) : ?>
                        <span class="news-card-category <?php echo esc_attr( cdn_category_color( $cat->slug ) ); ?>"><?php echo esc_html( $cat->name ); ?></span>
                        <?php endif; ?>
                        <h2 class="archive-item-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <p class="archive-item-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></p>
                        <p class="archive-item-meta"><time datetime="<?php echo get_the_date( 'c' ); ?>"><?php echo get_the_date(); ?></time></p>
                    </div>
                </article>
                <?php endwhile; ?>
            </div>
            <div style="margin-top:2rem"><?php cdn_pagination(); ?></div>

            <?php else : ?>
            <div style="text-align:center;padding:4rem 0">
                <span class="material-symbols-outlined" style="font-size:4rem;color:var(--color-border);display:block;margin-bottom:1rem">search_off</span>
                <p style="color:var(--color-text-muted)">Nenhum resultado para <strong>"<?php echo esc_html( get_search_query() ); ?>"</strong>.</p>
                <p style="color:var(--color-text-muted);margin-top:.5rem">Tente outras palavras-chave.</p>
            </div>
            <?php endif; ?>
        </div>
        <?php get_sidebar(); ?>
    </div>
</main>
<?php get_footer(); ?>

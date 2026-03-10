<?php
/**
 * archive.php — Arquivo/Categoria/Tag
 */
get_header();
$term     = get_queried_object();
$cat_name = is_category() ? single_cat_title( '', false ) : ( is_tag() ? single_tag_title( '', false ) : get_the_archive_title() );
?>
<main id="main-content" class="archive-page inst-page">

    <!-- HERO DE CATEGORIA -->
    <section class="inst-hero cat-hero" style="padding: 4rem 0 3rem; margin-bottom: 2rem;">
        <div class="inst-hero-bg" aria-hidden="true">
            <?php
            // Cor baseada no slug da categoria para os orbs
            $slug        = is_category() ? get_queried_object()->slug : '';
            $color_class = cdn_category_color( $slug );
            $orb_color   = match( $color_class ) {
                'economia'   => 'rgba(0,123,255,.3)',
                'esportes'   => 'rgba(40,167,69,.3)',
                'cultura'    => 'rgba(255,193,7,.3)',
                'politica'   => 'rgba(220,53,69,.3)',
                'tecnologia' => 'rgba(111,66,193,.3)',
                default      => 'rgba(255,255,255,.15)',
            };
            ?>
            <div class="hero-orb hero-orb-1" style="background:<?php echo $orb_color; ?>"></div>
            <div class="hero-orb hero-orb-2" style="background:rgba(0,0,0,.25)"></div>
            <div class="hero-noise"></div>
        </div>
        <div class="container inst-hero-inner">
            <div class="inst-hero-badge">
                <?php if ( is_category() ) : ?>📂 Categoria Editorial
                <?php elseif ( is_tag() ) : ?>🏷️ Assunto em pauta
                <?php elseif ( is_author() ) : ?>✍️ Colunista
                <?php else : ?>📰 Acervo de Notícias<?php endif; ?>
            </div>
            <h1 class="inst-hero-title"><?php echo esc_html( $cat_name ); ?></h1>
            <?php $desc = get_the_archive_description(); if ( $desc ) : ?>
                <div class="inst-hero-sub" style="margin-bottom:0"><?php echo wp_kses_post( $desc ); ?></div>
            <?php else : ?>
                <p class="inst-hero-sub" style="margin-bottom:0">Acompanhe as reportagens e materiais exclusivos do Correio do Norte sobre <?php echo esc_html( mb_strtolower( $cat_name ) ); ?>.</p>
            <?php endif; ?>
        </div>
    </section>

    <div class="container archive-layout">
        <div>

            <?php if ( have_posts() ) : ?>
            <div class="archive-list">
                <?php while ( have_posts() ) : the_post();
                    $cats = get_the_category();
                    $cat  = $cats ? $cats[0] : null;
                ?>
                <article class="archive-item" itemscope itemtype="https://schema.org/NewsArticle">
                    <?php if ( has_post_thumbnail() ) : ?>
                    <a href="<?php the_permalink(); ?>" class="archive-item-thumb" aria-hidden="true" tabindex="-1">
                        <?php the_post_thumbnail( 'cdn-archive', [ 'itemprop' => 'image', 'loading' => 'lazy' ] ); ?>
                    </a>
                    <?php endif; ?>
                    <div>
                        <?php if ( $cat ) : ?>
                        <span class="news-card-category <?php echo esc_attr( cdn_category_color( $cat->slug ) ); ?>"><?php echo esc_html( $cat->name ); ?></span>
                        <?php endif; ?>
                        <h2 class="archive-item-title" itemprop="headline">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <p class="archive-item-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 22 ); ?></p>
                        <p class="archive-item-meta">
                            <time datetime="<?php echo get_the_date( 'c' ); ?>" itemprop="datePublished"><?php echo get_the_date(); ?></time>
                            &middot; <?php echo cdn_reading_time(); ?>
                        </p>
                    </div>
                </article>
                <?php endwhile; ?>
            </div>
            <div style="margin-top:2rem"><?php cdn_pagination(); ?></div>
            <?php else : ?>
            <p>Nenhuma notícia encontrada nesta categoria.</p>
            <?php endif; ?>
        </div>
        <?php get_sidebar(); ?>
    </div>
</main>
<?php get_footer(); ?>

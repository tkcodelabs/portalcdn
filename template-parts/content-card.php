<?php
/**
 * template-parts/content-card.php — Card de notícia
 */
$cats     = get_the_category();
$cat      = $cats ? $cats[0] : null;
$cat_color = $cat ? cdn_category_color( $cat->slug ) : 'default';
?>
<article class="news-card" itemscope itemtype="https://schema.org/NewsArticle">
    <a href="<?php the_permalink(); ?>" class="news-card-thumbnail" aria-label="<?php the_title_attribute(); ?>">
        <?php if ( has_post_thumbnail() ) :
            the_post_thumbnail( 'cdn-card', [ 'itemprop' => 'image', 'loading' => 'lazy', 'alt' => get_the_title() ] );
        else : ?>
            <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder.svg' ); ?>" alt="" loading="lazy">
        <?php endif; ?>
    </a>
    <div>
        <?php if ( $cat ) : ?>
        <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"
           class="news-card-category <?php echo esc_attr( $cat_color ); ?>">
            <?php echo esc_html( $cat->name ); ?>
        </a>
        <?php endif; ?>
        <h3 class="news-card-title" itemprop="headline">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>
        <p class="news-card-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></p>
        <div class="news-card-meta">
            <time datetime="<?php echo get_the_date( 'c' ); ?>" itemprop="datePublished"><?php echo get_the_date(); ?></time>
            <span>·</span>
            <span><?php echo cdn_reading_time(); ?></span>
        </div>
    </div>
</article>

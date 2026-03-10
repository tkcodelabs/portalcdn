<?php
/**
 * single.php — Template de post individual (notícia)
 */
get_header();
while ( have_posts() ) :
    the_post();
    $cats          = get_the_category();
    $primary_cat   = $cats ? $cats[0] : null;
    $cat_color     = $primary_cat ? cdn_category_color( $primary_cat->slug ) : 'default';
    $tags          = get_the_tags();
    
    // Track views natively via PHP
    cdn_track_post_views(get_the_ID());
?>

<main id="main-content" class="container">
    <div class="single-layout">

        <!-- ---- ARTICLE ---- -->
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> itemscope itemtype="https://schema.org/NewsArticle">

            <!-- Breadcrumb -->
            <nav aria-label="Caminho de navegação" style="margin:1.5rem 0 1rem;font-size:.8rem;color:var(--color-text-muted)">
                <a href="<?php echo esc_url( home_url() ); ?>" style="color:var(--color-primary)">Home</a>
                <?php if ( $primary_cat ) : ?>
                    &rsaquo;
                    <a href="<?php echo esc_url( get_category_link( $primary_cat->term_id ) ); ?>" style="color:var(--color-primary)">
                        <?php echo esc_html( $primary_cat->name ); ?>
                    </a>
                <?php endif; ?>
                &rsaquo; <span><?php echo esc_html( wp_trim_words( get_the_title(), 8 ) ); ?></span>
            </nav>

            <!-- Post Header -->
            <header class="post-header">
                <?php if ( $primary_cat ) : ?>
                <a href="<?php echo esc_url( get_category_link( $primary_cat->term_id ) ); ?>"
                   class="post-category news-card-category <?php echo esc_attr( $cat_color ); ?>">
                    <?php echo esc_html( $primary_cat->name ); ?>
                </a>
                <?php endif; ?>

                <h1 itemprop="headline"><?php the_title(); ?></h1>

                <?php if ( has_excerpt() ) : ?>
                <p class="post-excerpt" itemprop="description"><?php the_excerpt(); ?></p>
                <?php endif; ?>

                <div class="post-meta">
                    <span class="author" itemprop="author" itemscope itemtype="https://schema.org/Person">
                        <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1rem">person</span>
                        <span itemprop="name"><?php the_author(); ?></span>
                    </span>
                    <span class="separator">|</span>
                    <time datetime="<?php echo get_the_date( 'c' ); ?>" itemprop="datePublished">
                        <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1rem">calendar_today</span>
                        <?php echo get_the_date(); ?>
                    </time>
                    <?php if ( get_the_modified_date() !== get_the_date() ) : ?>
                    <span class="separator">|</span>
                    <span>Atualizado: <time datetime="<?php echo get_the_modified_date( 'c' ); ?>" itemprop="dateModified"><?php echo get_the_modified_date(); ?></time></span>
                    <?php endif; ?>
                    <span class="separator">|</span>
                    <span>
                        <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1rem">schedule</span>
                        <?php echo cdn_reading_time(); ?>
                    </span>
                    <span class="separator">|</span>
                    <span title="Visualizações da matéria">
                        <span class="material-symbols-outlined" aria-hidden="true" style="font-size:1rem">visibility</span>
                        <span id="cnn-view-counter-dyn">
                            <?php 
                            $views = get_post_meta( get_the_ID(), 'post_views_count', true );
                            echo $views ? number_format_i18n( $views ) . ' views' : '1 view'; 
                            ?>
                        </span>
                    </span>
                </div>
            </header>

            <!-- Featured Image -->
            <?php if ( has_post_thumbnail() ) : ?>
            <figure class="post-thumbnail" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
                <?php the_post_thumbnail( 'cdn-hero', [ 'itemprop' => 'url', 'loading' => 'eager' ] ); ?>
                <?php $caption = get_the_post_thumbnail_caption(); if ( $caption ) : ?>
                <figcaption><?php echo esc_html( $caption ); ?></figcaption>
                <?php endif; ?>
            </figure>
            <?php endif; ?>

            <!-- Ad (inline topo) -->
            <?php 
            $single_ad_img  = get_option('cdn_single_ad_img');
            $single_ad_link = get_option('cdn_single_ad_link');
            if ( $single_ad_img ) : 
            ?>
            <div class="ad-banner ad-banner-single" role="complementary" aria-label="Publicidade">
                <a href="<?php echo esc_url($single_ad_link ?: '#'); ?>" target="_blank" rel="noopener noreferrer">
                    <img src="<?php echo esc_url($single_ad_img); ?>" alt="Publicidade">
                </a>
            </div>
            <?php else : ?>
            <div class="ad-banner ad-banner-single" role="complementary" aria-label="Publicidade">
                <span class="ad-label">Publicidade</span>
                <span class="ad-text" style="line-height:1.4">Espaço para Anúncio<br><small style="font-size:0.75rem">728 x 90 px</small></span>
            </div>
            <?php endif; ?>

            <!-- Post Content -->
            <div class="post-content" itemprop="articleBody">
                <?php the_content( 'Continuar lendo &rarr;' ); ?>
                <?php
                wp_link_pages( [
                    'before'      => '<div class="page-links"><strong>Páginas:</strong>',
                    'after'       => '</div>',
                    'link_before' => '<span class="page-numbers">',
                    'link_after'  => '</span>',
                ] );
                ?>
            </div>

            <!-- Tags -->
            <?php if ( $tags ) : ?>
            <div class="post-tags">
                <?php foreach ( $tags as $tag ) : ?>
                <a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" class="post-tag">
                    #<?php echo esc_html( $tag->name ); ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Share -->
            <?php
            $post_url   = urlencode( get_permalink() );
            $post_title = urlencode( get_the_title() );
            ?>
            <div class="modern-share-box" style="display:flex; flex-direction:column; gap:1.25rem; padding: 2rem; background: var(--color-surface); border-radius: var(--radius-lg); border: 1px solid var(--color-border); margin: 3rem 0; box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 1.25rem; margin:0; color: var(--color-text-heading); display:flex; align-items:center; gap:0.5rem; font-weight:800;">
                    <span class="material-symbols-outlined" style="color:var(--color-primary)">share</span> Gostou da matéria? Compartilhe:
                </h3>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem;">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $post_url; ?>" target="_blank" rel="noopener noreferrer" style="display:flex; align-items:center; justify-content:center; gap:0.5rem; padding: 0.875rem; border-radius: 8px; background: #1877F2; color: #fff; text-decoration: none; font-weight: 700; font-size: 0.95rem; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(24,119,242,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo $post_url; ?>&text=<?php echo $post_title; ?>" target="_blank" rel="noopener noreferrer" style="display:flex; align-items:center; justify-content:center; gap:0.5rem; padding: 0.875rem; border-radius: 8px; background: #1DA1F2; color: #fff; text-decoration: none; font-weight: 700; font-size: 0.95rem; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(29,161,242,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        X / Twitter
                    </a>
                    <a href="https://api.whatsapp.com/send?text=<?php echo $post_title . ' ' . $post_url; ?>" target="_blank" rel="noopener noreferrer" style="display:flex; align-items:center; justify-content:center; gap:0.5rem; padding: 0.875rem; border-radius: 8px; background: #25D366; color: #fff; text-decoration: none; font-weight: 700; font-size: 0.95rem; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(37,211,102,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        WhatsApp
                    </a>
                    <button onclick="navigator.clipboard.writeText('<?php echo urldecode($post_url); ?>'); const btn = this; btn.style.background='var(--color-primary)'; btn.style.color='#fff'; setTimeout(()=> { btn.style.background='var(--color-bg)'; btn.style.color='var(--color-text-heading)'; }, 2000);" style="display:flex; align-items:center; justify-content:center; gap:0.5rem; padding: 0.875rem; border-radius: 8px; background: var(--color-bg); color: var(--color-text-heading); border: 1px solid var(--color-border); font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        <span class="material-symbols-outlined" style="font-size:1.1rem;">link</span> Copiar Link
                    </button>
                </div>
            </div>

            <!-- Related Posts -->
            <?php
            $current_id   = get_the_ID();
            $tag_ids      = wp_list_pluck( (array) get_the_tags(), 'term_id' );
            $cat_ids      = $primary_cat ? [ $primary_cat->term_id ] : [];
            $related      = [];

            // Nível 1: Mesma categoria E mesma tag, ordenado por mais lidas
            if ( $cat_ids && $tag_ids ) {
                $related = get_posts( [
                    'numberposts'    => 2,
                    'post__not_in'   => [ $current_id ],
                    'category__in'   => $cat_ids,
                    'tag__in'        => $tag_ids,
                    'meta_key'       => 'post_views_count',
                    'orderby'        => 'meta_value_num',
                    'order'          => 'DESC',
                    'ignore_sticky_posts' => true,
                ] );
            }

            // Nível 2: Só categoria, por mais lidas
            if ( count( $related ) < 2 && $cat_ids ) {
                $exclude = array_merge( [ $current_id ], wp_list_pluck( $related, 'ID' ) );
                $extra = get_posts( [
                    'numberposts'    => 2 - count( $related ),
                    'post__not_in'   => $exclude,
                    'category__in'   => $cat_ids,
                    'meta_key'       => 'post_views_count',
                    'orderby'        => 'meta_value_num',
                    'order'          => 'DESC',
                    'ignore_sticky_posts' => true,
                ] );
                $related = array_merge( $related, $extra );
            }

            // Nível 3: Fallback — matérias recentes gerais
            if ( count( $related ) < 2 ) {
                $exclude = array_merge( [ $current_id ], wp_list_pluck( $related, 'ID' ) );
                $extra = get_posts( [
                    'numberposts'    => 2 - count( $related ),
                    'post__not_in'   => $exclude,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                ] );
                $related = array_merge( $related, $extra );
            }

            if ( $related ) :
            ?>
            <section class="related-posts" aria-label="Notícias relacionadas">
                <div class="section-header" style="margin-bottom: 2rem;">
                    <div class="accent-bar"></div>
                    <h2>Leia Também</h2>
                </div>
                <div class="related-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem;">
                    <?php foreach ( $related as $rel_post ) :
                        setup_postdata( $GLOBALS['post'] =& $rel_post );
                        get_template_part( 'template-parts/content', 'card' );
                    endforeach;
                    wp_reset_postdata(); ?>
                </div>
            </section>
            <?php endif; ?>


        </article>

        <!-- ---- SIDEBAR ---- -->
        <?php get_sidebar( 'single' ); ?>

    </div>
</main>

<?php endwhile; get_footer(); ?>

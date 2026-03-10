<?php
/**
 * page.php — Template de página estática
 */
get_header();
while ( have_posts() ) : the_post(); ?>
<main id="main-content" class="container" style="padding:2.5rem 0 5rem">
    <div class="single-layout">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="post-header">
                <h1 style="font-size:clamp(1.75rem,4vw,2.5rem);font-weight:900;margin-bottom:1.5rem"><?php the_title(); ?></h1>
            </header>
            <?php if ( has_post_thumbnail() ) : ?>
            <figure class="post-thumbnail" style="margin-bottom:2rem">
                <?php the_post_thumbnail( 'cdn-hero', [ 'loading' => 'eager' ] ); ?>
            </figure>
            <?php endif; ?>
            <div class="post-content"><?php the_content(); ?></div>
        </article>
        <?php get_sidebar(); ?>
    </div>
</main>
<?php endwhile; get_footer(); ?>

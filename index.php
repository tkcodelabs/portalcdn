<?php
/**
 * index.php — Homepage do tema Correio do Norte
 */
get_header();
?>

<!-- ====== AD BANNER TOPO ====== -->
<div class="ad-banner-wrap">
    <div class="container">
        <?php
        $ad_url = get_option('cdn_ad_home_url');
        $ad_link = get_option('cdn_ad_home_link') ?: home_url( '/anuncie/' );
        
        if ( $ad_url ) :
        ?>
        <a href="<?php echo esc_url( $ad_link ); ?>" target="_blank" rel="noopener" aria-label="Publicidade Principal">
            <div class="ad-banner">
                <img src="<?php echo esc_url( $ad_url ); ?>" alt="Publicidade Principal" loading="eager">
            </div>
        </a>
        <?php else : ?>
        <div class="ad-banner" style="margin-bottom:2rem;" role="complementary" aria-label="Publicidade">
            <span class="ad-label">Publicidade</span>
            <span class="ad-text" style="line-height:1.4">Espaço para Anúncio Principal<br><small style="font-size:0.75rem">Exibição na Home</small></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ====== MAIN CONTENT ====== -->
<main id="main-content" class="container">
    <div class="home-layout">

        <!-- ---- COLUNA PRINCIPAL ---- -->
        <div>

            <?php
            // Hero: primeiro post
            $hero_query = new WP_Query( [ 'posts_per_page' => 1, 'ignore_sticky_posts' => true ] );
            if ( $hero_query->have_posts() ) :
                $hero_query->the_post();
                $cats   = get_the_category();
                $cat    = $cats ? $cats[0] : null;
            ?>
            <!-- Hero -->
            <article class="hero-article" itemscope itemtype="https://schema.org/NewsArticle">
                <a href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
                    <div class="hero-thumbnail-wrap">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'cdn-hero', [ 'class' => 'hero-thumbnail', 'loading' => 'eager', 'itemprop' => 'image', 'alt' => get_the_title() ] ); ?>
                        <?php else : ?>
                            <img class="hero-thumbnail" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/placeholder.svg' ); ?>" alt="">
                        <?php endif; ?>

                        <?php if ( $cat ) : ?>
                        <span class="hero-badge"><?php echo esc_html( $cat->name ); ?></span>
                        <?php endif; ?>

                        <div class="hero-overlay">
                            <h2 class="hero-title" itemprop="headline"><?php the_title(); ?></h2>
                            <p class="hero-excerpt"><?php echo wp_trim_words( get_the_excerpt(), 20 ); ?></p>
                            <div class="hero-meta">
                                <span itemprop="author"><?php the_author(); ?></span>
                                <span>·</span>
                                <time itemprop="datePublished" datetime="<?php echo get_the_date( 'c' ); ?>"><?php echo get_the_date(); ?></time>
                                <span>·</span>
                                <span><?php echo cdn_reading_time(); ?></span>
                            </div>
                        </div>
                    </div>
                </a>
            </article>
            <?php wp_reset_postdata(); endif; ?>

            <!-- Grid / Lista de notícias (posts seguintes) -->
            <?php $home_layout = get_option( 'cdn_home_layout', 'grid' ); ?>
            <div class="section-header">
                <div class="accent-bar"></div>
                <h2><?php echo $home_layout === 'lista' ? 'Últimas Notícias' : 'Últimas Notícias'; ?></h2>
            </div>

            <?php
            $grid_query = new WP_Query( [
                'posts_per_page'      => 14,
                'offset'              => 1,
                'ignore_sticky_posts' => true,
            ] );
            if ( $grid_query->have_posts() ) :
                if ( $home_layout === 'lista' ) :
            ?>
            <section class="news-list" id="cdn-news-grid" aria-label="Lista de notícias" style="display: flex; flex-direction: column; gap: 4rem; max-width: 820px;">
                <?php while ( $grid_query->have_posts() ) : $grid_query->the_post();
                    get_template_part( 'template-parts/content', 'list' );
                endwhile; ?>
            </section>
            <?php else : ?>
            <section class="news-grid" id="cdn-news-grid" aria-label="Grade de notícias">
                <?php while ( $grid_query->have_posts() ) : $grid_query->the_post();
                    get_template_part( 'template-parts/content', 'card' );
                endwhile; ?>
            </section>
            <?php endif; ?>
            
            <?php if ( $grid_query->found_posts > 15 ) : // 1 hero + 14 grid = 15 ?>
            <!-- Load more -->
            <div style="text-align:center; margin-top:3rem" id="load-more-container">
                <button id="cdn-load-more" class="btn" data-offset="15" data-ppp="14" data-layout="<?php echo esc_attr( $home_layout ); ?>" style="padding: 15px 40px; font-weight:800; border-radius: 50px; font-size: 1.1rem; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); background: var(--color-primary); color: #fff; cursor: pointer; transition: all 0.3s ease; border: none;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.1)';">
                    <span class="material-symbols-outlined" style="font-size: 1.3rem;">sync</span> 
                    Carregar Mais Matérias
                </button>
            </div>
            <?php endif; ?>
            <?php wp_reset_postdata(); endif; ?>

        </div><!-- /.col-main -->

        <!-- ---- SIDEBAR ---- -->
        <?php get_sidebar(); ?>

    </div><!-- /.home-layout -->

    <!-- ====== VAGAS DE EMPREGO (LATEST) ====== -->
    <?php
    $vagas_query = new WP_Query([
        'post_type'      => 'vagas',
        'posts_per_page' => 3,
        'post_status'    => 'publish',
    ]);
    if ( $vagas_query->have_posts() ) :
    ?>
    <section class="home-vagas-section" style="margin-top: 4rem; padding-top: 4rem; padding-bottom: 2rem; border-top: 1px solid var(--color-border); position: relative;">
        <!-- Background Decorativo -->
        <div style="position: absolute; top:0; left:0; width:100%; height:100%; background: linear-gradient(180deg, rgba(var(--color-bg-alt-rgb), 0.5) 0%, transparent 100%); z-index:-1;"></div>
        
        <div class="vagas-split-layout" style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 3rem; align-items: start;">
            
            <!-- COLUNA ESQUERDA: CTA Anunciar Vaga -->
            <div class="vagas-promo-box" style="background: var(--color-surface); padding: 3rem 2.5rem; border-radius: min(var(--radius-lg), 24px); border: 1px solid var(--color-border); box-shadow: var(--shadow-md); position: sticky; top: 100px;">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background: rgba(var(--color-primary-rgb), 0.1); color: var(--color-primary); border-radius: 50%; margin-bottom: 1.5rem;">
                    <span class="material-symbols-outlined" style="font-size: 32px;">campaign</span>
                </div>
                <h2 style="font-size: 2rem; font-weight: 800; color: var(--color-text-heading); margin-bottom: 1rem; line-height: 1.2;">Procurando<br>Talentos?</h2>
                <p style="color: var(--color-text-body); font-size: 1.05rem; line-height: 1.6; margin-bottom: 2rem;">A vitrine de empregos mais acessada da região. Anuncie sua vaga de graça no Correio do Norte e alcance os melhores profissionais.</p>
                
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <a href="<?php echo esc_url( home_url('/anunciar-vaga/') ); ?>" class="btn-primary" style="padding: 1rem; border-radius: var(--radius-md); font-size: 1.05rem; font-weight: 700; width: 100%; justify-content: center; text-align: center;">
                        <span class="material-symbols-outlined">add_circle</span> Anunciar Vaga
                    </a>
                    <a href="<?php echo esc_url( home_url('/vagas/') ); ?>" style="padding: 1rem; border-radius: var(--radius-md); font-size: 1rem; font-weight: 700; width: 100%; text-align: center; color: var(--color-text-heading); background: transparent; border: 1px solid var(--color-border); transition: all 0.2s; text-decoration: none;" onmouseover="this.style.background='var(--color-bg-alt)';" onmouseout="this.style.background='transparent';">
                        Ver todas as vagas
                    </a>
                </div>
            </div>

            <!-- COLUNA DIREITA: Listagem de Vagas (Indeed Style) -->
            <div class="vagas-list-container" style="display: flex; flex-direction: column; gap: 1.25rem;">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <div style="display:flex; align-items:center; gap:0.5rem">
                        <span class="material-symbols-outlined" style="color:var(--color-primary); font-size: 28px;">work_history</span>
                        <h3 style="font-size: 1.5rem; font-weight: 800; margin:0;">Oportunidades Recentes</h3>
                    </div>
                </div>

                <?php while ( $vagas_query->have_posts() ) : $vagas_query->the_post(); 
                    $empresa = get_post_meta(get_the_ID(), 'cdn_vaga_empresa', true);
                    $local = get_post_meta(get_the_ID(), 'cdn_vaga_local', true);
                ?>
                <a href="<?php the_permalink(); ?>" class="vaga-list-item" style="display: flex; align-items: center; background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-md); padding: 1.5rem; text-decoration: none; color: inherit; transition: all 0.2s ease; gap: 1.5rem;">
                    
                    <div class="vaga-logo-placeholder" style="width: 64px; height: 64px; border-radius: 12px; background: var(--color-bg-alt); display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid var(--color-border);">
                        <span class="material-symbols-outlined" style="font-size: 32px; color: var(--color-text-muted);">domain</span>
                    </div>

                    <div style="flex-grow: 1;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.35rem;">
                            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--color-text-heading); margin: 0; line-height: 1.2;"><?php the_title(); ?></h3>
                            <span class="badge-nova-vaga" style="font-size: 0.65rem; text-transform: uppercase; font-weight: 800; color: var(--color-surface); background: var(--color-primary); padding: 0.2rem 0.6rem; border-radius: 50px; letter-spacing: 1px;">Novo</span>
                        </div>
                        
                        <div style="font-size: 0.95rem; color: var(--color-text-muted); display: flex; flex-wrap: wrap; gap: 1.25rem; margin-top: 0.5rem;">
                            <?php if ($empresa): ?><span style="display:flex; align-items:center; gap:6px; font-weight: 600; color: var(--color-text-body);"><span class="material-symbols-outlined" style="font-size:18px; color: var(--color-text-muted);">business</span> <?php echo esc_html($empresa); ?></span><?php endif; ?>
                            <?php if ($local): ?><span style="display:flex; align-items:center; gap:6px;"><span class="material-symbols-outlined" style="font-size:18px; color: var(--color-text-muted);">location_on</span> <?php echo esc_html($local); ?></span><?php endif; ?>
                            <span style="display:flex; align-items:center; gap:6px; margin-left: auto;"><span class="material-symbols-outlined" style="font-size:18px; color: var(--color-text-muted);">calendar_today</span> <?php echo get_the_date('d \d\e F'); ?></span>
                        </div>
                    </div>

                    <div class="vaga-list-cta" style="flex-shrink: 0;">
                        <span class="view-job-link" style="color: var(--color-primary); font-weight: 700; display: flex; align-items: center; gap: 4px; font-size: 0.95rem;">Ver Vaga <span class="material-symbols-outlined" style="font-size: 20px;">chevron_right</span></span>
                    </div>
                </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
        </div>
    </section>
    <style>
        .vaga-list-item:hover { transform: translateX(8px); box-shadow: var(--shadow-lg); border-color: var(--color-primary); }
        .vaga-list-item:hover .view-job-link { text-decoration: underline; }
        @media (max-width: 992px) {
            .vagas-split-layout { grid-template-columns: 1fr !important; gap: 2rem !important; }
            .vagas-promo-box { position: relative !important; top: 0 !important; }
        }
        @media (max-width: 768px) {
            .vaga-list-item { flex-direction: column; align-items: flex-start; gap: 1rem !important; position: relative; }
            .badge-nova-vaga { position: absolute; top: 1.5rem; right: 1.5rem; margin: 0; }
            .vaga-list-cta { width: 100%; border-top: 1px solid var(--color-border); padding-top: 1rem; margin-top: 0.5rem; }
            .view-job-link { justify-content: space-between; width: 100%; }
        }
    </style>
    <?php endif; ?>

</main>

<?php get_footer(); ?>

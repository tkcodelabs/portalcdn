<?php
/**
 * sidebar-single.php — Sidebar para página de notícia individual
 * Registra como 'sidebar-single' e exibe buscador + mais lidas + publicidade
 */
?>
<aside class="sidebar" role="complementary" aria-label="Barra lateral da notícia">

    <!-- Widget: Busca -->
    <div class="widget-box widget-search">
        <h4 class="widget-title">
            <span class="material-symbols-outlined" aria-hidden="true">search</span>
            Pesquisar
        </h4>
        <div class="search-form-inner">
            <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <label for="single-sidebar-search" class="sr-only">Pesquisar</label>
                <input type="search" id="single-sidebar-search" name="s" placeholder="O que você procura?" value="<?php echo get_search_query(); ?>" autocomplete="off">
                <span class="material-symbols-outlined search-icon" aria-hidden="true">search</span>
            </form>
        </div>
    </div>

    <!-- Widget: Mais Lidas -->
    <?php
    $most_read = get_posts( [
        'numberposts' => 5,
        'post_status' => 'publish',
        'post__not_in'=> [ get_the_ID() ],
        'meta_key'    => 'post_views_count',
        'orderby'     => 'meta_value_num',
        'order'       => 'DESC',
    ] );
    if ( empty( $most_read ) ) {
        $most_read = get_posts( [ 'numberposts' => 5, 'post_status' => 'publish', 'post__not_in' => [ get_the_ID() ], 'orderby' => 'date', 'order' => 'DESC' ] );
    }
    if ( $most_read ) : ?>
    <div class="widget-box">
        <h4 class="widget-title">
            <span class="material-symbols-outlined" aria-hidden="true">trending_up</span>
            Mais Lidas
        </h4>
        <ul class="most-read-list">
            <?php foreach ( $most_read as $i => $item ) :
                $cats = get_the_category( $item->ID );
                $cat_name = $cats ? $cats[0]->name : 'Geral';
            ?>
            <li class="most-read-item">
                <a href="<?php echo esc_url( get_permalink( $item ) ); ?>" aria-hidden="true" tabindex="-1">
                    <span class="most-read-rank"><?php echo str_pad( $i + 1, 2, '0', STR_PAD_LEFT ); ?></span>
                </a>
                <div>
                    <a href="<?php echo esc_url( get_permalink( $item ) ); ?>" class="most-read-title"><?php echo esc_html( get_the_title( $item ) ); ?></a>
                    <span class="most-read-cat"><?php echo esc_html( $cat_name ); ?></span>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Publicidade (Dinâmica Single Sidebar) -->
    <?php 
    for ($i = 1; $i <= 7; $i++) {
        $img_key = ($i === 1) ? 'cdn_single_sidebar_ad_img' : 'cdn_single_sidebar_ad'.$i.'_img';
        $link_key = ($i === 1) ? 'cdn_single_sidebar_ad_link' : 'cdn_single_sidebar_ad'.$i.'_link';
        $img = get_option($img_key);
        $link = get_option($link_key);
        
        if ($img) :
        ?>
        <div class="sidebar-ad" role="complementary" aria-label="Espaço publicitário lateral">
            <a href="<?php echo esc_url($link ?: '#'); ?>" target="_blank" rel="noopener noreferrer">
                <img src="<?php echo esc_url($img); ?>" alt="Anúncio Comercial">
            </a>
        </div>
        <?php elseif ($i === 1) : ?>
        <div class="sidebar-ad" role="complementary" aria-label="Publicidade">
            <span class="ad-label">Publicidade</span>
            <span class="material-symbols-outlined ad-icon" aria-hidden="true">ads_click</span>
            <p><strong>Espaço Publicitário</strong><br>(300×250)</p>
            <p class="ad-contact">
                <a href="mailto:comercial@correiodonorte.com.br">comercial@correiodonorte.com.br</a>
            </p>
        </div>
        <?php endif;
    }
    ?>

    <?php if ( is_active_sidebar( 'sidebar-single' ) ) dynamic_sidebar( 'sidebar-single' ); ?>

</aside>

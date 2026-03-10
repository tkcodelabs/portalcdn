<?php
/**
 * header.php — Cabeçalho do tema Correio do Norte
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
<script>
    (function(){
        try {
            var savedTheme = localStorage.getItem('cdn_theme');
            if(savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)){
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        } catch(e){}
    })();
</script>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- ====== BREAKING NEWS BAR ====== -->
<?php
$breaking_items = cdn_get_breaking_news();
if ( $breaking_items ) : ?>
<div class="breaking-bar" role="marquee" aria-label="Últimas notícias">
    <div class="container breaking-inner">
        <span class="breaking-label" style="background-color: <?php echo esc_attr( get_option( 'cdn_urgente_color', '#f50000' ) ); ?>;"><?php echo esc_html( get_option( 'cdn_urgente_text', 'Urgente' ) ); ?></span>
        <div class="breaking-ticker">
            <span>
                <?php foreach ( $breaking_items as $item ) : ?>
                    <a href="<?php echo esc_url( get_permalink( $item ) ); ?>" style="color:inherit;margin-right:3rem;">
                        <?php echo esc_html( $item->post_title ); ?>
                    </a>
                <?php endforeach; ?>
            </span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ====== SITE HEADER ====== -->
<header class="site-header" role="banner" id="site-header">
    <div class="container">

        <!-- Top Bar -->
        <div class="header-topbar" aria-label="Barra de informações">
            <div class="topbar-info">
                <span>
                    <span class="material-symbols-outlined" aria-hidden="true" style="font-size:.875rem">calendar_today</span>
                    <?php echo esc_html( date_i18n( 'l, j \d\e F \d\e Y' ) ); ?>
                </span>
                <span class="weather">
                    <span class="material-symbols-outlined" aria-hidden="true" style="font-size:.875rem">location_on</span>
                    Parnaíba — PI
                    <?php
                    // Clima compacto inline — lê do cache (transient), sem bloquear o carregamento
                    $clima = get_transient( 'cdn_tempo_parnaiba' );
                    if ( $clima ) :
                        $di = match( true ) {
                            str_contains( strtolower( $clima['desc'] ?? '' ), 'chuv' )  => '☁️',
                            str_contains( strtolower( $clima['desc'] ?? '' ), 'nubl' )  => '⛅',
                            str_contains( strtolower( $clima['desc'] ?? '' ), 'sol' )   => '☀️',
                            default => '🌡️',
                        };
                    ?>
                    <span class="topbar-clima"><?php echo $di; ?> <strong><?php echo esc_html( $clima['temp'] ); ?>&deg;C</strong></span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="topbar-links topbar-redes">
                <?php
                $top_links = get_option('cdn_topbar_links');
                if ( $top_links ) {
                    $linhas = explode( "\n", $top_links );
                    foreach( $linhas as $linha ) {
                        $partes = explode( "|", $linha );
                        if ( count($partes) >= 2 ) {
                            echo '<a href="'.esc_url(trim($partes[1])).'">'.esc_html(trim($partes[0])).'</a>';
                        }
                    }
                }
                echo '<span class="separator" aria-hidden="true" style="opacity:0.3;">|</span>';
                
                $redes = [
                    'facebook'  => [ 'url' => get_theme_mod( 'cdn_facebook',  'https://facebook.com' ),  'label' => 'Facebook' ],
                    'instagram' => [ 'url' => get_theme_mod( 'cdn_instagram', 'https://instagram.com' ), 'label' => 'Instagram' ],
                    'youtube'   => [ 'url' => get_theme_mod( 'cdn_youtube',   'https://youtube.com' ),   'label' => 'YouTube' ],
                    'twitter'   => [ 'url' => get_theme_mod( 'cdn_twitter',   'https://twitter.com' ),   'label' => 'Twitter / X' ],
                ];
                $svgs = [
                    'facebook'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 1 0-11.563 9.876V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988A10.002 10.002 0 0 0 22 12z"/></svg>',
                    'instagram' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>',
                    'youtube'   => '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M23.495 6.205a3.007 3.007 0 0 0-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 0 0 .527 6.205a31.247 31.247 0 0 0-.522 5.805 31.247 31.247 0 0 0 .522 5.783 3.007 3.007 0 0 0 2.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 0 0 2.088-2.088 31.247 31.247 0 0 0 .5-5.783 31.247 31.247 0 0 0-.5-5.805zM9.609 15.601V8.408l6.264 3.602z"/></svg>',
                    'twitter'   => '<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.742l7.736-8.836L1.254 2.25H8.08l4.259 5.629zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
                ];
                foreach ( $redes as $rede => $data ) : if ( $data['url'] ) : ?>
                <a href="<?php echo esc_url( $data['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $data['label'] ); ?>" class="topbar-social-link">
                    <?php echo $svgs[ $rede ]; ?>
                </a>
                <?php endif; endforeach; ?>
            </div>
        </div>

        <!-- Main Header Row -->
        <div class="header-main">

            <!-- Logo -->
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" rel="home" aria-label="<?php bloginfo( 'name' ); ?> — Página inicial">
                <?php $logo = get_option('cdn_logo'); if ( $logo ) : ?>
                    <img src="<?php echo esc_url( $logo ); ?>" alt="<?php bloginfo( 'name' ); ?>" style="max-height: 50px; width: auto;">
                <?php else : ?>
                    <div class="logo-icon" aria-hidden="true">
                        <span class="material-symbols-outlined">newspaper</span>
                    </div>
                    <span class="logo-text">CORREIO<span>DO</span>NORTE</span>
                <?php endif; ?>
            </a>

            <!-- Primary Nav (desktop) -->
            <nav class="primary-nav" aria-label="Menu principal" id="primary-navigation">
                <?php
                wp_nav_menu( [
                    'theme_location' => 'primary',
                    'menu_class'     => '',
                    'container'      => false,
                    'fallback_cb'    => 'cdn_fallback_nav',
                ] );
                ?>
            </nav>

            <!-- Header Actions -->
            <div class="header-actions">
                <button class="btn-search" id="theme-toggle" aria-label="Alterar Tema" title="Alterar Tema" aria-checked="false">
                    <span class="material-symbols-outlined" aria-hidden="true" id="theme-icon">dark_mode</span>
                </button>
                <button class="btn-search" id="btn-search-open" aria-label="Abrir busca" aria-controls="search-overlay" aria-expanded="false">
                    <span class="material-symbols-outlined" aria-hidden="true">search</span>
                </button>
                <a href="<?php echo esc_url( home_url( '/edicao-digital/' ) ); ?>" class="btn-edicao hidden-sm btn-pulse-anim" title="Leia a edição impressa digital">
                    <span class="edicao-icon" aria-hidden="true">📰</span>
                    <span class="edicao-texto">
                        <span class="edicao-label"><?php echo esc_html( get_option('cdn_digital_btn_text', 'Edição do Mês') ); ?></span>
                        <span class="edicao-numero"><?php echo esc_html( get_option('cdn_digital_btn_number', 'Nº 267') ); ?></span>
                    </span>
                </a>
                <!-- Mobile toggle -->
                <button class="mobile-menu-toggle" id="btn-mobile-menu" aria-label="Abrir menu" aria-controls="mobile-nav" aria-expanded="false">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>

        </div><!-- /.header-main -->
    </div><!-- /.container -->
</header>

<!-- ====== MOBILE MENU NAV ====== -->
<div class="mobile-nav-overlay" id="mobile-nav-overlay" aria-hidden="true"></div>
<nav class="mobile-nav" id="mobile-nav" aria-label="Menu mobile" aria-hidden="true">
    <div class="mobile-nav-header">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" style="text-decoration:none">
            <div class="logo-icon" aria-hidden="true" style="width:1.75rem;height:1.75rem;font-size:1rem"><span class="material-symbols-outlined">newspaper</span></div>
            <span class="logo-text" style="font-size:1.1rem">CORREIO<span>DO</span>NORTE</span>
        </a>
        <button class="btn-close-mobile" id="btn-mobile-close" aria-label="Fechar menu">
            <span class="material-symbols-outlined" aria-hidden="true">close</span>
        </button>
    </div>
    <?php
    wp_nav_menu( [
        'theme_location' => 'primary',
        'container'      => false,
        'fallback_cb'    => 'cdn_fallback_nav',
    ] );
    ?>
    <div style="margin-top:auto;padding-top:1.5rem;border-top:1px solid var(--color-border)">
        <a href="<?php echo esc_url( home_url( '/edicao-digital/' ) ); ?>" class="btn-primary" style="display:block;text-align:center;padding:.875rem">📰 Edição do Dia &mdash; N&ordm;&#x200A;267</a>
    </div>
</nav>

<!-- ====== SEARCH OVERLAY ====== -->
<div class="search-overlay" id="search-overlay" role="dialog" aria-label="Busca" aria-modal="true" aria-hidden="true">
    <button class="search-close" id="btn-search-close" aria-label="Fechar busca">
        <span class="material-symbols-outlined" aria-hidden="true">close</span>
    </button>
    <div class="search-inner">
        <form class="search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
            <label for="search-overlay-input" class="sr-only"><?php _e( 'Pesquisar', 'correiodonorte' ); ?></label>
            <input type="search" id="search-overlay-input" name="s" placeholder="O que você procura?" autocomplete="off" value="<?php echo get_search_query(); ?>">
            <button type="submit" class="search-submit" aria-label="Buscar">
                <span class="material-symbols-outlined" aria-hidden="true">search</span>
            </button>
        </form>
        <p style="color:rgba(255,255,255,.5);font-size:.8rem;margin-top:.75rem;text-align:center">Pressione ESC para fechar</p>
    </div>
</div>

<?php
/**
 * Fallback nav — exibe links padrão se menu não estiver configurado
 */
function cdn_fallback_nav() {
    $cats = get_categories( [ 'orderby' => 'count', 'order' => 'DESC', 'number' => 6 ] );
    echo '<ul>';
    echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li>';
    foreach ( $cats as $cat ) {
        echo '<li><a href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a></li>';
    }
    echo '</ul>';
}

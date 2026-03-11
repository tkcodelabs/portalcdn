<?php
/**
 * Correio do Norte - functions.php
 * SEO, segurança e funcionalidades do tema.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Carregar Painel CDN (Opções do Tema)
require_once get_template_directory() . '/inc/theme-options.php';

// Carregar Sistema de Newsletter
require_once get_template_directory() . '/inc/newsletter.php';

// Carregar Configuração SMTP Nativa
require_once get_template_directory() . '/inc/smtp.php';

// Carregar Activity Log (monitoramento de eventos e arquivos)
require_once get_template_directory() . '/inc/activity-log.php';

// Desativar adivinhação automática de URLs do WordPress (URL Guessing)
// Previne que URLs com Erro 404 redirecionem acidentalmente para posts com letras parecidas
add_filter( 'redirect_canonical', function( $redirect_url ) {
    if ( is_404() && ! isset( $_GET['p'] ) ) {
        return false;
    }
    return $redirect_url;
});

// ============================================================
// SETUP DO TEMA
// ============================================================
function cdn_theme_setup() {
    load_theme_textdomain( 'correiodonorte', get_template_directory() . '/languages' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'customize-selective-refresh-widgets' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'responsive-embeds' );
    add_image_size( 'cdn-hero',    1200, 675,  true );
    add_image_size( 'cdn-card',    800,  450,  true );
    add_image_size( 'cdn-thumb',   400,  225,  true );
    add_image_size( 'cdn-archive', 360,  240,  true );
    register_nav_menus( [
        'primary'  => __( 'Menu Principal', 'correiodonorte' ),
        'footer'   => __( 'Menu Rodapé',    'correiodonorte' ),
        'mobile'   => __( 'Menu Mobile',    'correiodonorte' ),
        'breaking' => __( 'Menu Breaking',  'correiodonorte' ),
    ] );
}
add_action( 'after_setup_theme', 'cdn_theme_setup' );

// ============================================================
// SCRIPTS & ESTILOS
// ============================================================
function cdn_enqueue_assets() {
    // Fontes do Google: carregadas de forma não-bloqueante com media swap
    wp_enqueue_style(
        'cdn-google-fonts',
        'https://fonts.googleapis.com/css2?family=Work+Sans:wght@300;400;500;600;700;800;900&display=swap',
        [],
        null
    );
    wp_enqueue_style(
        'cdn-material-icons',
        'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap',
        [],
        null
    );
    wp_enqueue_style(
        'cdn-style',
        get_stylesheet_uri(),
        [ 'cdn-google-fonts', 'cdn-material-icons' ],
        wp_get_theme()->get( 'Version' )
    );
    wp_enqueue_script(
        'cdn-main',
        get_template_directory_uri() . '/assets/js/main.js',
        [],
        wp_get_theme()->get( 'Version' ),
        [ 'strategy' => 'defer' ]
    );
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
    wp_localize_script( 'cdn-main', 'cdnData', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'cdn_nonce' ),
        'homeUrl' => home_url(),
    ] );
}
add_action( 'wp_enqueue_scripts', 'cdn_enqueue_assets' );

// Adicionar preconnect hints para fontes externas (acelera a resoluo DNS)
add_action( 'wp_head', function() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    // PWA Manifest
    echo '<link rel="manifest" href="' . esc_url( get_template_directory_uri() . '/assets/manifest.json' ) . '">' . "\n";
    echo '<meta name="theme-color" content="#c0392b">' . "\n";
    echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
    echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
    echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";
    echo '<meta name="apple-mobile-web-app-title" content="' . esc_attr( get_bloginfo('name') ) . '">' . "\n";
}, 1 );

// PWA: Registrar Service Worker no footer
add_action( 'wp_footer', function() {
    $sw_url = esc_url( get_template_directory_uri() . '/assets/sw.js' );
    echo "<script>if('serviceWorker' in navigator){window.addEventListener('load',function(){navigator.serviceWorker.register('{$sw_url}').catch(function(){});});}</script>\n";
}, 99 );

// ============================================================
// POP-UP NEWSLETTER — exibido 1x por dia (via localStorage)
// ============================================================
add_action( 'wp_footer', function() {
    // Obter nonce para o form de newsletter já existente
    $nonce = wp_create_nonce( 'cdn_nonce' );
    $ajax  = esc_url( admin_url( 'admin-ajax.php' ) );
    $logo  = esc_url( get_template_directory_uri() . '/assets/images/logo.png' );
    ?>
    <!-- Pop-up Newsletter CDN -->
    <div id="cdn-newsletter-popup" role="dialog" aria-modal="true" aria-labelledby="cdn-popup-title" style="display:none;">
        <div class="cdn-popup-overlay" id="cdn-popup-overlay"></div>
        <div class="cdn-popup-card">
            <button class="cdn-popup-close" id="cdn-popup-close" aria-label="Fechar">
                <span class="material-symbols-outlined">close</span>
            </button>

            <!-- Ícone decorativo -->
            <div class="cdn-popup-icon" aria-hidden="true">
                <span class="material-symbols-outlined">mark_email_unread</span>
            </div>

            <p class="cdn-popup-badge">✦ Totalmente Grátis</p>
            <h2 class="cdn-popup-title" id="cdn-popup-title">Quer saber toda nova notícia em primeira mão?</h2>
            <p class="cdn-popup-desc">Assine o <strong>Newsletter Correio do Norte</strong> e receba os destaques do dia direto no seu e-mail, sem custo algum.</p>

            <form class="cdn-popup-form" id="cdn-popup-form" novalidate>
                <input type="hidden" name="action" value="cdn_newsletter_subscribe">
                <input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">
                <div style="display:none" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                <div class="cdn-popup-input-wrap">
                    <label for="cdn-popup-email" class="sr-only">Seu e-mail</label>
                    <input type="email" id="cdn-popup-email" name="email" placeholder="Seu melhor e-mail" required autocomplete="email">
                    <button type="submit" id="cdn-popup-btn">
                        <span class="material-symbols-outlined" aria-hidden="true">send</span>
                        Quero receber!
                    </button>
                </div>
                <p class="cdn-popup-msg" id="cdn-popup-msg" style="display:none"></p>
            </form>

            <button class="cdn-popup-skip" id="cdn-popup-skip">Não, obrigado</button>
        </div>
    </div>

    <style>
    #cdn-newsletter-popup {
        position:fixed;inset:0;z-index:99999;
        display:flex;align-items:center;justify-content:center;
        padding:1rem;
    }
    .cdn-popup-overlay {
        position:absolute;inset:0;
        background:rgba(0,0,0,.65);
        backdrop-filter:blur(4px);
        -webkit-backdrop-filter:blur(4px);
        cursor:pointer;
    }
    .cdn-popup-card {
        position:relative;
        background:var(--color-bg,#fff);
        border-radius:20px;
        padding:2.5rem 2rem 2rem;
        max-width:440px;width:100%;
        text-align:center;
        box-shadow:0 25px 60px rgba(0,0,0,.25);
        animation:cdn-popup-in .35s cubic-bezier(.34,1.56,.64,1) both;
        overflow:hidden;
    }
    .cdn-popup-card::before {
        content:'';position:absolute;top:0;left:0;right:0;height:4px;
        background:linear-gradient(90deg,var(--color-primary,#22609e),var(--color-accent-red,#e72127));
    }
    @keyframes cdn-popup-in {
        from{opacity:0;transform:scale(.88) translateY(18px)}
        to{opacity:1;transform:scale(1) translateY(0)}
    }
    .cdn-popup-close {
        position:absolute;top:.875rem;right:.875rem;
        width:2rem;height:2rem;border-radius:50%;
        display:flex;align-items:center;justify-content:center;
        background:var(--color-surface,#f8fafc);border:1px solid var(--color-border,#e2e8f0);
        color:var(--color-text-muted,#64748b);cursor:pointer;
        transition:background .15s,color .15s;font-size:1rem;
    }
    .cdn-popup-close:hover{background:var(--color-accent-red,#e72127);color:#fff;border-color:transparent;}
    .cdn-popup-icon {
        width:60px;height:60px;border-radius:50%;margin:0 auto 1rem;
        background:linear-gradient(135deg,var(--color-primary,#22609e),#4a90d9);
        display:flex;align-items:center;justify-content:center;
        color:#fff;font-size:1.75rem;
        box-shadow:0 8px 20px rgba(34,96,158,.3);
    }
    .cdn-popup-badge {
        font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;
        color:var(--color-primary,#22609e);margin-bottom:.625rem;
    }
    .cdn-popup-title {
        font-size:1.3rem;font-weight:900;line-height:1.25;
        color:var(--color-text-heading,#1a1a1a);margin-bottom:.75rem;
    }
    .cdn-popup-desc {
        font-size:.9rem;color:var(--color-text-muted,#64748b);
        line-height:1.6;margin-bottom:1.5rem;
    }
    .cdn-popup-desc strong{color:var(--color-primary,#22609e);}
    .cdn-popup-input-wrap {
        display:flex;gap:.5rem;margin-bottom:.75rem;
        border:2px solid var(--color-border,#e2e8f0);
        border-radius:10px;overflow:hidden;
        transition:border-color .2s;
    }
    .cdn-popup-input-wrap:focus-within{border-color:var(--color-primary,#22609e);}
    #cdn-popup-email {
        flex:1;padding:.75rem 1rem;border:none;outline:none;
        font-size:.875rem;background:transparent;
        color:var(--color-text,#333);min-width:0;
    }
    #cdn-popup-btn {
        display:flex;align-items:center;gap:.35rem;
        background:var(--color-primary,#22609e);color:#fff;
        padding:.75rem 1.1rem;font-size:.8rem;font-weight:700;
        white-space:nowrap;cursor:pointer;border:none;
        transition:background .15s;border-radius:0;
    }
    #cdn-popup-btn:hover{background:var(--color-primary-dark,#1a4e80);}
    #cdn-popup-btn .material-symbols-outlined{font-size:1rem;}
    .cdn-popup-msg {
        font-size:.8rem;margin-top:.5rem;font-weight:600;
        padding:.5rem .75rem;border-radius:6px;
    }
    .cdn-popup-skip {
        font-size:.75rem;color:var(--color-text-muted,#94a3b8);
        cursor:pointer;background:none;border:none;
        margin-top:.25rem;transition:color .15s;
    }
    .cdn-popup-skip:hover{color:var(--color-text,#333);}
    @media(max-width:480px){
        .cdn-popup-card{padding:2rem 1.25rem 1.5rem;}
        .cdn-popup-title{font-size:1.1rem;}
        .cdn-popup-input-wrap{flex-direction:column;overflow:visible;border:none;gap:.5rem;}
        #cdn-popup-email{border:2px solid var(--color-border,#e2e8f0);border-radius:8px;padding:.75rem 1rem;}
        #cdn-popup-btn{border-radius:8px;justify-content:center;padding:.875rem;}
        .cdn-popup-input-wrap:focus-within{border-color:transparent;}
    }
    </style>

    <script>
    (function(){
        var KEY_SHOWN     = 'cdn_popup_shown';
        var KEY_SUBSCRIBED= 'cdn_subscribed';
        var DELAY_MS      = 4000; // 4 segundos de espera antes de abrir

        // Verificar se já assinou ou já viu hoje
        if ( localStorage.getItem(KEY_SUBSCRIBED) ) return;
        var today = new Date().toDateString();
        if ( localStorage.getItem(KEY_SHOWN) === today ) return;

        var popup   = document.getElementById('cdn-newsletter-popup');
        var overlay = document.getElementById('cdn-popup-overlay');
        var form    = document.getElementById('cdn-popup-form');
        var msg     = document.getElementById('cdn-popup-msg');
        var btn     = document.getElementById('cdn-popup-btn');
        var btnClose= document.getElementById('cdn-popup-close');
        var btnSkip = document.getElementById('cdn-popup-skip');
        if (!popup) return;

        function openPopup(){
            popup.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closePopup(){
            popup.style.display = 'none';
            document.body.style.overflow = '';
            localStorage.setItem(KEY_SHOWN, today);
        }

        // Exibir após delay
        setTimeout(openPopup, DELAY_MS);

        btnClose.addEventListener('click', closePopup);
        btnSkip.addEventListener('click', closePopup);
        overlay.addEventListener('click', closePopup);
        document.addEventListener('keydown', function(e){ if(e.key==='Escape') closePopup(); });

        form.addEventListener('submit', function(e){
            e.preventDefault();
            var email = document.getElementById('cdn-popup-email').value.trim();
            if (!email) return;

            var original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined">hourglass_top</span> Enviando...';
            msg.style.display = 'none';

            var fd = new FormData(form);
            fd.set('email', email);

            fetch('<?php echo $ajax; ?>', {method:'POST', body:fd})
            .then(function(r){ return r.json(); })
            .then(function(res){
                btn.disabled = false;
                btn.innerHTML = original;
                if (res.success) {
                    msg.style.display = 'block';
                    msg.style.background = '#e8f5e9';
                    msg.style.color = '#2e7d32';
                    msg.textContent = '✅ ' + res.data.message;
                    localStorage.setItem(KEY_SUBSCRIBED, '1');
                    setTimeout(closePopup, 2500);
                } else {
                    msg.style.display = 'block';
                    msg.style.background = '#fce4ec';
                    msg.style.color = '#c62828';
                    msg.textContent = '❌ ' + (res.data ? res.data.message : 'Erro ao inscrever.');
                }
            })
            .catch(function(){
                btn.disabled = false;
                btn.innerHTML = original;
                msg.style.display = 'block';
                msg.style.background = '#fce4ec';
                msg.style.color = '#c62828';
                msg.textContent = '❌ Erro de conexão. Tente novamente.';
            });
        });
    })();
    </script>
    <?php
}, 98 ); // priority 98 — antes do SW (99)



// ============================================================
// CRON: ATUALIZAR CLIMA EM BACKGROUND (Sem bloquear requisies)
// ============================================================
add_filter( 'cron_schedules', function( $schedules ) {
    $schedules['cdn_30min'] = [
        'interval' => 30 * MINUTE_IN_SECONDS,
        'display'  => 'A cada 30 minutos',
    ];
    return $schedules;
});

if ( ! wp_next_scheduled( 'cdn_fetch_weather_cron' ) ) {
    wp_schedule_event( time(), 'cdn_30min', 'cdn_fetch_weather_cron' );
}

add_action( 'cdn_fetch_weather_cron', 'cdn_do_fetch_weather' );
function cdn_do_fetch_weather() {
    $r = wp_remote_get( 'https://wttr.in/Parnaiba+PI+Brasil?format=j1', [ 'timeout' => 10 ] );
    if ( is_wp_error( $r ) ) return;
    $b = json_decode( wp_remote_retrieve_body( $r ), true );
    if ( ! isset( $b['current_condition'][0] ) ) return;
    $c = $b['current_condition'][0];
    set_transient( 'cdn_tempo_parnaiba', [
        'temp'     => $c['temp_C'],
        'feels'    => $c['FeelsLikeC'],
        'humidity' => $c['humidity'],
        'wind'     => $c['windspeedKmph'],
        'desc'     => $c['lang_pt'][0]['value'] ?? $c['weatherDesc'][0]['value'],
    ], 35 * MINUTE_IN_SECONDS );
}

// ============================================================
// WIDGETS / SIDEBARS
// ============================================================
function cdn_register_sidebars() {
    $common = [ 'before_widget' => '', 'after_widget' => '', 'before_title' => '<h4 class="widget-title">', 'after_title' => '</h4>' ];
    register_sidebar( array_merge( $common, [
        'name' => __( 'Sidebar Principal', 'correiodonorte' ),
        'id'   => 'sidebar-main',
        'description' => 'Sidebar da página inicial e arquivo.',
    ] ) );
    register_sidebar( array_merge( $common, [
        'name' => __( 'Sidebar Single', 'correiodonorte' ),
        'id'   => 'sidebar-single',
        'description' => 'Sidebar da página de notícia.',
    ] ) );
}
add_action( 'widgets_init', 'cdn_register_sidebars' );

// ============================================================
// SEO — META TAGS & OPEN GRAPH
// ============================================================
function cdn_seo_meta_tags() {
    if ( is_admin() ) return;

    global $post;
    $site_name    = get_bloginfo( 'name' );
    $site_desc    = get_bloginfo( 'description' );
    $current_url  = ( is_ssl() ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $og_type      = 'website';
    $title        = '';
    $description  = '';
    $image_url    = '';

    if ( is_singular() && ! empty( $post ) ) {
        $og_type     = is_single() ? 'article' : 'website';
        $title       = get_the_title( $post );
        $description = wp_strip_all_tags( get_the_excerpt( $post ) );
        if ( has_post_thumbnail( $post ) ) {
            $img         = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'cdn-hero' );
            $image_url   = $img ? $img[0] : '';
        }
    } elseif ( is_category() || is_tag() || is_tax() ) {
        $term        = get_queried_object();
        $title       = $term->name . ' — ' . $site_name;
        $description = wp_strip_all_tags( term_description() );
    } elseif ( is_home() || is_front_page() ) {
        $title       = $site_name . ( $site_desc ? ' — ' . $site_desc : '' );
        $description = $site_desc;
    } elseif ( is_search() ) {
        $title       = 'Busca: ' . get_search_query() . ' — ' . $site_name;
        $description = 'Resultados de busca para "' . esc_html( get_search_query() ) . '" no ' . $site_name;
    } else {
        $title       = $site_name;
        $description = $site_desc;
    }

    if ( empty( $image_url ) ) {
        $image_url = get_template_directory_uri() . '/assets/images/og-default.png';
    }

    $description = mb_strimwidth( wp_strip_all_tags( $description ), 0, 160, '…' );

    echo "\n<!-- SEO & Open Graph — Correio do Norte -->\n";
    echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
    echo '<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">' . "\n";
    echo '<link rel="canonical" href="' . esc_url( $current_url ) . '">' . "\n";
    echo '<meta property="og:locale" content="pt_BR">' . "\n";
    echo '<meta property="og:type" content="' . esc_attr( $og_type ) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url( $current_url ) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '">' . "\n";
    if ( $image_url ) {
        echo '<meta property="og:image" content="' . esc_url( $image_url ) . '">' . "\n";
        echo '<meta property="og:image:width" content="1200">' . "\n";
        echo '<meta property="og:image:height" content="630">' . "\n";
    }
    // Twitter Card
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
    if ( $image_url ) {
        echo '<meta name="twitter:image" content="' . esc_url( $image_url ) . '">' . "\n";
    }
    // Article specific
    if ( is_single() && ! empty( $post ) ) {
        echo '<meta property="article:published_time" content="' . get_the_date( 'c', $post ) . '">' . "\n";
        echo '<meta property="article:modified_time" content="' . get_the_modified_date( 'c', $post ) . '">' . "\n";
        $cats = get_the_category( $post );
        if ( $cats ) {
            foreach ( $cats as $cat ) {
                echo '<meta property="article:section" content="' . esc_attr( $cat->name ) . '">' . "\n";
            }
        }
    }
    echo "<!-- / SEO -->\n\n";
}
add_action( 'wp_head', 'cdn_seo_meta_tags', 1 );

// ============================================================
// SCHEMA.ORG — NewsArticle (Single posts)
// ============================================================
function cdn_schema_markup() {
    if ( ! is_singular( 'post' ) ) return;
    global $post;
    setup_postdata( $post );

    $image_url = '';
    if ( has_post_thumbnail( $post ) ) {
        $img = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'cdn-hero' );
        $image_url = $img ? $img[0] : '';
    }

    $schema = [
        '@context'         => 'https://schema.org',
        '@type'            => 'NewsArticle',
        'headline'         => get_the_title( $post ),
        'description'      => wp_strip_all_tags( get_the_excerpt( $post ) ),
        'datePublished'    => get_the_date( 'c', $post ),
        'dateModified'     => get_the_modified_date( 'c', $post ),
        'url'              => get_permalink( $post ),
        'author'           => [
            '@type' => 'Person',
            'name'  => get_the_author_meta( 'display_name', $post->post_author ),
        ],
        'publisher'        => [
            '@type' => 'NewsMediaOrganization',
            'name'  => get_bloginfo( 'name' ),
            'url'   => home_url(),
            'logo'  => [
                '@type' => 'ImageObject',
                'url'   => get_template_directory_uri() . '/assets/images/logo.png',
            ],
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id'   => get_permalink( $post ),
        ],
    ];

    if ( $image_url ) {
        $schema['image'] = [ '@type' => 'ImageObject', 'url' => $image_url ];
    }

    $cats = get_the_category( $post );
    if ( $cats ) {
        $schema['articleSection'] = $cats[0]->name;
        $schema['keywords']       = implode( ', ', wp_list_pluck( $cats, 'name' ) );
    }

    echo "\n" . '<script type="application/ld+json">' . "\n";
    echo wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
    echo "\n" . '</script>' . "\n";

    wp_reset_postdata();
}
add_action( 'wp_head', 'cdn_schema_markup' );

// ============================================================
// SCHEMA.ORG — Website/Organization (homepage)
// ============================================================
function cdn_schema_website() {
    if ( ! ( is_home() || is_front_page() ) ) return;
    $schema = [
        '@context' => 'https://schema.org',
        '@type'    => 'NewsMediaOrganization',
        'name'     => get_bloginfo( 'name' ),
        'url'      => home_url(),
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => [ '@type' => 'EntryPoint', 'urlTemplate' => home_url( '/?s={search_term_string}' ) ],
            'query-input' => 'required name=search_term_string',
        ],
    ];
    echo "\n" . '<script type="application/ld+json">' . "\n";
    echo wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
    echo "\n" . '</script>' . "\n";
}
add_action( 'wp_head', 'cdn_schema_website' );

// ============================================================
// SITEMAP — Remover usuários (privacidade + SEO)
// ============================================================
add_filter( 'wp_sitemaps_add_provider', function( $provider, $name ) {
    // Remove a listagem de autores do sitemap nativo (/wp-sitemap-users-1.xml)
    if ( 'users' === $name ) return false;
    return $provider;
}, 10, 2 );

// ============================================================
// SEO — Breadcrumbs com BreadcrumbList Schema (JSON-LD)
// ============================================================
function cdn_breadcrumb_schema() {
    $items = [];
    $pos   = 1;

    // Sempre começa com Home
    $items[] = [
        '@type'    => 'ListItem',
        'position' => $pos++,
        'name'     => get_bloginfo( 'name' ),
        'item'     => home_url( '/' ),
    ];

    if ( is_singular( 'post' ) ) {
        $cats = get_the_category();
        if ( $cats ) {
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => esc_html( $cats[0]->name ),
                'item'     => esc_url( get_category_link( $cats[0]->term_id ) ),
            ];
        }
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => esc_html( get_the_title() ),
            'item'     => esc_url( get_permalink() ),
        ];
    } elseif ( is_category() ) {
        $cat = get_queried_object();
        if ( $cat->parent ) {
            $parent = get_category( $cat->parent );
            $items[] = [
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => esc_html( $parent->name ),
                'item'     => esc_url( get_category_link( $parent->term_id ) ),
            ];
        }
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => esc_html( $cat->name ),
            'item'     => esc_url( get_category_link( $cat->term_id ) ),
        ];
    } elseif ( is_tag() ) {
        $tag = get_queried_object();
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => esc_html( $tag->name ),
            'item'     => esc_url( get_tag_link( $tag->term_id ) ),
        ];
    } elseif ( is_search() ) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => 'Busca: ' . esc_html( get_search_query() ),
            'item'     => esc_url( get_search_link() ),
        ];
    } elseif ( is_page() ) {
        global $post;
        if ( $post->post_parent ) {
            $ancestors = array_reverse( get_post_ancestors( $post ) );
            foreach ( $ancestors as $ancestor_id ) {
                $items[] = [
                    '@type'    => 'ListItem',
                    'position' => $pos++,
                    'name'     => esc_html( get_the_title( $ancestor_id ) ),
                    'item'     => esc_url( get_permalink( $ancestor_id ) ),
                ];
            }
        }
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => esc_html( get_the_title() ),
            'item'     => esc_url( get_permalink() ),
        ];
    } elseif ( is_singular( 'vagas' ) ) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => 'Vagas de Emprego',
            'item'     => esc_url( home_url( '/vagas/' ) ),
        ];
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => esc_html( get_the_title() ),
            'item'     => esc_url( get_permalink() ),
        ];
    }

    // Só emite o script se houver mais de 1 item
    if ( count( $items ) < 2 ) return;

    $schema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ];

    echo "\n" . '<script type="application/ld+json">' . "\n";
    echo wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
    echo "\n" . '</script>' . "\n";
}
add_action( 'wp_head', 'cdn_breadcrumb_schema' );


// ============================================================
// SEGURANÇA — Cabeçalhos HTTP
// ============================================================
function cdn_security_headers() {
    if ( ! headers_sent() ) {
        header( 'X-Content-Type-Options: nosniff' );
        header( 'X-Frame-Options: SAMEORIGIN' );
        header( 'X-XSS-Protection: 1; mode=block' );
        header( 'Referrer-Policy: strict-origin-when-cross-origin' );
        header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
    }
}
add_action( 'send_headers', 'cdn_security_headers' );

// ============================================================
// SEGURANÇA — Ocultar versão do WP
// ============================================================
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

// Remover versão de scripts/estilos
function cdn_remove_version( $src ) {
    $parts = explode( '?ver', $src );
    return $parts[0];
}
add_filter( 'style_loader_src',  'cdn_remove_version', 9999 );
add_filter( 'script_loader_src', 'cdn_remove_version', 9999 );

// Remover cabeçalho X-Pingback
add_filter( 'wp_headers', function( $headers ) {
    unset( $headers['X-Pingback'] );
    return $headers;
} );

// Desabilitar XML-RPC
add_filter( 'xmlrpc_enabled', '__return_false' );

// Remover links desnecessários do head
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

// Desabilitar REST API para não logados (exceto rotas necessárias)
add_filter( 'rest_authentication_errors', function( $access ) {
    if ( ! is_user_logged_in() && ! is_null( $access ) ) return $access;
    if ( ! is_user_logged_in() ) {
        return new WP_Error( 'rest_not_logged_in', 'A API REST só está disponível para usuários autenticados.', [ 'status' => 401 ] );
    }
    return $access;
} );

// ============================================================
// SEGURANÇA — Bloquear enumeração de usuários
// ============================================================
function cdn_block_user_enumeration() {
    if ( ! is_admin() && isset( $_REQUEST['author'] ) && is_numeric( $_REQUEST['author'] ) ) {
        wp_redirect( home_url(), 301 );
        exit;
    }
    if ( ! is_admin() ) {
        parse_str( $_SERVER['QUERY_STRING'] ?? '', $qs );
        if ( isset( $qs['author'] ) && is_numeric( $qs['author'] ) ) {
            wp_redirect( home_url(), 301 );
            exit;
        }
    }
}
add_action( 'template_redirect', 'cdn_block_user_enumeration' );

// Ocultar erros de login (não informar se foi usuário ou senha errado)
add_filter( 'login_errors', function() {
    return 'Usuário ou senha incorretos.';
} );

// ============================================================
// EXCERPT
// ============================================================
add_filter( 'excerpt_length', fn() => 30 );
add_filter( 'excerpt_more',   fn() => '...' );

// ============================================================
// CACHE — Limpar transientes ao salvar/atualizar post
// ============================================================
add_action( 'save_post', function( $post_id ) {
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) return;
    delete_transient( 'cdn_most_read_posts' );
} );

// ============================================================
// AJAX — Formulário Fale Conosco (wp_mail com rate limit)
// ============================================================
add_action( 'wp_ajax_cdn_contact_form',        'cdn_ajax_contact_form' );
add_action( 'wp_ajax_nopriv_cdn_contact_form', 'cdn_ajax_contact_form' );
function cdn_ajax_contact_form() {
    // Verificar nonce
    check_ajax_referer( 'cdn_nonce', 'nonce' );

    // Honeypot anti-spam
    if ( ! empty( $_POST['website'] ) ) {
        wp_send_json_success( [ 'message' => 'Mensagem enviada com sucesso!' ] ); // Silencia o bot
    }

    // Rate limit por IP: 3 mensagens por hora
    $ip_key = 'cdn_contact_' . md5( $_SERVER['REMOTE_ADDR'] ?? 'unknown' );
    $attempts = (int) get_transient( $ip_key );
    if ( $attempts >= 3 ) {
        wp_send_json_error( [ 'message' => 'Muitas tentativas. Aguarde uma hora e tente novamente.' ] );
    }

    // Sanitizar entradas
    $nome     = sanitize_text_field( wp_unslash( $_POST['nome']     ?? '' ) );
    $email    = sanitize_email( wp_unslash( $_POST['email']         ?? '' ) );
    $assunto  = sanitize_text_field( wp_unslash( $_POST['assunto']  ?? '' ) );
    $mensagem = sanitize_textarea_field( wp_unslash( $_POST['mensagem'] ?? '' ) );

    // Validações
    if ( empty( $nome ) || strlen( $nome ) < 2 ) {
        wp_send_json_error( [ 'message' => 'Por favor, informe seu nome completo.' ] );
    }
    if ( ! is_email( $email ) ) {
        wp_send_json_error( [ 'message' => 'Por favor, informe um e-mail válido.' ] );
    }
    if ( empty( $assunto ) ) {
        wp_send_json_error( [ 'message' => 'Selecione o assunto da mensagem.' ] );
    }
    if ( empty( $mensagem ) || strlen( $mensagem ) < 10 ) {
        wp_send_json_error( [ 'message' => 'A mensagem precisa ter pelo menos 10 caracteres.' ] );
    }

    $site_name = get_bloginfo( 'name' );

    // Roteamento de e-mail por assunto
    $email_map = [
        'Sugestão de pauta'  => 'redacao@correiodonorte.com.br',
        'Denúncia'           => 'denuncia@correiodonorte.com.br',
        'Correção de matéria'=> 'errata@correiodonorte.com.br',
        'Publicidade'        => 'comercial@correiodonorte.com.br',
    ];
    $dest_email = $email_map[ $assunto ] ?? 'contato@portalcorreiodonorte.com.br';

    $subject    = '[' . $site_name . '] ' . $assunto . ' — via Fale Conosco';

    $body = '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"></head>
    <body style="margin:0;padding:0;background:#f4f4f8;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px;">
    <tr><td align="center">
    <table width="580" style="max-width:580px;width:100%;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">
      <tr><td style="background:#1a1a2e;padding:24px 32px;">
        <h2 style="margin:0;color:#fff;font-size:18px;">✉️ Nova mensagem — Fale Conosco</h2>
        <p style="margin:4px 0 0;font-size:12px;color:rgba(255,255,255,.6);">' . esc_html( $site_name ) . '</p>
      </td></tr>
      <tr><td style="padding:32px;">
        <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
          <tr><td style="padding:10px 0;border-bottom:1px solid #eee;font-size:13px;color:#888;width:120px;">Nome</td><td style="padding:10px 0;border-bottom:1px solid #eee;font-size:14px;font-weight:600;color:#1a1a2e;">' . esc_html( $nome ) . '</td></tr>
          <tr><td style="padding:10px 0;border-bottom:1px solid #eee;font-size:13px;color:#888;">E-mail</td><td style="padding:10px 0;border-bottom:1px solid #eee;font-size:14px;color:#1a1a2e;"><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></td></tr>
          <tr><td style="padding:10px 0;border-bottom:1px solid #eee;font-size:13px;color:#888;">Assunto</td><td style="padding:10px 0;border-bottom:1px solid #eee;font-size:14px;font-weight:600;color:#c0392b;">' . esc_html( $assunto ) . '</td></tr>
        </table>
        <div style="margin-top:24px;background:#f8f8ff;border-radius:8px;padding:20px;border-left:4px solid #1a1a2e;">
          <p style="margin:0;font-size:14px;line-height:1.7;color:#333;">' . nl2br( esc_html( $mensagem ) ) . '</p>
        </div>
      </td></tr>
      <tr><td style="padding:16px 32px;background:#f8f8f8;text-align:center;">
        <p style="margin:0;font-size:11px;color:#aaa;">Mensagem enviada em ' . current_time( 'd/m/Y \à\s H:i' ) . ' · IP: ' . esc_html( $_SERVER['REMOTE_ADDR'] ?? '—' ) . '</p>
      </td></tr>
    </table>
    </td></tr></table></body></html>';

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: ' . $nome . ' <' . $email . '>',
    ];

    $sent = wp_mail( $dest_email, $subject, $body, $headers );

    if ( $sent ) {
        // Incrementar rate limit
        set_transient( $ip_key, $attempts + 1, HOUR_IN_SECONDS );
        wp_send_json_success( [ 'message' => 'Mensagem enviada com sucesso! Respondemos em até 2 dias úteis. 📩' ] );
    } else {
        wp_send_json_error( [ 'message' => 'Não foi possível enviar a mensagem. Tente por e-mail diretamente.' ] );
    }
}


// ============================================================
// HELPER: Cor da categoria
// ============================================================
function cdn_category_color( $cat_slug ) {
    $map = [
        'economia'   => 'economia',
        'esportes'   => 'esportes',
        'cultura'    => 'cultura',
        'politica'   => 'politica',
        'política'   => 'politica',
        'tecnologia' => 'tecnologia',
    ];
    return $map[ mb_strtolower( $cat_slug ) ] ?? 'default';
}

// ============================================================
// HELPER: Breaking news (últimas 6 notícias para ticker)
// ============================================================
function cdn_get_breaking_news() {
    return get_posts( [
        'numberposts' => 6,
        'post_status' => 'publish',
        'orderby'     => 'date',
        'order'       => 'DESC',
    ] );
}

// ============================================================
// HELPER: Leitura estimada
// ============================================================
function cdn_reading_time( $post_id = null ) {
    $content   = get_post_field( 'post_content', $post_id );
    $word_count = str_word_count( wp_strip_all_tags( $content ) );
    $minutes    = max( 1, (int) ceil( $word_count / 200 ) );
    return $minutes . ' min de leitura';
}

// ============================================================
// PAGINAÇÃO
// ============================================================
function cdn_pagination( $args = [] ) {
    $defaults = [
        'prev_text' => '<span class="material-symbols-outlined">chevron_left</span>',
        'next_text' => '<span class="material-symbols-outlined">chevron_right</span>',
        'type'      => 'plain',
    ];
    echo paginate_links( array_merge( $defaults, $args ) );
}

// ============================================================
// CUSTOMIZER — Cores e informações básicas
// ============================================================
function cdn_customizer( $wp_customize ) {
    // Site Info section
    $wp_customize->add_section( 'cdn_info', [
        'title'    => 'Informações do Portal',
        'priority' => 25,
    ] );

    // Breaking news
    $wp_customize->add_setting( 'cdn_breaking_text', [
        'default'           => 'Acompanhe todas as notícias em tempo real.',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'cdn_breaking_text', [
        'label'   => 'Texto do Breaking News',
        'section' => 'cdn_info',
        'type'    => 'text',
    ] );

    // Phone
    $wp_customize->add_setting( 'cdn_phone', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    $wp_customize->add_control( 'cdn_phone', [
        'label'   => 'Telefone de contato',
        'section' => 'cdn_info',
        'type'    => 'text',
    ] );

    // Commercial email
    $wp_customize->add_setting( 'cdn_commercial_email', [
        'default'           => 'comercial@correiodonorte.com.br',
        'sanitize_callback' => 'sanitize_email',
    ] );
    $wp_customize->add_control( 'cdn_commercial_email', [
        'label'   => 'E-mail Comercial',
        'section' => 'cdn_info',
        'type'    => 'email',
    ] );

    // Social links
    foreach ( [ 'facebook' => 'Facebook URL', 'instagram' => 'Instagram URL', 'twitter' => 'Twitter/X URL', 'youtube' => 'YouTube URL' ] as $key => $label ) {
        $wp_customize->add_setting( "cdn_social_{$key}", [ 'default' => '', 'sanitize_callback' => 'esc_url_raw' ] );
        $wp_customize->add_control( "cdn_social_{$key}", [ 'label' => $label, 'section' => 'cdn_info', 'type' => 'url' ] );
    }
}
add_action( 'customize_register', 'cdn_customizer' );

// ============================================================
// ADMIN BAR — só exibir para admins no frontend
// ============================================================
add_filter( 'show_admin_bar', fn( $show ) => current_user_can( 'manage_options' ) ? $show : false );

// ============================================================
// IMAGEM PADRÃO para posts sem featured image
// ============================================================
function cdn_default_thumbnail( $html, $post_id ) {
    if ( ! $html ) {
        $placeholder = get_template_directory_uri() . '/assets/images/placeholder.svg';
        $html = '<img src="' . esc_url( $placeholder ) . '" alt="' . esc_attr( get_the_title( $post_id ) ) . '" loading="lazy">';
    }
    return $html;
}
add_filter( 'post_thumbnail_html', 'cdn_default_thumbnail', 10, 2 );

// ============================================================
// ATALHO NA BARRA DE ADMINISTRAÇÃO (ADMIN BAR)
// ============================================================
function cdn_add_admin_bar_link( $wp_admin_bar ) {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $args = array(
        'id'    => 'cdn_theme_options_link',
        'title' => '<span class="ab-icon dashicons-before dashicons-admin-generic"></span> Painel CDN',
        'href'  => admin_url( 'themes.php?page=painel-cdn' ),
        'meta'  => array(
            'title' => 'Acessar as Configurações do Tema',
        ),
    );
    $wp_admin_bar->add_node( $args );
}
add_action( 'admin_bar_menu', 'cdn_add_admin_bar_link', 999 );

// ============================================================
// AJAX — Newsletter (honeypot + nonce)
// ============================================================
add_action( 'wp_ajax_nopriv_cdn_newsletter', 'cdn_handle_newsletter' );
add_action( 'wp_ajax_cdn_newsletter',         'cdn_handle_newsletter' );
function cdn_handle_newsletter() {
    check_ajax_referer( 'cdn_nonce', 'nonce' );
    // Honeypot check
    if ( ! empty( $_POST['website'] ) ) {
        wp_send_json_error( 'Bot detected.' );
    }
    $email = sanitize_email( $_POST['email'] ?? '' );
    if ( ! is_email( $email ) ) {
        wp_send_json_error( 'E-mail inválido.' );
    }
    // TODO: integrar com MailChimp/Sendinblue/etc.
    wp_send_json_success( 'Inscrição realizada com sucesso!' );
}

// ============================================================
// LIMPEZA — remover emojis para performance
// ============================================================
remove_action( 'wp_head',             'print_emoji_detection_script', 7 );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'wp_print_styles',     'print_emoji_styles' );
remove_action( 'admin_print_styles',  'print_emoji_styles' );
remove_filter( 'the_content_feed',    'wp_staticize_emoji' );
remove_filter( 'comment_text_rss',    'wp_staticize_emoji' );
remove_filter( 'wp_mail',             'wp_staticize_emoji_for_email' );

// ============================================================
// CONTADOR DE VISUALIZAÇÕES (NATIVO PHP)
// ============================================================
function cdn_track_post_views($post_id) {
    if ( !is_single() ) return;
    if ( empty ( $post_id) ) {
        global $post;
        $post_id = $post->ID;
    }
    cdn_set_post_views($post_id);
}
function cdn_set_post_views($post_ID) {
    $count_key = 'post_views_count';
    $count = get_post_meta($post_ID, $count_key, true);
    if($count == ''){
        $count = 0;
        delete_post_meta($post_ID, $count_key);
        add_post_meta($post_ID, $count_key, '1');
    }else{
        $count++;
        update_post_meta($post_ID, $count_key, $count);
    }
}

// ============================================================
// LIMPEZA — ocultar metabox de categorias não utilizadas
// ============================================================
function cdn_custom_login_style() {
    // Remove WP default logo on login
    echo '<style>.login h1 a { background-size: contain; background-image: url(' . get_template_directory_uri() . '/assets/images/logo.png) !important; }</style>';
}
add_action( 'login_head', 'cdn_custom_login_style' );

// ============================================================
// AJAX LOAD MORE POSTS
// ============================================================
add_action('wp_ajax_cdn_load_more', 'cdn_load_more_ajax');
add_action('wp_ajax_nopriv_cdn_load_more', 'cdn_load_more_ajax');
function cdn_load_more_ajax() {
    check_ajax_referer('cdn_nonce', 'nonce');
    
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 7;
    $ppp    = isset($_POST['ppp']) ? intval($_POST['ppp']) : 6;
    
    $args = [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => $ppp,
        'offset'              => $offset,
        'ignore_sticky_posts' => true
    ];
    
    $query = new WP_Query($args);
    
    if ( $query->have_posts() ) {
        ob_start();
        while ( $query->have_posts() ) {
            $query->the_post();
            get_template_part('template-parts/content', 'card');
        }
        $html = ob_get_clean();
        
        $more_posts = 0;
        if ( ($offset + $ppp) < $query->found_posts ) {
            $more_posts = 1;
        }

        wp_send_json_success([
            'html' => $html,
            'has_more' => $more_posts
        ]);
    } else {
        wp_send_json_error(['message' => 'Nenhuma matéria encontrada.']);
    }
    wp_die();
}

// ============================================================
// CPT VAGAS DE EMPREGO
// ============================================================
add_action('init', 'cdn_registrar_cpt_vagas');
function cdn_registrar_cpt_vagas() {
    $labels = [
        'name'               => 'Vagas',
        'singular_name'      => 'Vaga',
        'menu_name'          => 'Vagas de Emprego',
        'add_new'            => 'Adicionar Nova',
        'add_new_item'       => 'Adicionar Nova Vaga',
        'edit_item'          => 'Editar Vaga',
        'new_item'           => 'Nova Vaga',
        'view_item'          => 'Ver Vaga',
        'search_items'       => 'Procurar Vagas',
        'not_found'          => 'Nenhuma vaga encontrada',
        'not_found_in_trash' => 'Nenhuma vaga na lixeira',
        'all_items'          => 'Todas as Vagas',
    ];
    $args = [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => [ 'slug' => 'vagas' ],
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-businessperson',
        'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
    ];
    register_post_type('vagas', $args);
}

// Metaboxes para as Vagas
add_action('add_meta_boxes', 'cdn_vagas_add_metaboxes');
function cdn_vagas_add_metaboxes() {
    add_meta_box('cdn_vagas_meta_id', 'Dados da Vaga', 'cdn_vagas_meta_callback', 'vagas', 'normal', 'high');
}

function cdn_vagas_meta_callback($post) {
    wp_nonce_field('cdn_vagas_save_meta', 'cdn_vagas_meta_nonce');
    $empresa = get_post_meta($post->ID, 'cdn_vaga_empresa', true);
    $salario = get_post_meta($post->ID, 'cdn_vaga_salario', true);
    $local   = get_post_meta($post->ID, 'cdn_vaga_local', true);
    $link    = get_post_meta($post->ID, 'cdn_vaga_link', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="cdn_vaga_empresa">Empresa</label></th>
            <td><input type="text" id="cdn_vaga_empresa" name="cdn_vaga_empresa" value="<?php echo esc_attr($empresa); ?>" class="regular-text" placeholder="Ex: Correio do Norte"></td>
        </tr>
        <tr>
            <th><label for="cdn_vaga_salario">Faixa Salarial</label></th>
            <td><input type="text" id="cdn_vaga_salario" name="cdn_vaga_salario" value="<?php echo esc_attr($salario); ?>" class="regular-text" placeholder="Ex: R$ 2.000 - R$ 3.500"></td>
        </tr>
        <tr>
            <th><label for="cdn_vaga_local">Local de Trabalho</label></th>
            <td><input type="text" id="cdn_vaga_local" name="cdn_vaga_local" value="<?php echo esc_attr($local); ?>" class="regular-text" placeholder="Ex: Parnaíba, PI"></td>
        </tr>
        <tr>
            <th><label for="cdn_vaga_link">Link/Contato</label></th>
            <td><input type="url" id="cdn_vaga_link" name="cdn_vaga_link" value="<?php echo esc_attr($link); ?>" class="regular-text" placeholder="https://..."></td>
        </tr>
    </table>
    <?php
}

add_action('save_post', 'cdn_vagas_save_meta');
function cdn_vagas_save_meta($post_id) {
    if (!isset($_POST['cdn_vagas_meta_nonce']) || !wp_verify_nonce($_POST['cdn_vagas_meta_nonce'], 'cdn_vagas_save_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['cdn_vaga_empresa'])) update_post_meta($post_id, 'cdn_vaga_empresa', sanitize_text_field($_POST['cdn_vaga_empresa']));
    if (isset($_POST['cdn_vaga_salario'])) update_post_meta($post_id, 'cdn_vaga_salario', sanitize_text_field($_POST['cdn_vaga_salario']));
    if (isset($_POST['cdn_vaga_local'])) update_post_meta($post_id, 'cdn_vaga_local', sanitize_text_field($_POST['cdn_vaga_local']));
    if (isset($_POST['cdn_vaga_link'])) update_post_meta($post_id, 'cdn_vaga_link', esc_url_raw($_POST['cdn_vaga_link']));
}

// ==========================================
// PROCESSAMENTO DE SUBMISSÃO DE VAGAS FRONT-END
// ==========================================
add_action('admin_post_nopriv_submit_vaga', 'cdn_process_vaga_submission');
add_action('admin_post_submit_vaga', 'cdn_process_vaga_submission');

function cdn_process_vaga_submission() {
    // Verificar Nonce de Segurança
    if ( !isset($_POST['vaga_nonce']) || !wp_verify_nonce($_POST['vaga_nonce'], 'submit_new_vaga') ) {
        wp_die('Falha na validação de segurança. Tente novamente.');
    }

    $titulo    = sanitize_text_field($_POST['vaga_titulo']);
    $empresa   = sanitize_text_field($_POST['vaga_empresa']);
    $salario   = sanitize_text_field($_POST['vaga_salario']);
    $local     = sanitize_text_field($_POST['vaga_local']);
    $email     = sanitize_email($_POST['vaga_email']);
    $descricao = wp_kses_post($_POST['vaga_descricao']);

    if (empty($titulo) || empty($empresa) || empty($email) || empty($descricao)) {
        wp_redirect(add_query_arg('status', 'error', wp_get_referer()));
        exit;
    }

    // Criar o Post como Pendente
    $post_data = array(
        'post_title'   => $titulo,
        'post_content' => $descricao,
        'post_status'  => 'pending',
        'post_type'    => 'vagas',
    );

    $post_id = wp_insert_post($post_data);

    if ($post_id) {
        // Salvar Metadados da Vaga
        update_post_meta($post_id, 'cdn_vaga_empresa', $empresa);
        update_post_meta($post_id, 'cdn_vaga_salario', $salario);
        update_post_meta($post_id, 'cdn_vaga_local', $local);
        update_post_meta($post_id, 'cdn_vaga_link', 'mailto:' . $email);
        update_post_meta($post_id, 'cdn_vaga_submitter_email', $email);

        // Notificar Administrador por E-mail (HTML)
        $admin_email = get_option('admin_email');
        $subject = 'Nova vaga anunciada (Pendente de Aprovação): ' . $titulo;
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        ob_start();
        ?>
        <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 30px 20px; background-color: #f4f7f6;">
            <div style="text-align: center; margin-bottom: 30px;">
                <div style="display: inline-block; background: #ffffff; padding: 15px; border-radius: 50%; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px;">
                    <span style="font-size: 32px; color: #e50914;">📋</span>
                </div>
                <h2 style="color: #1a1a1a; margin: 0; font-size: 24px;">Nova Oportunidade Submetida</h2>
                <p style="color: #666; font-size: 16px; margin-top: 8px;">Uma nova vaga enviada pela comunidade aguarda sua revisão.</p>
            </div>
            
            <div style="background-color: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                <table style="width: 100%; border-collapse: collapse; font-size: 15px;">
                    <tr>
                        <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; width: 35%; align-items: center;"><strong style="color: #1a1a1a;">📌 Título:</strong></td>
                        <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; color: #4a4a4a;"><?php echo esc_html($titulo); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0;"><strong style="color: #1a1a1a;">🏢 Empresa:</strong></td>
                        <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; color: #4a4a4a;"><strong><?php echo esc_html($empresa); ?></strong></td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0;"><strong style="color: #1a1a1a;">📍 Local:</strong></td>
                        <td style="padding: 12px 0; border-bottom: 1px solid #f0f0f0; color: #4a4a4a;"><?php echo esc_html($local); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 12px 0;"><strong style="color: #1a1a1a;">✉️ E-mail:</strong></td>
                        <td style="padding: 12px 0;"><a href="mailto:<?php echo esc_attr($email); ?>" style="color: #e50914; text-decoration: none; font-weight: 500;"><?php echo esc_html($email); ?></a></td>
                    </tr>
                </table>
            </div>
            
            <div style="text-align: center; margin-top: 40px; margin-bottom: 20px;">
                <a href="<?php echo admin_url('edit.php?post_status=pending&post_type=vagas'); ?>" style="background-color: #e50914; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; font-size: 16px; box-shadow: 0 4px 10px rgba(229, 9, 20, 0.3);">Revisar Vaga no Painel</a>
            </div>
            
            <p style="text-align: center; font-size: 13px; color: #a0a0a0; margin-top: 30px; border-top: 1px solid #e0e0e0; padding-top: 20px;">
                Este é um alerta automático do portal comercial <strong>Correio do Norte</strong>.
            </p>
        </div>
        <?php
        $message = ob_get_clean();

        wp_mail($admin_email, $subject, $message, $headers);

        // Redirecionar com Sucesso
        wp_redirect(add_query_arg('status', 'success', wp_get_referer()));
        exit;
    } else {
        wp_redirect(add_query_arg('status', 'error', wp_get_referer()));
        exit;
    }
}

// ==========================================
// NOTIFICAÇÃO DE VAGA APROVADA (PARA O SUBMETENTE)
// ==========================================
add_action('transition_post_status', 'cdn_notificar_vaga_aprovada', 10, 3);
function cdn_notificar_vaga_aprovada($new_status, $old_status, $post) {
    if ($post->post_type === 'vagas' && $old_status === 'pending' && $new_status === 'publish') {
        $email_submitter = get_post_meta($post->ID, 'cdn_vaga_submitter_email', true);
        
        // Fallback pra pegar do link se for vaga antiga
        if (empty($email_submitter)) {
            $link = get_post_meta($post->ID, 'cdn_vaga_link', true);
            if (strpos($link, 'mailto:') === 0) {
                $email_submitter = str_replace('mailto:', '', $link);
            }
        }

        if (is_email($email_submitter)) {
            $subject = '✔️ Sua vaga foi aprovada e publicada!';
            $headers = array('Content-Type: text/html; charset=UTF-8');
            
            $titulo = get_the_title($post->ID);
            $url = get_permalink($post->ID);
            
            ob_start();
            ?>
            <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 30px; background-color: #f4f7f6;">
                <div style="text-align: center; margin-bottom: 30px;">
                    <div style="display: inline-block; background: #28a745; padding: 15px; border-radius: 50%; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px;">
                        <span style="font-size: 32px; color: #ffffff;">💼</span>
                    </div>
                    <h2 style="color: #1a1a1a; margin: 0; font-size: 24px;">Vaga Publicada com Sucesso!</h2>
                    <p style="color: #666; font-size: 16px; margin-top: 8px;">A oportunidade que você nos enviou acaba de ser aprovada e publicada no portal.</p>
                </div>
                
                <div style="background-color: #ffffff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); text-align: center;">
                    <h3 style="margin-top:0; color:#1a1a1a; font-size: 20px;"><?php echo esc_html($titulo); ?></h3>
                    <p style="color: #666; font-size: 15px; line-height: 1.6; margin-bottom: 25px;">Sua vaga já está disponível em nosso Hub de Empregos e está visível para milhares de leitores do <strong>Correio do Norte</strong>.</p>
                    
                    <a href="<?php echo esc_url($url); ?>" style="background-color: #e50914; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; font-size: 16px; box-shadow: 0 4px 10px rgba(229, 9, 20, 0.3);">Ver Minha Vaga no Site</a>
                </div>

                <p style="text-align: center; font-size: 13px; color: #a0a0a0; margin-top: 30px; border-top: 1px solid #e0e0e0; padding-top: 20px;">
                    Atenciosamente,<br>Equipe Comercial - Correio do Norte
                </p>
            </div>
            <?php
            $message = ob_get_clean();

            wp_mail($email_submitter, $subject, $message, $headers);
        }
    }
}

<?php
/**
 * sidebar.php — Sidebar principal do tema Correio do Norte
 */
?>
<aside class="sidebar" role="complementary" aria-label="Barra lateral">

    <!-- Widget: Busca -->
    <div class="widget-box widget-search">
        <h4 class="widget-title">
            <span class="material-symbols-outlined" aria-hidden="true">search</span>
            Pesquisar
        </h4>
        <div class="search-form-inner">
            <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                <label for="sidebar-search-input" class="sr-only">Pesquisar</label>
                <input type="search" id="sidebar-search-input" name="s" placeholder="O que você procura?" value="<?php echo get_search_query(); ?>" autocomplete="off">
                <span class="material-symbols-outlined search-icon" aria-hidden="true">search</span>
            </form>
        </div>
    </div>

    <!-- Widget: Mais Lidas -->
    <?php
    $most_read = get_transient( 'cdn_most_read_posts' );
    if ( false === $most_read ) {
        $most_read = get_posts( [
            'numberposts' => 5,
            'post_status' => 'publish',
            'meta_key'    => 'post_views_count',
            'orderby'     => 'meta_value_num',
            'order'       => 'DESC',
            'ignore_sticky_posts' => true,
        ] );
        // Fallback: recentes se não tiver contagem de visualizações
        if ( empty( $most_read ) ) {
            $most_read = get_posts( [ 'numberposts' => 5, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ] );
        }
        set_transient( 'cdn_most_read_posts', $most_read, 30 * MINUTE_IN_SECONDS );
    }

    if ( $most_read ) : ?>
    <div class="widget-box">
        <h4 class="widget-title">
            <span class="material-symbols-outlined" aria-hidden="true">trending_up</span>
            Mais Lidas
        </h4>
        <ul class="most-read-list">
            <?php foreach ( $most_read as $index => $item ) :
                $cats = get_the_category( $item->ID );
                $cat_name = $cats ? $cats[0]->name : 'Geral';
            ?>
            <li class="most-read-item">
                <a href="<?php echo esc_url( get_permalink( $item ) ); ?>" aria-hidden="true" tabindex="-1">
                    <span class="most-read-rank"><?php echo str_pad( $index + 1, 2, '0', STR_PAD_LEFT ); ?></span>
                </a>
                <div>
                    <a href="<?php echo esc_url( get_permalink( $item ) ); ?>" class="most-read-title">
                        <?php echo esc_html( get_the_title( $item ) ); ?>
                    </a>
                    <span class="most-read-cat"><?php echo esc_html( $cat_name ); ?></span>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <?php
    // Widget: Agenda de Futebol — Parnahyba SC (redesigned)
    $jogos = get_option( 'cdn_futebol_proximos', [] );
    if ( empty( $jogos ) ) {
        $jogos = [
            [ 'data' => '05/04', 'hora' => 'A Dif.', 'casa' => 'A Definir', 'fora' => 'Parnahyba SC', 'competicao' => 'Br. Série D', 'rodada' => '1ª Rod.' ],
            [ 'data' => '12/04', 'hora' => 'A Dif.', 'casa' => 'Parnahyba SC', 'fora' => 'A Definir', 'competicao' => 'Br. Série D', 'rodada' => '2ª Rod.' ],
        ];
    } else {
        // Renomear chaves `mandante`/`visitante` para a estrutura que a sidebar antiga usa `casa`/`fora`
        foreach ( $jogos as &$j ) {
            $j['casa'] = $j['mandante'];
            $j['fora'] = $j['visitante'];
            $j['competicao'] = $j['comp'];
            $j['rodada'] = '';
        }
    }
    ?>
    <div class="widget-box widget-futebol">
        <div class="futebol-header">
            <span class="futebol-header-left">
                <span class="material-symbols-outlined" aria-hidden="true">sports_soccer</span>
                Parnahyba SC
            </span>
            <span class="futebol-header-tag">Próximos Jogos</span>
        </div>
        <div class="partidas-list">
        <?php foreach ( $jogos as $i => $jogo ) :
            $parna_casa = ( $jogo['casa'] === 'Parnahyba SC' );
        ?>
            <div class="partida-card <?php echo $i === 0 ? 'partida-proxima' : ''; ?>">
                <?php if ( $i === 0 ) : ?>
                <div class="partida-proximo-badge">⚡ Próximo jogo</div>
                <?php endif; ?>
                <div class="partida-times">
                    <div class="partida-time <?php echo $parna_casa ? 'time-destaque' : ''; ?>">
                        <div class="time-escudo <?php echo $parna_casa ? 'escudo-parna' : 'escudo-adversario'; ?>">
                            <?php echo $parna_casa ? '🔵' : '⚪'; ?>
                        </div>
                        <span class="time-nome"><?php echo esc_html( $jogo['casa'] ); ?></span>
                    </div>
                    <div class="partida-vs">
                        <span class="vs-hora"><?php echo esc_html( $jogo['hora'] ); ?></span>
                        <span class="vs-texto">×</span>
                        <span class="vs-data"><?php echo esc_html( $jogo['data'] ); ?></span>
                    </div>
                    <div class="partida-time <?php echo ! $parna_casa ? 'time-destaque' : ''; ?>">
                        <div class="time-escudo <?php echo ! $parna_casa ? 'escudo-parna' : 'escudo-adversario'; ?>">
                            <?php echo ! $parna_casa ? '🔵' : '⚪'; ?>
                        </div>
                        <span class="time-nome"><?php echo esc_html( $jogo['fora'] ); ?></span>
                    </div>
                </div>
                <div class="partida-footer">
                    <span class="partida-comp"><?php echo esc_html( $jogo['competicao'] ); ?></span>
                    <span class="partida-rodada"><?php echo esc_html( $jogo['rodada'] ); ?></span>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        <a href="<?php echo esc_url( home_url( '/tabela-futebol/' ) ); ?>" class="ver-todos-jogos">Ver tabela completa →</a>
    </div>

    <!-- Widget: Jornal Digital -->
    <div class="widget-box widget-digital">
        <h4 class="widget-title">

            <span class="material-symbols-outlined" aria-hidden="true">menu_book</span>
            Jornal Digital
        </h4>
        <div class="digital-cover">
            <?php 
            $cover = get_option( 'cdn_digital_cover' );
            if ( ! $cover ) $cover = content_url( 'uploads/2026/03/edicao-digital.jpg' );
            ?>
            <img src="<?php echo esc_url( $cover ); ?>"
                 alt="Capa do jornal impresso Correio do Norte"
                 loading="lazy">
            <div class="hover-btn">
                <button onclick="window.location.href='<?php echo esc_url( home_url( '/edicao-digital/' ) ); ?>'">Abrir Edição</button>
            </div>
        </div>
        <p class="digital-date">Edição Atualizada</p>
    </div>

    <!-- Widget: Eventos Culturais -->
    <?php
    // Eventos fictícios — prontos para serem substituídos por um CPT ou plugin de eventos
    $eventos = [
        [
            'data'   => '08/03',
            'titulo' => 'Mostra de Artes Visuais do Piauí',
            'local'  => 'Casa da Cultura, Parnaíba',
            'link'   => '#',
        ],
        [
            'data'   => '12/03',
            'titulo' => 'Festival de Música Nordestina',
            'local'  => 'Praça da Graça',
            'link'   => '#',
        ],
        [
            'data'   => '15/03',
            'titulo' => 'Feira do Livro Piauiense 2026',
            'local'  => 'Centro de Convenções',
            'link'   => '#',
        ],
        [
            'data'   => '20/03',
            'titulo' => 'Espetáculo: Lendas do Delta',
            'local'  => 'Teatro Municipal',
            'link'   => '#',
        ],
        [
            'data'   => '26/03',
            'titulo' => 'Exposição Fotográfica Amazônia',
            'local'  => 'Museu do Vento Norte',
            'link'   => '#',
        ],
    ];
    ?>
    <div class="widget-box widget-eventos">
        <h4 class="widget-title">
            <span class="material-symbols-outlined" aria-hidden="true">event</span>
            Eventos Culturais
        </h4>
        <ul class="eventos-list">
            <?php foreach ( $eventos as $ev ) : ?>
            <li class="evento-item">
                <span class="evento-data"><?php echo esc_html( $ev['data'] ); ?></span>
                <div class="evento-info">
                    <a href="<?php echo esc_url( $ev['link'] ); ?>" class="evento-titulo"><?php echo esc_html( $ev['titulo'] ); ?></a>
                    <span class="evento-local">
                        <span class="material-symbols-outlined" style="font-size:.8rem;vertical-align:middle" aria-hidden="true">location_on</span>
                        <?php echo esc_html( $ev['local'] ); ?>
                    </span>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <a href="<?php echo esc_url( home_url( '/eventos/' ) ); ?>" class="ver-todos-eventos">Ver todos os eventos &rarr;</a>
    </div>

    <!-- Widget: Área publicitária (Dinâmica em Loop) -->
    <?php
    for ( $i = 1; $i <= 7; $i++ ) {
        $img = get_option( 'cdn_sidebar_ad' . $i . '_img' );
        $link = get_option( 'cdn_sidebar_ad' . $i . '_link' );
        
        // Tratar fallback para o card 1 se não houver configurações, para manter a demonstração
        if ( ! $img ) {
            if ( $i === 1 ) {
                $img = content_url( 'uploads/2026/03/ad-sidebar-supermercado.jpg' );
                $link = home_url( '/anuncie/' );
            } else {
                continue; // Pular as que não existem
            }
        }
        
        if ( ! $link ) $link = home_url( '/anuncie/' );
        ?>
        <div class="sidebar-ad" role="complementary" aria-label="Espaço publicitário">
            <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener sponsored">
                <img src="<?php echo esc_url( $img ); ?>"
                     alt="Publicidade Correio do Norte"
                     loading="lazy">
            </a>
        </div>
        <?php
    }
    ?>

    <!-- Widgets registrados via WP -->
    <?php
    if ( is_active_sidebar( 'sidebar-main' ) ) {
        dynamic_sidebar( 'sidebar-main' );
    }
    ?>

</aside>

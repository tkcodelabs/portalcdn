<?php
/**
 * Template Name: Eventos Culturais
 * Template Post Type: page
 */
get_header();

// Obter eventos do banco de dados (Painel CDN > Eventos)
$eventos_lista = get_option( 'cdn_eventos_lista', [] );

// Fallback para eventos fictícios se a lista estiver vazia
if ( empty( $eventos_lista ) ) {
    $eventos_lista = [
        [
            'data'      => '08/03/2026',
            'dia_semana'=> 'Domingo',
            'hora'      => '19:00',
            'titulo'    => 'Mostra de Artes Visuais do Piauí',
            'local'     => 'Casa da Cultura, Parnaíba',
            'desc'      => 'Exposição com artistas locais explorando raízes nordestinas através da pintura e escultura. Entrada Gratuita.',
            'categ'     => 'Exposição',
            'link'      => '#',
            'destaque'  => true
        ],
        [
            'data'      => '12/03/2026',
            'dia_semana'=> 'Quinta-feira',
            'hora'      => '20:30',
            'titulo'    => 'Festival de Música Nordestina',
            'local'     => 'Praça da Graça',
            'desc'      => 'Bandas regionais de forró pé-de-serra animam a noite no centro histórico da cidade.',
            'categ'     => 'Música',
            'link'      => '#',
            'destaque'  => false
        ],
        [
            'data'      => '15/03/2026',
            'dia_semana'=> 'Domingo',
            'hora'      => '14:00 às 22:00',
            'titulo'    => 'Feira do Livro Piauiense 2026',
            'local'     => 'Centro de Convenções',
            'desc'      => 'Encontro com centenas de autores locais, debates sobre a literatura regional, oficinas gratuitas para jovens e lançamentos de livros.',
            'categ'     => 'Literatura',
            'link'      => '#',
            'destaque'  => true
        ],
        [
            'data'      => '20/03/2026',
            'dia_semana'=> 'Sexta-feira',
            'hora'      => '19:00',
            'titulo'    => 'Espetáculo: Lendas do Delta',
            'local'     => 'Teatro Municipal',
            'desc'      => 'Peça de teatro que reconta fábulas e mitos dos pescadores originários do Delta do Parnaíba.',
            'categ'     => 'Teatro',
            'link'      => '#',
            'destaque'  => false
        ],
        [
            'data'      => '26/03/2026',
            'dia_semana'=> 'Quinta-feira',
            'hora'      => '10:00 às 18:00',
            'titulo'    => 'Exposição Fotográfica Amazônia',
            'local'     => 'Museu do Vento Norte',
            'desc'      => 'Fotografias vencedoras do concurso nacional de preservação ambiental na zona rural do norte.',
            'categ'     => 'Fotografia',
            'link'      => '#',
            'destaque'  => false
        ],
        [
            'data'      => '02/04/2026',
            'dia_semana'=> 'Quinta-feira',
            'hora'      => '16:00',
            'titulo'    => 'Oficina de Cordel para Iniciantes',
            'local'     => 'Biblioteca Pública Estadual',
            'desc'      => 'Aprenda métrica, rima, xilogravura e a história da literatura de cordel com mestres renomados.',
            'categ'     => 'Oficina',
            'link'      => '#',
            'destaque'  => false
        ]
    ];
}
?>
<main id="main-content" class="inst-page eventos-page">

    <!-- HERO -->
    <section class="inst-hero eventos-hero" style="background:#2C0B3D">
        <div class="inst-hero-bg" aria-hidden="true">
            <div class="hero-orb hero-orb-1" style="background:rgba(219,39,119,.4); animation-duration: 18s"></div>
            <div class="hero-orb hero-orb-2" style="background:rgba(255,165,0,.3)"></div>
        </div>
        <div class="container inst-hero-inner">
            <div class="inst-hero-badge" style="background: rgba(255,255,255,0.1); color: #FFD700; border-color: rgba(255,215,0,0.3)">🎭 Agenda Cultural de Parnaíba</div>
            <h1 class="inst-hero-title">Eventos &<br><span>Programação</span></h1>
            <p class="inst-hero-sub">Fique por dentro das exposições, festivais, feiras e espetáculos que movimentam a arte e a cultura da nossa região nesta temporada.</p>
        </div>
    </section>

    <div class="container" style="padding: 4rem 1.5rem; max-width: 1000px;">

        <div class="eventos-header" style="display:flex; justify-content:space-between; align-items:flex-end; border-bottom: 2px solid var(--border); padding-bottom:1rem; margin-bottom: 2.5rem;">
            <h2 style="font-family: var(--font-heading); font-size: 2rem; font-weight:800; margin:0; line-height:1">Próximos Eventos</h2>
            <div class="view-mode-toggles" style="display:flex; gap:0.5rem">
                <button class="btn-icon" aria-label="Visualização em Lista" style="background:var(--color-primary); color:#fff; border:none; padding:8px; border-radius:6px; cursor:pointer"><span class="material-symbols-outlined">view_list</span></button>
            </div>
        </div>

        <div class="eventos-grid" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <?php foreach($eventos_lista as $evento): ?>
            <article class="evento-card <?php echo $evento['destaque'] ? 'evento-destaque' : ''; ?>" style="display:flex; flex-direction: <?php echo $evento['destaque'] ? 'column' : 'row'; ?>; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow:hidden; transition: transform 0.3s ease, box-shadow 0.3s ease; gap: 0;">
                
                <!-- Date Block -->
                <div class="ev-date" style="background: <?php echo $evento['destaque'] ? 'var(--color-primary)' : 'var(--bg-alt)'; ?>; color: <?php echo $evento['destaque'] ? '#fff' : 'var(--text-main)'; ?>; min-width: 140px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; border-right: 1px solid var(--border); border-bottom: <?php echo $evento['destaque'] ? '1px solid var(--border)' : 'none'; ?>">
                    <span style="font-size: 0.85rem; font-weight:600; text-transform:uppercase; letter-spacing:1px; opacity:0.8; margin-bottom:0.25rem"><?php echo $evento['dia_semana']; ?></span>
                    <strong style="font-size: 1.8rem; font-family: var(--font-heading); line-height:1; margin-bottom: 0.5rem"><?php echo explode('/', $evento['data'])[0] . ' <span style="font-size:1rem; opacity:0.7">/' . explode('/', $evento['data'])[1] . '</span>'; ?></strong>
                    <span style="display:flex; align-items:center; gap:4px; font-size: 0.85rem; font-weight: 500"><span class="material-symbols-outlined" style="font-size:1rem">schedule</span> <?php echo $evento['hora']; ?></span>
                </div>

                <!-- Info Block -->
                <div class="ev-info" style="padding: 1.5rem 2rem; flex: 1; display:flex; flex-direction:column; justify-content:center;">
                    <div class="ev-meta" style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 0.75rem">
                        <span style="background: rgba(0,0,0,0.05); padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; color: var(--text-muted); border: 1px solid var(--border)"><?php echo $evento['categ']; ?></span>
                        <?php if($evento['destaque']): ?>
                        <span style="color: var(--color-primary); font-size:0.8rem; font-weight:700; display:flex; align-items:center; gap:4px"><span class="material-symbols-outlined" style="font-size:1.1rem">star</span> Evento Destaque</span>
                        <?php endif; ?>
                    </div>
                    
                    <h3 style="font-family: var(--font-heading); font-size: 1.5rem; margin: 0 0 0.5rem 0; line-height: 1.3">
                        <a href="<?php echo $evento['link']; ?>" style="color: var(--text-main); text-decoration: none; transition: color 0.2s"><?php echo $evento['titulo']; ?></a>
                    </h3>
                    
                    <p style="color: var(--text-muted); margin: 0 0 1.25rem 0; font-size: 0.95rem; line-height: 1.5; flex: 1"><?php echo $evento['desc']; ?></p>
                    
                    <div style="display: flex; align-items:center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-top: auto">
                        <div style="display:flex; align-items:center; gap: 6px; color: var(--text-main); font-weight:500; font-size:0.9rem">
                            <span class="material-symbols-outlined" style="color: var(--text-muted)">location_on</span>
                            <?php echo $evento['local']; ?>
                        </div>
                        <a href="<?php echo $evento['link']; ?>" style="font-weight:700; font-size:0.9rem; color:var(--color-primary); display:flex; align-items:center; gap:4px; text-decoration:none">Saber mais <span class="material-symbols-outlined" style="font-size:1.1rem">arrow_forward</span></a>
                    </div>
                </div>

            </article>
            <?php endforeach; ?>
        </div>

    </div>

    <style>
        .evento-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); border-color: rgba(0,0,0,0.1) }
        html.dark .evento-card:hover { box-shadow: 0 10px 30px rgba(0,0,0,0.3); border-color: rgba(255,255,255,0.15) }
        .evento-card h3 a:hover { color: var(--color-primary) !important; }
        .evento-destaque { flex-direction: column !important; border-top: 4px solid var(--color-primary) !important }
        .evento-destaque .ev-date { flex-direction: row !important; align-items: center !important; justify-content:flex-start !important; gap: 1.5rem !important; padding: 1rem 2rem !important; border-right: none !important; border-bottom: 1px solid var(--border) !important }
        .evento-destaque .ev-date strong { font-size: 1.4rem !important; margin:0 !important }
        @media(max-width: 768px) {
            .evento-card { flex-direction: column !important; }
            .ev-date { flex-direction: row !important; align-items: center !important; justify-content:flex-start !important; gap: 1.5rem !important; padding: 1rem 1.5rem !important; border-right: none !important; border-bottom: 1px solid var(--border) !important }
            .ev-date strong { font-size: 1.4rem !important; margin: 0 !important }
            .ev-info { padding: 1.5rem !important }
        }
    </style>

</main>
<?php get_footer(); ?>

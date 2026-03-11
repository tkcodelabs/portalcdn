<?php
/**
 * Template Name: Tabela Futebol
 * Template Post Type: page
 */
get_header();
// Buscar dados dinâmicos salvos no Painel CDN -> Futebol
$classificacao = get_option( 'cdn_futebol_classificacao', [] );
$proximos      = get_option( 'cdn_futebol_proximos', [] );
$resultados    = get_option( 'cdn_futebol_resultados', [] );

// Se os dados estiverem completamente vazios (primeiro uso), exibir placeholder
if ( empty( $classificacao ) ) {
    $classificacao = [
        [ 'pos' => '-', 'time' => 'Parnahyba SC', 'p' => 0, 'j' => 0, 'v' => 0, 'e' => 0, 'd' => 0, 'gp' => 0, 'gc' => 0,  'sg' => 0 ],
        [ 'pos' => '-', 'time' => 'A Definir 1',  'p' => 0, 'j' => 0, 'v' => 0, 'e' => 0, 'd' => 0, 'gp' => 0, 'gc' => 0,  'sg' => 0 ],
        [ 'pos' => '-', 'time' => 'A Definir 2',  'p' => 0, 'j' => 0, 'v' => 0, 'e' => 0, 'd' => 0, 'gp' => 0, 'gc' => 0,  'sg' => 0 ],
        [ 'pos' => '-', 'time' => 'A Definir 3',  'p' => 0, 'j' => 0, 'v' => 0, 'e' => 0, 'd' => 0, 'gp' => 0, 'gc' => 0,  'sg' => 0 ],
    ];
}

if ( empty( $proximos ) ) {
    $proximos = [
        [ 'data' => '05/04', 'hora' => 'A Def.', 'mandante' => 'Adversário',   'visitante' => 'Parnahyba SC', 'estadio' => 'A Definir',    'comp' => 'Série D' ],
        [ 'data' => '12/04', 'hora' => 'A Def.', 'mandante' => 'Parnahyba SC', 'visitante' => 'Adversário',   'estadio' => 'Pedro Alelaf', 'comp' => 'Série D' ],
    ];
}
?>
<main id="main-content" class="inst-page futebol-page">

    <!-- HERO -->
    <section class="inst-hero futebol-hero">
        <div class="inst-hero-bg" aria-hidden="true">
            <div class="hero-orb hero-orb-1" style="background:rgba(10,77,10,.4)"></div>
            <div class="hero-orb hero-orb-2" style="background:rgba(255,255,255,.1)"></div>
        </div>
        <div class="container inst-hero-inner">
            <div class="inst-hero-badge">⚽ Brasileirão Série D 2026</div>
            <h1 class="inst-hero-title">Parnahyba SC<br><span>Tabela & Resultados</span></h1>
            <p class="inst-hero-sub">Acompanhe o <strong>Tubarão do Litoral</strong> na disputa da Série D de 2026! Fundado em 1913, o Parnahyba é o clube mais antigo do esporte piauiense e manda seus jogos no Estádio Pedro Alelaf. Relembre aqui também os recordes de Vavá e os craques do litoral.</p>
        </div>
    </section>

    <div class="container futebol-content">

        <!-- TABS -->
        <div class="futebol-tabs">
            <button class="ftab active" data-tab="proximos">📅 Próximos Jogos</button>
            <button class="ftab" data-tab="resultados">📊 Resultados</button>
            <button class="ftab" data-tab="classificacao">🏆 Classificação</button>
        </div>

        <!-- PRÓXIMOS JOGOS -->
        <div class="ftab-content active" id="tab-proximos">
            <div class="partidas-completas">
            <?php foreach ( $proximos as $i => $jogo ) :
                $parna_manda  = $jogo['mandante']  === 'Parnahyba SC';
                $parna_visita = $jogo['visitante'] === 'Parnahyba SC';
            ?>
                <div class="pc-card <?php echo ( $parna_manda || $parna_visita ) ? 'pc-card--parna' : ''; ?> <?php echo $i === 0 ? 'pc-card--proximo' : ''; ?>">
                    <?php if ( $i === 0 ) : ?><div class="pc-proximo-label">⚡ Próximo Jogo</div><?php endif; ?>
                    <div class="pc-comp-badge"><?php echo esc_html( $jogo['comp'] ); ?></div>
                    <div class="pc-matchup">
                        <div class="pc-team <?php echo $parna_manda ? 'pc-team--parna' : ''; ?>">
                            <div class="pc-escudo"><?php echo $parna_manda ? '🔵' : '⚪'; ?></div>
                            <span><?php echo esc_html( $jogo['mandante'] ); ?></span>
                            <small>Casa</small>
                        </div>
                        <div class="pc-vs-block">
                            <span class="pc-hora"><?php echo esc_html( $jogo['hora'] ); ?></span>
                            <span class="pc-vs">×</span>
                            <span class="pc-data"><?php echo esc_html( $jogo['data'] ); ?></span>
                        </div>
                        <div class="pc-team <?php echo $parna_visita ? 'pc-team--parna' : ''; ?>">
                            <div class="pc-escudo"><?php echo $parna_visita ? '🔵' : '⚪'; ?></div>
                            <span><?php echo esc_html( $jogo['visitante'] ); ?></span>
                            <small>Visitante</small>
                        </div>
                    </div>
                    <div class="pc-estadio">📍 <?php echo esc_html( $jogo['estadio'] ); ?></div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>

        <!-- RESULTADOS -->
        <div class="ftab-content" id="tab-resultados">
            <div class="partidas-completas">
            <?php if ( empty( $resultados ) ) : ?>
                <div style="text-align:center; padding: 3rem 1rem; color: var(--color-text-mut)">
                    <p style="font-size:1.125rem; font-weight:500;">⚽ O Tubarão do Litoral ainda não estreou esse ano.</p>
                    <p style="font-size:.9rem; margin-top:.5rem;">A Série D começa no dia 5 de Abril! Fique ligado.</p>
                </div>
            <?php else : ?>
                <?php foreach ( $resultados as $res ) :
                    $parna_manda  = $res['mandante']  === 'Parnahyba SC';
                    $parna_visita = $res['visitante'] === 'Parnahyba SC';
                    $gP = $parna_manda ? $res['gm'] : $res['gv'];
                    $gA = $parna_manda ? $res['gv'] : $res['gm'];
                    $result_class = $gP > $gA ? 'vitoria' : ( $gP < $gA ? 'derrota' : 'empate' );
                ?>
                    <div class="pc-card pc-card--resultado pc-resultado--<?php echo $result_class; ?>">
                        <div class="pc-resultado-badge"><?php echo $result_class === 'vitoria' ? '✅ Vitória' : ( $result_class === 'derrota' ? '❌ Derrota' : '🟡 Empate' ); ?></div>
                        <div class="pc-comp-badge"><?php echo esc_html( $res['comp'] ); ?></div>
                        <div class="pc-matchup">
                            <div class="pc-team <?php echo $parna_manda ? 'pc-team--parna' : ''; ?>">
                                <div class="pc-escudo"><?php echo $parna_manda ? '🔵' : '⚪'; ?></div>
                                <span><?php echo esc_html( $res['mandante'] ); ?></span>
                            </div>
                            <div class="pc-vs-block">
                                <span class="pc-placar"><?php echo $res['gm']; ?> — <?php echo $res['gv']; ?></span>
                                <span class="pc-data"><?php echo esc_html( $res['data'] ); ?></span>
                            </div>
                            <div class="pc-team <?php echo $parna_visita ? 'pc-team--parna' : ''; ?>">
                                <div class="pc-escudo"><?php echo $parna_visita ? '🔵' : '⚪'; ?></div>
                                <span><?php echo esc_html( $res['visitante'] ); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
        </div>

        <!-- CLASSIFICAÇÃO -->
        <div class="ftab-content" id="tab-classificacao">
            <div class="tabela-wrap">
                <table class="tabela-classif">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Time</th>
                            <th title="Pontos">P</th>
                            <th title="Jogos">J</th>
                            <th title="Vitórias">V</th>
                            <th title="Empates">E</th>
                            <th title="Derrotas">D</th>
                            <th title="Gols Pró">GP</th>
                            <th title="Gols Contra">GC</th>
                            <th title="Saldo de Gols">SG</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $classificacao as $row ) :
                        $is_parna = $row['time'] === 'Parnahyba SC';
                        $zona = $row['pos'] <= 2 ? 'zona-titulo' : ( $row['pos'] <= 4 ? 'zona-classi' : ( $row['pos'] >= 7 ? 'zona-rebaixamento' : '' ) );
                    ?>
                        <tr class="<?php echo $is_parna ? 'row-parna' : ''; ?> <?php echo $zona; ?>">
                            <td class="td-pos"><?php echo esc_html( $row['pos'] ); ?></td>
                            <td class="td-time">
                                <?php echo $is_parna ? '<span class="parna-dot">🔵</span>' : ''; ?>
                                <?php echo esc_html( $row['time'] ); ?>
                                <?php echo $is_parna ? '<span class="parna-tag">Nosso time</span>' : ''; ?>
                            </td>
                            <td class="td-pts"><strong><?php echo $row['p']; ?></strong></td>
                            <td><?php echo $row['j']; ?></td>
                            <td class="td-v"><?php echo $row['v']; ?></td>
                            <td><?php echo $row['e']; ?></td>
                            <td class="td-d"><?php echo $row['d']; ?></td>
                            <td><?php echo $row['gp']; ?></td>
                            <td><?php echo $row['gc']; ?></td>
                            <td class="<?php echo $row['sg'] > 0 ? 'sg-pos' : ($row['sg'] < 0 ? 'sg-neg' : ''); ?>"><?php echo ($row['sg'] > 0 ? '+' : '') . $row['sg']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="tabela-legenda">
                    <span class="leg-titulo">🟦 Classificado para a final</span>
                    <span class="leg-classi">🟩 Classificado p/ próxima fase</span>
                    <span class="leg-rebai">🟥 Zona de rebaixamento</span>
                </div>
            </div>
        </div>

    </div><!-- /.container -->
</main>

<script>
document.querySelectorAll('.ftab').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.ftab').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.ftab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
    });
});
</script>
<?php get_footer(); ?>

<?php
/**
 * Template Name: Tabela Futebol
 * Template Post Type: page
 */
get_header();

$classificacao = [
    [ 'pos' => 1, 'time' => 'Altos FC',        'p' => 22, 'j' => 8, 'v' => 7, 'e' => 1, 'd' => 0, 'gp' => 18, 'gc' => 5,  'sg' => 13 ],
    [ 'pos' => 2, 'time' => 'River AC',         'p' => 19, 'j' => 8, 'v' => 6, 'e' => 1, 'd' => 1, 'gp' => 15, 'gc' => 7,  'sg' => 8  ],
    [ 'pos' => 3, 'time' => 'Parnahyba SC',     'p' => 17, 'j' => 8, 'v' => 5, 'e' => 2, 'd' => 1, 'gp' => 14, 'gc' => 6,  'sg' => 8  ],
    [ 'pos' => 4, 'time' => '4 de Julho',       'p' => 14, 'j' => 8, 'v' => 4, 'e' => 2, 'd' => 2, 'gp' => 11, 'gc' => 10, 'sg' => 1  ],
    [ 'pos' => 5, 'time' => 'Tiradentes-PI',    'p' => 11, 'j' => 8, 'v' => 3, 'e' => 2, 'd' => 3, 'gp' => 9,  'gc' => 11, 'sg' => -2 ],
    [ 'pos' => 6, 'time' => 'Flamengo-PI',      'p' => 9,  'j' => 8, 'v' => 3, 'e' => 0, 'd' => 5, 'gp' => 8,  'gc' => 14, 'sg' => -6 ],
    [ 'pos' => 7, 'time' => 'Cori-Sabbá',       'p' => 6,  'j' => 8, 'v' => 2, 'e' => 0, 'd' => 6, 'gp' => 5,  'gc' => 16, 'sg' => -11 ],
    [ 'pos' => 8, 'time' => 'Comercial-PI',     'p' => 3,  'j' => 8, 'v' => 1, 'e' => 0, 'd' => 7, 'gp' => 4,  'gc' => 20, 'sg' => -16 ],
];

$proximos = [
    [ 'data' => '08/03', 'hora' => '16h', 'mandante' => 'Parnahyba SC',  'visitante' => 'River AC',         'estadio' => 'Lindolfo Monteiro', 'comp' => 'Piauiense' ],
    [ 'data' => '12/03', 'hora' => '19h', 'mandante' => 'Flamengo-PI',   'visitante' => 'Parnahyba SC',     'estadio' => 'Alberto Silva',     'comp' => 'Piauiense' ],
    [ 'data' => '19/03', 'hora' => '16h', 'mandante' => 'Parnahyba SC',  'visitante' => '4 de Julho',       'estadio' => 'Lindolfo Monteiro', 'comp' => 'Piauiense' ],
    [ 'data' => '26/03', 'hora' => '20h', 'mandante' => 'Altos FC',      'visitante' => 'Parnahyba SC',     'estadio' => 'Felipão',           'comp' => 'Copa NE'   ],
    [ 'data' => '02/04', 'hora' => '16h', 'mandante' => 'Parnahyba SC',  'visitante' => 'Tiradentes-PI',    'estadio' => 'Lindolfo Monteiro', 'comp' => 'Piauiense' ],
    [ 'data' => '09/04', 'hora' => '19h', 'mandante' => 'Parnahyba SC',  'visitante' => 'Cori-Sabbá',       'estadio' => 'Lindolfo Monteiro', 'comp' => 'Piauiense' ],
    [ 'data' => '16/04', 'hora' => '16h', 'mandante' => 'Comercial-PI',  'visitante' => 'Parnahyba SC',     'estadio' => 'Verdão',            'comp' => 'Piauiense' ],
    [ 'data' => '20/04', 'hora' => '20h', 'mandante' => 'Parnahyba SC',  'visitante' => 'Altos FC',         'estadio' => 'Lindolfo Monteiro', 'comp' => 'Copa NE'   ],
];

$resultados = [
    [ 'data' => '01/03', 'mandante' => 'Parnahyba SC', 'gm' => 2, 'gv' => 1, 'visitante' => 'Cori-Sabbá',  'comp' => 'Piauiense' ],
    [ 'data' => '22/02', 'mandante' => 'Flamengo-PI',  'gm' => 0, 'gv' => 0, 'visitante' => 'Parnahyba SC','comp' => 'Piauiense' ],
    [ 'data' => '15/02', 'mandante' => 'Parnahyba SC', 'gm' => 3, 'gv' => 0, 'visitante' => 'Comercial-PI','comp' => 'Piauiense' ],
    [ 'data' => '08/02', 'mandante' => 'River AC',     'gm' => 1, 'gv' => 2, 'visitante' => 'Parnahyba SC','comp' => 'Piauiense' ],
    [ 'data' => '01/02', 'mandante' => 'Parnahyba SC', 'gm' => 1, 'gv' => 0, 'visitante' => '4 de Julho',  'comp' => 'Piauiense' ],
];
?>
<main id="main-content" class="inst-page futebol-page">

    <!-- HERO -->
    <section class="inst-hero futebol-hero">
        <div class="inst-hero-bg" aria-hidden="true">
            <div class="hero-orb hero-orb-1" style="background:rgba(10,77,10,.4)"></div>
            <div class="hero-orb hero-orb-2" style="background:rgba(255,255,255,.1)"></div>
        </div>
        <div class="container inst-hero-inner">
            <div class="inst-hero-badge">⚽ Campeonato Piauiense 2026</div>
            <h1 class="inst-hero-title">Parnahyba SC<br><span>Tabela & Resultados</span></h1>
            <p class="inst-hero-sub">Acompanhe o Leão do Norte na disputa pelo título piauiense de 2026. Todos os jogos, horários e classificação.</p>
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

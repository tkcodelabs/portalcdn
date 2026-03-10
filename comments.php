<?php
/**
 * comments.php — Seção de comentários
 */
if ( post_password_required() ) return;
?>
<div class="comments-section" id="comments">

    <?php if ( have_comments() ) : ?>
    <h2 class="comments-title">
        <?php
        $count = get_comments_number();
        echo $count === '1' ? '1 Comentário' : $count . ' Comentários';
        ?>
    </h2>
    <div class="comment-list">
        <?php wp_list_comments( [
            'style'      => 'div',
            'callback'   => 'cdn_comment_template',
            'short_ping' => true,
        ] ); ?>
    </div>
    <div style="margin-top:1.5rem"><?php the_comments_pagination( [ 'prev_text' => '&laquo; Anteriores', 'next_text' => 'Próximos &raquo;' ] ); ?></div>
    <?php endif; ?>

    <?php if ( comments_open() ) : ?>
    <div class="comment-form-wrap" id="respond">
        <h3><?php comment_form_title( 'Deixe um Comentário', 'Responder' ); ?></h3>
        <?php
        comment_form( [
            'title_reply'         => '',
            'title_reply_to'      => '',
            'cancel_reply_link'   => 'Cancelar',
            'label_submit'        => 'Publicar Comentário',
            'class_submit'        => 'btn-primary',
            'comment_field'       => '<div class="form-group"><label for="comment">' . __( 'Comentário', 'correiodonorte' ) . ' <span>*</span></label><textarea id="comment" name="comment" rows="5" required></textarea></div>',
            'fields' => [
                'author' => '<div class="form-row"><div class="form-group"><label for="author">Nome *</label><input type="text" id="author" name="author" required autocomplete="name"></div>',
                'email'  => '<div class="form-group"><label for="email">E-mail * <small>(não publicado)</small></label><input type="email" id="email" name="email" required autocomplete="email"></div></div>',
                'url'    => '',
                'cookies'=> '',
            ],
        ] );
        ?>
    </div>
    <?php elseif ( ! is_user_logged_in() ) : ?>
    <p style="color:var(--color-text-muted);font-size:.875rem">Os comentários estão fechados.</p>
    <?php endif; ?>

</div>

<?php
function cdn_comment_template( $comment, $args, $depth ) {
    $avatar = get_avatar( $comment, 44 );
    ?>
    <div id="comment-<?php comment_ID(); ?>" <?php comment_class( 'comment-item', $comment ); ?>>
        <div class="comment-avatar"><?php echo $avatar ?: esc_html( mb_substr( get_comment_author( $comment ), 0, 1 ) ); ?></div>
        <div>
            <span class="comment-author"><?php echo esc_html( get_comment_author( $comment ) ); ?></span>
            <span class="comment-date"><?php echo esc_html( get_comment_date( '', $comment ) ); ?></span>
            <?php if ( '0' === $comment->comment_approved ) : ?>
            <p style="font-size:.8rem;color:var(--color-accent-orange)">Aguardando moderação.</p>
            <?php endif; ?>
            <div class="comment-body"><?php comment_text(); ?></div>
        </div>
    </div>
    <?php
}

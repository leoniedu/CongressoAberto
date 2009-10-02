<?php // Do not delete these lines
	if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');

	if (!empty($post->post_password)) { // if there's a password
		if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie
			?>

			<p class="nocomments">This post is password protected. Enter the password to view comments.</p>

			<?php
			return;
		}
	}

?>

<!-- You can start editing here. -->


<?php if ($comments) : ?>
	<h3 id="comments"><?php comments_number('Não há comentários', 'Um comentário', '% Comentários' );?> <a href="#respond" title="<?php _e("Deixe um comentário"); ?>">&raquo;</a></h3>
<ul class="commentlist">

	<?php foreach ($comments as $comment) : ?>
	
<?php
$isByAuthor = false;
if($comment->comment_author_email == get_the_author_email()) {
$isByAuthor = true;
}?>
		<div class="commentlist">

		<li id="comment-<?php comment_ID() ?>" <?php if($isByAuthor ) { echo 'class="my_comment"';} ?>>

		<div class="clearfloat">

		<?php echo get_avatar( $comment, $size = '55' ); ?>
	

			<div class="commenttext">
			<cite><strong><?php comment_author_link() ?>  </strong> : </cite>

			<?php if ($comment->comment_approved == '0') : ?>
			<em>Seu comentário aguarda moderação.</em>
			<?php endif; ?>
			<?php comment_text() ?>
			</div>	

	
		</div>
		
		<div class="commentmetadata"># <?php comment_date('j/n/Y') ?> às <?php comment_time() ?> </div>
		</li>


	<?php endforeach; /* end for each comment */ ?>
</ul>
	

 <?php else : // this is displayed if there are no comments so far ?>

	<?php if ('open' == $post->comment_status) : ?>
		<!-- If comments are open, but there are no comments. -->

	 <?php else : // comments are closed ?>
		<!-- If comments are closed. -->
		<p class="nocomments">Não é mais possível comentar este artigo.</p>

	<?php endif; ?>
<?php endif; ?>


<?php if ('open' == $post->comment_status) : ?>


<h3 id="respond">Comente este artigo!</h3>

<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
<p>Você precisa estar <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php echo urlencode(get_permalink()); ?>">logado</a> para comentar este artigo.</p>
<?php else : ?>

<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">

<?php if ( $user_ID ) : ?>

<p>Logado como <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="Log out of this account">Logout &raquo;</a></p>

<?php else : ?>

<p>Comente no espaço abaixo, ou faça <a href="<?php trackback_url(true); ?>" rel="trackback">trackback</a> de seu próprio site. Você pode  <?php comments_rss_link('acompanhar os comentários'); ?> via RSS.</p>

<p>Be nice. Keep it clean. Stay on topic. No spam.</p>

<p><input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="22" tabindex="1" class="field" />
<label for="author"><small>Name <?php if ($req) echo "(required)"; ?></small></label></p>

<p><input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" class="field" />
<label for="email"><small>Mail (will not be published) <?php if ($req) echo "(required)"; ?></small></label></p>

<p><input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22" tabindex="3" class="field" />
<label for="url"><small>Website (optional)</small></label></p>

<?php endif; ?>

<p><textarea name="comment" id="comment" cols="100%" rows="10" tabindex="4"></textarea></p>
<p>Você pode usar os seguintes tags:<br/><code><?php echo allowed_tags(); ?></code></p>
<p>Você pode utilizar seu gravatar nesse site. Consiga seu próprio gravatar no  <a href="http://www.gravatar.com">Gravatar</a>.</p>

<p><input name="envio" class="searchbutton" type="submit" id="submit" tabindex="5" value="Envie seu comentário" />
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
</p>
<?php do_action('comment_form', $post->ID); ?>

</form>

<?php endif; // If registration required and not logged in ?>

<?php endif; ?>



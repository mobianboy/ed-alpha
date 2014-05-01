<?php

// Get first notification single post from posts loop
if(!$post) {
  $post = $posts->posts[0];
}

?>
<div class="note self <?= $post->meta['type'][0] ?>" data-content="<?= $post->ID; ?>"> <?/* type = self|other */?>
  <? include($post->meta['type'][0].'.php') ?>
</div>


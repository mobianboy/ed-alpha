<?php

// Get the shout author user object
$post->initiator = get_user_by('id', $post->post_author);

// Get the shout parent post object
$post->parent = get_post($post->meta['parent'][0]);

?>
<a class="template" href="/profile/<?= $post->initiator->data->user_login ?>">
  <img class="user" src="<?= user::get_user_image(45, 45, $post->initiator->data->ID) ?>" alt="<?= $post->initiator->data->display_name ?>" width="45px" height="45px" />
</a>
<div class="content">
  <div class="title">Shout!</div>
  <a class="template" href="/profile/<?= user::get_user_login_by_id(get_current_user_id()) ?>">
    <?= $post->initiator->data->display_name ?> posted a shout on your wall.
  </a>
  <span class="time"><?= relative_date($post->parent->post_date) ?></span>
</div>


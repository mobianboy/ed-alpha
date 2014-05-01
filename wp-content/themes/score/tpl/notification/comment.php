<?php

// Get the comment author user object
$post->initiator = get_user_by('id', $post->post_author);

// Get the comment parent post object
$post->parent = get_post($post->meta['parent'][0]);

?>
<a class="template" href="/profile/<?= $post->initiator->data->user_login ?>">
  <img class="user" src="<?= user::get_user_image(45, 45, $post->initiator->data->ID) ?>" alt="<?= $post->initiator->data->display_name ?>" width="45px" height="45px" />
</a>
<div class="content">
  <div class="title">Comment</div>
  <a class="template" href="/profile/<?= $post->initiator->data->user_login ?>">
    <?= $post->initiator->data->display_name ?> commented on your shout.
  </a>
  <span class="time"><?= relative_date($post->parent->post_date) ?></span>
</div>


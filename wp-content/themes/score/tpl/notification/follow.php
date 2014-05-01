<?php

// Get follow initiator object
$post->initiator = get_user_by('id', $post->post_author);
$post->initiator->data->meta = get_usermeta($post->initiator->data->ID);

?>
<a class="template" href="/profile/<?= $post->initiator->data->user_login ?>">
  <img class="user" src="<?= user::get_user_image(45, 45, $post->initiator->data->ID) ?>" alt="<?= $post->initiator->data->display_name ?>" width="45px" height="45px" />
</a>
<div class="content">
  <a class="template" href="/profile/<?= $post->initiator->data->user_login ?>">
    <?= $post->initiator->data->display_name ?>
  </a> &nbsp;is now 
  <?php if($post->initiator->data->meta['profile_type'] == "fan" && $post->owner->meta['profile_type'] == "fan"): ?>
    friends with.
  <?php else: ?>
    following 
  <?php endif ?>
  you.
  <span class="time"><?= relative_date($post->post_date) ?></span>
</div>


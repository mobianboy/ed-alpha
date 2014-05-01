<?php

// Get follow initiator object
$post->initiator = get_user_by('id', $post->post_author);
$post->initiator->data->meta = get_usermeta($post->initiator->data->ID);

?>
<a class="template" href="/profile/<?= $post->initiator->data->user_login ?>">
  <img class="user" src="<?= user::get_user_image(45, 45, $post->initiator->data->ID) ?>" alt="<?= $post->initiator->data->display_name ?>" width="45px" height="45px" />
</a>
<div class="content">
  Congratulations, you and <a class="template" href="/profile/<?= $post->initiator->data->user_login ?>"><?= $post->initiator->data->display_name ?></a> &nbsp;are now friends!
  <span class="time"><?= relative_date($post->post_date) ?></span>
</div>


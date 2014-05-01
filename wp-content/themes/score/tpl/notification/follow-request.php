<?php

// Get follow initiator object
$post->initiator = get_user_by('id', $post->post_author);
$post->initiator->data->meta = get_usermeta($post->initiator->data->ID);

?>
<a class="template" href="/profile/<?= $post->initiator->data->user_login ?>">
  <img class="user" src="<?= user::get_user_image(45, 45, $post->initiator->data->ID) ?>" alt="<?= $post->initiator->data->display_name ?>" width="45px" height="45px" />
</a>
<div class="content">
  <a class="template" href="/profile/<?= $post->initiator->data->user_login ?>"><?= $post->initiator->data->display_name ?></a> &nbsp;is requesting to follow you.
  <div class="confirmDeny"><input class="follow_accept" type="button" name="confirm" value="Confirm" data-content="<?= $post->initiator->data->ID ?>"/><input class="follow_deny" type="button" name="deny" value="Deny" data-content="<?= $post->initiator->data->ID ?>" /></div>
  <span class="time"><?= relative_date($post->post_date) ?></span>
</div>


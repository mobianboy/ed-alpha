<li class="message" data-content="<?= $post->ID ?>">
  <img alt="<?= $post->owner->display_name ?>" src="<?= user::get_user_image(28, 28, $post->owner->ID) ?>" width="28" height="28" />
  <div class="meta">
    <div class="name"><?= $post->owner->display_name ?></div>
    <div class="date"><?= relative_date($post->post_date) ?></div>
  </div>
  <div class="content"><?= $post->post_content ?></div>
  <div class="delete"></div>
</li>


<ul data-content="<?= $content ?>">
	<li class="dateNote">Conversation started <?= relative_date($posts->posts[0]->post_date) ?></li>
<? if(count($posts->posts)): ?>
  <? foreach($posts->posts as $post): ?>
    <? if(!message::is_marked($post->ID, 'hide')): ?>
      <?= balls::get_balls_template(array(
        'post_type' => 'message',
        'template'  => 'single',
        'content'   => $post->ID,
      )) ?>
    <? endif ?>
  <? endforeach ?>
<? endif ?>
</ul>


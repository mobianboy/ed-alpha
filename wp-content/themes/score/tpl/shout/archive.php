<ul>
<? if(count($posts)): ?>
  <? foreach($posts as $post): ?>
    <? balls::get_balls_template(array(
      'post_type' => 'shout',
      'template'  => 'single',
      'content'   =>  $post->ID,
    )) ?>
  <? endforeach ?>
<? endif ?>
</ul>


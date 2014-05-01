<section class="notification widget" data-post-type="notification" data-template="archive">
  <div class="wrapper" id="the_notification">
    <h2>Notes</h2>
    <div class="context">
    <? if(count($posts->posts)): ?>
    	<? foreach($posts->posts as $post): ?>
        <? include('single.php') ?>
      <? endforeach ?>
    <? endif ?>
    </div>
    <div class="widgetClose"></div>
  </div>
</section>


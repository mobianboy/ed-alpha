<? if(count($posts->posts)): ?>
  <ul>
  <? foreach($posts->posts as $post): ?>
    <? include('single.php') ?>
  <? endforeach ?>
  </ul>
<? else: ?>
  <ul></ul>
<? endif ?>
<?//<button class="showMore">Show More Comments</button>?>
<div class="dishInput">
  <img class="currUser" src="<?= user::get_user_image(75, 75) ?>"/>
  <div class="comment"><textarea name="comment" class="shout" placeholder="Dish Back?"></textarea></div>
  <input type="submit" class="post submitComment" value="Reply" />
</div>


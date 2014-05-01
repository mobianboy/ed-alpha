<section class="context">
  <? while($posts->have_posts()): ?>
    <? $posts->the_post() ?>
    <? reward::track_activity(get_current_user_id(), get_the_ID(), 3) // Track article view in analytics ?>
    <? $tags = wp_get_post_tags(get_the_ID()) ?>
    <? $tags = news::filter_tags($tags) ?>
    <img src="<?= news::get_news_image(get_the_ID(), 625, 400) ?>" width="625" height="400" />
    <div class="date">
      <? the_time(get_option('date_format')) ?> 
    </div>
    <div class="digArticle">
      <? include(get_template_directory().'/tpl/dig/single.php') ?>
    </div>
    <h1 class="title">
      <? the_title() ?>
    </h1>
    <h2 class="subtitle">
       <?= get_post_meta(get_the_ID(), 'subheading', TRUE) ?>
    </h2>
    <div class="content">
      <? the_content() ?>
    </div>
  <? if(count($tags)): ?>
    <div class="tags cf">
      <span class="label">Tags:</span>
    <? if(count($tags)): ?>
      <? foreach($tags as $key => $tag): ?>
      <span>
        <a class="template" href="/profile/<?= $tag ?>"><?= $tag ?></a><?= ($key < count($tags) - 1) ? ',' : '' ?>
      </span>
      <? endforeach ?>
    <? endif ?>
    </div>
  <? endif ?>
    <div class="comment">
      <? //comment_form() ?>
    </div>
  <? endwhile ?>
</section>

<section class="rail right user">
  <? include('rr-single.php') ?>
</section>


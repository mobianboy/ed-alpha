<? while($posts->have_posts()): ?>
  <? $posts->the_post() ?>
  <article>
    <div class="thumbnail">
      <a class="template" href="/article/<?= news::get_post_name_by_id(get_the_ID()) ?>"><img src="<?= news::get_news_image(get_the_ID(), 200, 124, TRUE) ?>" /></a>
      <div class="date"><? the_time(get_option('date_format')) ?></div>
    </div>
    <div class="content" data-post-type="<?= get_post_type() ?>"  data-content="<?= get_the_ID() ?>">
      <h3 class="title"><a class="template" href="/article/<?= news::get_post_name_by_id(get_the_ID()) ?>"><? the_title() ?></a></h3>
      <h4 class="subtitle" title="<?= get_post_meta(get_the_ID(), 'subheading', TRUE) ?>"><?= get_post_meta(get_the_ID(), 'subheading', TRUE) ?></h4>
      <p class="excerpt">
        <?= news::get_excerpt(get_the_excerpt(), 80) ?>
      </p>
      <div class="metabar">
        <ul class="horizontal">
          <li>
            <? include(get_template_directory().'/tpl/dig/single.php') ?>
          </li>
          <?/*
          <li>
            <span></span> <a>Comments</a>
          </li>
          <li>
            <a>Videos</a>
          </li>
          <li>
            <a>Photos</a>
          </li>
          */?>
          <li>
            <a class="templateX btnX" href="/article/<?= news::get_post_name_by_id(get_the_ID()) ?>">Read More</a>
          </li>
        </ul>
      </div>
    </div>
  </article>
<? endwhile ?>


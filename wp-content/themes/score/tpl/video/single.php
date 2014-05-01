<?= balls::get_balls_position(array(
  'position_id' => 'top-a',
  'parent'      => "video-single-$content",
), TRUE) ?>

<div id="test">This is a test of video single template id #<?= $content ?>.</div>

<?= balls::get_balls_position(array(
  'position_id' => 'sidebar-a',
  'parent'      => "video-single-$content",
), TRUE) ?>

<div id="sidebar">
  <?= balls::get_balls_position(array(
    'position_id' => 'sidebar',
    'parent'      => "video-single-$content",
  ), TRUE) ?>
</div>


<?= balls::get_balls_position(array(
  'position_id' => 'top-a',
  'parent'      => "video-archive-$content",
), TRUE) ?>

<div id="test">This is a test of video archive template.</div>

<?= balls::get_balls_position(array(
  'position_id' => 'sidebar-a',
  'parent'      => "video-archive-$content",
), TRUE) ?>

<div id="sidebar">
  <?= balls::get_balls_position(array(
    'position_id' => 'sidebar',
    'parent'      => "video-archive-$content",
  ), TRUE) ?>
</div>


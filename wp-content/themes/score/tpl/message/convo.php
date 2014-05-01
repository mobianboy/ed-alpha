<li class="thread" data-content="<?= $thread['last']->post_title ?>">
  <span class="user"><?= $thread['participants'] ?></span>
  <?/* span class="date"><-?= relative_date($thread['last']->post_date) ?></span */?>
  <?/* span class="excerpt"><-?= $thread['last']->post_content ?></span */?>
  <span class="unread"><?= ($thread['unread']) ? $thread['unread'] : '' ?></span>
  <div class="delete"></div>
</li>


<?php

/** 
*	Template for post types and their corresponding templates
*	called from balls.map.list.php
**/

?>
<div class="left" id="post-type-templates">
	<h2 class="cname"><? _e('Post Types') ?></h2>
	<p class="description">
		<? _e('Select the corresponding post type template, then slot your position.') ?>
	</p>
	<ul>
<? if(count($post_types)): ?>
  <?foreach($post_types as $key => $post_type): ?>
	  <? if($post_type->metadata['positions']): ?>
		  <? $templates = balls::get_templates_by_post_type($post_type->id) ?>
      <? if(count($templates)): ?>
		    <li>
			    <h3><?= $post_type->name ?></h3>
			    <ul class="inside">
          <? if(count($templates)): ?>
			      <? foreach($templates as $key => $template): ?>
				      <li>
					      <a href="admin.php?page=admin_position_map&template_id=<?= $template->id ?>" class="button">
						      <?= $template->template_type ?>
                </a>
				      </li>
			      <? endforeach ?>
          <? endif ?>
			    </ul>
		    </li>
      <? endif ?>
    <? endif ?>
  <? endforeach ?>
<? endif ?>
	</ul>	
</div>


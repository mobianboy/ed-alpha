<?php

/** 
*	Template for positions from  their corresponding post type templates
*	called from balls.list.php
**/

?>
<div class="right" id="positions_map">
	<div class="widget-liquid-right">
    <h2 class="cname">"<?= balls::get_template_name($template_id) ?>" Positions</h2>
    <p class="description"></p>
		<div id="widgets-right">
		<? $positions = balls::get_positions_by_template($template_id) ?>
    <? //$i = 0 ?>
    <? if(count($positions)): ?>
		  <? foreach($positions as $key => $position): ?>
			  <? $wrap_class = 'widgets-holder-wrap' ?>
			  <? /* // Used to apply opened closed state on load
			  if(!empty($registered_sidebar['class'])) {
				  $wrap_class .= ' sidebar-'.$registered_sidebar['class'];
        }
			  if($i) {
				  $wrap_class .= ' closed';
        } */ ?>
		    <div class="<?= esc_attr($wrap_class) ?>">
			    <div class="sidebar-name">
				    <div class="sidebar-name-arrow"><br /></div>
					  <h3><?= esc_html($position->name) ?>
						  <span>
							  <img src="<?= esc_url(admin_url('images/wpspin_dark.gif')) ?>" class="ajax-feedback" title="" alt="" />
						  </span>
					  </h3>
				  </div>
				  <? wp_list_widget_controls($position->name) // Show control forms for each of the widgets in this sidebar ?>
			  </div>
		    <? //$i++ ?>
		  <? endforeach ?>
    <? endif ?>
		</div>
	</div>
</div>


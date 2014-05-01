<?php

/** 
*	Template for positions from  their corresponding post type templates
*	called from balls.map.list.php
**/

// Get all positions available
$positions = balls::get_positions();

// Get all sloted positions for this template
$slotted = balls::get_positions_by_template($template_id);

// Filter slotted positions from full list
if(count($positions)) {
  foreach($positions as $key => $position) {
    if(count($slotted)) {
      foreach($slotted as $k => $slot) {
        if($position->id == $slot->id) {
          unset($positions[$key]);
        }
      }
    }
  }
}

?>
<div class="right" id="positions">
	<div class="widget-liquid-right">
		<div id="widgets-right">

      <h2 class="cname">"<?= balls::get_template_name($template_id) ?>" Positions</h2>

      <form action="?page=admin_position_map&template_id=<?= $template_id ?>" method="post">
        <div>
        <? if(count($positions)): ?>
          Slot position: <select name="set">
		      <? if(count($positions)): ?>
		        <? foreach($positions as $key => $position): ?>
		          <option value="<?= $position->id ?>"><?= $position->name ?></option>
		        <? endforeach ?>
          <? endif ?>
          </select>
        <? endif ?>
          <input type="submit" value="Map It" class="button" />
        </div>
      </form>

      <? if(count($slotted)): ?>
        <ul id="positions_map">
        <? if(count($slotted)): ?>
          <? foreach($slotted as $key => $position): ?>
            <li>
              <div>
                <a href="?page=balls_admin&action=position_map&template_id=<?= $template_id ?>&delete=<?=$position->id?>"></a> <span class="position_name"><?= $position->name ?></span>
              </div>
              <pre>
                <span>Place this code inside the template:<br/></span>
  &lt;?= balls::get_balls_position(array(
    'position_id' =&gt; '<?= $position->name ?>',
    'parent'      =&gt; "$post_type-$template-$content",
  ), TRUE) ?&gt;
              </pre>
            </li>
          <? endforeach ?>
        <? endif ?>
        </ul>
      <? endif ?>

		</div>
	</div>
</div>


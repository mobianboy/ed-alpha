<div class="right" id="positions">
	<div>
		<div>

      <h2 class="cname">Positions</h2>

      <form action="?page=balls_admin&action=positions&set=1" method="post">
        <table>
          <tr>
            <td>Name:</td>
            <td><input type="text" name="name" value="" /></td>
          </tr>
          <tr>
            <td>Desc:</td>
            <td><textarea name="desc"></textarea></td>
          </tr>
          <tr>
            <td>TTL:</td>
            <td><input type="text" name="ttl" value="" /></td>
          </tr>
          <tr>
            <td></td>
            <td><input type="submit" value="Add" class="button" /></td>
          </tr>
        </table>
      </form>

      <? if(count($positions)): ?>
        <ul id="positions_map">
		    <? foreach($positions as $key => $position): ?>
		      <li>
					  <div>
              <a href="?page=balls_admin&action=positions&delete=<?= $position->id ?>"></a> <span class="position_name"><?= $position->name ?></span>
            </div>
            <pre>
              <form action="?page=balls_admin&action=positions&set=1" method="post">
                <input type="hidden" name="id" value="<?= $position->id ?>" />
                <table>
                  <tr>
                    <td class="label">Name:</td>
                    <td><input type="text" name="name" size="40" value="<?= $position->name ?>" /></td>
                  </tr>
                  <tr>
                    <td class="label">TTL:</td>
                    <td><input type="text" name="ttl" size="40" value="<?= $position->ttl ?>" /></td>
                  </tr>
                  <tr>
                    <td class="label">Desc:</td>
                    <td><textarea name="desc" cols="40"><?= $position->description ?></textarea></td>
                  </tr>
                  <tr>
                    <td class="save" colspan="2"><input type="submit" value="Save" class="button" /></td>
                  </tr>
                </table>
              </form>
            </pre>
			    </li>
		    <? endforeach ?>
        </ul>
      <? endif ?>

		</div>
	</div>
</div>


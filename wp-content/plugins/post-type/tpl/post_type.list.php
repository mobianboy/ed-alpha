<style>
  tbody.post_type_item {
    cursor: pointer;
  }
  tr.quickedit {
    display: none;
    background: yellow;
  }
</style>

<div class="wrap">
  <div class="icon32"></div>
  <h2>Post Type Registration System <a class="add-new-h2" href="/wp-admin/admin.php?page=admin_post_types&action=edit">Add new</a></h2>
  <div id="post-types-list">
    <form id="types" method="get">
      <table class="wp-list-table widefat fixed" width="100%" border="0" cellspacing="0" cellpadding="0">
        <thead>
          <tr>
            <th>Name</th>
            <th>Desc</th>
            <th>Enabled</th>
            <th>Action</th>
          </tr>
        </thead>

    <? if(count($post_types)): ?>
      <? foreach($post_types as $post_type): ?>
        <tbody id="<?=$post_type->name?>" class="post_type_item">
          <tr class="list">
            <td>
              <a href="?page=admin_post_types&action=edit&id=<?= $post_type->id ?>"><?= $post_type->name ?></a>
            </td>
            <td>
              <a href="?page=admin_post_types&action=edit&id=<?= $post_type->id ?>"><?= $post_type->description ?></a>
            </td>
            <td>
              <?= ($post_type->active) ? 'Yes' : 'No' ?>
            </td>
            <td>
              <a href="?page=admin_post_types&action=delete&id=<?= $post_type->id ?>">Delete</a>
            </td>
          </tr>
        </tbody>
      <? endforeach ?>
    <? endif ?>

      </table>
    </form>
  </div>
</div>


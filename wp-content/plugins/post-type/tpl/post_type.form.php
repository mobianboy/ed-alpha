<div class="wrap">
  <div id="icon-edit"class="icon32"></div>
  <h2>Edit/Add <?= ($post_type->name) ? $post_type->name : 'Post Type' ?></h2>
  <form id="post_type" method="post" name="post_type">
  <? if($id): ?>
    <input id="id" type="hidden" value="<?= $post_type->id ?>" name="id" />
  <? endif ?>
    <div id="poststuff" class="metabox-holder post-types-wrap">
      <div id="post-body">
        <div id="post-body-content">
          <div id="titlediv">
            <div id="titlewrap">
            <? if($id): ?>
              <input readonly="readonly" id="title" type="text" name="name" class="hide-if-no-js" for="title" autocomplete="off" value="<?= $post_type->name ?>" tabindex="1" size="30"></input>
            <? else: ?>
              <label class="hide-if-no-js" for="title">Enter title here</label> 
              <input id="title" type="text" name="name" class="hide-if-no-js" autocomplete="off" value="" tabindex="1" size="30"></input>
            <? endif ?>
              <input class="button-primary" type="submit" name="Save" value="<? _e("Save Post Type") ?>" id="submitbutton" />
            </div>
          </div>
          <div id="normal-sortables" class"meta-box-sortables ui-sortable">
            <div class="column-1">
              <div id="generalsettings" class="postbox">
                <h3 class="hndle"><span>Post Type Settings</span></h3>
                <div class="inside">
                  <p class="meta-options">
                    <input id="active" value="1" type="checkbox" <?= ($post_type->active) ? 'checked' : '' ?> name="active" />
                    <label class="selectit" for="active">Enable Post Type</label>
                  </p>
                  <p class="meta-options">
                    <input id="description" name="description" type="text" value="<?= $post_type->description ?>" />
                    <label class="selectit" for="description">Description</label>
                  </p>
                  <p class="meta-options">
                    <input id="taxonomies" type="text" value="<?= ($post_type->metadata['taxonomies']) ? $post_type->metadata['taxonomies'] : '' ?>" name="metadata[taxonomies]" />
                    <label class="selectit" for="taxonomies">Taxonomie for categories</label>
                  </p>
                  <p class="meta-options">
                    <input type="text" id="slug" value="<?= ($post_type->metadata['slug']) ? $post_type->metadata['slug'] : inflect::pluralize($post_type->name) ?>" name="metadata[slug]" />
                    <label class="selectit" for="slug">
                    <? if($id): ?>
                      <i>(current slug is "<b><?= inflect::pluralize($post_type->name) ?>"</b> Change if needed *re-save Permalink structure after modification)</i>
                    <? else: ?>
                      <i>(default is the pluralization of the Post-Type name "cat" = "cats" *re-save Permalink structure after modification)</i>
                    <? endif ?>
                    </label>
                  </p>
                </div>
              </div>
              <div id="generalsettings2" class="postbox">
                <h3 class="hndle"><span>General Options</span></h3>
                <div class="inside">
                  <p class="meta-options">
                    <input id="public" value="true" type="checkbox" <?= ($post_type->metadata['public']) ? 'checked' : '' ?> name="metadata[public]" />
                    <label class="selectit" for="public">Public</label>
                  </p>
                  <p class="meta-options">
                    <input id="post_tag" value="true" type="checkbox" <?= ($post_type->metadata['post_tag']) ? 'checked' : '' ?> name="metadata[post_tag]" />
                    <label class="selectit" for="post_tag">Post Tags</label>
                  </p>
                  <p class="meta-options">
                    <input id="exclude_from_search" value="true" type="checkbox" <?= ($post_type->metadata['exclude_from_search']) ? 'checked' : '' ?> name="metadata[exclude_from_search]" />
                    <label class="selectit" for="exclude_from_search">Exclude from search</label>
                  </p>
                  <p class="meta-options">
                    <input id="publicly_queryable" value="true" type="checkbox" <?= ($post_type->metadata['publicly_queryable']) ? 'checked' : '' ?> name="metadata[publicly_queryable]" />
                    <label class="selectit" for="publicly_queryable">Publicly Queryable</label>
                  </p>
                  <p class="meta-options">								
                    <input value="true" id="show_ui" type="checkbox" <?= ($post_type->metadata['show_ui']) ? 'checked' : '' ?> name="metadata[show_ui]" />
                    <label class="selectit" for="show_ui">Show UI</label>
                  </p>
                  <p class="meta-options">
                    <input id="has_archive" value="true" type="checkbox" <?= ($post_type->metadata['has_archive']) ? 'checked' : '' ?> name="metadata[has_archive]" />
                    <label class="selectit" for="has_archive">Has Archive</label>
                  </p>
                  <p class="meta-options">
                    <input id="show_in_menu" value="true" type="checkbox" <?= ($post_type->metadata['show_in_menu']) ? 'checked' : '' ?> name="metadata[show_in_menu]" />
                    <label class="selectit" for="show_in_menu">Show in menu</label>
                  </p>
                  <p class="meta-options">
                    <input id="menu_position" value="<?= ($post_type->metadata['menu_position']) ? $post_type->metadata['menu_position'] : '' ?>" type="number" name="metadata[menu_position]" />
                    <label class="selectit" for="menu_position">Menu Position</label>
                  </p>
                  <p class="meta-options">
                    <input id="query_var" value="true" <?= ($post_type->metadata['query_var']) ? 'checked' : '' ?> type="checkbox" name="metadata[query_var]" />
                    <label class="selectit" for="query_var">Query Var</label>
                  </p>
                </div>
              </div>
            </div>
            <div class="column-2">
              <div id="generalsettings3" class="postbox">
                <h3 class="hndle"><span>Post Type Supports</span></h3>
                <div class="inside">
                  <p class="meta-options">
                    <input id="editor" value="true" type="checkbox" <?= ($post_type->metadata['supports']['editor']) ? 'checked' : '' ?> name="metadata[supports][editor]" />
                    <label class="selectit" for="editor">Editor</label>
                  </p>
                  <p class="meta-options">
                    <input id="thumbnail" value="true" type="checkbox" <?= ($post_type->metadata['supports']['thumbnail']) ? 'checked' : '' ?> name="metadata[supports][thumbnail]" />
                    <label class="selectit" for="thumbnail">Thumbnail</label>
                  </p>
                  <p class="meta-options">
                    <input id="can_excerpt" value="true" type="checkbox" <?= ($post_type->metadata['supports']['excerpt']) ? 'checked' : '' ?> name="metadata[supports][excerpt]" />
                    <label class="selectit" for="can_excerpt">Excerpt</label>
                  </p>
                  <p class="meta-options">
                  <input id="author" value="true" type="checkbox" <?= ($post_type->metadata['supports']['author']) ? 'checked' : '' ?> name="metadata[supports][author]" />
                    <label class="selectit" for="author">Author</label>
                  </p>
                  <p class="meta-options">
                    <input id="can_title" value="true" type="checkbox" <?= ($post_type->metadata['supports']['title']) ? 'checked' : '' ?> name="metadata[supports][title]" />
                    <label class="selectit" for="can_title">Title</label>
                  </p>
                  <p class="meta-options">
                    <input id="trackbacks" value="true" type="checkbox" <?= ($post_type->metadata['supports']['trackbacks']) ? 'checked' : '' ?> name="metadata[supports][trackbacks]" />
                    <label class="selectit" for="trackbacks">Trackbacks</label>
                  </p>
                  <p class="meta-options">
                    <input id="custom-fields" value="true" type="checkbox" <?= ($post_type->metadata['supports']['custom-fields']) ? 'checked' : '' ?> name="metadata[supports][custom-fields]" />
                    <label class="selectit" for="custom-fields">Custom Fields</label>
                  </p>
                  <p class="meta-options">
                    <input id="comments" value="true" type="checkbox" <?= ($post_type->metadata['supports']['comments']) ? 'checked' : '' ?> name="metadata[supports][comments]" />
                    <label class="selectit" for="comments">Comments</label>
                  </p>
                  <p class="meta-options">
                    <input id="revisions" value="true" type="checkbox" <?= ($post_type->metadata['supports']['revisions']) ? 'checked' : '' ?> name="metadata[supports][revisions]" />
                    <label class="selectit" for="revisions">Revisions</label>
                  </p>
                  <p class="meta-options">
                    <input id="page-attributes" value="true" type="checkbox" <?= ($post_type->metadata['supports']['page-attributes']) ? 'checked' : '' ?> name="metadata[supports][page-attributes]" />
                    <label class="selectit" for="page-attributes">Page Attributes</label>							
                  </p>
                  <p class="meta-options">
                    <input id="post-formats" value="true" type="checkbox" <?= ($post_type->metadata['supports']['post-formats']) ? 'checked' : '' ?> name="metadata[supports][post-formats]" />
                    <label class="selectit" for="post-formats">Post Formats</label>
                  </p>
                </div>
              </div>
              <div id="generalsettings3" class="postbox">
                <h3 class="hndle"><span>Post Type Templates</span></h3>
                <div class="inside">
              <? if(count($template_types)): ?>
                <? foreach($template_types as $template_type): ?>
                  <p class="meta-options">
                    <input id="<?= $template_type->name ?>" value="true" type="checkbox" <?= ($post_type->templates[$template_type->id]) ? 'checked' : '' ?> name="templates[<?= $template_type->id ?>]" />
                    <label class="selectit" for="<?= $template_type->name ?>"><?= ucfirst($template_type->name) ?></label>
                  </p>
                <? endforeach ?>
              <? endif ?>
                </div>
              </div>
              <div id="generalsettings4" class="postbox">
                <h3 class="hndle"><span>Post Type Extended</span></h3>
                <div class="inside">
                  <p class="meta-options">
                    <input id="positions" value="true" type="checkbox" <?= ($post_type->metadata['positions']) ? 'checked' : '' ?> name="metadata[positions]" />
                    <label class="selectit" for="positions">
                      Template Positions
                      <i>(Does this post type have templates with positions?)</i>
                    </label>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>


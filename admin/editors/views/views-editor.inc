<?php
/*
 * Where do all these variables come from?
 * They are coming from the arguments sent along with the theme('query_edit', $args) function in query-wrangler.php
 *
 * All keys in the argument array become variables in the template file
 *
 * See the following link for more details on how that works:
 * https://github.com/daggerhart/Query-Wrangler/wiki/Template-Wrangler
 */

?>
<form id="qw-edit-query-form" action='admin.php?page=query-wrangler&action=update&edit=<?php print $query_id; ?>&noheader=true' method='post'>
  <div id="qw-query-action-buttons">
    <div id="query-actions">
      <a href="admin.php?page=query-wrangler&export=<?php print $query_id; ?>">Export</a>
    </div>
    <input class='button-primary' type="submit" name="save" value="Save" />
    <input type="hidden" name="query-id" value="<?php print $query_id; ?>" />
  </div>
  <div id="message" class="updated qw-changes">
    <p><strong>*</strong> Changes have been made that need to be saved.</p>
  </div>

  <div class="qw-clear-gone"><!-- ie hack -->&nbsp;</div>

  <div id="qw-query-admin-options-wrap">

<!-- left column -->
    <div class="qw-query-admin-column">
      <div id="qw-query-args" class="qw-query-admin-options qw-handler-items">
        <h4>Basic Settings</h4>
        <?php
          foreach($basics as $basic) {
            // no display types set means all querys  || query type is in display types
            if (!isset($basic['query_display_types']) || (in_array($query_type, $basic['query_display_types'])))
            {
              // TODO template this someday
              ob_start();
                ?>
                  <div id="qw-basic-<?php print $basic['hook_key']; ?>" class="qw-item-form">
                    <?php $basic['form_callback']($basic, $options[$basic['option_type']]); ?>
                  </div>
                <?php
              $basic['form'] =  ob_get_clean();
              ?>
                <div class="qw-handler-item">
                  <div class="qw-handler-item-title"><?php print $basic['title']; ?></div>
                  <div class="qw-handler-item-form">
                    <?php print $basic['form']; ?>
                  </div>
                </div>
              <?php
            }
          }
        ?>
      </div> <!-- /qw-query-args -->
      <div class="qw-clear-gone"><!-- ie hack -->&nbsp;</div>
    </div>
    <!-- /column -->
<!-- middle column -->
    <div class="qw-query-admin-column">
      <?php
        if($query_type == 'override')
        { ?>
          <!-- override settings -->
          <div id="qw-override-settings" class="qw-query-admin-options qw-handler-items">
            <h4>Override Settings</h4>

            <!-- override categories -->
            <div class="qw-handler-item">
              <div class="qw-handler-item-title">Categories</div>
              <div class="qw-handler-item-form">
                <div class="qw-item-form">
                  <p>
                    Select which categories to override.
                  </p>
                  <div class="qw-checkboxes">
                    <?php
                    // List all categories as checkboxes
                    foreach($category_ids as $cat_id)
                    {
                      $cat_name = get_cat_name($cat_id);
                      $cat_checked = (isset($options['override']['cats'][$cat_id])) ? 'checked="checked"' : '';
                      ?>
                      <label class="qw-query-checkbox">
                        <input class="qw-js-title"
                               type="checkbox"
                               name="qw-query-options[override][cats][<?php print $cat_id; ?>]"
                               value="<?php print $cat_name; ?>"
                          <?php print $cat_checked; ?> />
                        <?php print $cat_name; ?>
                      </label>
                    <?php
                    }
                    ?>
                  </div>
                </div>
              </div>
            </div>

            <!-- override tags -->
            <div class="qw-handler-item">
              <div class="qw-handler-item-title">Tags</div>
              <div class="qw-handler-item-form">
                <div class="qw-item-form">
                  <p>
                    Select which tags to override.
                  </p>
                  <div class="qw-checkboxes">
                    <?php
                    foreach($tags as $tag)
                    {
                      $tag_checked = (isset($options['override']['tags'][$tag->term_id])) ? 'checked="checked"' : '';
                      ?>
                      <label class="qw-query-checkbox">
                        <input class="qw-js-title"
                               type="checkbox"
                               name="qw-query-options[override][tags][<?php print $tag->term_id; ?>]"
                               value="<?php print $tag->name; ?>"
                          <?php print $tag_checked; ?> />
                        <?php print $tag->name; ?>
                      </label>
                    <?php
                    }
                    ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- /override settings -->
          <?php
        }
      ?>

      <div id="qw-query-fields" class="qw-query-admin-options">
        <div class="qw-query-add-titles">
          <span class="qw-rearrange-title">
            Rearrange
          </span>
          <span class="qw-add-title" data-handler-type="field" data-form-id="qw-display-add-fields">
            Add
          </span>
        </div>
				
				<h4>Fields</h4>
        <div class="qw-clear-gone"><!-- ie hack -->&nbsp;</div>
        <div id="query-fields" class="qw-handler-items">
          <?php
            if(is_array($fields)){
              foreach($fields as $field)
              {
                $tokens[$field['name']] = '{{'.$field['name'].'}}';
                $args = array(
                  'image_sizes' => $image_sizes,
                  'file_styles' => $file_styles,
                  'field' => $field,
                  'weight' => $field['weight'],
                  'options' => $options,
                  'display' => $display,
                  'args'  => $args,
                  'tokens' => $tokens,
                );
                ?>
                <div class="qw-handler-item">
                  <div class="qw-handler-item-title"><?php print $field['title'];  ?></div>
                  <div class="qw-handler-item-form">
                    <?php print theme('query_field', $args); ?>
                  </div>
                </div>
                <?php
              }
            }
          ?>
        </div>
      </div>
      <!-- /fields -->
    </div>
    <!-- /column -->

<!-- right column -->
    <div class="qw-query-admin-column">
    <!-- sorts -->
      <div id="qw-query-sorts" class="qw-query-admin-options">
        <div class="qw-query-add-titles">
          <span class="qw-rearrange-title">
            Rearrange
          </span>
          <span class="qw-add-title" data-handler-type="sort" data-form-id="qw-display-add-sorts">
            Add
          </span>
        </div>
        <h4>Sort Options</h4>
        <div class="qw-clear-gone"><!-- ie hack -->&nbsp;</div>
        <div id="query-sorts" class="qw-handler-items">
          <?php
            if(is_array($sorts)){
              foreach($sorts as $sort)
              {
                $args = array(
                  'sort' => $sort,
                  'weight' => $sort['weight'],
                );
                ?>
                <div class="qw-handler-item">
                  <div class="qw-handler-item-title"><?php print $sort['title'];  ?></div>
                  <div class="qw-handler-item-form">
                    <?php  print theme('query_sort', $args); ?>
                  </div>
                </div>
                <?php
              }
            }
          ?>
        </div>
      </div>

    <!-- filters -->
      <div id="qw-query-filters" class="qw-query-admin-options">
        <div class="qw-query-add-titles">
          <span class="qw-rearrange-title">
            Rearrange
          </span>
          <span class="qw-add-title" data-handler-type="filter" data-form-id="qw-display-add-filters">
            Add
          </span>
        </div>

        <h4>Filters</h4>
        <div class="qw-clear-gone"><!-- ie hack -->&nbsp;</div>

        <div id="query-filters" class="qw-handler-items">
          <?php
            // loop through and display
            if(is_array($filters)){
              foreach($filters as $filter_name => $filter)
              {
                $args = array(
                  'filter' => $filter,
                  'weight' => $filter['weight'],
                );
                ?>
                  <div class="qw-handler-item">
                    <div class="qw-handler-item-title"><?php print $filter['title']; ?></div>
                    <div class="qw-handler-item-form">
                      <?php print theme('query_filter', $args); ?>
                    </div>
                  </div>
                <?php
              }
            }
          ?>
        </div>
      </div>
    </div>
    <div class="qw-clear-gone"><!-- ie hack -->&nbsp;</div>
  </div>
</form>

<!-- ------- HIDDEN FORMS --------- -->
<div id="qw-options-forms">

  <!-- all sorts -->
  <div id="qw-display-add-sorts" class="qw-hidden">
    <input class="add-handler-type" type="hidden" value="sort">
    <p class="description">Select options for sorting the query results.</p>
    <div class="qw-checkboxes">
      <?php
        // loop through sorts
        foreach($all_sorts as $hook_key => $sort)
        {
          ?>
          <label class="qw-sort-checkbox">
            <input type="checkbox"
                   value="<?php print $sort['type']; ?>" />
            <input class="qw-handler-hook_key"
                   type="hidden"
                   value="<?php print $sort['hook_key']; ?>" />
            <?php print $sort['title']; ?>
          </label>
          <p class="description"><?php print $sort['description']; ?></p>
          <?php
        }
      ?>
    </div>
  </div>

  <!-- all fields -->
  <div id="qw-display-add-fields" class="qw-hidden">
    <input class="add-handler-type" type="hidden" value="field">
    <p class="description">Select Fields to add to this query's output.</p>
    <div class="qw-checkboxes">
      <?php
        // loop through fields
        foreach($all_fields as $hook_key => $field)
        {
          ?>
          <label class="qw-field-checkbox">
            <input type="checkbox"
                   value="<?php print $field['type']; ?>" />
            <input class="qw-handler-hook_key"
                   type="hidden"
                   value="<?php print $field['hook_key']; ?>" />
            <?php print $field['title']; ?>
          </label>
          <p class="description"><?php print $field['description']; ?></p>
          <?php
        }
      ?>
    </div>
  </div>

  <!-- all filters -->
  <div id="qw-display-add-filters" class="qw-hidden">
    <input class="add-handler-type" type="hidden" value="filter">
    <p class="description">Select filters to affect the query's results.</p>
    <div class="qw-checkboxes">
      <?php
        // loop through filters
        foreach($all_filters as $hook_key => $filter)
        {
          // for now, this is how I'll prevent certain filters on overrides
          if(isset($filter['query_display_types']) && is_array($filter['query_display_types']) && in_array($query_type, $filter['query_display_types']))
          { ?>
            <label class="qw-filter-checkbox">
              <input type="checkbox"
                     value="<?php print $filter['type']; ?>" />
            <input class="qw-handler-hook_key"
                   type="hidden"
                   value="<?php print $filter['hook_key']; ?>" />
            <?php print $filter['title']; ?>
            </label>
            <p class="description"><?php print $filter['description']; ?></p>
            <?php
          }
        }
      ?>
    </div>
  </div>
</div><!-- options forms -->

<?php
  // editor-shared preview area html template
  include_once QW_PLUGIN_DIR . '/admin/editors/preview-area.php';
?>
WordPress hooks

### Actions

File | Signature | Description
---|---
admin/class-qw-admin-pages.php | `do_action( 'qw_delete_query', $query_id );` | Perform additional events on query deletion.
includes/exposed.php | `do_action_ref_array('qw_process_exposed_sorts', array(&$exposed_sorts));` | ??? Modify exposed sort options.


### Filters

File | Signature | Description
---|---
admin/class-qw-admin-pages.php | `apply_filters( 'qw_pre_save', $options, $query_id );` | Alter the query data before it is saved to the database. 
includes/class-qw-query.php | `apply_filters( 'qw_pre_preview', $options );` | Alter the query options before preview execution.
includes/class-qw-query.php | `apply_filters('qw_generate_query_args', array(), $options );` | Modify the query args before query execution.
includes/class-qw-query.php | `apply_filters( 'qw_pre_query', $args, $options );` | Modify the query args before query execution.
includes/class-qw-query.php | `apply_filters( 'qw_pre_render', $options, $args );` | Modify the query options after the query has been executed, but before it has been rendered.
includes/class-qw-shortcodes.php | `apply_filters( 'qw_shortcode_default_attributes', array( 'id' => '', 'slug' => '' ) );` | Allow additional shortcode attributes.
includes/class-qw-shortcodes.php | `apply_filters( 'qw_shortcode_attributes', $atts, $options_override );` | Set the value of shortcode attributes
includes/class-qw-shortcodes.php | `apply_filters( 'qw_shortcode_options', $options_override, $atts );` | Modify the query options before shortcode is executed.
includes/exposed.php | `apply_filters( 'qw_process_exposed_filters', $exposed_filters );` | ????
includes/hooks.php | `apply_filters( 'qw_meta_value_display_handlers', array() );` | Additional mechanisms for handling custom field (meta value) output.
includes/hooks.php | `apply_filters( 'qw_handlers', array() );` | ??? All handler item types
includes/hooks.php | `apply_filters( 'qw_basics', array() );` | Register a basic handler item type.
includes/hooks.php | `apply_filters( 'qw_fields', array() );` | Register a field handler item type.
includes/hooks.php | `apply_filters( 'qw_filters', array() );` | Register a filter handler item type.
includes/hooks.php | `apply_filters( 'qw_overrides', array() );` | Register a override handler item type.
includes/hooks.php | `apply_filters( 'qw_sort_options', array() );` | Register a sort option handler item type.
includes/hooks.php | `apply_filters( 'qw_post_statuses', $default );` | Modify the allowed list of post statuses. 
includes/hooks.php | `apply_filters( 'qw_post_types', array() );` | Modify the allowed list of post types.
includes/hooks.php | `apply_filters( 'qw_styles', $styles );` | Register a new template style.
includes/hooks.php | `apply_filters( 'qw_row_styles', array() );` | Register a new row style.
includes/hooks.php | `apply_filters( 'qw_file_styles', $default );` | Register a new file output style.
includes/hooks.php | `apply_filters( 'qw_pager_types', $pagers );` | Register a new pager style.
includes/hooks.php | `return apply_filters( 'qw_default_template_file', 'index.php' );` | Set the default template style name.


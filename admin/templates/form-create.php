<?php if ( !defined('QW_PLUGIN_DIR') ) exit; ?>

<div class="wrap">
	<h2><?php print esc_html( get_admin_page_title() ); ?></h2>

	<div id="poststuff" class="admin-content">
		<div id="qw-create" class="postbox">
			<div class="inside">
				<?php

				print $form->open();

				print $form->render_field( array(
						'type' => 'text',
						'name' => 'name',
						'title' => __( 'Query Name' ),
						'description' => __( 'Query name is a way for you, the admin, to identify the query easily.' ),
						'value' => '',
				) );

				print $form->render_field( array(
						'type' => 'select',
						'name' => 'type',
						'title' => __( 'Query Type' ),
						'description' => __( 'Query type determines how the query works within WordPress. View descriptions on the right.' ),
						'value' => '',
						'options' => array(
								'widget' => __( 'Widget & Shortcode' ),
								'override' => __( 'Override' ),
						),
				) );

				print $form->render_field( array(
						'type' => 'submit',
						'name' => 'create-query',
						'value' => __( 'Create' ),
						'class' => array( 'button-primary' ),
				) );

				print $form->close();
				?>
			</div>
		</div>

		<div id="qw-create-sidebar">
			<div class="postbox">
				<h2 class="hndle"><span><?php _e( 'Widget & Shortcode Queries' ); ?></span></h2>
				<div class="inside">
					<p><?php _e( 'The Query Wrangler comes with a reusable WordPress Widget that an be places in sidebars. When you create a query of the this type, that query becomes selectable in the Widget settings.' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h2 class="hndle"><?php _e( 'Override Queries' ); ?></h2>
				<div class="inside">
					<p><?php _e( 'An Override Query allows you to alter the way existing Wordpress pages such as Category and Tag pages display.' ); ?></p>
					<p><strong><?php _e( 'This feature is still in beta development. It has only been tested with permalinks set to /%category%/%postname%/' ); ?></strong></p>
				</div>
			</div>
			<div  class="postbox" style="color: #888;">
				<h2  class="hndle" style="color: #888; text-decoration: line-through;"><?php _e( 'Page Queries' ); ?></h2>
				<div class="inside">
					<p><?php _e( 'Deprecated and disabled. For using Queries as a page, create a normal WP Page and place the query shortcode on it.' ); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>

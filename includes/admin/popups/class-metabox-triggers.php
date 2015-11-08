<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class PUM_Popup_Triggers_Metabox {
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_metabox' ) );
		add_action( 'print_media_templates', array( __CLASS__, 'media_templates' ) );
	}
	/**
	 * Register the metabox for popup post type.
	 *
	 * @since 1.4
	 * @return void
	 */
	public static function register_metabox() {
		add_meta_box( 'pum_popup_triggers', __( 'Triggers', 'popup-maker' ), array( __CLASS__, 'render_metabox' ), 'popup', 'normal', 'high' );
	}

	/**
	 * Display Metabox
	 *
	 * @since 1.4
	 * @return void
	 */
	public static function render_metabox() {
		global $post; ?>
		<div id="pum_popup_trigger_fields" class="popmake_meta_table_wrap">
			<button type="button" class="button button-primary add-new"><?php _e( 'Add New', 'popup-maker' ); ?></button>
			<?php do_action( 'pum_popup_triggers_metabox_before', $post->ID ); ?>
			<table id="pum_popup_triggers_list" class="form-table">
				<thead>
					<tr>
						<th><?php _e( 'Type', 'popup-maker' ); ?></th>
						<th><?php _e( 'Settings', 'popup-maker' ); ?></th>
						<th><?php _e( 'Actions', 'popup-maker' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr data-index="0">
						<td><span class="edit"><?php _e( 'Auto Open', 'popup-maker' ); ?></span>
							<input class="popup_triggers_field_type" type="hidden" name="popup_triggers[0][type]" value="auto_open" />
							<input class="popup_triggers_field_settings" type="hidden" name="popup_triggers[0][settings]" value="<?php esc_html_e( json_encode( array(
								'delay' => 0,
								'cookie' => array(
									'trigger' => 'close',
									'time' => '1 month',
									'session' => 0,
									'path' => '/',
									'key' => ''
								)
							) ) ); ?>" />
						</td>
						<td>Delay: 0 Cookie: On Close / 1 Month</td>
						<td class="actions">
							<i class="edit dashicons dashicons-edit"></i>
							<i class="remove dashicons dashicons-no"></i>
						</td>
					</tr>
				</tbody>
			</table>
			<?php do_action( 'pum_popup_triggers_metabox_after', $post->ID ); ?>
		</div><?php
	}


	public static function media_templates() { ?>

		<script type="text/template" id="pum_trigger_row_templ">
			<?php static::render_row( array(
				'index' => '<%= index %>',
				'type' => '<%= type %>',
				'labels' => array(
					'triggers' => array(
						'open' => 'On Open',
						'close:' => 'On Close',
						'manual' => 'Manual',
						'disabled' => 'Disabled',
					),
				),
				'columns' => array(
					'type' => '<%= PUMTriggers.getLabel(type) %>',
					'settings' => '<%= PUMTriggers.getSettingsDesc(type, trigger_settings) %>',
				),
				'settings' => '<%= encodeURIComponent(JSON.stringify(trigger_settings)) %>',
			) ); ?>
		</script>

		<script type="text/template" id="pum_trigger_add_type_templ"><?php
			ob_start(); ?>
			<select id="popup_trigger_add_type">
				<?php foreach ( apply_filters( 'pum_trigger_types', array(
					__( 'Auto Open', 'popup-maker' ) => 'auto_open',
					__( 'Click', 'popup-maker' ) => 'click_open',
				) ) as $option => $value ) : ?>
					<option value="<?php echo $value; ?>"><?php echo $option; ?></option>
				<?php endforeach ?>
			</select><?php
			$content = ob_get_clean();

			PUM_Admin_Helpers::modal( array(
				'id' => 'pum_trigger_add_type_modal',
				'title' => __( 'Choose what type of trigger to add?', 'popup-maker' ),
				'content' => $content
			) ); ?>
		</script>

		<?php foreach ( PUM_Triggers::instance()->get_triggers() as $id => $trigger ) { ?>
		<script type="text/template" class="pum-trigger-settings <?php esc_attr_e( $id ); ?> templ" id="pum_trigger_settings_<?php esc_attr_e( $id ); ?>_templ">

			<?php ob_start(); ?>

			<input type="hidden" name="type" class="type" value="<?php esc_attr_e( $id ); ?>"/>
			<input type="hidden" name="index" class=index" value="<%= index %>"/>

			<div class="pum-tabs-container vertical-tabs tabbed-form">

				<ul class="tabs">
					<?php
					/**
					 * Render Each settings tab.
					 */
					foreach ( $trigger->get_sections() as $tab => $label ) { ?>
						<li class="tab">
							<a href="#<?php esc_attr_e( $id . '_' . $tab ); ?>_settings"><?php esc_html_e( $label ); ?></a>
						</li>
					<?php } ?>
				</ul>

				<?php
				/**
				 * Render Each settings tab contents.
				 */
				foreach ( $trigger->get_sections() as $tab => $label ) { ?>
					<div id="<?php esc_attr_e( $id . '_' . $tab ); ?>_settings" class="tab-content">
						<?php $trigger->render_templ_fields( $tab ); ?>
					</div>
				<?php } ?>

			</div><?php

			$content = ob_get_clean();

			PUM_Admin_Helpers::modal( array(
				'id' => 'pum_trigger_settings_' . $id,
				'title' => $trigger->get_label( 'modal_title' ),
				'class' => 'tabbed-content trigger-editor',
				'save_button_text' => '<%= save_button_text %>',
				'content' => $content
			) ); ?>
		</script><?php
		}

	}

	public static function render_row( $row = array() ) {
		$row = wp_parse_args( $row, array(
			'index' => 0,
			'type' => 'auto_open',
			'columns' => array(
				'type' => __( 'Auto Open', 'popup-maker' ),
				'settings' => __( 'Delay: 0', 'popup-maker' ),
			),
			'settings' => array(
				'delay' => 0,
				'cookie' => array(
					'trigger' => 'close',
					'time' => '1 month',
					'session' => 0,
					'path' => '/',
					'key' => ''
				)
			),
		) );
		?>
		<tr data-index="<?php echo $row['index']; ?>">
			<td><span class="edit"><?php echo $row['columns']['type']; ?></span>
				<input class="popup_triggers_field_type" type="hidden" name="popup_triggers[<?php echo $row['index']; ?>][type]" value="<?php echo $row['type']; ?>" />
				<input class="popup_triggers_field_settings" type="hidden" name="popup_triggers[<?php echo $row['index']; ?>][settings]" value="<?php echo maybe_json_attr( $row['settings'] ); ?>" />
			</td>
			<td><?php echo $row['columns']['settings']; ?></td>
			<td class="actions">
				<i class="edit dashicons dashicons-edit"></i>
				<i class="remove dashicons dashicons-no"></i>
			</td>
		</tr>
		<?php
	}

}
PUM_Popup_Triggers_Metabox::init();
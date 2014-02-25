<?php
/*
Plugin Name: WP CLI Widget Import
Description: Import and Export Sidebars
Author: Tom J Nowell, Code For The People
Version: 1.0
Author URI: http://codeforthepeople.net
*/

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * Manage widget settings.
	 *
	 * @package wp-cli
	 */
	class Widget_Command extends WP_CLI_Command {

		public function list_sidebars( $args, $assoc_args ) {
			$sidebars_array = get_option( 'sidebars_widgets' );

			$data = array();
			foreach ( $sidebars_array as $location => $widgets ) {
				$data[] = array( $location, is_array( $widgets ) ? implode( ', ', $widgets ) : '' );
			}

			$table = new \cli\Table();
			$table->setHeaders( array( 'Location', 'Widgets' ) );
			$table->setRows( $data );
			$table->display();
		}

		/**
		 * export sidebar options
		 *
		 * @synopsis [--json]
		 */
		public function export_sidebars( $args, $assoc_args ) {
			$assoc_args['format'] = 'json';
			$temp_sidebars_array  = get_option( 'sidebars_widgets' );
			$sidebars_array       = array();
			// don't include empty sidebars
			foreach ( $temp_sidebars_array as $key => $value ) {
				if ( ! empty( $value ) ) {
					$sidebars_array[$key] = $value;
				}
			}
			$widgets = array_values( $sidebars_array );
			$tmp     = array();
			foreach ( $widgets as $widget_list ) {
				if ( count( $widget_list ) && is_array( $widget_list ) ) {
					$values = array_values( $widget_list );
					$tmp    = array_merge( $values, $tmp );
				}
			}
			$widget_options = array();
			foreach ( $tmp as $widget ) {
				$matches = array();
				$match   = preg_match( '/^([a-zA-Z0-9\-_]+)\-(\d+)$/', $widget, $matches );
				if ( ! $match ) {
					continue;
				}
				if ( count( $matches ) >= 2 ) {
					list( , $widget_name ) = $matches;
					$options = get_option( 'widget_' . $widget_name );
					$widget_options['widget_' . $widget_name] = $options;
				}
			}
			$export = array(
				'sidebars'       => $sidebars_array,
				'widget_options' => $widget_options,
			);
			echo json_encode( $export );
		}

		public function import_sidebars( $args, $assoc_args ) {
			$file = 'php://stdin';
			if ( ! empty( $assoc_args['data'] ) ) {
				$file = $assoc_args['data'];
			}
			$json = file_get_contents( $file );
			if ( empty( $json ) ) {
				WP_CLI::error( 'failed to read input' );
			}

			$data = json_decode( trim( $json ), true, 1500 );
			if ( ! is_array( $data ) ) {
				WP_CLI::error( 'Unexpected input' );
			}
			if ( ! isset( $data['sidebars'] ) ) {
				WP_CLI::error( 'Unexpected input, no sidebars' );
			}
			if ( ! isset( $data['widget_options'] ) ) {
				WP_CLI::error( 'Unexpected input, no widget options' );
			}
			$sidebars       = $data['sidebars'];
			$widget_options = $data['widget_options'];

			foreach ( $widget_options as $widget_name => $value ) {
				$current_value = get_option( $widget_name );
				if ( $current_value == $value ) {
					continue;
				}
				if ( ! update_option( $widget_name, $value ) ) {
					WP_CLI::error( "Could not update option '$widget_name'.", false ); // continue run
				} else {
					WP_CLI::success( "Updated options '$widget_name'." );
				}
			}

			$updated = update_option( 'sidebars_widgets', $sidebars );
			if ( $updated ) {
				WP_CLI::success( 'Sidebar options updated' );
			}
		}

	}

	WP_CLI::add_command( 'widget', 'Widget_Command' );
}
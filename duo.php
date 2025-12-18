<?php
/*
Plugin Name: Duo
Description: Minimal plugin that adds a single admin page.
Version: 1.0.0
Author: Duo
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin page hook suffix for conditional asset loading.
 *
 * @var string|null
 */
$GLOBALS['duo_admin_hook_suffix'] = null;

/**
 * Register plugin settings.
 */
function duo_register_settings() {
	register_setting(
		'duo_settings',
		'duo_user_settings',
		[
			'type'              => 'array',
			'sanitize_callback' => 'duo_sanitize_user_settings',
			'default'           => [],
		]
	);
}

add_action( 'admin_init', 'duo_register_settings' );

/**
 * Sanitize settings.
 *
 * @param mixed $value Incoming value.
 *
 * @return array
 */
function duo_sanitize_user_settings( $value ) {
	$value = is_array( $value ) ? $value : [];

	$out          = [];
	$out['name']  = isset( $value['name'] ) ? sanitize_text_field( $value['name'] ) : '';
	$out['email'] = isset( $value['email'] ) ? sanitize_email( $value['email'] ) : '';

	$checkboxes = [
		'animations',
		'sound_effects',
		'listening_exercises',
		'motivational_messages',
		'notifications',
	];
	foreach ( $checkboxes as $key ) {
		$out[ $key ] = ! empty( $value[ $key ] ) ? 1 : 0;
	}

	$connections = [
		'facebook_connect',
		'google_connect',
	];
	foreach ( $connections as $key ) {
		$out[ $key ] = ! empty( $value[ $key ] ) ? 1 : 0;
	}

	return $out;
}

/**
 * Register a single top-level admin page for Duo.
 */
function duo_register_admin_page() {
	$GLOBALS['duo_admin_hook_suffix'] = add_menu_page(
		'Duo',               // Page title
		'Duo',               // Menu title
		'manage_options',    // Capability
		'duo-admin',         // Menu slug
		'duo_render_admin_page', // Callback
		'dashicons-admin-generic', // Icon
		80                   // Position
	);
}

add_action( 'admin_menu', 'duo_register_admin_page' );

/**
 * Enqueue admin assets for the Duo page.
 *
 * @param string $hook_suffix Current admin page.
 */
function duo_admin_enqueue_assets( $hook_suffix ) {
	if ( empty( $GLOBALS['duo_admin_hook_suffix'] ) || $hook_suffix !== $GLOBALS['duo_admin_hook_suffix'] ) {
		return;
	}

	wp_enqueue_style(
		'duo-admin',
		plugin_dir_url( __FILE__ ) . 'assets/duo-admin.css',
		[],
		'1.0.0'
	);
}

add_action( 'admin_enqueue_scripts', 'duo_admin_enqueue_assets' );

/**
 * Render the Duo admin page content.
 */
function duo_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$user     = wp_get_current_user();
	$settings = get_option( 'duo_user_settings', [] );

	$name  = isset( $settings['name'] ) && $settings['name'] !== '' ? $settings['name'] : $user->display_name;
	$email = isset( $settings['email'] ) && $settings['email'] !== '' ? $settings['email'] : $user->user_email;

	$username         = $user->user_login;
	$view_profile_url = get_edit_profile_url( $user->ID );
	$wp_logout_url    = wp_logout_url( admin_url() );
	$form_action      = admin_url( 'options.php' );

	$toggle_defaults = [
		'animations'            => 0,
		'sound_effects'         => 0,
		'listening_exercises'   => 0,
		'motivational_messages' => 0,
		'notifications'         => 0,
		'facebook_connect'      => 0,
		'google_connect'        => 0,
	];
	$settings        = wp_parse_args( $settings, $toggle_defaults );

	?>
	<div class="wrap duo-admin">
		<div class="duo-layout">
			<main class="duo-content" aria-label="<?php echo esc_attr__( 'Account settings', 'duo' ); ?>">
				<h1 class="duo-title"><?php echo esc_html__( 'Account', 'duo' ); ?></h1>

				<form id="duo-settings-form" class="duo-form" method="post"
					  action="<?php echo esc_url( $form_action ); ?>" enctype="multipart/form-data">
					<?php settings_fields( 'duo_settings' ); ?>

					<div class="duo-panel" id="duo-profile">
						<div class="duo-panel__row duo-panel__row--picture">
							<div class="duo-row__label duo-picture-label"><?php echo esc_html__( 'Profile picture', 'duo' ); ?></div>
							<div class="duo-picture-actions"
								 aria-label="<?php echo esc_attr__( 'Profile picture actions', 'duo' ); ?>">
								<div class="duo-picture-button">
									<label class="duo-button duo-button--ghost duo-picture-choose"
										   for="duo-profile-picture">
										<?php echo esc_html__( 'Choose file', 'duo' ); ?>
									</label>
									<input class="duo-file__input" type="file" id="duo-profile-picture"
										   name="duo_profile_picture" accept="image/*"/>
									<div class="duo-picture-context">
										<?php echo esc_html__( 'no file selected', 'duo' ); ?>
									</div>
								</div>
								<div class="duo-help duo-help--lg duo-picture-max">
									<?php echo esc_html__( 'maximum image size is 1 MB', 'duo' ); ?>
								</div>
							</div>
						</div>

						<div class="duo-panel__row">
							<div class="duo-row">
								<div class="duo-row__label"><?php echo esc_html__( 'Name', 'duo' ); ?></div>
								<div class="duo-row__input">
									<label class="screen-reader-text"
										   for="duo-name"><?php echo esc_html__( 'Name', 'duo' ); ?></label>
									<input class="duo-input" type="text" id="duo-name" name="duo_user_settings[name]"
										   value="<?php echo esc_attr( $name ); ?>"/>
								</div>
							</div>
						</div>

						<div class="duo-panel__row">
							<div class="duo-row">
								<div class="duo-row__label"><?php echo esc_html__( 'Username', 'duo' ); ?></div>
								<div class="duo-row__input">
									<label class="screen-reader-text"
										   for="duo-username"><?php echo esc_html__( 'Username', 'duo' ); ?></label>
									<input class="duo-input" type="text" id="duo-username"
										   value="<?php echo esc_attr( $username ); ?>" disabled/>
								</div>
							</div>
						</div>

						<div class="duo-panel__row">
							<div class="duo-row">
								<div class="duo-row__label duo-row__label--lg"><?php echo esc_html__( 'Email', 'duo' ); ?></div>
								<div class="duo-row duo-row--with-note">
									<div class="duo-row__input">
										<label class="screen-reader-text"
											   for="duo-email"><?php echo esc_html__( 'Email', 'duo' ); ?></label>
										<input class="duo-input" type="email" id="duo-email"
											   name="duo_user_settings[email]"
											   value="<?php echo esc_attr( $email ); ?>"/>
									</div>
									<a class="duo-row__note" href="#duo-verify-email">
										<?php echo esc_html__( 'Email not verified. Verify now', 'duo' ); ?>
									</a>
								</div>
							</div>
						</div>

						<div class="duo-panel__row" id="duo-password">
							<div class="duo-row">
								<div class="duo-row__label"><?php echo esc_html__( 'Password', 'duo' ); ?></div>
								<div class="duo-row__input">
									<label class="screen-reader-text"
										   for="duo-password-input"><?php echo esc_html__( 'Password', 'duo' ); ?></label>
									<input class="duo-input" type="password" id="duo-password-input" value="********"
										   disabled/>
								</div>
							</div>
						</div>
					</div>

					<div class="duo-panel" id="duo-more">
						<div class="duo-panel__row duo-panel__row--toggle">
							<span class="duo-toggle__label"><?php echo esc_html__( 'Facebook Connect', 'duo' ); ?></span>
							<label class="duo-toggle">
								<input type="checkbox" name="duo_user_settings[facebook_connect]"
									   value="1" <?php checked( 1, (int) $settings['facebook_connect'] ); ?> />
								<span class="duo-toggle__ui" aria-hidden="true"></span>
							</label>
						</div>
						<div class="duo-panel__row duo-panel__row--toggle">
							<span class="duo-toggle__label"><?php echo esc_html__( 'Google+ Connect', 'duo' ); ?></span>
							<label class="duo-toggle">
								<input type="checkbox" name="duo_user_settings[google_connect]"
									   value="1" <?php checked( 1, (int) $settings['google_connect'] ); ?> />
								<span class="duo-toggle__ui" aria-hidden="true"></span>
							</label>
						</div>
					</div>

					<div class="duo-panel" id="duo-notifications">
						<div class="duo-panel__row duo-panel__row--toggle">
							<span class="duo-toggle__label"><?php echo esc_html__( 'Notifications', 'duo' ); ?></span>
							<label class="duo-toggle">
								<input type="checkbox" name="duo_user_settings[notifications]"
									   value="1" <?php checked( 1, (int) $settings['notifications'] ); ?> />
								<span class="duo-toggle__ui" aria-hidden="true"></span>
							</label>
						</div>
						<div class="duo-panel__row duo-panel__row--toggle">
							<span class="duo-toggle__label"><?php echo esc_html__( 'Sound effects', 'duo' ); ?></span>
							<label class="duo-toggle">
								<input type="checkbox" name="duo_user_settings[sound_effects]"
									   value="1" <?php checked( 1, (int) $settings['sound_effects'] ); ?> />
								<span class="duo-toggle__ui" aria-hidden="true"></span>
							</label>
						</div>
						<div class="duo-panel__row duo-panel__row--toggle">
							<span class="duo-toggle__label"><?php echo esc_html__( 'Animations', 'duo' ); ?></span>
							<label class="duo-toggle">
								<input type="checkbox" name="duo_user_settings[animations]"
									   value="1" <?php checked( 1, (int) $settings['animations'] ); ?> />
								<span class="duo-toggle__ui" aria-hidden="true"></span>
							</label>
						</div>
						<div class="duo-panel__row duo-panel__row--toggle">
							<span class="duo-toggle__label"><?php echo esc_html__( 'Motivational messages', 'duo' ); ?></span>
							<label class="duo-toggle">
								<input type="checkbox" name="duo_user_settings[motivational_messages]"
									   value="1" <?php checked( 1, (int) $settings['motivational_messages'] ); ?> />
								<span class="duo-toggle__ui" aria-hidden="true"></span>
							</label>
						</div>
						<div class="duo-panel__row duo-panel__row--toggle">
							<span class="duo-toggle__label"><?php echo esc_html__( 'Listening exercises', 'duo' ); ?></span>
							<label class="duo-toggle">
								<input type="checkbox" name="duo_user_settings[listening_exercises]"
									   value="1" <?php checked( 1, (int) $settings['listening_exercises'] ); ?> />
								<span class="duo-toggle__ui" aria-hidden="true"></span>
							</label>
						</div>
					</div>
				</form>

				<div class="duo-danger" id="duo-privacy">
					<a class="duo-danger__link"
					   href="<?php echo esc_url( $wp_logout_url ); ?>"><?php echo esc_html__( 'Logout', 'duo' ); ?></a>
					<a class="duo-danger__link"
					   href="#duo-export"><?php echo esc_html__( 'Export my data', 'duo' ); ?></a>
					<a class="duo-danger__link duo-danger__link--danger"
					   href="#duo-delete"><?php echo esc_html__( 'Delete my account', 'duo' ); ?></a>
				</div>

				<footer class="duo-footer">
					<div class="duo-footer__links">
						<a class="duo-footer__link" href="#privacy"><?php echo esc_html__( 'Privacy', 'duo' ); ?></a>
						<a class="duo-footer__link" href="#terms"><?php echo esc_html__( 'Terms', 'duo' ); ?></a>
						<a class="duo-footer__link"
						   href="#investors"><?php echo esc_html__( 'INVESTORS', 'duo' ); ?></a>
						<a class="duo-footer__link" href="#careers"><?php echo esc_html__( 'Careers', 'duo' ); ?></a>
						<a class="duo-footer__link"
						   href="#guidelines"><?php echo esc_html__( 'Guidelines', 'duo' ); ?></a>
						<a class="duo-footer__link" href="#help"><?php echo esc_html__( 'Help', 'duo' ); ?></a>
						<a class="duo-footer__link" href="#about"><?php echo esc_html__( 'About', 'duo' ); ?></a>
						<a class="duo-footer__link" href="#blog"><?php echo esc_html__( 'Blog', 'duo' ); ?></a>
					</div>
					<div class="duo-footer__meta">
						<span class="duo-footer__text"><?php echo esc_html__( 'efficacy', 'duo' ); ?></span>
						<a class="duo-footer__link"
						   href="mailto:<?php echo esc_attr( 'hello@designdrops.io' ); ?>"><?php echo esc_html__( 'hello@designdrops.io', 'duo' ); ?></a>
					</div>
				</footer>
			</main>

			<aside class="duo-menu" id="duo-menu" aria-label="<?php echo esc_attr__( 'Settings menu', 'duo' ); ?>">
				<div class="duo-card duo-menu-card">
					<div class="duo-user">
						<div class="duo-user__avatar">
							<?php echo get_avatar( $user->ID, 40 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<div class="duo-user__meta">
							<div class="duo-user__username"><?php echo esc_html( $username ); ?></div>
							<a class="duo-link duo-link--primary" href="<?php echo esc_url( $view_profile_url ); ?>">
								<?php echo esc_html__( 'View your profile', 'duo' ); ?>
							</a>
						</div>
					</div>

					<nav class="duo-nav" aria-label="<?php echo esc_attr__( 'Account sections', 'duo' ); ?>">
						<a class="duo-nav__item" href="#duo-privacy"><?php echo esc_html__( 'Privacy', 'duo' ); ?></a>
						<a class="duo-nav__item"
						   href="#duo-schools"><?php echo esc_html__( 'Duolingo for Schools', 'duo' ); ?></a>
						<a class="duo-nav__item"
						   href="#duo-super"><?php echo esc_html__( 'Super Duolingo', 'duo' ); ?></a>
						<a class="duo-nav__item" href="#duo-password"><?php echo esc_html__( 'Password', 'duo' ); ?></a>
						<a class="duo-nav__item"
						   href="#duo-courses"><?php echo esc_html__( 'Manage Courses', 'duo' ); ?></a>
						<a class="duo-nav__item"
						   href="#duo-leaderboards"><?php echo esc_html__( 'Leaderboards', 'duo' ); ?></a>
						<a class="duo-nav__item"
						   href="#duo-notifications"><?php echo esc_html__( 'Notifications', 'duo' ); ?></a>
						<a class="duo-nav__item" href="#duo-profile"><?php echo esc_html__( 'Profile', 'duo' ); ?></a>
						<a class="duo-nav__item" href="#duo-more"><?php echo esc_html__( 'More', 'duo' ); ?></a>
					</nav>

					<div class="duo-menu-actions">
						<button type="submit" form="duo-settings-form" class="duo-button duo-button--muted">
							<?php echo esc_html__( 'Save changes', 'duo' ); ?>
						</button>
					</div>
				</div>
			</aside>
		</div>
	</div>
	<?php
}

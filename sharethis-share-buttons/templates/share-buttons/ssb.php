<?php
/**
 * Template for ssb selector.
 *
 * @package ShareThisShareButtons
 */

?>
<p class="st-preview-message ssb-select">
	<?php esc_html_e( 'Preview: for reference only', 'sharethis-custom' ); ?>
</p>
<div class="network-select-type-wrap">
	<h2 style="text-align: center;"><?php esc_html_e( 'Channels', 'sharethis-custom' ); ?></h2>
	<div class="manual-share network-type st-radio-config engage">
		<div class="item">
			<input name="network-select-type engage" class="with-gap" type="radio" value="manual-share" checked="checked" />
			<label>
				<?php esc_html_e( 'Choose Buttons Manually', 'sharethis-custom' ); ?>
			</label>
			<p>
				<?php esc_html_e( 'Select your own social networks and customize', 'sharethis-custom' ); ?>
			</p>
		</div>
	</div>
	<div class="smart-share network-type st-radio-config">
		<div class="item">
			<input name="network-select-type" class="with-gap" type="radio" value="smart-share" />
			<label>
				<?php esc_html_e( 'Smart Share Buttons', 'sharethis-custom' ); ?>
			</label>
		</div>
		<p>
			<?php esc_html_e( 'Automatically selects which social channels to display based on each user’s geolocation and device type', 'sharethis-custom' ); ?>
		</p>
		<label for="social-service-count">
			<?php esc_html_e( 'Select the number or Social Services' ); ?>
			<select id="social-service-count" name="social-service-count">
				<?php foreach ( array( 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ) as $count ) : ?>
					<option value="<?php echo esc_html( $count ); ?>" <?php echo selected( 6, $count ); ?>><?php echo esc_html( $count ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
	</div>
</div>

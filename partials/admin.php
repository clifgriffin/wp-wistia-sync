<?php
global $CGD_WistiaSync; // we'll need this below
$post_types = get_post_types('', 'objects');
?>
<div class="wrap">
    <h2>Wistia Sync Settings</h2>

    <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    	<?php $CGD_WistiaSync->the_nonce(); ?>

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">Wistia API Key</th>
					<td>
						<label>
							<input type="text" name="<?php echo $CGD_WistiaSync->get_field_name('api_key'); ?>" value="<?php echo $CGD_WistiaSync->get_setting('api_key'); ?>" /><br />
							Wistia API Key. (Only accessible by account owner. Account -> API Access)
						</label>
					</td>
				</tr>

                <tr>
                    <th scope="row" valign="top">Post Type</th>
                    <td>
                        <label>
							<select name="<?php echo $CGD_WistiaSync->get_field_name('post_type'); ?>">
								<option>Select post type</option>

								<?php foreach( $post_types as $pt ): $selected = false; ?>
									<?php if ( $CGD_WistiaSync->get_setting('post_type') == $pt->name ) $selected = true; ?>
									<option value="<?php echo $pt->name; ?>" <?php if ($selected) echo "selected"; ?> ><?php echo $pt->label; ?></option>
								<?php endforeach; ?>

							</select>
                            The post type to sync with Wistia. Other post types will be ignored.
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row" valign="top">Video ID Meta Key</th>
                    <td>
                        <label>
                            <input type="text" name="<?php echo $CGD_WistiaSync->get_field_name('video_id_meta_key'); ?>" value="<?php echo $CGD_WistiaSync->get_setting('video_id_meta_key'); ?>" /><br />
                            The key for the meta value that holds your Wistia video ID. (e.g., video_id)
                        </label>
                    </td>
                </tr>

				<tr>
                    <th scope="row" valign="top">Play Count Meta Key</th>
                    <td>
                        <label>
                            <input type="text" name="<?php echo $CGD_WistiaSync->get_field_name('play_count_meta_key'); ?>" value="<?php echo $CGD_WistiaSync->get_setting('play_count_meta_key'); ?>" /><br />
                            The key for the meta value that holds your Wistia play count. (e.g., play_count)
                        </label>
                    </td>
                </tr>

				<tr>
					<th scope="row" valign="top">Schedule</th>
					<td>
						<label>
							<select name="<?php echo $CGD_WistiaSync->get_field_name('schedule'); ?>">
								<option>Select scheduled frequency</option>

								<?php foreach( wp_get_schedules() as $sc_slug => $sc ): $selected = false; ?>
									<?php if ( $CGD_WistiaSync->get_setting('schedule') == $sc_slug ) $selected = true; ?>
									<option value="<?php echo $sc_slug; ?>" <?php if ($selected) echo "selected"; ?> ><?php echo $sc['display']; ?></option>
								<?php endforeach; ?>

							</select>
							How often should we sync posts with Wistia stats?
						</label>
					</td>
				</tr>
			</tbody>
    	</table>

    	<?php submit_button('Save Settings'); ?>
    </form>
</div>

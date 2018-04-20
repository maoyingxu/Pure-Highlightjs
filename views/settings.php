<div class="wrap">
    <h2>Pure Highlightjs <?php echo __('Settings', 'pure-highlightjs'); ?></h2>
    <form method="post" action="options.php">
        <?php settings_fields( 'pure-highlightjs-group' ); ?>
        <?php do_settings_sections( 'pure-highlightjs-group' ); ?>
		<?php $setting = line_color_get_setting(); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php echo __('Theme', 'pure-highlightjs') ?></th>
                <td>
                    <select name="pure-highlightjs-theme">
                        <?php foreach (pure_highlightjs_get_style_list() as $style) {
                            echo '<option value="' . esc_attr($style) . '"';
                            if ( $style == pure_highlightjs_option('pure-highlightjs-theme', PURE_HIGHLIGHTJS_DEFAULT_THEME) ) {
                                echo ' selected="selected"';
                            }
                            echo '>' . esc_attr($style) . '</option>';
                        } ?>
                    </select>
                </td>
            </tr>
			<tr valign="top">
					<th scope="row"><label>颜色设置</label></th>
					<td>
						<ul class="line-color-setting-ul">
							<?php $color = array(
									array(
										'title' => '鼠标悬浮行',
										'key' => 'hover-color',
										'default' => '#607d8b4d'
									),
									array(
										'title' => '被标记行',
										'key' => 'mark-color',
										'default' => '#423c36'
									)
								);
								foreach ($color as $key => $V) {
									?>
									<li class="line-color-setting-li">
										<code><?php echo $V['title'];?></code>
										<?php $color = $setting[$V['key']] ? $setting[$V['key']] : $V['default'];?>
										<input name="<?php echo line_color_setting_key($V['key']);?>" type="text" value="<?php echo $color;?>" id="line-default-color" data-default-color="<?php echo $V['default'];?>" class="regular-text line-color-picker" />
									</li>
								<?php } 
							?>
						</ul>
					</td>
				</tr>
        </table>
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save Changes', 'pure-highlightjs');?>">
            <input type="hidden" name="formaction" value="update-pure-highlightjs">
        </p>
    </form>
	<style>
		.line-color-setting-li{position: relative;padding-left: 80px}
		.line-color-setting-li code{position: absolute;left: 0;top: 1px;}
	</style>
</div>
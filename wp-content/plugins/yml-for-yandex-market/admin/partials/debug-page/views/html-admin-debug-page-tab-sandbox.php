<?php
/**
 * Display the Sandbox tab.
 * 
 * @version    5.0.0 (25-03-2025)
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/debug_page/
 */
defined( 'ABSPATH' ) || exit; ?>
<div class="postbox">
	<div class="inside">
		<?php
		try {
			y4ym_run_sandbox();
		} catch (Exception $e) {
			echo 'Exception: ', $e->getMessage(), "\n";
		}
		?>
	</div>
</div>
<?php
class GoogleAnalytics extends Plugin
{

	public function action_init()
	{
		$this->add_rule('"ga.js"', 'serve_ga');
		$this->add_rule('"gaextra.js"', 'serve_gaextra');
	}

	public function action_plugin_act_serve_ga()
	{
		if (Cache::has('ga.js')) {
			$js = Cache::get('ga.js');
		} else {
			$js = RemoteRequest::get_contents('http://www.google-analytics.com/ga.js');
			Cache::set('ga.js', $js, 86400); // cache for 1 day
		}

		// Clean the output buffer, so we can output from the header/scratch
		ob_clean();
		header('Content-Type: application/javascript');

		echo $js;
	}

	public function action_plugin_act_serve_gaextra()
	{
		ob_clean();
		header('Content-Type: application/javascript');

		include 'googleanalytics.js.php';
	}

	private function detect_ssl()
	{
		if ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1 || $_SERVER['SERVER_PORT'] == 443) {
			return true;
		}

		return false;
	}

	public function filter_plugin_config($actions, $plugin_id)
	{
		if ($plugin_id == $this->plugin_id()) {
			$actions[] = _t('Configure');
		}
		return $actions;
	}

	public function action_plugin_ui($plugin_id, $action)
	{
		if ( $plugin_id == $this->plugin_id() ) {
			switch ($action) {
				case _t('Configure'):
					$form = new FormUI(strtolower(get_class($this)));
					$form->append('text', 'clientcode', 'googleanalytics__clientcode', _t('Analytics Client Code'));
					$form->append('checkbox', 'loggedintoo', 'googleanalytics__loggedintoo', _t('Track logged-in users too'));
					$form->append('checkbox', 'trackoutgoing', 'googleanalytics__trackoutgoing', _t('Track outgoing links'));
					$form->append('checkbox', 'trackmailto', 'googleanalytics__trackmailto', _t('Track mailto links'));
					$form->append('checkbox', 'trackfiles', 'googleanalytics__trackfiles', _t('Track download links'));
					$form->append('textarea', 'track_extensions', 'googleanalytics__trackfiles_extensions', _t('File extensions to track (comma separated)'));
					$form->append('checkbox', 'cache', 'googleanalytics__cache', _t('Cache tracking code file locally'));
					$form->append('submit', 'save', 'Save');
					$form->out();
				break;
			}
		}
	}

	/**
	 * Add update beacon support
	 **/
	public function action_update_check()
	{
		Update::add( 'GoogleAnalytics', '7e57a660-3bd1-11dd-ae16-0800200c9a66', $this->info->version );
	}

	public function theme_footer()
	{
		if (URL::get_matched_rule()->entire_match == 'user/login') {
			// Login page; don't display
			return;
		}

		$clientcode = Options::get('googleanalytics__clientcode');

		// get the url for the main Google Analytics code
		if (Options::get('googleanalytics__cache')) {
			$ga_url = Site::get_url('habari') . '/ga.js';
		} else {
			$ga_url = (self::detect_ssl()) ? 'https://ssl.google-analytics.com/ga.js' : 'http://www.google-analytics.com/ga.js';
		}

		// only actually track the page if we're not logged in, or we're told to always track
		$do_tracking = (!User::identify()->loggedin || Options::get('googleanalytics__loggedintoo'));

		$ga_extra_url = ($do_tracking) ? '<script src="' . Site::get_url('habari') . '/gaextra.js' . '" type="text/javascript"></script>' : '';
		$track_page   = ($do_tracking) ? 'pageTracker._trackPageview();' : '';

		echo <<<ANALYTICS
{$ga_extra_url}
<script src="{$ga_url}" type="text/javascript"></script>
<script type="text/javascript">
<!--//--><![CDATA[//><!--
try {var pageTracker = _gat._getTracker("{$clientcode}");{$track_page}} catch(e) {}
//--><!]]>
</script>
ANALYTICS;
	}

}
?>

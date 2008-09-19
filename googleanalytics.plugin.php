<?php
class GoogleAnalytics extends Plugin {
	function info()
	{
		return array(
			'url' => 'http://iamgraham.net/plugins',
			'name' => 'GoogleAnalytics',
			'description'   => 'Automaticly adds Google Analytics code to the bottom of your webpage.',
			'license' => 'Apache License 2.0',
			'author' => 'Graham Christensen',
			'authorurl' => 'http://iamgraham.net/',
			'version' => '0.5-alpha'
		);
	}

	public function filter_plugin_config($actions, $plugin_id)
	{
		if ($plugin_id == $this->plugin_id()) {
			$actions[]= _t('Configure');
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

	function theme_footer()
	{
		if ( URL::get_matched_rule()->entire_match == 'user/login') {
			// Login page; don't dipslay
			return;
		}
		if ( User::identify() ) {
			// Only track the logged in user if we were told to
			if ( !Options::get('googleanalytics__loggedintoo') ) {
				return;
			}
		}
		$clientcode = Options::get('googleanalytics__clientcode');
		echo <<<ENDAD
<script type='text/javascript'>
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
	var pageTracker = _gat._getTracker("{$clientcode}");
	pageTracker._initData();
	pageTracker._trackPageview();
</script>
ENDAD;
	}

}
?>

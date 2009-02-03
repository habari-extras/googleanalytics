<?php
class GoogleAnalytics extends Plugin {
	function info()
	{
		return array(
			'url' => 'http://iamgraham.net/plugins',
			'name' => 'GoogleAnalytics',
			'description'   => 'Automatically adds Google Analytics code to the bottom of your webpage.',
			'license' => 'Apache License 2.0',
			'author' => 'Graham Christensen',
			'authorurl' => 'http://iamgraham.net/',
			'version' => '0.5.1'
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
		
		$clientcode = Options::get('googleanalytics__clientcode');
		
		$script1 = <<<SCRIPT1
<script type='text/javascript'>
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
SCRIPT1;

		$script2 = <<<SCRIPT2
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("{$clientcode}");
} catch(err) {}
</script>
SCRIPT2;

		$script3 = <<<SCRIPT3
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("{$clientcode}");
pageTracker._trackPageview();
} catch(err) {}
</script>
SCRIPT3;


		// always output the first part, so things like the site overlay work for logged in users
		echo <<<SCRIPT1
<script type='text/javascript'>
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
SCRIPT1;

		echo <<<SCRIPT2
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("{$clientcode}");
SCRIPT2;

		// only actually track the page if we're not logged in, or we're told to always track
		if ( User::identify()->loggedin == false || Options::get('googleanalytics__loggedintoo') ) {
			echo <<<SCRIPT3
pageTracker._trackPageview();
SCRIPT3;
		}

		echo <<<SCRIPT4
} catch(err) {}</script>
SCRIPT4;
		
	}

}
?>

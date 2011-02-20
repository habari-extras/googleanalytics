<?php

class GoogleAnalytics extends Plugin
{
	public function action_init()
	{
		$this->add_rule('"gaextra.js"', 'serve_gaextra');
	}

	/**
	 * Add the tracking code to the template_header_javascript Stack.
	 *
	 * @todo determine if there is a better action to use
	 * @return null
	 */
	public function action_init_theme_any( $theme )
	{
		$code = $this->tracking_code();
		if ( $code != null ) {
			Stack::add('template_header_javascript', $code, 'googleanalytics');
		}
	}

	public function action_plugin_act_serve_gaextra()
	{
		ob_clean();
		header('Content-Type: application/javascript');

		// format extensions for regex match
		$extensions = explode(',', Options::get('googleanalytics__trackfiles_extensions'));
		$extensions = array_map('trim', $extensions);
		$extensions = implode('|', $extensions);

		include 'googleanalytics.js.php';
	}

	public function configure()
	{
		$form = new FormUI(strtolower(get_class($this)));
		$form->append('text', 'clientcode', 'googleanalytics__clientcode', _t('Analytics Client Code'));
		$form->append('checkbox', 'loggedintoo', 'googleanalytics__loggedintoo', _t('Track logged-in users too'));
		$form->append('checkbox', 'trackoutgoing', 'googleanalytics__trackoutgoing', _t('Track outgoing links'));
		$form->append('checkbox', 'trackmailto', 'googleanalytics__trackmailto', _t('Track mailto links'));
		$form->append('checkbox', 'trackfiles', 'googleanalytics__trackfiles', _t('Track download links'));
		$form->append('textarea', 'track_extensions', 'googleanalytics__trackfiles_extensions', _t('File extensions to track (comma separated)'));
		$form->append('submit', 'save', 'Save');
		return $form;
	}

	private function tracking_code()
	{
		if ( URL::get_matched_rule()->entire_match == 'user/login' ) {
			// Login page; don't display
			return;
		}

		$clientcode = Options::get('googleanalytics__clientcode');

		if ( empty($clientcode) ) {
			return;
		}

		// only actually track the page if we're not logged in, or we're told to always track
		$do_tracking = !User::identify()->loggedin || Options::get('googleanalytics__loggedintoo');
		$track_pageview = ($do_tracking) ? "_gaq.push(['_trackPageview']);" : '';
		$habari_url = Site::get_url('habari');

		$extra = <<<EXTRA
var extra = document.createElement('script');
extra.src = '{$habari_url}/gaextra.js';
extra.setAttribute('async', 'true');
document.documentElement.firstChild.appendChild(extra);
EXTRA;

		return <<<ANALYTICS
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '{$clientcode}']);
{$track_pageview}

(function() {
  var ga = document.createElement('script');
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' :
      'http://www') + '.google-analytics.com/ga.js';
  ga.setAttribute('async', 'true');
  document.documentElement.firstChild.appendChild(ga);
  {$extra}
})();
ANALYTICS;
	}
}

?>

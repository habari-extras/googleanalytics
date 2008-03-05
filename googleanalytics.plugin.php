<?php
class GoogleAnalytics extends Plugin {
        function info() {
                return array(
                        'url'                   => 'http://iamgraham.net/plugins',
                        'name'                  => 'GoogleAnalytics',
                        'description'   => 'Automaticly adds Google Analytics code to the bottom of your webpage.',
                        'license'               => 'Apache License 2.0',
                        'author'                => 'Graham Christensen',
                        'authorurl'             => 'http://iamgraham.net/',
                        'version'               => '0.3'
                );
        }

        public function filter_plugin_config($actions, $plugin_id) {
                if ($plugin_id == $this->plugin_id()) {
                        $actions[] = _t('Configure');
                }
                return $actions;
        }

        public function action_plugin_ui($plugin_id, $action) {
                if ($plugin_id == $this->plugin_id()) {
                        switch ($action) {
                                case _t('Configure'):
                                        $ui = new FormUI(strtolower(get_class($this)));
                                        $clientcode     = $ui->add('text', 'clientcode', _t('Analytics Client Code'));
                                        
                                        $ui->on_success(array($this, 'updated_config'));
                                        $ui->out();
                                break;
                        }
                }
        }

        public function updated_config($ui) {
                return true;
        }

        private static function getvar($var) {
                return Options::get('googleanalytics:'.$var);
        }

        function theme_footer() {
                if ( URL::get_matched_rule()->entire_match == 'user/login') {
                        // Login page; don't dipslay
                        return;
                }
                if ( User::identify() ) {
                        // User is logged in, don't want to record that, do we?
                        return;
                }
                $code = <<<ENDAD
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("CLIENTCODE");
pageTracker._initData();
pageTracker._trackPageview();
</script>
ENDAD;
                $replace = array(
                        'CLIENTCODE'    => self::getvar('clientcode'));
                $code = str_replace(array_keys($replace), array_values($replace), $code);
                echo $code;
        }

}
?>

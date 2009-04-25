<?php
$extensions = explode(',', Options::get('googleanalytics__trackfiles_extensions'));
$extensions = array_map('trim', $extensions);
$extensions = implode('|', $extensions);
?>
var GoogleAnalytics = {
	trackClick: function (a) {
		var isInternal = new RegExp("^(https?):\/\/" + window.location.host, "i");
		var isDownload = new RegExp("\\.(<?php echo $extensions ?>)$", "i");

		var trackoutgoing = <?php echo (Options::get('googleanalytics__trackoutgoing')) ? 'true' : 'false' ?>;
		var trackmailto   = <?php echo (Options::get('googleanalytics__trackmailto')) ? 'true' : 'false' ?>;
		var trackfiles    = <?php echo (Options::get('googleanalytics__trackfiles')) ? 'true' : 'false' ?>;

		try {
			if (isInternal.test(a.href)) {
				if (trackfiles && isDownload.test(a.href)) {
					var extension = isDownload.exec(a.href);
					pageTracker._trackEvent("Downloads", extension[1].toUpperCase(), a.href.replace(isInternal, ''));
				}
			} else {
				if (trackmailto && a.protocol === "mailto:") {
					pageTracker._trackEvent("Mailtos", "Click", a.href.substring(7));
				} else if (trackoutgoing) {
					pageTracker._trackEvent("Outgoing Links", "Click", a.href);
				}
			}
		}
		catch (e) {}
	},

	attachListeners: function () {
		var hrefs = document.getElementsByTagName("a");

		for (var i = 0; i < hrefs.length; i++) {
			GoogleAnalytics.addEvent(hrefs[i], "click", function () {
				GoogleAnalytics.trackClick(this);
			});
		}
	},

	addEvent: function (obj, type, fn) {
		if (obj.attachEvent) {
			obj['e' + type + fn] = fn;
			obj[type + fn] = function () {
				obj['e' + type + fn](window.event);
			};
			obj.attachEvent('on' + type, obj[type + fn]);
		} else {
			obj.addEventListener(type, fn, false);
		}
	}
};

GoogleAnalytics.attachListeners();

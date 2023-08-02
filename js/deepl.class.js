class DeepLApi {

	/**
	 * Config values are set at init
	 * @type {{}}
	 */
	config = {
		hostFree: 'api-free.deepl.com',
		hostPro: 'api.deepl.com',
		APIUsePro:false,// to set at init
		APIKey: false, // to set at init
	};

	/**
	 * langs translation
	 * @type {{}}
	 */
	langs = {
		errorAjaxCall: "Erreur d'appel ajax"
	};

	/**
	 *
	 * @param config
	 * @param langs
	 */
	constructor(config = {}, langs = {}) {

		if (langs && typeof langs === 'object') {
			this.langs = Object.assign(this.langs, langs);
		}

		if (config && typeof config === 'object') {
			this.config = Object.assign(this.config, config);
		}
	}

	getDeeplApiUrl(addHttp = true){
		if(this.config.APIUsePro){
			return (addHttp?'https://':'') + this.config.hostPro;
		}

		return (addHttp?'https://':'') + this.config.hostFree;
	}

	/**
	 * set event messages
	 *
	 * @param {string} msg
	 * @param {boolean} status
	 * @param {boolean} sticky
	 */
	setEventMessage(msg, status = true, sticky = false) {

		let jnotifyConf = {
			delay: 1500                               // the default time to show each notification (in milliseconds)
			, type: 'error'
			, sticky: sticky                             // determines if the message should be considered "sticky" (user must manually close notification)
			, closeLabel: "&times;"                     // the HTML to use for the "Close" link
			, showClose: true                           // determines if the "Close" link should be shown if notification is also sticky
			, fadeSpeed: 150                           // the speed to fade messages out (in milliseconds)
			, slideSpeed: 250                           // the speed used to slide messages out (in milliseconds)
		}


		if (msg.length > 0) {
			if (status) {
				jnotifyConf.type = '';
				$.jnotify(msg, jnotifyConf);
			} else {
				$.jnotify(msg, jnotifyConf);
			}
		} else {
			$.jnotify('ErrorMessageEmpty', jnotifyConf);
		}
	}


	/**
	 *
	 * @param {string[]} texts
	 * @param {string}target_lang
	 * @param {string}source_lang default false to autodetect
	 * @callback callBackFunction
	 */
	getTranslations(texts, target_lang,source_lang = false, callBackFunction){
		let sendData = {
			"text":texts,
			"target_lang": target_lang
		};

		if(source_lang){
			sendData.source_lang = source_lang;
		}

		this.callDeeplInterface('/v2/translate', sendData, callBackFunction)
	}


	/**
	 *
	 * @param {string} url
	 * @param {object} sendData
	 * @callback callBackFunction
	 */
	callDeeplInterface(url, sendData = {}, callBackFunction, callBackErrorFunction = false) {




		sendData = Object.assign(sendData, {
			auth_key:this.config.APIKey
		});

		$.ajax({
			method: 'GET',
			url: this.getDeeplApiUrl(true) + url,
			// contentType: 'application/json',
			dataType:'jsonp',
			data: sendData,
			success:  (response) => {
				if (typeof callBackFunction === 'function') {
					callBackFunction(response);
				} else {
					console.error('Callback function invalide callDeeplInterface');
				}
			},
			error:  (err) => {
				if (typeof callBackFunction === 'function') {
					callBackFunction(err);
				}else{
					this.setEventMessage(this.langs.errorAjaxCall, false);
				}
			}
		});
	}
}

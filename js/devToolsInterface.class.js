class DevToolsInterface {

	/**
	 * Config values are set at init
	 * @type {{}}
	 */
	config = {
		interfaceUrl: false, // to set at init
		token : false // to set at init
	};

	/**
	 * langs translation
	 * @type {{}}
	 */
	langs = {
		errorAjaxCall: "Ajax call error",
		errorAjaxCallDisconnected:"You are probably disconnected",
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
	 * @param {string} action
	 * @param {object} sendData
	 * @callback callBackFunction
	 * @callback callBackErrorFunction
	 */
	callInterface(action, sendData = {}, callBackFunction) {

		if(!this.config.interfaceUrl){
			this.setEventMessage('Interface url not provided', false);
			return false;
		}

		let ajaxData = {
			'data': sendData,
			'token': this.config.token,
			'action': action,
		};

		$.ajax({
			method: 'POST',
			url: this.config.interfaceUrl,
			dataType: 'json',
			data: ajaxData,
			success: (response) => {

				if (typeof callBackFunction === 'function'){
					callBackFunction(response);
				} else {
					console.error('Callback function invalide for callKanbanInterface');
				}

				if(response.newToken != undefined){
					this.config.token = response.newToken;
				}

				if(response.msg.length > 0) {
					this.setEventMessage(response.msg, response.result > 0 ? true : false, response.result == 0 ? true : false );
				}

				if(response.debug.length > 0) {
					console.log(response.debug);
				}
			},
			error: (err) => {

				if(err.responseText.length > 0){

					// detect login page in case of just disconnected
					let loginPage = $(err.responseText).find('[name="actionlogin"]');
					if(loginPage != undefined && loginPage.val() == 'login'){
						this.setEventMessage(this.langs.errorAjaxCallDisconnected, false);

						setTimeout(function (){
							location.reload();
						}, 2000);

					}else{
						this.setEventMessage(this.langs.errorAjaxCall, false);
					}
				}
				else{
					this.setEventMessage(this.langs.errorAjaxCall, false);
				}
			}
		});
	}
}

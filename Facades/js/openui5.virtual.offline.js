// @ts-nocheck

// const SPEEDTEST_DUMMY_DATA_NAME = 'speedtest.data.json';


// export class NetworkUtils {
// 	/**
// 	 * 
// 	 * Enables browser XHR API Mock. All XHR calls made from other libraries/facades intercepted and a network error is returned for them.
// 	 * 
// 	 * @returns void
// 	 */
// 	static enableXhrMock() {
// 		// Store original method to disable mock in future
// 		window['_originalXMLHttpRequest'] = window.XMLHttpRequest;

// 		function MockXHR() {
// 			const xhr = new window['_originalXMLHttpRequest']();
// 			const self = this;
		
// 			// Store the request details
// 			this.requestDetails = {};

// 			// Manually copy event handler properties
// 			const eventHandlers = [
// 				"onreadystatechange", "onload", "onerror", "onprogress", 
// 				"onabort", "ontimeout", "onloadstart", "onloadend"
// 			];
// 			eventHandlers.forEach(handler => {
// 				Object.defineProperty(self, handler, {
// 					get: function() { return xhr[handler]; },
// 					set: function(val) { xhr[handler] = val; }
// 				});
// 			});
		
// 			// Manually copy response properties
// 			const responseProperties = [
// 				"readyState", "responseText", "response", "status", "statusText", 
// 				"responseType", "responseURL", "responseXML"
// 			];
// 			responseProperties.forEach(prop => {
// 				Object.defineProperty(self, prop, {
// 					get: function() { return self[`_${prop}`] || xhr[prop]; },
// 					set: function(val) { self[`_${prop}`] = val; }
// 				});
// 			});
		
// 			// Bind the methods
// 			for (const attr in xhr) {
// 				if (typeof xhr[attr] === 'function') {
// 					this[attr] = xhr[attr].bind(xhr);
// 				}
// 			}
	
// 			this.open = function(method, url, async, user, password) {
// 				this.requestDetails.url = url;
// 				this.requestDetails.method = method;
// 				this.requestDetails.async = async !== undefined ? async : true;
// 				this.requestDetails.user = user || null;
// 				this.requestDetails.password = password || null;
		
// 				xhr.open.apply(xhr, arguments);
// 			};

// 			this.setRequestHeader = function(header, value) {
// 				if (!this.requestDetails.headers) {
// 					this.requestDetails.headers = {};
// 				}
// 				this.requestDetails.headers[header] = value;
// 				xhr.setRequestHeader.apply(xhr, arguments);
// 			};
		
		
// 			this.send = async function(body) {
// 				const cacheData = await CacheUtils.getCachedResponse(this.requestDetails.url, body);
		
// 				if (cacheData) {
// 					// Simulate a successful response from cache
// 					setTimeout(() => {
// 						self.readyState = 4; // DONE
// 						self.status = 200; // HTTP OK
// 						self.responseText = JSON.stringify(cacheData);
// 						self.responseType = 'json';
// 						self.response = cacheData;
		
// 						// Trigger onreadystatechange if defined
// 						if (self.onreadystatechange) {
// 							self.onreadystatechange();
// 						}
		
// 						// Trigger onload if defined
// 						if (self.onload) {
// 							self.onload();
// 						}
// 					}, 0);
// 				} else {
// 					// If no cache hit, proceed with normal XHR
// 					if (this.requestDetails.url && this.requestDetails.url.includes(SPEEDTEST_DUMMY_DATA_NAME)) {
// 						xhr.send.apply(xhr, arguments);
// 					} else {
// 						setTimeout(() => {
// 							if (self.onreadystatechange) {
// 								self.readyState = 4;
// 								self.status = 0;
// 								self.onreadystatechange();
// 							}
// 							if (self.onerror) {
// 								self.onerror();
// 							}
// 						}, 0);
// 					}
// 				}
// 			};
// 		}

// 		// Replace network functions with mock functions
// 		window.XMLHttpRequest = MockXHR;
// 	}

// 	/**
// 	 * 
// 	 * Disables browser XHR API Mock and use browser's native XHR function;
// 	 */
// 	static disableXhrMock() {
// 		window.XMLHttpRequest = window['_originalXMLHttpRequest'];
// 	}

// 	/**
// 	 * 
// 	 * Enables browser Fetch API Mock. All Fetch calls made from other libraries/facades intercepted and a network error is returned for them.
// 	 * 
// 	 * @returns void
// 	 */
// 	static enableFetchMock() {
// 		window._originalFetch = window.fetch;

// 		function MockFetch (input, init) {
// 			// Check if the URL contains dummy data name
// 			if (typeof input === "string" && input.includes(SPEEDTEST_DUMMY_DATA_NAME)) {
// 				return window._originalFetch(input, init);
// 			}
// 			return new Promise((resolve, reject) => {
// 				reject(new Error('Network error'));
// 			});
// 		};

// 		window.fetch = MockFetch;
// 	}

// 	/**
// 	 * 
// 	 * Disables browser Fetch API Mock and use browser's native Fetch function
// 	 */
// 	static disableFetchMock() {
// 		window.fetch = window['_originalFetch'];
// 	}
// }


export class ServiceWorkerUtils {

	/**
	 * Sends a message as an event to the service worker
	 * @param {Object} message A message string/object to send to the current service worker
	 */
	static message(message) {
		if (navigator?.serviceWorker?.controller) {
			navigator.serviceWorker.controller.postMessage(message);
		} else {
			navigator.serviceWorker.ready.then((registration) => {
				if (registration.active) {
					registration.active.postMessage(message);
				}
			});
		}
	}	
}

export class CacheUtils {
	static async getCachedResponse (url, body) {
		// Check cacheStorage for GET requests
		const cache = await caches.open('data-cache');
		const storageCacheObject = await cache.match(url);

		// Return if the entity exists in storage cache
		if (storageCacheObject) return storageCacheObject.json();


		// Check IndexDB for POST requests
		const db = new Dexie("sw-tools");
		db.version(1).stores({
            cache: 'key,response,timestamp'
        });
		let dexieCacheObject = null;
		try {
			const cacheItems = await db.cache.toArray();
			for (let item of cacheItems) {
				const parsedKey = JSON.parse(item.key);
				if (parsedKey.url && parsedKey.body) {
					if ((parsedKey.url === url || parsedKey.url.includes(url)) && parsedKey.body === body) {
						dexieCacheObject = JSON.parse(item.response.body);
						break;
					}
				}
			}
		} catch (error) { dexieCacheObject = null; }
		return dexieCacheObject;
	};
}
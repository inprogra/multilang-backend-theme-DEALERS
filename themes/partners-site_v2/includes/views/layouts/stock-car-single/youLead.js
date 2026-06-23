function initYlProductDataPush() {
	window.addEventListener(
		'load',
		() => {
			const element = document.querySelector( '.js-stock-car-single' );
			if (element && window.ylData) {
				const offerId = element.dataset.offerId
				const x       = window.ylData.push( {'product': {'products': [offerId]}} );
			}
		}
	);
}

export default initYlProductDataPush;
class Video {
	constructor(el) {
		const iframe = el.querySelector( 'iframe' )

		el.addEventListener(
			'click',
			event => {
				el.classList.add( 'is-active' )
				iframe.src = iframe.dataset.src
			}
		)
	}
}

document.addEventListener(
	'DOMContentLoaded',
	() => {
		let elements = document.querySelectorAll( '.js-video' )
		elements.forEach(
			(el) => {
				new Video( el );
			}
		)
	}
);

/* kacb.js  â€“ initialise every .kacb-carousel */
document.addEventListener('DOMContentLoaded', () => {

	const els = document.querySelectorAll('.kacb-carousel');
	if (!els.length || typeof Swiper === 'undefined') return;

	els.forEach(el => {

		/* create nav / pagination containers */
		const pag  = el.appendChild(Object.assign(document.createElement('div'), { className: 'swiper-pagination' }));
		const next = el.appendChild(Object.assign(document.createElement('div'), { className: 'swiper-button-next' }));
		const prev = el.appendChild(Object.assign(document.createElement('div'), { className: 'swiper-button-prev' }));
		const ind  = el.querySelector('.kacb-indicator');

		const swiper = new Swiper(el, {
			loop: true,
			slidesPerView: 1,
			pagination: { el: pag, clickable: true },
			navigation:  { nextEl: next, prevEl: prev },
			on: {
				init:        update,
				slideChange: update
			}
		});

		function update(sw) {
			/* count only real (nonâ€‘duplicate) slides */
			const total = [...sw.slides].filter(s => !s.classList.contains('swiper-slide-duplicate')).length;
			ind.textContent = `${sw.realIndex + 1} / ${total}`;
		}
	});
});

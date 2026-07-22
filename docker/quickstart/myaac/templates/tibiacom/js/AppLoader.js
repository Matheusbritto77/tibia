/**
 * AppLoader.js
 * Central Front-end Component Registry & Instantiator
 */
import { FooterComponent } from './components/FooterComponent.js';
import { StatusWidget } from './components/StatusWidget.js';

class AppLoader {
	constructor() {
		this.registry = {
			'FooterComponent': FooterComponent,
			'StatusWidget': StatusWidget,
		};
	}

	mount() {
		const elements = document.querySelectorAll('[data-component]');
		elements.forEach(element => {
			const name = element.getAttribute('data-component');
			const ComponentClass = this.registry[name];
			if (ComponentClass) {
				try {
					new ComponentClass(element);
				} catch (err) {
					console.error(`[AppLoader] Failed to mount component '${name}':`, err);
				}
			}
		});
	}
}

document.addEventListener('DOMContentLoaded', () => {
	const app = new AppLoader();
	app.mount();
});

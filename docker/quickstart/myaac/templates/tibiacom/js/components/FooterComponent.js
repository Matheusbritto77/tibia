/**
 * FooterComponent.js
 * Front-end ES6 Class Component for rendering website footer metadata
 */
export class FooterComponent {
	constructor(element) {
		this.container = element;
		this.apiUrl = '/api/v1/footer.php';
		this.init();
	}

	async init() {
		try {
			const data = await this.fetchData();
			this.render(data);
		} catch (error) {
			console.warn('[FooterComponent] API unavailable, rendering fallback:', error);
			this.renderFallback();
		}
	}

	async fetchData() {
		const response = await fetch(this.apiUrl);
		if (!response.ok) {
			throw new Error(`HTTP error ${response.status}`);
		}
		return await response.json();
	}

	render(data) {
		const visitors = data.visitors ?? 1;
		const views = data.page_views ?? 1;
		const poweredBy = data.powered_by || 'Britto Dev';

		this.container.innerHTML = `
			<div class="footer-stats" style="margin-bottom: 4px;">
				<span>Currently there is ${visitors} visitor.</span><br />
				<span>Page has been viewed ${views} times.</span>
			</div>
			<div class="footer-branding" style="font-weight: bold; color: #ffffff;">
				Powered by ${poweredBy}
			</div>
		`;
	}

	renderFallback() {
		this.container.innerHTML = `
			<div class="footer-branding" style="font-weight: bold; color: #ffffff;">
				Powered by Britto Dev
			</div>
		`;
	}
}

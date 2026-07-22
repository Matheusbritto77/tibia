/**
 * FooterComponent.js
 * Front-end ES6 Class Component for rendering modern Sticky Footer Bar & metadata
 */
export class FooterComponent {
	constructor(element) {
		this.container = element;
		this.apiUrl = '/api/v1/footer.php';
		this.pollInterval = 30000; // Poll every 30 seconds
		this.timer = null;
		this.init();
	}

	async init() {
		await this.refresh();
		this.startPolling();
	}

	startPolling() {
		if (this.timer) clearInterval(this.timer);
		this.timer = setInterval(() => this.refresh(), this.pollInterval);
	}

	async refresh() {
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
		const online = data.players_online ?? 0;
		const poweredBy = data.powered_by || 'Britto Dev';

		this.container.innerHTML = `
			<footer class="app-footer-bar">
				<div class="footer-content">
					<div class="footer-left">
						<span class="badge">Visitors: <strong>${visitors}</strong></span>
						<span class="badge">Views: <strong>${views}</strong></span>
						<span class="badge online">Players Online: <strong>${online}</strong></span>
					</div>
					<div class="footer-right">
						<span class="powered-by">Powered by <strong>${poweredBy}</strong></span>
					</div>
				</div>
			</footer>
		`;
	}

	renderFallback() {
		this.container.innerHTML = `
			<footer class="app-footer-bar">
				<div class="footer-content">
					<div class="footer-left">
						<span class="badge">Visitors: <strong>1</strong></span>
						<span class="badge online">Players Online: <strong>0</strong></span>
					</div>
					<div class="footer-right">
						<span class="powered-by">Powered by <strong>Britto Dev</strong></span>
					</div>
				</div>
			</footer>
		`;
	}
}

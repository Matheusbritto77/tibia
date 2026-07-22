/**
 * StatusWidget.js
 * Front-end ES6 Class Component for real-time Server Status & Players Online counter
 */
export class StatusWidget {
	constructor(element) {
		this.container = element;
		this.apiUrl = '/api/v1/status.php';
		this.pollInterval = 30000; // Poll every 30s
		this.timer = null;
		this.init();
	}

	async init() {
		await this.updateStatus();
		this.startPolling();

		// Add click handler to navigate to online players list
		this.container.style.cursor = 'pointer';
		this.container.addEventListener('click', () => {
			window.location.href = '?subtopic=online';
		});
	}

	startPolling() {
		if (this.timer) clearInterval(this.timer);
		this.timer = setInterval(() => this.updateStatus(), this.pollInterval);
	}

	async updateStatus() {
		try {
			const data = await this.fetchStatus();
			this.render(data);
		} catch (error) {
			console.warn('[StatusWidget] Failed to fetch server status:', error);
		}
	}

	async fetchStatus() {
		const response = await fetch(this.apiUrl);
		if (!response.ok) {
			throw new Error(`HTTP error ${response.status}`);
		}
		return await response.json();
	}

	render(data) {
		if (data.online) {
			this.container.innerHTML = `
				<div id="players" style="display: inline; font-weight: bold; font-size: 14px; color: #50e3c2;">
					${data.players}
				</div>
				<br />
				<span style="font-size: 11px; color: #f0d1a4;">Players Online</span>
			`;
		} else {
			this.container.innerHTML = `
				<span style="color: #ff4d4f; font-weight: bold;">Server<br />OFFLINE</span>
			`;
		}
	}
}

/**
 * Watso WhatsApp Chat - Frontend JavaScript
 * Debug mode improvements
 */

(function($) {
	'use strict';

	class WatsoChat {
		constructor() {
			this.settings = watso_data.settings || {};
			this.debugMode = this.settings.debug_mode || false; // Get from settings
			this.widget = null;
			this.dropdownMenu = null;
			this.isDropdownOpen = false;

			this.init();
		}

		init() {
			this.log('Initializing Watso Chat', {
				settings: this.settings,
				debugMode: this.debugMode
			});

			// Wait for DOM ready
			$(document).ready(() => {
				this.widget = $('#watso-chat-widget');
				this.dropdownMenu = this.widget.find('.watso-dropdown-menu');

				this.log('DOM Ready', {
					widgetFound: this.widget.length > 0,
					dropdownFound: this.dropdownMenu.length > 0
				});

				if (this.widget.length === 0) {
					this.error('Widget not found in DOM');
					this.debugDOM();
					return;
				}

				this.injectDynamicCSS();
				this.bindEvents();
				this.initializeWidget();

				this.log('Widget initialized successfully');
			});
		}

		// Centralized logging function
		log(message, data = null) {
			if (!this.debugMode) return;

			const timestamp = new Date().toISOString();
			console.log(`[Watso Frontend ${timestamp}] ${message}`, data || '');
		}

		error(message, data = null) {
			if (!this.debugMode) return;

			const timestamp = new Date().toISOString();
			console.error(`[Watso Frontend ERROR ${timestamp}] ${message}`, data || '');
		}

		injectDynamicCSS() {
			this.log('Injecting dynamic CSS');

			const settings = this.settings;
			const color = settings.button_color || '#25D366';
			const radius = settings.button_radius || 15;

			this.log('CSS values', { color, radius });

			// Create dynamic CSS based on settings
			const css = `
				/* Dynamic styles for Watso Chat Button */
				#watso-chat-widget .watso-chat-button {
					background-color: ${color} !important;
					border-radius: ${radius}px !important;
				}
				
				#watso-chat-widget .watso-chat-button:hover {
					background-color: ${this.adjustBrightness(color, -20)} !important;
				}
				
				#watso-chat-widget .watso-contact-avatar {
					background: ${color} !important;
				}
				
				/* Position-specific adjustments */
				${this.getPositionCSS(settings.position)}
			`;

			// Inject the CSS
			if (!document.getElementById('watso-dynamic-css')) {
				const style = document.createElement('style');
				style.id = 'watso-dynamic-css';
				style.textContent = css;
				document.head.appendChild(style);
				this.log('Dynamic CSS injected successfully');
			} else {
				this.log('Dynamic CSS already exists, updating');
				document.getElementById('watso-dynamic-css').textContent = css;
			}
		}

		getPositionCSS(position) {
			this.log('Generating position CSS', { position });

			switch (position) {
				case 'bottom-right':
					return `
						#watso-chat-widget { bottom: 20px !important; right: 20px !important; }
						@media (max-width: 768px) {
							#watso-chat-widget { bottom: 16px !important; right: 16px !important; }
						}
					`;
				case 'bottom-left':
					return `
						#watso-chat-widget { bottom: 20px !important; left: 20px !important; }
						@media (max-width: 768px) {
							#watso-chat-widget { bottom: 16px !important; left: 16px !important; }
						}
					`;
				case 'middle-right':
					return `
						#watso-chat-widget { 
							top: 50% !important; 
							right: 20px !important; 
							transform: translateY(-50%) !important; 
						}
						@media (max-width: 768px) {
							#watso-chat-widget { right: 16px !important; }
						}
					`;
				case 'middle-left':
					return `
						#watso-chat-widget { 
							top: 50% !important; 
							left: 20px !important; 
							transform: translateY(-50%) !important; 
						}
						@media (max-width: 768px) {
							#watso-chat-widget { left: 16px !important; }
						}
					`;
				default:
					this.error('Unknown position', { position });
					return '';
			}
		}

		adjustBrightness(hex, percent) {
			this.log('Adjusting color brightness', { hex, percent });

			// Remove # if present
			hex = hex.replace('#', '');

			// Parse RGB
			const num = parseInt(hex, 16);
			const amt = Math.round(2.55 * percent);
			const R = (num >> 16) + amt;
			const G = (num >> 8 & 0x00FF) + amt;
			const B = (num & 0x0000FF) + amt;

			// Ensure values are within 0-255 range
			const newR = Math.max(0, Math.min(255, R));
			const newG = Math.max(0, Math.min(255, G));
			const newB = Math.max(0, Math.min(255, B));

			const result = `#${((1 << 24) + (newR << 16) + (newG << 8) + newB).toString(16).slice(1)}`;
			this.log('Color adjusted', { original: hex, result });

			return result;
		}

		debugDOM() {
			this.error('=== DOM DEBUG ===');
			this.log('Body children count', $('body').children().length);
			this.log('Looking for watso elements...');

			// Check for any watso related elements
			$('[class*="watso"], [id*="watso"]').each((index, element) => {
				this.log('Found watso element', {
					index,
					tagName: element.tagName,
					className: element.className,
					id: element.id
				});
			});

			// Check if HTML is being output
			if ($('body').html().indexOf('watso') !== -1) {
				this.log('Found "watso" text in body HTML');
			} else {
				this.error('NO "watso" text found in body HTML');
			}
		}

		bindEvents() {
			this.log('Binding events');

			const $widget = this.widget;
			const $button = $widget.find('.watso-chat-button');
			const $dropdown = this.dropdownMenu;
			const $closeBtn = $dropdown.find('.watso-close-dropdown');
			const $dropdownItems = $dropdown.find('.watso-dropdown-item');

			this.log('Event binding elements found', {
				button: $button.length,
				dropdown: $dropdown.length,
				closeBtn: $closeBtn.length,
				dropdownItems: $dropdownItems.length
			});

			// Multiple numbers - show dropdown
			if ($button.hasClass('watso-multiple-numbers')) {
				this.log('Multiple numbers detected, binding dropdown events');

				$button.on('click', (e) => {
					e.preventDefault();
					e.stopPropagation();
					this.log('Multiple numbers button clicked');
					this.toggleDropdown();
				});

				// Handle keyboard activation
				$button.on('keydown', (e) => {
					if (e.keyCode === 13 || e.keyCode === 32) { // Enter or Space
						e.preventDefault();
						this.log('Multiple numbers button keyboard activated', { keyCode: e.keyCode });
						this.toggleDropdown();
					}
				});

				// Close dropdown when clicking close button
				$closeBtn.on('click', (e) => {
					e.preventDefault();
					e.stopPropagation();
					this.log('Close button clicked');
					this.closeDropdown();
				});

				// Track clicks on dropdown items
				$dropdownItems.on('click', (e) => {
					const $item = $(e.currentTarget);
					const number = $item.data('number');
					this.log('Dropdown item clicked', { number });
					this.trackClick(number);
					this.closeDropdown();
				});

				// Close dropdown when clicking outside
				$(document).on('click', (e) => {
					if (!$widget.is(e.target) && $widget.has(e.target).length === 0) {
						if (this.isDropdownOpen) {
							this.log('Clicked outside widget, closing dropdown');
							this.closeDropdown();
						}
					}
				});

				// Handle escape key
				$(document).on('keydown', (e) => {
					if (e.keyCode === 27 && this.isDropdownOpen) { // Escape key
						this.log('Escape key pressed, closing dropdown');
						this.closeDropdown();
						$button.focus(); // Return focus to button
					}
				});
			} else {
				this.log('Single number detected, binding direct click');

				// Single number - direct click
				$button.on('click', (e) => {
					const number = $button.data('number');
					this.log('Single number button clicked', { number });
					this.trackClick(number);
				});
			}

			// Handle keyboard navigation
			this.bindKeyboardEvents();
		}

		bindKeyboardEvents() {
			this.log('Binding keyboard events');

			const $dropdown = this.dropdownMenu;
			const $items = $dropdown.find('.watso-dropdown-item');
			let currentIndex = -1;

			$dropdown.on('keydown', (e) => {
				this.log('Dropdown keydown', { keyCode: e.keyCode, currentIndex });

				switch (e.keyCode) {
					case 38: // Up arrow
						e.preventDefault();
						currentIndex = currentIndex > 0 ? currentIndex - 1 : $items.length - 1;
						$items.eq(currentIndex).focus();
						this.log('Navigation up', { newIndex: currentIndex });
						break;
					case 40: // Down arrow
						e.preventDefault();
						currentIndex = currentIndex < $items.length - 1 ? currentIndex + 1 : 0;
						$items.eq(currentIndex).focus();
						this.log('Navigation down', { newIndex: currentIndex });
						break;
					case 13: // Enter
						if (currentIndex >= 0) {
							this.log('Enter pressed on item', { index: currentIndex });
							$items.eq(currentIndex)[0].click();
						}
						break;
					case 27: // Escape
						this.log('Escape pressed in dropdown');
						this.closeDropdown();
						break;
				}
			});

			// Reset index when dropdown closes
			$dropdown.on('watso-dropdown-closed', () => {
				currentIndex = -1;
				this.log('Dropdown closed, keyboard index reset');
			});
		}

		toggleDropdown() {
			this.log('Toggling dropdown', { currentState: this.isDropdownOpen });

			if (this.isDropdownOpen) {
				this.closeDropdown();
			} else {
				this.openDropdown();
			}
		}

		openDropdown() {
			this.log('Opening dropdown');

			this.dropdownMenu.addClass('watso-show');
			this.isDropdownOpen = true;

			// Update ARIA attributes
			this.widget.find('.watso-chat-button').attr('aria-expanded', 'true');

			// Focus first item
			setTimeout(() => {
				this.dropdownMenu.find('.watso-dropdown-item').first().focus();
				this.log('Dropdown opened, first item focused');
			}, 100);

			// Fire custom event
			this.dropdownMenu.trigger('watso-dropdown-opened');
		}

		closeDropdown() {
			this.log('Closing dropdown');

			this.dropdownMenu.removeClass('watso-show');
			this.isDropdownOpen = false;

			// Update ARIA attributes
			this.widget.find('.watso-chat-button').attr('aria-expanded', 'false');

			// Fire custom event
			this.dropdownMenu.trigger('watso-dropdown-closed');
		}

		trackClick(number) {
			this.log('Tracking click', { number });

			if (!watso_data.ajax_url || !watso_data.nonce) {
				this.error('AJAX not configured for tracking', {
					ajax_url: watso_data.ajax_url,
					nonce: watso_data.nonce
				});
				return;
			}

			const data = {
				action: 'watso_track_click',
				nonce: watso_data.nonce,
				number: number,
				source_url: watso_data.current_url || window.location.href,
				timestamp: new Date().getTime()
			};

			this.log('Sending tracking request', data);

			$.ajax({
					   url: watso_data.ajax_url,
					   type: 'POST',
					   data: data,
					   success: (response) => {
						   this.log('Click tracked successfully', response);
					   },
					   error: (xhr, status, error) => {
						   this.error('Error tracking click', { xhr, status, error });
					   }
				   });

			// Fire custom event
			$(document).trigger('watso_click_tracked', {
				number: number,
				source_url: data.source_url
			});

			// Find the number index for multiple numbers
			let numberIndex = 0;
			let eventName = 'Watso Contact';

			if (this.settings.numbers && Array.isArray(this.settings.numbers) && this.settings.numbers.length > 1) {
				// Find which number was clicked
				for (let i = 0; i < this.settings.numbers.length; i++) {
					if (this.settings.numbers[i].number === number) {
						numberIndex = i;
						break;
					}
				}

				// Create event name with index for multiple numbers
				if (numberIndex > 0) {
					eventName = `Watso Contact ${numberIndex + 1}`;
				}
			}

			this.log('Tracking details', { numberIndex, eventName });

			// Meta/Facebook tracking with dynamic event name
			if (this.settings.meta_tracking && typeof fbq !== 'undefined') {
				fbq('track', eventName, {
					method: 'whatsapp',
					number: number,
					contact_index: numberIndex + 1
				});
				this.log('Meta tracking fired', { eventName });
			}

			// Google Analytics tracking with contact index
			if (typeof gtag !== 'undefined') {
				gtag('event', 'whatsapp_click', {
					event_category: 'contact',
					event_label: `${number} (Contact ${numberIndex + 1})`,
					contact_index: numberIndex + 1,
					value: 1
				});
				this.log('Google Analytics tracking fired');
			}

			// Universal Analytics (legacy) with contact index
			if (typeof ga !== 'undefined') {
				ga('send', 'event', 'WhatsApp', 'Click', `${number} (Contact ${numberIndex + 1})`);
				this.log('Universal Analytics tracking fired');
			}
		}

		checkSchedule() {
			this.log('Checking schedule', { scheduleEnabled: this.settings.schedule_enabled });

			if (!this.settings.schedule_enabled) {
				this.log('Schedule not enabled, showing widget');
				return true;
			}

			const now = new Date();
			const currentDay = now.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
			const currentTime = now.toTimeString().substr(0, 5); // HH:MM format

			this.log('Current time info', { currentDay, currentTime });

			// Check holidays
			if (this.settings.holidays && Array.isArray(this.settings.holidays)) {
				const today = now.toISOString().split('T')[0]; // YYYY-MM-DD format
				const isHoliday = this.settings.holidays.some(holiday => holiday.date === today);

				this.log('Holiday check', { today, isHoliday, holidays: this.settings.holidays });

				if (isHoliday) {
					this.hideWidget();
					this.log('Widget hidden - Holiday');
					return false;
				}
			}

			// Check schedule hours
			if (this.settings.schedule_hours && this.settings.schedule_hours[currentDay]) {
				const daySchedule = this.settings.schedule_hours[currentDay];

				this.log('Day schedule check', { currentDay, daySchedule });

				if (!daySchedule.enabled) {
					this.hideWidget();
					this.log('Widget hidden - Day not enabled');
					return false;
				}

				const startTime = daySchedule.start || '09:00';
				const endTime = daySchedule.end || '18:00';

				this.log('Time range check', { currentTime, startTime, endTime });

				if (currentTime < startTime || currentTime > endTime) {
					this.hideWidget();
					this.log('Widget hidden - Outside schedule', { startTime, endTime });
					return false;
				}
			}

			this.showWidget();
			this.log('Schedule check passed, widget visible');
			return true;
		}

		checkVisibility() {
			this.log('Checking visibility conditions');

			// Check if mobile and mobile is disabled
			const isMobileDevice = this.isMobile();
			const showMobile = this.settings.show_mobile;

			this.log('Mobile check', { isMobileDevice, showMobile });

			if (isMobileDevice && !showMobile) {
				this.hideWidget();
				this.log('Widget hidden - Mobile disabled');
				return false;
			}

			this.log('Visibility check passed');
			return true;
		}

		initializeWidget() {
			this.log('Initializing widget visibility');

			if (!this.checkVisibility()) {
				return;
			}

			if (!this.checkSchedule()) {
				return;
			}

			this.showWidget();
		}

		hideWidget() {
			this.log('Hiding widget');
			if (this.widget) {
				this.widget.removeClass('watso-visible');
				this.widget.hide();
			}
		}

		showWidget() {
			this.log('Showing widget');
			if (this.widget) {
				this.widget.addClass('watso-visible');
				this.widget.show();
			}
		}

		isMobile() {
			const userAgent = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
			const windowWidth = window.innerWidth <= 768;
			const result = userAgent || windowWidth;

			this.log('Mobile detection', { userAgent, windowWidth, result });
			return result;
		}

		// Public API methods
		static getInstance() {
			if (!window.watsoChat) {
				window.watsoChat = new WatsoChat();
			}
			return window.watsoChat;
		}

		// Public methods for external use
		open() {
			this.log('Public API: open() called');
			if (this.dropdownMenu && this.dropdownMenu.length) {
				this.openDropdown();
			}
		}

		close() {
			this.log('Public API: close() called');
			if (this.dropdownMenu && this.dropdownMenu.length) {
				this.closeDropdown();
			}
		}

		show() {
			this.log('Public API: show() called');
			this.showWidget();
		}

		hide() {
			this.log('Public API: hide() called');
			this.hideWidget();
		}

		updateSettings(newSettings) {
			this.log('Public API: updateSettings() called', newSettings);

			const oldDebugMode = this.debugMode;
			this.settings = { ...this.settings, ...newSettings };
			this.debugMode = this.settings.debug_mode || false;

			// Log debug mode change
			if (oldDebugMode !== this.debugMode) {
				if (this.debugMode) {
					console.log('[Watso Frontend] Debug mode enabled via updateSettings!');
				} else {
					console.log('[Watso Frontend] Debug mode disabled via updateSettings!');
				}
			}

			this.injectDynamicCSS(); // Re-inject CSS with new settings
			this.checkSchedule();
			this.checkVisibility();
		}

		refreshSettings() {
			this.log('Refreshing settings from server');

			if (!watso_data.ajax_url || !watso_data.nonce) {
				this.error('AJAX not configured for settings refresh');
				return;
			}

			$.ajax({
				   url: watso_data.ajax_url,
				   type: 'POST',
				   data: {
					   action: 'watso_get_current_settings',
					   nonce: watso_data.nonce
				   },
				   success: (response) => {
					   if (response.success && response.data) {
						   this.log('Settings refreshed successfully');
						   this.updateSettings(response.data);
					   }
				   },
				   error: (xhr, status, error) => {
					   this.error('Error refreshing settings', { xhr, status, error });
				   }
			   });
		}
	}

	// Initialize when DOM is ready
	$(function() {
		// Initialize the chat widget
		const watsoChat = WatsoChat.getInstance();

		// Make it globally available
		window.WatsoChat = WatsoChat;

		// Expose some methods to global scope for easier access
		window.watsoOpen = () => watsoChat.open();
		window.watsoClose = () => watsoChat.close();
		window.watsoShow = () => watsoChat.show();
		window.watsoHide = () => watsoChat.hide();
	});

	// Handle schedule checks and settings refresh every 30 seconds
	setInterval(() => {
		if (window.watsoChat) {
			if (window.watsoChat.debugMode) {
				window.watsoChat.log('Periodic check - schedule and settings');
			}

			// Refresh settings from server
			window.watsoChat.refreshSettings();
		}
	}, 30000); // Check every 30 seconds

})(jQuery);

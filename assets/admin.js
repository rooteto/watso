/**
 * Watso WhatsApp Chat - Admin JavaScript
 * Debug mode improvements
 */

(function($) {
	'use strict';

	class WatsoAdmin {
		constructor() {
			this.currentTab = 'general';
			this.debugMode = false; // Will be set from settings

			this.init();
		}

		init() {
			$(document).ready(() => {
				// Initialize debug mode first
				this.initDebugMode();

				this.initTabs();
				this.initColorPickers();
				this.initMediaUploaders();
				this.initToggleFields();
				this.initDynamicFields();
				this.bindEvents();

				// Preview system
				this.initLivePreview();
			});
		}

		// Initialize debug mode from settings
		initDebugMode() {
			const debugCheckbox = $('input[name="watso_settings[debug_mode]"]');
			this.debugMode = debugCheckbox.length > 0 && debugCheckbox.is(':checked');

			this.log('Debug mode initialized', { enabled: this.debugMode });
		}

		// Centralized logging function
		log(message, data = null) {
			if (!this.debugMode) return;

			const timestamp = new Date().toISOString();
			console.log(`[Watso Admin ${timestamp}] ${message}`, data || '');
		}

		error(message, data = null) {
			if (!this.debugMode) return;

			const timestamp = new Date().toISOString();
			console.error(`[Watso Admin ERROR ${timestamp}] ${message}`, data || '');
		}

		initTabs() {
			this.log('Initializing tabs');

			$('.watso-tab').on('click', (e) => {
				e.preventDefault();
				const $tab = $(e.currentTarget);
				const tabId = $tab.data('tab');

				this.log('Tab clicked', { tabId });
				this.switchTab(tabId);
			});
		}

		switchTab(tabId) {
			this.log('Switching to tab', { tabId });

			// Update navigation
			$('.nav-tab').removeClass('nav-tab-active');
			$(`.watso-tab[data-tab="${tabId}"]`).addClass('nav-tab-active');

			// Hide all panels
			$('.watso-tab-panel').removeClass('active').hide();

			// Show selected panel
			$(`#tab-${tabId}`).addClass('active').show();

			this.currentTab = tabId;
			this.log('Tab switched successfully', { currentTab: this.currentTab });
		}

		initColorPickers() {
			this.log('Initializing color pickers');

			$('.watso-color-picker').wpColorPicker({
													   change: (event, ui) => {
														   this.log('Color picker changed', {
															   field: $(event.target).attr('name'),
															   color: ui.color.toString()
														   });
														   this.rerenderPreview();
													   },
													   clear: () => {
														   this.log('Color picker cleared');
														   this.rerenderPreview();
													   }
												   });
		}

		initMediaUploaders() {
			this.log('Initializing media uploaders');

			// Custom icon uploader
			$(document).on('click', '.watso-upload-icon', (e) => {
				e.preventDefault();
				this.log('Upload icon clicked');
				this.openMediaUploader('custom_icon');
			});

			// Remove icon buttons
			$(document).on('click', '.watso-remove-icon', (e) => {
				e.preventDefault();
				this.log('Remove icon clicked');
				this.removeIcon('custom_icon');
			});

			// Reset buttons
			$(document).on('click', '.watso-reset-icon', (e) => {
				e.preventDefault();
				this.log('Reset icon clicked');
				this.resetIcon();
			});

			$(document).on('click', '.watso-reset-color', (e) => {
				e.preventDefault();
				this.log('Reset color clicked');
				this.resetColor();
			});

			// New color reset buttons
			$(document).on('click', '.watso-reset-description-color', (e) => {
				e.preventDefault();
				this.log('Reset description color clicked');
				$('input[name="watso_settings[short_description_color]"]').wpColorPicker('color', '#888888');
				this.rerenderPreview();
			});

			$(document).on('click', '.watso-reset-status-color', (e) => {
				e.preventDefault();
				this.log('Reset status color clicked');
				$('input[name="watso_settings[status_text_color]"]').wpColorPicker('color', '#4CAF50');
				this.rerenderPreview();
			});

			$(document).on('click', '.watso-reset-department-color', (e) => {
				e.preventDefault();
				this.log('Reset department color clicked');
				$('input[name="watso_settings[department_color]"]').wpColorPicker('color', '#666666');
				this.rerenderPreview();
			});
		}

		openMediaUploader(type) {
			this.log('Opening media uploader', { type });

			const frame = wp.media({
									   title: 'Select Icon',
									   button: {
										   text: 'Use Icon'
									   },
									   multiple: false,
									   library: {
										   type: 'image'
									   }
								   });

			frame.on('select', () => {
				const attachment = frame.state().get('selection').first().toJSON();
				this.log('Media selected', { url: attachment.url, id: attachment.id });
				this.setIcon(type, attachment.url);
			});

			frame.open();
		}

		setIcon(type, url) {
			this.log('Setting icon', { type, url });

			const urlField = $(`.watso-${type.replace('_', '-')}-url`);
			const preview = $(`.watso-${type.replace('_', '-')}-preview`);
			const removeBtn = $(`.watso-remove-${type.replace('_', '-')}-icon`);

			urlField.val(url);
			preview.find('img').attr('src', url);
			preview.show();
			removeBtn.show();

			this.log('Icon set successfully');
			this.rerenderPreview();
		}

		removeIcon(type) {
			this.log('Removing icon', { type });

			const urlField = $(`.watso-${type.replace('_', '-')}-url`);
			const preview = $(`.watso-${type.replace('_', '-')}-preview`);
			const removeBtn = $(`.watso-remove-${type.replace('_', '-')}-icon`);

			urlField.val('');
			preview.hide();
			removeBtn.hide();

			this.log('Icon removed successfully');
			this.rerenderPreview();
		}

		resetIcon() {
			this.log('Resetting icon to default');
			this.removeIcon('custom_icon');
			this.rerenderPreview();
		}

		resetColor() {
			this.log('Resetting button color to default');
			$('input[name="watso_settings[button_color]"]').wpColorPicker('color', '#119849');
			this.rerenderPreview();
		}

		initDynamicFields() {
			this.log('Initializing dynamic fields');

			// Add number button
			$(document).on('click', '.watso-add-number', (e) => {
				e.preventDefault();
				this.log('Add number clicked');
				this.addNumber();
			});

			// Remove number buttons
			$(document).on('click', '.watso-remove-number', (e) => {
				e.preventDefault();
				this.log('Remove number clicked');
				this.removeNumber($(e.target).closest('.watso-number-card'));
			});

			// Add holiday button
			$(document).on('click', '.watso-add-holiday', (e) => {
				e.preventDefault();
				this.log('Add holiday clicked');
				this.addHoliday();
			});

			// Remove holiday buttons
			$(document).on('click', '.watso-remove-holiday', (e) => {
				e.preventDefault();
				this.log('Remove holiday clicked');
				this.removeHoliday($(e.target).closest('.watso-holiday-card'));
			});
		}

		addNumber() {
			const container = $('.watso-numbers-container');
			const index = container.find('.watso-number-card').length;

			this.log('Adding new number', { index });

			// Number card template with new fields
			const template = `
                <div class="watso-number-card">
                    <div class="watso-number-header">
                        <h4>Contact #${index + 1}</h4>
                        <label class="watso-toggle watso-toggle-small">
                            <input type="checkbox" name="watso_settings[numbers][${index}][active]" value="1" checked>
                            <span class="watso-toggle-slider"></span>
                        </label>
                    </div>
                    <div class="watso-number-fields">
                        <div class="watso-form-field">
                            <label>Phone Number</label>
                            <input type="text" name="watso_settings[numbers][${index}][number]" value="" placeholder="e.g: +905551234567" class="watso-input">
                        </div>
                        <div class="watso-form-field">
                            <label>Title</label>
                            <input type="text" name="watso_settings[numbers][${index}][title]" value="" placeholder="e.g: John Smith" class="watso-input">
                        </div>
                        <div class="watso-form-field">
                            <label>Department</label>
                            <input type="text" name="watso_settings[numbers][${index}][department]" value="" placeholder="e.g: Technical Support" class="watso-input">
                        </div>
                        <div class="watso-form-field">
                            <label>Status Text</label>
                            <input type="text" name="watso_settings[numbers][${index}][status_text]" value="" placeholder="e.g: Online, Busy, In Meeting" class="watso-input">
                            <p class="watso-field-description">If left blank, status will not be displayed</p>
                        </div>
                        <div class="watso-form-field watso-field-full">
                            <label>Short Description</label>
                            <input type="text" name="watso_settings[numbers][${index}][short_description]" value="" placeholder="e.g: Available Mon-Fri 9AM-6PM" class="watso-input">
                            <p class="watso-field-description">Displayed in small font. You can write working hours, availability, or any brief note.</p>
                        </div>
                    </div>
                    <div class="watso-number-actions">
                        <button type="button" class="watso-btn watso-btn-danger watso-btn-small watso-remove-number">Delete</button>
                    </div>
                </div>
            `;

			container.append(template);
			this.updateRemoveButtons();
			this.log('Number added successfully');
			this.rerenderPreview();
		}

		removeNumber($row) {
			const currentCount = $('.watso-number-card').length;
			this.log('Attempting to remove number', { currentCount });

			if (currentCount > 1) {
				$row.remove();
				this.updateRemoveButtons();
				this.log('Number removed successfully');
				this.rerenderPreview();
			} else {
				this.log('Cannot remove last number');
				alert('At least one number is required.');
			}
		}

		updateRemoveButtons() {
			const $removeButtons = $('.watso-remove-number');
			const count = $('.watso-number-card').length;

			this.log('Updating remove buttons', { count });

			if (count <= 1) {
				$removeButtons.prop('disabled', true);
			} else {
				$removeButtons.prop('disabled', false);
			}
		}

		addHoliday() {
			const container = $('.watso-holidays-container');
			const index = container.find('.watso-holiday-card').length;

			this.log('Adding holiday', { index });

			const template = `
                <div class="watso-holiday-card">
                    <div class="watso-holiday-fields">
                        <input type="date" name="watso_settings[holidays][${index}][date]" value="" class="watso-input">
                        <input type="text" name="watso_settings[holidays][${index}][title]" value="" placeholder="Holiday Name" class="watso-input">
                    </div>
                    <button type="button" class="watso-btn watso-btn-danger watso-btn-small watso-remove-holiday">Delete</button>
                </div>
            `;

			container.append(template);
			this.log('Holiday added successfully');
		}

		removeHoliday($row) {
			this.log('Removing holiday');
			$row.remove();
		}

		initToggleFields() {
			this.log('Initializing toggle fields');

			// Schedule toggle
			$(document).on('change', '#watso_schedule_enabled', (e) => {
				const isChecked = $(e.target).is(':checked');
				this.log('Schedule toggle changed', { enabled: isChecked });

				if (isChecked) {
					$('.watso-schedule-settings').slideDown();
				} else {
					$('.watso-schedule-settings').slideUp();
				}
			});

			// UTM toggle
			$(document).on('change', 'input[name="watso_settings[utm_enabled]"]', (e) => {
				const isChecked = $(e.target).is(':checked');
				this.log('UTM toggle changed', { enabled: isChecked });

				if (isChecked) {
					$('.watso-utm-fields').slideDown();
				} else {
					$('.watso-utm-fields').slideUp();
				}
			});

			// Source URL toggle
			$(document).on('change', '#watso_show_source_url', (e) => {
				const isChecked = $(e.target).is(':checked');
				this.log('Source URL toggle changed', { enabled: isChecked });

				if (isChecked) {
					$('.watso-source-message-field').slideDown();
				} else {
					$('.watso-source-message-field').slideUp();
				}
			});

			// Debug mode toggle - SPECIAL HANDLING
			$(document).on('change', 'input[name="watso_settings[debug_mode]"]', (e) => {
				const wasEnabled = this.debugMode;
				this.debugMode = $(e.target).is(':checked');

				if (this.debugMode && !wasEnabled) {
					console.log('[Watso Admin] Debug mode enabled!');
				} else if (!this.debugMode && wasEnabled) {
					console.log('[Watso Admin] Debug mode disabled!');
				}

				this.log('Debug mode toggled', {
					previous: wasEnabled,
					current: this.debugMode
				});
			});

			// Set initial states on page load
			$(document).ready(() => {
				this.log('Setting initial toggle states');

				// UTM fields initial state
				const $utmCheckbox = $('input[name="watso_settings[utm_enabled]"]');
				if ($utmCheckbox.length > 0 && !$utmCheckbox.is(':checked')) {
					$('.watso-utm-fields').hide();
				}

				// Source URL field initial state
				const $sourceCheckbox = $('#watso_show_source_url');
				if ($sourceCheckbox.length > 0 && !$sourceCheckbox.is(':checked')) {
					$('.watso-source-message-field').hide();
				}

				// Schedule settings initial state
				const $scheduleCheckbox = $('#watso_schedule_enabled');
				if ($scheduleCheckbox.length > 0 && !$scheduleCheckbox.is(':checked')) {
					$('.watso-schedule-settings').hide();
				}
			});

			// Custom toggle switches
			$(document).on('change', '.watso-toggle input', function() {
				const fieldName = $(this).attr('name');
				const isChecked = this.checked;

				// Use closure to access the class instance
				const self = WatsoAdmin.getInstance();
				if (self) {
					self.log('Toggle changed', { field: fieldName, checked: isChecked });
				}

				$(this).closest('.watso-toggle').toggleClass('active', this.checked);

				// Update preview
				if (self) {
					self.debouncedPreviewUpdate(100);
				}
			});

			// Initialize toggle states
			$('.watso-toggle input:checked').closest('.watso-toggle').addClass('active');
		}

		bindEvents() {
			this.log('Binding events');

			// Range slider updates
			$(document).on('input', '.watso-range-slider', (e) => {
				const $slider = $(e.currentTarget);
				const value = $slider.val();
				const fieldName = $slider.attr('name');

				this.log('Range slider changed', { field: fieldName, value });

				// Update range value display
				$slider.next('.watso-range-value').text(value + 'px');

				// Update preview
				this.debouncedPreviewUpdate(50);
			});

			// Form validation
			$('#watso-settings-form').submit((e) => {
				this.log('Form submission started');

				if (!this.validateForm()) {
					this.error('Form validation failed');
					e.preventDefault();
					return false;
				}

				this.log('Form validation passed');
			});
		}

		validateForm() {
			this.log('Validating form');

			let isValid = true;
			const errors = [];

			// Validate phone numbers
			$('.watso-number-card').each(function(index) {
				const number = $(this).find('input[name*="[number]"]').val();
				const title = $(this).find('input[name*="[title]"]').val();

				if (number && !title) {
					errors.push('Please enter a title for phone number: ' + number);
					isValid = false;
				}

				if (number && !/^[+]?[0-9\s\-\(\)]{10,}$/.test(number.replace(/\s/g, ''))) {
					errors.push('Invalid phone number format: ' + number);
					isValid = false;
				}
			});

			if (!isValid) {
				this.error('Form validation errors', errors);
				alert('Please fix the following errors:\n\n' + errors.join('\n'));
			} else {
				this.log('Form validation successful');
			}

			return isValid;
		}

		// Preview system
		initLivePreview() {
			this.log('Initializing live preview');

			// Make preview update timeout global
			this.previewUpdateTimeout = null;
			this.isUpdating = false;

			// Initial render
			setTimeout(() => {
				this.log('Starting initial preview render');
				this.rerenderPreview();
			}, 1000);

			// Listen to form changes
			$(document).on('input change', '#watso-settings-form input, #watso-settings-form select, #watso-settings-form textarea', (e) => {
				const fieldName = $(e.target).attr('name');
				const value = $(e.target).val();

				this.log('Form field changed', { field: fieldName, value });
				this.debouncedPreviewUpdate();
			});

			// Color picker special listener
			$(document).on('wpcolorpickerchange', '.watso-color-picker', () => {
				this.log('Color picker changed via special event');
				this.debouncedPreviewUpdate(200);
			});

			// Range slider special listener
			$(document).on('input', '.watso-range-slider', () => {
				this.log('Range slider changed via special event');
				this.debouncedPreviewUpdate(50);
			});
		}

		// Debounced preview update
		debouncedPreviewUpdate(delay = 300) {
			if (this.isUpdating) {
				this.log('Preview update skipped - already updating');
				return;
			}

			this.log('Debounced preview update scheduled', { delay });

			clearTimeout(this.previewUpdateTimeout);

			this.previewUpdateTimeout = setTimeout(() => {
				this.log('Executing debounced preview update');
				this.rerenderPreview();
			}, delay);
		}

		isDebugEnabled() {
			// Always return current state
			return this.debugMode;
		}

		rerenderPreview() {
			if (this.isUpdating) {
				this.log('Preview rerender skipped - already updating');
				return;
			}

			this.isUpdating = true;
			this.log('Starting preview rerender');

			// Remove all old previews
			$('#watso-preview-button-container, .watso-preview-container, [id^="watso-preview"]').remove();
			$(document).off('click.watso-preview');

			// Collect form data
			const formData = this.getFormData();
			this.log('Form data collected', { formData });

			// AJAX URL check
			if (!watso_ajax || !watso_ajax.ajax_url) {
				this.error('AJAX URL not found', { watso_ajax });
				this.isUpdating = false;
				return;
			}

			// AJAX request
			const requestData = {
				action: 'watso_render_preview',
				nonce: watso_ajax.nonce,
				settings: formData
			};

			this.log('Sending AJAX request', { requestData });

			$.ajax({
					   url: watso_ajax.ajax_url,
					   type: 'POST',
					   data: requestData,
					   dataType: 'json',
					   timeout: 10000,
					   beforeSend: () => {
						   this.log('AJAX request started');
					   },
					   success: (response) => {
						   this.log('AJAX request successful', { response });

						   if (response && response.success) {
							   $('body').append(response.data.html);
							   this.initPreviewDropdown();

							   this.log('Preview updated successfully');
							   if (response.data.debug) {
								   this.log('Server debug info', response.data.debug);
							   }
						   } else {
							   this.error('Preview update failed', response);

							   let errorMsg = 'Unknown error';
							   if (response && response.data && response.data.message) {
								   errorMsg = response.data.message;
							   }
							   $('body').append('<div id="watso-preview-button-container" style="position:fixed;bottom:20px;right:20px;background:red;color:white;padding:10px;border-radius:5px;z-index:9999;max-width:300px;">Preview Error: ' + errorMsg + '</div>');
						   }

						   this.isUpdating = false;
					   },
					   error: (xhr, status, error) => {
						   this.error('AJAX error', {
							   status: xhr.status,
							   statusText: xhr.statusText,
							   responseText: xhr.responseText,
							   error: error
						   });

						   let errorMsg = 'AJAX Error: ' + error;
						   if (xhr.status === 400) {
							   errorMsg = 'Bad Request - Server rejecting data';
						   } else if (xhr.status === 403) {
							   errorMsg = 'Permission denied';
						   } else if (xhr.status === 500) {
							   errorMsg = 'Server error';
						   }

						   $('body').append('<div id="watso-preview-button-container" style="position:fixed;bottom:20px;right:20px;background:red;color:white;padding:10px;border-radius:5px;z-index:9999;max-width:300px;">' + errorMsg + (this.debugMode ? '<br><small>Check console for details</small>' : '') + '</div>');

						   this.isUpdating = false;
					   }
				   });
		}

		getFormData() {
			const settings = {};

			this.log('Starting getFormData');

			// Collect all form fields
			$('#watso-settings-form').find('input, select, textarea').each(function() {
				const $field = $(this);
				const name = $field.attr('name');

				if (!name || !name.startsWith('watso_settings[')) return;

				// Clean field name
				let fieldName = name.replace('watso_settings[', '').replace(/\]/g, '');

				// Handle array fields
				if (fieldName.includes('[')) {
					const parts = fieldName.split('[');
					const mainField = parts[0];

					if (mainField === 'numbers' || mainField === 'holidays' || mainField === 'schedule_hours') {
						if (!settings[mainField]) settings[mainField] = {};

						if (parts.length === 3) {
							const index = parts[1];
							const subField = parts[2];

							if (!settings[mainField][index]) settings[mainField][index] = {};

							if ($field.attr('type') === 'checkbox') {
								settings[mainField][index][subField] = $field.is(':checked');
							} else {
								settings[mainField][index][subField] = $field.val();
							}
						}
					}
				} else {
					// Normal fields
					if ($field.attr('type') === 'checkbox') {
						const isChecked = $field.is(':checked');
						settings[fieldName] = isChecked;
					} else if (fieldName === 'button_radius') {
						const rawValue = $field.val();

						if (rawValue === '' || rawValue === null || rawValue === undefined) {
							settings[fieldName] = 15;
						} else {
							const numValue = Number(rawValue);
							if (isNaN(numValue)) {
								settings[fieldName] = 15;
							} else {
								settings[fieldName] = Math.max(0, Math.min(30, numValue));
							}
						}
					} else {
						settings[fieldName] = $field.val();
					}
				}
			});

			this.log('Form data collected successfully', { settings });

			// Fix numbers array
			if (settings.numbers && typeof settings.numbers === 'object') {
				const numbersArray = [];
				Object.keys(settings.numbers).forEach(key => {
					if (settings.numbers[key] && typeof settings.numbers[key] === 'object') {
						numbersArray.push(settings.numbers[key]);
					}
				});
				settings.numbers = numbersArray;
				this.log('Numbers array fixed', { count: numbersArray.length });
			}

			return settings;
		}

		// Preview dropdown functionality
		initPreviewDropdown() {
			this.log('Initializing preview dropdown');

			$(document).off('click.watso-preview');
			$('.watso-preview-container').off('click');

			const $previewContainer = $('.watso-preview-container');
			const $button = $previewContainer.find('.watso-chat-button');
			const $dropdown = $previewContainer.find('.watso-dropdown-menu');
			const $closeBtn = $dropdown.find('.watso-close-dropdown');

			// Click button to open/close dropdown
			$button.off('click').on('click', (e) => {
				e.preventDefault();
				this.log('Preview button clicked');
				$dropdown.toggleClass('watso-show');
			});

			// Click close button to close dropdown
			$closeBtn.off('click').on('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				this.log('Preview close button clicked');
				$dropdown.removeClass('watso-show');
			});

			// Click outside to close dropdown
			$(document).off('click.watso-preview').on('click.watso-preview', (e) => {
				if (!$previewContainer.is(e.target) && $previewContainer.has(e.target).length === 0) {
					this.log('Clicked outside preview, closing dropdown');
					$dropdown.removeClass('watso-show');
				}
			});

			// Click dropdown items to close and do nothing
			$dropdown.find('.watso-dropdown-item').off('click').on('click', (e) => {
				e.preventDefault();
				this.log('Preview dropdown item clicked');
				$dropdown.removeClass('watso-show');
				return false;
			});
		}

		static getInstance() {
			if (!window.watsoAdmin) {
				window.watsoAdmin = new WatsoAdmin();
			}
			return window.watsoAdmin;
		}
	}

	// Initialize admin
	$(function() {
		const watsoAdmin = WatsoAdmin.getInstance();
		window.WatsoAdmin = WatsoAdmin;
	});

})(jQuery);

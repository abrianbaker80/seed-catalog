(function($) {
    'use strict';

    // Debug function for development
    function debug(message, obj) {
        if (typeof console !== 'undefined' && seedCatalogAdmin.debug) {
            if (obj) {
                console.log('Seed Catalog Admin:', message, obj);
            } else {
                console.log('Seed Catalog Admin:', message);
            }
        }
    }

    $(document).ready(function() {
        const aiButton = $('#seed-catalog-ai-search');
        const aiLoading = $('.seed-catalog-loading');
        const aiResults = $('#seed-catalog-ai-results');
        const aiContent = $('#seed-catalog-ai-suggestions');
        const imageRecognition = $('#seed-catalog-image-recognition');
        const imageResults = $('#seed-catalog-image-results');
        const imageSuggestions = $('#seed-catalog-image-suggestions');
        const apiKeyField = $('#seed_catalog_gemini_api_key');

        debug('Admin JS initialized');

        // Initialize all functionality
        initAPIKeyHandling();
        initAIFeatures();
        initImageRecognition();
        initDebugPanel();

        function initAPIKeyHandling() {
            if (apiKeyField.length) {
                const toggleButton = $('<button type="button" class="seed-catalog-api-key-toggle"><span class="dashicons dashicons-visibility"></span></button>');
                
                apiKeyField.after(toggleButton);
                apiKeyField.attr('type', 'password');
                
                toggleButton.on('click', function() {
                    const isPassword = apiKeyField.attr('type') === 'password';
                    apiKeyField.attr('type', isPassword ? 'text' : 'password');
                    toggleButton.find('.dashicons')
                        .toggleClass('dashicons-visibility')
                        .toggleClass('dashicons-hidden');
                });
            }
        }

        function initAIFeatures() {
            aiButton.on('click', function(e) {
                e.preventDefault();
                const seedName = $('#seed_name').val().trim();
                const seedVariety = $('#seed_variety').val().trim();
                
                if (!seedName && !seedVariety) {
                    showMessage(seedCatalogAdmin.strings.noSeedName, 'error');
                    return;
                }
                
                getAISuggestions(seedName, seedVariety);
            });
        }

        function initImageRecognition() {
            imageRecognition.on('click', function(e) {
                e.preventDefault();
                const fileInput = $('#seed_image_upload')[0];
                
                if (!fileInput.files || !fileInput.files[0]) {
                    showMessage(seedCatalogAdmin.strings.noImageSelected, 'error');
                    return;
                }
                
                processImageRecognition(fileInput.files[0]);
            });
        }

        function getAISuggestions(name, variety) {
            showLoading();
            
            $.ajax({
                url: seedCatalogAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_seed_details',
                    nonce: seedCatalogAdmin.geminiNonce,
                    name: name,
                    variety: variety
                },
                success: function(response) {
                    hideLoading();
                    if (response.success && response.data) {
                        displayAISuggestions(response.data);
                    } else {
                        showMessage(seedCatalogAdmin.strings.noResults, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    showMessage(seedCatalogAdmin.strings.errorOccurred + ' ' + error, 'error');
                    debug('AI Search Error:', { xhr, status, error });
                }
            });
        }

        function processImageRecognition(file) {
            const formData = new FormData();
            formData.append('action', 'seed_catalog_gemini_image_recognition');
            formData.append('nonce', seedCatalogAdmin.geminiNonce);
            formData.append('image', file);
            
            showLoading();
            
            $.ajax({
                url: seedCatalogAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    hideLoading();
                    if (response.success && response.data) {
                        displayImageResults(response.data);
                    } else {
                        showMessage(response.data.message || seedCatalogAdmin.strings.noResults, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    hideLoading();
                    showMessage(seedCatalogAdmin.strings.errorOccurred + ' ' + error, 'error');
                    debug('Image Recognition Error:', { xhr, status, error });
                }
            });
        }

        function displayAISuggestions(data) {
            aiContent.empty();
            
            Object.entries(data).forEach(([key, value]) => {
                if (!value) return;
                
                const suggestion = $('<div class="seed-catalog-ai-suggestion"></div>')
                    .append(`<h4>${seedCatalogAdmin.strings[key] || key}</h4>`)
                    .append(`<div class="suggestion-content">${value}</div>`)
                    .append(`<div class="seed-catalog-suggestion-actions">
                        <button type="button" class="button apply-suggestion" data-field="${key}">
                            ${seedCatalogAdmin.strings.apply}
                        </button>
                    </div>`);
                
                aiContent.append(suggestion);
            });
            
            aiResults.removeClass('hidden');
        }

        function displayImageResults(data) {
            imageSuggestions.empty();
            
            if (data.identification) {
                const result = $('<div class="seed-catalog-ai-suggestion"></div>')
                    .append(`<h4>${seedCatalogAdmin.strings.identifiedAs}</h4>`)
                    .append(`<div class="suggestion-content">${data.identification}</div>`)
                    .append(`<div class="seed-catalog-suggestion-actions">
                        <button type="button" class="button apply-identification">
                            ${seedCatalogAdmin.strings.apply}
                        </button>
                    </div>`);
                
                imageSuggestions.append(result);
            }
            
            imageResults.removeClass('hidden');
        }

        function initDebugPanel() {
            if (!seedCatalogAdmin.debug) return;

            const content = $('<div class="debug-content"></div>');
            const aiActions = $('<div class="seed-catalog-ai-actions"></div>')
                .append(`<button type="button" class="button button-primary apply-all-suggestions">
                    ${seedCatalogAdmin.strings.applyAllInfo}
                </button>`);

            content.append(aiActions);
            aiContent.html(content);

            // Add debug panel to page
            const panel = $('<div id="seed-catalog-debug-panel"></div>')
                .append('<div class="debug-panel-header"><h3>Debug Panel</h3><span class="debug-panel-close">&times;</span></div>')
                .append('<div class="debug-panel-content"></div>');

            $('body').append(panel);

            // Make panel draggable
            if ($.fn.draggable) {
                panel.draggable({
                    handle: '.debug-panel-header',
                    containment: 'window'
                });
            }

            // Toggle panel visibility
            $('.debug-panel-close').on('click', function() {
                panel.toggleClass('visible');
            });

            // Show panel by default
            panel.addClass('visible');

            // Keyboard shortcut (Ctrl+Shift+D)
            $(document).on('keydown', function(e) {
                if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                    panel.toggleClass('visible');
                }
            });

            debug('Debug panel initialized');
        }

        // Handle applying suggestions
        $(document).on('click', '.apply-suggestion', function() {
            const field = $(this).data('field');
            const value = $(this).closest('.seed-catalog-ai-suggestion')
                .find('.suggestion-content').text();
            
            if (confirm(seedCatalogAdmin.strings.confirmApply)) {
                $(`#${field}`).val(value);
            }
        });

        // Handle applying image identification
        $(document).on('click', '.apply-identification', function() {
            const value = $(this).closest('.seed-catalog-ai-suggestion')
                .find('.suggestion-content').text();
            
            if (confirm(seedCatalogAdmin.strings.confirmApply)) {
                $('#seed_name').val(value);
            }
        });

        // Utility functions
        function showLoading() {
            aiLoading.show();
        }
        
        function hideLoading() {
            aiLoading.hide();
        }
        
        function showMessage(message, type = 'info') {
            const messageDiv = $('<div class="seed-catalog-message"></div>')
                .addClass(type)
                .text(message)
                .hide();
            
            aiResults.before(messageDiv);
            messageDiv.fadeIn();
            
            setTimeout(() => messageDiv.fadeOut(() => messageDiv.remove()), 5000);
        }
        
        // Debug log wrapper
        window.seedCatalogDebug = {
            log: debug,
            showMessage: showMessage,
            showLoading: showLoading,
            hideLoading: hideLoading
        };
    });
})(jQuery);
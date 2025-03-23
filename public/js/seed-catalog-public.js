(function($) {
    'use strict';

    // Debug function to help troubleshoot
    function debug(message, obj) {
        if (typeof console !== 'undefined') {
            if (obj) {
                console.log("SEED DEBUG: " + message, obj);
            } else {
                console.log("SEED DEBUG: " + message);
            }
        }
    }

    // When the DOM is fully loaded
    $(function() {
        debug("Seed Catalog JS initialized");

        const searchForm = $('.seed-catalog-search-form');
        const searchResults = $('.seed-catalog-search-results');
        let searchTimer;

        // Handle search form submissions
        searchForm.on('submit', function(e) {
            e.preventDefault();
            performSearch();
        });

        // Handle search input with debouncing
        searchForm.find('input[type="search"]').on('input', function() {
            clearTimeout(searchTimer);
            if ($(this).val().length >= 2) {
                searchTimer = setTimeout(performSearch, 500);
            } else {
                searchResults.empty();
            }
        });

        // Handle filter changes
        $('.seed-catalog-filter select, .seed-catalog-filter input[type="checkbox"]').on('change', function() {
            performSearch();
        });

        // Reset filters
        $('.seed-catalog-reset-filters').on('click', function(e) {
            e.preventDefault();
            $('.seed-catalog-filter select').val('');
            $('.seed-catalog-filter input[type="checkbox"]').prop('checked', false);
            performSearch();
        });

        // Mobile filter toggle
        $('.seed-catalog-filter-toggle').on('click', function(e) {
            e.preventDefault();
            $('.seed-catalog-filters-container').toggleClass('filters-visible');
            $(this).toggleClass('active');
            
            if ($(this).hasClass('active')) {
                $(this).text('Hide Filters');
            } else {
                $(this).text('Show Filters');
            }
        });

        // Perform AJAX search
        function performSearch() {
            const searchTerm = searchForm.find('input[type="search"]').val();
            const filters = collectFilters();
            
            // Show loading indicator
            searchResults.html('<div class="seed-catalog-loading"><span class="spinner"></span> Searching...</div>');

            // Make AJAX request
            $.ajax({
                url: seedCatalogPublic.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'seed_catalog_search',
                    nonce: seedCatalogPublic.nonce,
                    search: searchTerm,
                    filters: filters
                },
                success: function(response) {
                    if (response.success && response.data.results) {
                        displaySearchResults(response.data.results);
                        updateFilterCounts(response.data.filter_counts);
                    } else {
                        searchResults.html('<p class="no-results">No seeds found matching your search.</p>');
                    }
                },
                error: function() {
                    searchResults.html('<p class="error">An error occurred while searching.</p>');
                }
            });
        }

        // Collect all filter values
        function collectFilters() {
            const filters = {};
            
            // Get category filters
            const categoryFilters = [];
            $('.seed-catalog-filter-categories input:checked').each(function() {
                categoryFilters.push($(this).val());
            });
            if (categoryFilters.length) {
                filters.categories = categoryFilters;
            }
            
            // Get dropdown filters
            $('.seed-catalog-filter select').each(function() {
                const filterName = $(this).data('filter-name');
                const filterValue = $(this).val();
                
                if (filterValue) {
                    filters[filterName] = filterValue;
                }
            });
            
            // Get checkbox filters for additional attributes
            $('.seed-catalog-filter-attributes').each(function() {
                const attributeName = $(this).data('attribute-name');
                const selectedValues = [];
                
                $(this).find('input:checked').each(function() {
                    selectedValues.push($(this).val());
                });
                
                if (selectedValues.length) {
                    filters[attributeName] = selectedValues;
                }
            });
            
            return filters;
        }

        // Update filter counts when results change
        function updateFilterCounts(filterCounts) {
            if (!filterCounts) return;
            
            // Update category counts
            if (filterCounts.categories) {
                Object.keys(filterCounts.categories).forEach(function(categoryId) {
                    const count = filterCounts.categories[categoryId];
                    const countElement = $('.seed-catalog-filter-categories label[for="category-' + categoryId + '"] .count');
                    
                    if (countElement.length) {
                        countElement.text('(' + count + ')');
                    }
                });
            }
            
            // Update other attribute counts
            if (filterCounts.attributes) {
                Object.keys(filterCounts.attributes).forEach(function(attrName) {
                    const attrCounts = filterCounts.attributes[attrName];
                    
                    Object.keys(attrCounts).forEach(function(termId) {
                        const count = attrCounts[termId];
                        const countElement = $('.seed-catalog-filter-attributes[data-attribute-name="' + attrName + '"] label[for="' + attrName + '-' + termId + '"] .count');
                        
                        if (countElement.length) {
                            countElement.text('(' + count + ')');
                        }
                    });
                });
            }
        }

        // Display search results
        function displaySearchResults(results) {
            if (!results.length) {
                searchResults.html('<p class="no-results">No seeds found matching your search.</p>');
                return;
            }

            const resultsList = $('<div class="seed-catalog-results-grid"></div>');

            results.forEach(function(seed) {
                const resultItem = $('<div class="seed-catalog-result-item"></div>');
                
                // Add image if available
                if (seed.image) {
                    resultItem.append(`
                        <div class="seed-catalog-result-image">
                            <a href="${seed.permalink}">
                                <img src="${seed.image}" alt="${seed.title}">
                            </a>
                        </div>
                    `);
                }

                // Add seed details
                const details = $('<div class="seed-catalog-result-details"></div>');
                details.append(`<h3><a href="${seed.permalink}">${seed.title}</a></h3>`);
                
                if (seed.seed_name) {
                    details.append(`<p class="seed-name">${seed.seed_name}</p>`);
                }
                
                if (seed.seed_variety) {
                    details.append(`<p class="seed-variety">${seed.seed_variety}</p>`);
                }
                
                // Add categories
                if (seed.categories && seed.categories.length) {
                    const categories = $('<div class="seed-catalog-categories"></div>');
                    categories.append('<span>Categories: </span>');
                    
                    seed.categories.forEach(function(category, index) {
                        categories.append(`
                            <a href="${category.link}">${category.name}</a>${index < seed.categories.length - 1 ? ', ' : ''}
                        `);
                    });
                    
                    details.append(categories);
                }
                
                // Add other attributes
                if (seed.attributes) {
                    Object.keys(seed.attributes).forEach(function(attrKey) {
                        const attrVal = seed.attributes[attrKey];
                        if (attrVal) {
                            details.append(`<p class="seed-attribute seed-${attrKey}">${attrKey}: ${attrVal}</p>`);
                        }
                    });
                }

                resultItem.append(details);
                resultsList.append(resultItem);
            });

            searchResults.html(resultsList);
            
            // Implement infinite scrolling if enabled
            if (seedCatalogPublic.infiniteScroll && results.length >= seedCatalogPublic.postsPerPage) {
                initializeInfiniteScroll();
            }
        }
        
        // Initialize infinite scrolling
        function initializeInfiniteScroll() {
            let page = 1;
            let loading = false;
            const loadMoreThreshold = 200;
            
            $(window).on('scroll', function() {
                if (loading) return;
                
                const scrollBottom = $(window).scrollTop() + $(window).height();
                const threshold = $(document).height() - loadMoreThreshold;
                
                if (scrollBottom >= threshold) {
                    loading = true;
                    page++;
                    
                    const searchTerm = searchForm.find('input[type="search"]').val();
                    const filters = collectFilters();
                    
                    searchResults.append('<div class="seed-catalog-loading-more"><span class="spinner"></span> Loading more...</div>');
                    
                    $.ajax({
                        url: seedCatalogPublic.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'seed_catalog_search',
                            nonce: seedCatalogPublic.nonce,
                            search: searchTerm,
                            filters: filters,
                            page: page
                        },
                        success: function(response) {
                            $('.seed-catalog-loading-more').remove();
                            
                            if (response.success && response.data.results && response.data.results.length) {
                                appendResults(response.data.results);
                            }
                            
                            loading = false;
                            
                            // If less results than per_page, we've reached the end
                            if (!response.success || !response.data.results || response.data.results.length < seedCatalogPublic.postsPerPage) {
                                $(window).off('scroll');
                                searchResults.append('<p class="no-more-results">No more seeds to display</p>');
                            }
                        },
                        error: function() {
                            $('.seed-catalog-loading-more').remove();
                            loading = false;
                        }
                    });
                }
            });
        }
        
        // Append results to existing result set (for infinite scrolling)
        function appendResults(results) {
            const resultsList = $('.seed-catalog-results-grid');
            
            results.forEach(function(seed) {
                const resultItem = $('<div class="seed-catalog-result-item"></div>');
                
                // Add image if available
                if (seed.image) {
                    resultItem.append(`
                        <div class="seed-catalog-result-image">
                            <a href="${seed.permalink}">
                                <img src="${seed.image}" alt="${seed.title}">
                            </a>
                        </div>
                    `);
                }

                // Add seed details
                const details = $('<div class="seed-catalog-result-details"></div>');
                details.append(`<h3><a href="${seed.permalink}">${seed.title}</a></h3>`);
                
                if (seed.seed_name) {
                    details.append(`<p class="seed-name">${seed.seed_name}</p>`);
                }
                
                if (seed.seed_variety) {
                    details.append(`<p class="seed-variety">${seed.seed_variety}</p>`);
                }
                
                // Add categories
                if (seed.categories && seed.categories.length) {
                    const categories = $('<div class="seed-catalog-categories"></div>');
                    categories.append('<span>Categories: </span>');
                    
                    seed.categories.forEach(function(category, index) {
                        categories.append(`
                            <a href="${category.link}">${category.name}</a>${index < seed.categories.length - 1 ? ', ' : ''}
                        `);
                    });
                    
                    details.append(categories);
                }
                
                // Add other attributes
                if (seed.attributes) {
                    Object.keys(seed.attributes).forEach(function(attrKey) {
                        const attrVal = seed.attributes[attrKey];
                        if (attrVal) {
                            details.append(`<p class="seed-attribute seed-${attrKey}">${attrKey}: ${attrVal}</p>`);
                        }
                    });
                }

                resultItem.append(details);
                resultsList.append(resultItem);
            });
        }

        // Initialize category dropdown if present
        const categoryDropdown = $('.seed-catalog-category-dropdown');
        if (categoryDropdown.length) {
            categoryDropdown.on('change', function() {
                const selectedUrl = $(this).val();
                if (selectedUrl) {
                    window.location.href = selectedUrl;
                }
            });
        }
        
        // Initialize any filter controls on page load
        if ($('.seed-catalog-filters-container').length) {
            // If there's a search term in the URL
            const urlParams = new URLSearchParams(window.location.search);
            const searchParam = urlParams.get('search');
            if (searchParam) {
                searchForm.find('input[type="search"]').val(searchParam);
            }
            
            // Run initial search if there are any filters or search terms
            if (searchParam || $('.seed-catalog-filter select').val() || $('.seed-catalog-filter input:checked').length) {
                performSearch();
            }
        }

        // Handle AI variety search in seed submission form
        const seedNameInput = $('#seed_name');
        const varietyList = $('.seed-catalog-variety-list');
        const detailsSection = $('#seed-details-section');
        const aiSuggestButton = $('.seed-catalog-ai-suggest');
        let searchTimeout;

        // Show seed details section if we have values already
        if (seedNameInput.val() && $('#seed_variety').val()) {
            detailsSection.show();
        }

        // Show/hide the variety list when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.seed-catalog-input-group').length) {
                varietyList.hide();
            }
        });

        // Handle AI suggest button click
        if (aiSuggestButton.length > 0) {
            aiSuggestButton.on('click', function(e) {
                e.preventDefault();
                debug("AI suggest button clicked");
                
                const searchTerm = seedNameInput.val().trim();
                debug("Search term:", searchTerm);
                
                if (searchTerm.length < 2) {
                    showMessage('Please enter a seed name to search.', 'error');
                    return;
                }

                showMessage('Searching for varieties of ' + searchTerm + '...', 'info');
                
                // Call the correct action for Gemini AI search
                $.ajax({
                    url: seedCatalogPublic.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'process_gemini_search',
                        nonce: seedCatalogPublic.nonce,
                        query: searchTerm,
                        context: 'seed_variety_search'
                    },
                    success: function(response) {
                        debug("AJAX success response:", response);
                        
                        if (response.success && response.data) {
                            // Check if we can extract varieties from the response
                            try {
                                let varieties = [];
                                const data = response.data;
                                
                                // Handle different response formats
                                if (typeof data === 'string') {
                                    // Try to parse JSON from string response
                                    const jsonData = JSON.parse(data);
                                    if (jsonData.varieties) {
                                        varieties = jsonData.varieties;
                                    }
                                } else if (data.varieties) {
                                    varieties = data.varieties;
                                }
                                
                                if (varieties.length > 0) {
                                    displayVarieties(varieties, searchTerm);
                                } else {
                                    // Fall back to regular variety search if we can't extract varieties
                                    searchVarieties(searchTerm);
                                }
                            } catch (e) {
                                debug("Error parsing Gemini response:", e);
                                // Fall back to regular variety search
                                searchVarieties(searchTerm);
                            }
                        } else {
                            // Fall back to regular variety search if Gemini search fails
                            searchVarieties(searchTerm);
                        }
                    },
                    error: function(xhr, status, error) {
                        debug("AJAX error for Gemini search:", { status, error, xhr });
                        // Fall back to regular variety search
                        searchVarieties(searchTerm);
                    }
                });
            });
        }

        function searchVarieties(term) {
            debug("searchVarieties called with term:", term);
            
            // Show loading state in the variety list
            varietyList.html('<div class="seed-catalog-loading">Searching for varieties...</div>').show();

            // Make AJAX request to search for varieties
            $.ajax({
                url: seedCatalogPublic.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'search_seed_varieties',
                    nonce: seedCatalogPublic.nonce,
                    term: term
                },
                success: function(response) {
                    debug("AJAX success response:", response);
                    
                    if (response.success && response.data && response.data.varieties && response.data.varieties.length > 0) {
                        displayVarieties(response.data.varieties, term);
                    } else {
                        let errorMessage = "No varieties found for this seed. Please try a different search term or enter details manually.";
                        
                        // Check for specific error messages from the server
                        if (response.data && response.data.message) {
                            errorMessage = response.data.message;
                            debug("Error message from server:", errorMessage);
                        }
                        
                        // If we got varieties from a fallback but still have an error message
                        if (response.data && response.data.varieties && response.data.varieties.length > 0) {
                            // Use the varieties anyway, but show a warning
                            displayVarieties(response.data.varieties, term);
                            showMessage("Using limited variety information. Some details may be missing.", "warning");
                        } else {
                            showError(errorMessage);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    debug("AJAX error:", { status, error, xhr });
                    
                    let errorMessage = "Error connecting to the server. Please try again later.";
                    
                    // Try to get more specific error information
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMessage = xhr.responseJSON.data.message;
                    } else if (xhr.status === 403) {
                        errorMessage = "Permission denied. Please check if your Gemini API key is configured correctly in the plugin settings.";
                    } else if (xhr.status === 404) {
                        errorMessage = "API endpoint not found. Please check your WordPress permalinks and try resaving them.";
                    } else if (xhr.status === 0) {
                        errorMessage = "Network error. Please check your internet connection.";
                    }
                    
                    showError(errorMessage);
                }
            });
        }

        function displayVarieties(varieties, plantType) {
            debug("displayVarieties called with:", { varieties, plantType });
            
            if (!varieties || varieties.length === 0) {
                showError("No varieties found for this seed.");
                return;
            }

            const list = $('<div class="varieties-grid"></div>');
            
            // Add a header
            list.append('<div class="variety-header"><h3>Select a variety</h3><p>Click on any variety to get detailed growing information</p></div>');
            
            // Add each variety as a clickable item
            varieties.forEach(function(variety) {
                const item = $('<div class="variety-item"></div>')
                    .append($('<h4></h4>').text(variety.name))
                    .append($('<p></p>').text(variety.description || 'No description available'))
                    .on('click', function() {
                        selectVariety(variety.name, plantType);
                    });
                list.append(item);
            });

            varietyList.empty().append(list).show();
            debug("Varieties displayed successfully");
        }

        function selectVariety(variety, plantType) {
            varietyList.html('<div class="seed-catalog-loading">Getting detailed information for ' + variety + '...</div>');

            $.ajax({
                url: seedCatalogPublic.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_seed_details',
                    nonce: seedCatalogPublic.nonce,
                    variety: variety,
                    plant_type: plantType
                },
                success: function(response) {
                    if (response.success && response.data) {
                        fillFormWithDetails(response.data);
                        varietyList.empty().hide();
                        detailsSection.slideDown(400, function() {
                            // Scroll to the details section
                            $('html, body').animate({
                                scrollTop: detailsSection.offset().top - 100
                            }, 500);
                        });
                    } else {
                        showError("Error getting seed details. Please try again or enter details manually.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", error);
                    showError("Error connecting to the server. Please check your connection and try again.");
                }
            });
        }

        function fillFormWithDetails(details) {
            // Auto-generate a title if none exists
            if (!$('#seed_title').val()) {
                $('#seed_title').val(details.seed_name + ' - ' + details.seed_variety);
            }
            
            // Fill in basic fields with the AI-provided data
            $('#seed_name').val(details.seed_name || '');
            $('#seed_variety').val(details.seed_variety || '');
            
            // Handle detailed description data
            if (details.detailed_description) {
                const desc = details.detailed_description;
                let detailedDesc = '';
                
                if (desc.plant_type) detailedDesc += `Plant Type: ${desc.plant_type}\n`;
                if (desc.growth_habit) detailedDesc += `Growth Habit: ${desc.growth_habit}\n`;
                if (desc.plant_size) detailedDesc += `Size: ${desc.plant_size}\n`;
                if (desc.fruit_flower_details) detailedDesc += `${details.seed_name.includes('flower') ? 'Flower' : 'Fruit'} Details: ${desc.fruit_flower_details}\n`;
                if (desc.flavor_profile) detailedDesc += `Flavor: ${desc.flavor_profile}\n`;
                if (desc.scent) detailedDesc += `Scent: ${desc.scent}\n`;
                if (desc.bloom_time) detailedDesc += `Bloom Time: ${desc.bloom_time}\n`;
                if (desc.days_to_maturity) detailedDesc += `Days to Maturity: ${desc.days_to_maturity}\n`;
                if (desc.special_characteristics) detailedDesc += `Special Characteristics: ${desc.special_characteristics}\n`;
                
                // Add remaining description text
                if (details.description) {
                    detailedDesc += `\n${details.description}`;
                }
                
                $('#seed_description').val(detailedDesc.trim());
            } else if (details.description) {
                $('#seed_description').val(details.description);
            }
            
            // Handle growing instructions
            if (details.growing_instructions) {
                const grow = details.growing_instructions;
                
                // Set simple form fields if they exist
                if (grow.sowing_depth) $('#planting_depth').val(grow.sowing_depth);
                if (grow.spacing) $('#planting_spacing').val(grow.spacing);
                if (grow.sunlight_needs) {
                    const sunlightSelect = $('#sunlight_needs');
                    // Try to find a matching option
                    const sunOption = findMatchingOption(sunlightSelect, grow.sunlight_needs);
                    if (sunOption) sunlightSelect.val(sunOption);
                }
                
                // Compile watering info
                if (grow.watering_requirements) $('#watering_requirements').val(grow.watering_requirements);
                
                // Add harvesting tips
                if (grow.harvesting_tips) $('#harvesting_tips').val(grow.harvesting_tips);
                
                // Add soil temperature if the field exists
                if (grow.soil_temperature && $('#soil_temperature').length) {
                    $('#soil_temperature').val(grow.soil_temperature);
                }
                
                // Add sowing method if the field exists
                if (grow.sowing_method && $('#sowing_method').length) {
                    const sowingSelect = $('#sowing_method');
                    // Try to find a matching option
                    const sowOption = findMatchingOption(sowingSelect, grow.sowing_method);
                    if (sowOption) sowingSelect.val(sowOption);
                }
                
                // Compile pest and disease info if the field exists
                if (grow.pest_disease_info && $('#pest_disease_info').length) {
                    $('#pest_disease_info').val(grow.pest_disease_info);
                }
                
                // Fertilizer recommendations if the field exists
                if (grow.fertilizer_recommendations && $('#fertilizer_recommendations').length) {
                    $('#fertilizer_recommendations').val(grow.fertilizer_recommendations);
                }
            }
            
            // Handle days to maturity
            if (details.detailed_description?.days_to_maturity) {
                $('#days_to_maturity').val(details.detailed_description.days_to_maturity);
            } else if (details.growing_instructions?.days_to_maturity) {
                $('#days_to_maturity').val(details.growing_instructions.days_to_maturity);
            }
            
            // Handle companion planting info
            if (details.additional_info?.companion_plants) {
                $('#companion_plants').val(details.additional_info.companion_plants);
            }
            
            // Handle seed information if fields exist
            if (details.seed_info) {
                const seed = details.seed_info;
                
                if (seed.seed_count && $('#seed_count').length) {
                    $('#seed_count').val(seed.seed_count);
                }
                
                if (seed.seed_type && $('#seed_type').length) {
                    const seedTypeSelect = $('#seed_type');
                    const typeOption = findMatchingOption(seedTypeSelect, seed.seed_type);
                    if (typeOption) seedTypeSelect.val(typeOption);
                }
                
                if (seed.germination_rate && $('#germination_rate').length) {
                    $('#germination_rate').val(seed.germination_rate);
                }
                
                if (seed.seed_saving && $('#seed_saving_notes').length) {
                    $('#seed_saving_notes').val(seed.seed_saving);
                }
            }
            
            // Handle additional information if fields exist
            if (details.additional_info) {
                const info = details.additional_info;
                
                if (info.hardiness_zones && $('#hardiness_zones').length) {
                    $('#hardiness_zones').val(info.hardiness_zones);
                }
                
                if (info.container_suitability && $('#container_suitability').length) {
                    $('#container_suitability').val(info.container_suitability);
                }
                
                if (info.storage_recommendations && $('#storage_info').length) {
                    $('#storage_info').val(info.storage_recommendations);
                }
                
                if (info.edible_parts && $('#edible_parts').length) {
                    $('#edible_parts').val(info.edible_parts);
                }
                
                if (info.historical_background && $('#historical_notes').length) {
                    $('#historical_notes').val(info.historical_background);
                }
                
                if (info.recipes && $('#recipe_ideas').length) {
                    $('#recipe_ideas').val(info.recipes);
                }
                
                if (info.regional_tips && $('#regional_tips').length) {
                    $('#regional_tips').val(info.regional_tips);
                }
                
                // Pollinator info
                if (info.pollinator_friendly && $('#pollinator_friendly').length) {
                    // It could be a checkbox or text field
                    const pollinatorEl = $('#pollinator_friendly');
                    if (pollinatorEl.is(':checkbox')) {
                        // Convert any yes/true value to checked
                        const isChecked = /yes|true|1/i.test(info.pollinator_friendly);
                        pollinatorEl.prop('checked', isChecked);
                    } else {
                        pollinatorEl.val(info.pollinator_friendly);
                    }
                }
            }
            
            // Handle brand and vendor info if fields exist
            if (details.brand && $('#brand').length) {
                $('#brand').val(details.brand);
            }
            
            if (details.sku && $('#sku').length) {
                $('#sku').val(details.sku);
            }
            
            if (details.vendor_info) {
                const vendor = details.vendor_info;
                
                if (vendor.pricing && $('#pricing').length) {
                    $('#pricing').val(vendor.pricing);
                }
                
                if (vendor.availability && $('#availability').length) {
                    $('#availability').val(vendor.availability);
                }
                
                if (vendor.company_name && $('#company_name').length) {
                    $('#company_name').val(vendor.company_name);
                }
                
                if (vendor.producer_details && $('#producer_details').length) {
                    $('#producer_details').val(vendor.producer_details);
                }
                
                if (vendor.website && $('#website').length) {
                    $('#website').val(vendor.website);
                }
            }

            // Show success message
            showMessage('Comprehensive seed information retrieved successfully! Please review and edit if needed.', 'success');
        }
        
        /**
         * Helper function to find the closest matching option in a select field
         * @param {jQuery} selectElement - The select DOM element
         * @param {string} value - The value to match
         * @return {string|null} - The matching option value or null if no match
         */
        function findMatchingOption(selectElement, value) {
            if (!value) return null;
            
            // First try exact match
            const lowerValue = value.toLowerCase();
            let foundOption = null;
            
            // Check for exact match
            selectElement.find('option').each(function() {
                if ($(this).val().toLowerCase() === lowerValue || 
                    $(this).text().toLowerCase() === lowerValue) {
                    foundOption = $(this).val();
                    return false; // break the loop
                }
            });
            
            // If exact match found, return it
            if (foundOption) return foundOption;
            
            // Otherwise look for partial match
            selectElement.find('option').each(function() {
                if ($(this).val().toLowerCase().includes(lowerValue) || 
                    $(this).text().toLowerCase().includes(lowerValue) ||
                    lowerValue.includes($(this).val().toLowerCase()) ||
                    lowerValue.includes($(this).text().toLowerCase())) {
                    foundOption = $(this).val();
                    return false; // break the loop
                }
            });
            
            return foundOption;
        }

        // Enhanced search function to handle more search parameters
        function searchSeedInfo() {
            const searchParams = {
                term: $('#seed_name').val().trim(),
                brand: $('#brand').length ? $('#brand').val().trim() : '',
                sku: $('#sku').length ? $('#sku').val().trim() : ''
            };
            
            // Validate we have at least one search parameter
            if (!searchParams.term && !searchParams.brand && !searchParams.sku) {
                showMessage('Please enter at least one search parameter: seed name, brand, or SKU/UPC.', 'error');
                return;
            }
            
            showMessage(`Searching for detailed seed information...`, 'info');
            
            // Show loading state
            const loadingHtml = '<div class="seed-catalog-loading">Searching seed databases and horticultural resources...</div>';
            varietyList.html(loadingHtml).show();
            
            // Make the AJAX request with all available search parameters
            $.ajax({
                url: seedCatalogPublic.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_seed_details',
                    nonce: seedCatalogPublic.nonce,
                    variety: searchParams.term,
                    plant_type: searchParams.term, // Using same value for plant_type and variety
                    brand: searchParams.brand,
                    sku: searchParams.sku
                },
                success: function(response) {
                    if (response.success && response.data) {
                        // Fill form with the returned data
                        fillFormWithDetails(response.data);
                        varietyList.empty().hide();
                        
                        // Show the details section
                        detailsSection.slideDown(400, function() {
                            // Scroll to the details section
                            $('html, body').animate({
                                scrollTop: detailsSection.offset().top - 100
                            }, 500);
                        });
                    } else {
                        showError("No seed information found. Please try a different search term or enter details manually.");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", error);
                    showError("Error connecting to the database. Please check your connection and try again.");
                }
            });
        }
        
        // Add a direct search button if it doesn't exist
        if ($('#seed_name').length && !$('#seed-direct-search').length) {
            const directSearchBtn = $('<button id="seed-direct-search" type="button" class="button seed-catalog-ai-suggest">Search Seed Database</button>');
            directSearchBtn.on('click', function(e) {
                e.preventDefault();
                searchSeedInfo();
            });
            
            // Add after the AI suggest button if it exists, otherwise after the input
            if ($('.seed-catalog-ai-suggest').length) {
                $('.seed-catalog-ai-suggest').after(directSearchBtn);
            } else {
                $('#seed_name').after(directSearchBtn);
            }
        }

        // Missing utility functions for showing messages and errors
        if (typeof showMessage !== 'function') {
            debug("showMessage function doesn't exist, defining it now");
        }
        
        if (typeof showError !== 'function') {
            debug("showError function doesn't exist, defining it now");
        }

        // Utility function for showing messages
        function showMessage(message, type) {
            debug("showMessage called with", { message, type });
            
            // Create or find message container
            let messageDiv = $('.seed-catalog-message');
            if (messageDiv.length === 0) {
                debug("Creating new message div");
                messageDiv = $('<div class="seed-catalog-message"></div>');
                
                // Add before variety list if it exists, otherwise add before the seed name input
                if (varietyList.length > 0) {
                    varietyList.before(messageDiv);
                } else if (seedNameInput.length > 0) {
                    seedNameInput.before(messageDiv);
                } else {
                    // Last resort - add to the body
                    $('body').prepend(messageDiv);
                }
            }
            
            // Display the message
            messageDiv
                .removeClass('success error info warning')
                .addClass(type || 'info')
                .html(message)
                .show();
                
            debug("Message div shown with classes", messageDiv.attr('class'));
            
            // Automatically hide after 5 seconds
            setTimeout(function() {
                messageDiv.fadeOut();
            }, 5000);
        }
        
        // Utility function for showing errors
        function showError(message) {
            debug("showError called with", message);
            showMessage(message, 'error');
            
            // Also show in the variety list if it's visible
            if (varietyList && varietyList.is(':visible')) {
                varietyList.html('<div class="seed-catalog-error">' + message + 
                    '<p class="error-help">If this error persists, please check the following:</p>' +
                    '<ul class="error-steps">' +
                    '<li>Make sure you have added a valid Gemini API key in the plugin settings</li>' +
                    '<li>Ensure your search term is a valid plant or seed name</li>' +
                    '<li>Try refreshing the page and trying again</li>' +
                    '</ul></div>');
                debug("Error also shown in variety list with help text");
            }
        }

        // Expose utility functions to global scope for testing
        window.seedCatalogDebug = {
            showMessage: showMessage,
            showError: showError,
            searchVarieties: searchVarieties
        };
        
        debug("Debug functions exposed to window.seedCatalogDebug");
    });

    // Enhanced public scripts with accessibility support
    const KEYCODE = {
        SPACE: 32,
        ENTER: 13,
        TAB: 9,
        ESC: 27,
        ARROW_UP: 38,
        ARROW_DOWN: 40
    };

    $(document).ready(function() {
        initializeAccessibleSearch();
        initializeAccessibleFilters();
        initializeVarietySearch();
        initializeModalAccessibility();
        setupKeyboardNavigation();
    });

    function initializeAccessibleSearch() {
        const searchForm = $('.seed-catalog-search-form');
        const searchResults = $('#seed-catalog-search-results');
        let searchTimeout;

        searchForm.find('input[type="search"]').on('input', function() {
            const searchInput = $(this);
            clearTimeout(searchTimeout);

            // Update ARIA states
            searchResults.attr('aria-busy', 'true');

            searchTimeout = setTimeout(function() {
                const searchTerm = searchInput.val();
                
                if (searchTerm.length >= 2) {
                    $.ajax({
                        url: seed_catalog.ajax_url,
                        data: {
                            action: 'search_seeds',
                            term: searchTerm,
                            nonce: seed_catalog.nonce
                        },
                        beforeSend: function() {
                            searchResults.html('<div class="seed-catalog-loading" role="status" aria-live="polite">' + 
                                '<span class="screen-reader-text">' + seed_catalog.loading_text + '</span>' +
                                '<div class="spinner"></div></div>');
                        },
                        success: function(response) {
                            if (response.success) {
                                displaySearchResults(response.data);
                            } else {
                                searchResults.html('<p role="alert">' + response.data + '</p>');
                            }
                            searchResults.attr('aria-busy', 'false');
                        }
                    });
                } else {
                    searchResults.empty().attr('aria-busy', 'false');
                }
            }, 500);
        });

        // Handle keyboard navigation in search results
        searchResults.on('keydown', '.seed-catalog-search-item a', function(e) {
            const target = $(e.target);
            
            switch(e.keyCode) {
                case KEYCODE.ARROW_DOWN:
                    e.preventDefault();
                    const nextItem = target.closest('.seed-catalog-search-item').next().find('a');
                    if (nextItem.length) nextItem.focus();
                    break;
                case KEYCODE.ARROW_UP:
                    e.preventDefault();
                    const prevItem = target.closest('.seed-catalog-search-item').prev().find('a');
                    if (prevItem.length) prevItem.focus();
                    break;
            }
        });
    }

    function initializeAccessibleFilters() {
        const filterToggle = $('.seed-catalog-filter-toggle');
        const filtersContainer = $('.seed-catalog-filters-container');

        filterToggle.attr({
            'aria-expanded': 'false',
            'aria-controls': 'seed-catalog-filters'
        });

        filtersContainer.attr('id', 'seed-catalog-filters');

        filterToggle.on('click', function() {
            const isExpanded = filterToggle.attr('aria-expanded') === 'true';
            filterToggle.attr('aria-expanded', !isExpanded);
            filtersContainer.toggleClass('filters-visible');
        });

        // Close filters when clicking outside on mobile
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.seed-catalog-filters-wrapper').length) {
                filterToggle.attr('aria-expanded', 'false');
                filtersContainer.removeClass('filters-visible');
            }
        });
    }

    function initializeVarietySearch() {
        const varietyList = $('.seed-catalog-variety-list');
        const seedNameInput = $('#seed_name');

        if (varietyList.length && seedNameInput.length) {
            varietyList.attr({
                'role': 'listbox',
                'aria-label': seed_catalog.variety_list_label
            });

            // Handle keyboard navigation in variety list
            varietyList.on('keydown', '.variety-item', function(e) {
                const target = $(e.target);
                
                switch(e.keyCode) {
                    case KEYCODE.ENTER:
                    case KEYCODE.SPACE:
                        e.preventDefault();
                        target.click();
                        break;
                    case KEYCODE.ARROW_DOWN:
                        e.preventDefault();
                        const nextItem = target.next('.variety-item');
                        if (nextItem.length) nextItem.focus();
                        break;
                    case KEYCODE.ARROW_UP:
                        e.preventDefault();
                        const prevItem = target.prev('.variety-item');
                        if (prevItem.length) prevItem.focus();
                        break;
                    case KEYCODE.ESC:
                        e.preventDefault();
                        closeVarietyList();
                        seedNameInput.focus();
                        break;
                }
            });

            // Make variety items focusable
            varietyList.find('.variety-item').attr({
                'tabindex': '0',
                'role': 'option'
            });
        }
    }

    function initializeModalAccessibility() {
        // Add backdrop for modals
        $('body').append('<div class="seed-catalog-modal-backdrop" tabindex="-1"></div>');
        const backdrop = $('.seed-catalog-modal-backdrop');

        // Handle ESC key for modals
        $(document).on('keydown', function(e) {
            if (e.keyCode === KEYCODE.ESC) {
                if (backdrop.hasClass('visible')) {
                    closeAllModals();
                }
            }
        });

        // Close modals when clicking backdrop
        backdrop.on('click', closeAllModals);
    }

    function setupKeyboardNavigation() {
        // Make seed items focusable
        $('.seed-catalog-item').attr('tabindex', '0');

        // Handle keyboard navigation between items
        $('.seed-catalog-grid').on('keydown', '.seed-catalog-item', function(e) {
            const target = $(e.target);
            const gridItems = $('.seed-catalog-item');
            const currentIndex = gridItems.index(target);
            
            switch(e.keyCode) {
                case KEYCODE.ENTER:
                case KEYCODE.SPACE:
                    e.preventDefault();
                    target.find('a:first').get(0).click();
                    break;
                case KEYCODE.ARROW_RIGHT:
                    e.preventDefault();
                    if (currentIndex < gridItems.length - 1) {
                        gridItems.eq(currentIndex + 1).focus();
                    }
                    break;
                case KEYCODE.ARROW_LEFT:
                    e.preventDefault();
                    if (currentIndex > 0) {
                        gridItems.eq(currentIndex - 1).focus();
                    }
                    break;
            }
        });

        // Add focus indication for interactive elements
        $('.seed-catalog-read-more, .seed-catalog-search-submit, .seed-catalog-reset-filters')
            .on('focus', function() {
                $(this).closest('.seed-catalog-item').addClass('focus-within');
            })
            .on('blur', function() {
                $(this).closest('.seed-catalog-item').removeClass('focus-within');
            });
    }

    // Helper Functions
    function displaySearchResults(results) {
        const searchResults = $('#seed-catalog-search-results');
        searchResults.empty();

        if (results.length > 0) {
            const resultsHtml = results.map(function(seed) {
                return `
                    <div class="seed-catalog-search-item">
                        <a href="${seed.link}" class="seed-catalog-search-link" tabindex="0">
                            ${seed.image ? `
                                <div class="seed-catalog-search-item-image">
                                    <img src="${seed.image}" alt="${seed.title}" />
                                </div>
                            ` : ''}
                            <div class="seed-catalog-search-item-content">
                                <h3>${seed.title}</h3>
                                ${seed.excerpt ? `<p>${seed.excerpt}</p>` : ''}
                            </div>
                        </a>
                    </div>
                `;
            }).join('');

            searchResults.html(resultsHtml);
            searchResults.attr('aria-label', seed_catalog.results_found.replace('%s', results.length));
        } else {
            searchResults.html(`
                <p class="no-results" role="alert">
                    ${seed_catalog.no_results_text}
                </p>
            `);
        }
    }

    function closeAllModals() {
        $('.seed-catalog-modal-backdrop').removeClass('visible');
        $('.seed-catalog-variety-list').hide();
    }

    function closeVarietyList() {
        $('.seed-catalog-variety-list').hide();
        $('.seed-catalog-modal-backdrop').removeClass('visible');
    }

})(jQuery);
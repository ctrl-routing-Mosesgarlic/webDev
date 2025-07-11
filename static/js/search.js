// Search functionality enhancements

// AJAX Search Implementation
const initSearch = () => {
    const searchForm = document.querySelector('.search-form');
    const searchResults = document.querySelector('.search-results-container');
    
    if (searchForm) {
        searchForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const searchInput = searchForm.querySelector('input[name="search"]');
            const searchTerm = searchInput.value.trim();
            
            if (!searchTerm) return;
            
            // Show loading state
            if (searchResults) {
                searchResults.innerHTML = '<div class="loading">Searching...</div>';
            }
            
            try {
                const response = await fetch(`/api/search.php?search=${encodeURIComponent(searchTerm)}`);
                const data = await response.json();
                
                if (searchResults) {
                    searchResults.innerHTML = data.html || 
                        '<div class="no-results">No products found matching your search.</div>';
                }
            } catch (error) {
                console.error('Search error:', error);
                if (searchResults) {
                    searchResults.innerHTML = '<div class="error">Search failed. Please try again.</div>';
                }
            }
        });
    }
    
    // Initialize filters
    initFilters();
};

// Filter and sort functionality
const initFilters = () => {
    const filterForm = document.querySelector('.filter-form');
    
    if (filterForm) {
        filterForm.addEventListener('change', () => {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData).toString();
            
            // Update URL without reload
            history.pushState({}, '', `?${params}`);
            
            // Trigger search update
            updateSearchResults(params);
        });
    }
};

const updateSearchResults = async (params) => {
    const searchResults = document.querySelector('.search-results-container');
    
    if (!searchResults) return;
    
    try {
        const response = await fetch(`/api/search.php?${params}`);
        const data = await response.json();
        
        searchResults.innerHTML = data.html || 
            '<div class="no-results">No products found matching your filters.</div>';
    } catch (error) {
        console.error('Filter error:', error);
        searchResults.innerHTML = '<div class="error">Failed to apply filters. Please try again.</div>';
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initSearch);

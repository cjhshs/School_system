// CJLG University - Table Search Functionality

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all search inputs
    initTableSearch();
});

function initTableSearch() {
    document.querySelectorAll('.table-search').forEach(function(searchInput) {
        const tableId = searchInput.getAttribute('data-table');
        const table = document.getElementById(tableId);
        
        if (!table) return;
        
        const tbody = table.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr');
        const clearBtn = searchInput.parentElement.querySelector('.search-clear');
        const countDisplay = document.getElementById(tableId + '_count');
        
        // Search input event
        searchInput.addEventListener('input', function() {
            filterTable(this.value.toLowerCase(), rows, tbody, countDisplay);
            
            // Show/hide clear button
            if (clearBtn) {
                clearBtn.style.display = this.value ? 'block' : 'none';
            }
        });
        
        // Clear button
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                filterTable('', rows, tbody, countDisplay);
                this.style.display = 'none';
                searchInput.focus();
            });
        }
    });
}

function filterTable(query, rows, tbody, countDisplay) {
    let visibleCount = 0;
    let hasEmptyState = false;
    
    rows.forEach(function(row) {
        // Skip empty state rows
        if (row.classList.contains('empty-state') || row.classList.contains('no-data')) {
            hasEmptyState = true;
            return;
        }
        
        const text = row.textContent.toLowerCase();
        const match = query === '' || text.includes(query);
        
        row.style.display = match ? '' : 'none';
        
        if (match) visibleCount++;
    });
    
    // Update count display
    if (countDisplay) {
        if (query === '') {
            countDisplay.textContent = 'Showing ' + visibleCount + ' entries';
        } else {
            countDisplay.textContent = 'Found ' + visibleCount + ' matching entries';
        }
    }
}

// Function to add search to a table
function addTableSearch(tableId, placeholder) {
    if (!placeholder) placeholder = 'Search...';
    
    return `
        <div class="search-container">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="table-search search-input" data-table="${tableId}" placeholder="${placeholder}">
                <button type="button" class="search-clear"><i class="fas fa-times"></i></button>
            </div>
            <div class="search-results-count" id="${tableId}_count"></div>
        </div>
    `;
}

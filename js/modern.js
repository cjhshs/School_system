/**
 * School Management System - Shared JavaScript
 * Modern UI Interactions
 */

(function() {
    'use strict';

    // ==========================================
    // Sidebar Toggle (Mobile)
    // ==========================================
    window.toggleSidebar = function() {
        const sidebar = document.querySelector('.app-sidebar');
        sidebar.classList.toggle('show');
    };

    // Close sidebar when clicking outside
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.app-sidebar');
        const toggleBtn = document.querySelector('.toggle-sidebar');
        
        if (window.innerWidth <= 1024 && 
            sidebar && 
            sidebar.classList.contains('show') &&
            !sidebar.contains(e.target) &&
            !toggleBtn.contains(e.target)) {
            sidebar.classList.remove('show');
        }
    });

    // ==========================================
    // Modal Functions
    // ==========================================
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    };

    // Close modal on backdrop click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop')) {
            const modal = e.target.closest('.modal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }
    });

    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modal => {
                modal.classList.remove('show');
            });
            document.body.style.overflow = '';
        }
    });

    // ==========================================
    // Toast Notifications
    // ==========================================
    window.showToast = function(type, title, message, duration = 4000) {
        const container = document.querySelector('.toast-container') || createToastContainer();
        
        const icons = {
            success: 'fa-check',
            error: 'fa-times',
            warning: 'fa-exclamation',
            info: 'fa-info'
        };

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="fas ${icons[type] || icons.info}"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                ${message ? `<div class="toast-message">${message}</div>` : ''}
            </div>
            <button class="modal-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideIn 0.3s ease reverse';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };

    function createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    }

    // ==========================================
    // Confirmation Dialog
    // ==========================================
    window.confirmAction = function(message, callback) {
        const overlay = document.createElement('div');
        overlay.className = 'modal-backdrop show';
        overlay.style.cssText = 'z-index: 1060;';
        
        const dialog = document.createElement('div');
        dialog.className = 'modal show';
        dialog.style.cssText = 'z-index: 1061;';
        
        dialog.innerHTML = `
            <div class="modal-content" style="max-width: 400px;">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                        Confirm Action
                    </h5>
                    <button type="button" class="modal-close" onclick="closeConfirmDialog()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>${message}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeConfirmDialog()">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmBtn">Confirm</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        document.body.appendChild(dialog);

        document.getElementById('confirmBtn').addEventListener('click', function() {
            closeConfirmDialog();
            if (typeof callback === 'function') callback();
        });

        function closeConfirmDialog() {
            overlay.remove();
            dialog.remove();
        }
    };

    // ==========================================
    // Delete Confirmation
    // ==========================================
    window.confirmDelete = function(form) {
        confirmAction('Are you sure you want to delete this record? This action cannot be undone.', function() {
            form.submit();
        });
        return false;
    };

    // ==========================================
    // Table Search & Filter
    // ==========================================
    window.initTableSearch = function(tableId, searchInputId) {
        const table = document.getElementById(tableId);
        const searchInput = document.getElementById(searchInputId);
        
        if (!table || !searchInput) return;

        searchInput.addEventListener('keyup', function() {
            const term = searchInput.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    };

    // ==========================================
    // Table Sorting
    // ==========================================
    window.sortTable = function(tableId, column, direction) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort(function(a, b) {
            const aVal = a.cells[column].textContent.trim();
            const bVal = b.cells[column].textContent.trim();
            
            const aNum = parseFloat(aVal);
            const bNum = parseFloat(bVal);
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return direction === 'asc' ? aNum - bNum : bNum - aNum;
            }
            
            return direction === 'asc' 
                ? aVal.localeCompare(bVal) 
                : bVal.localeCompare(aVal);
        });

        rows.forEach(row => tbody.appendChild(row));
    };

    // ==========================================
    // Table Pagination
    // ==========================================
    window.initPagination = function(tableId, rowsPerPage = 10) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const totalPages = Math.ceil(rows.length / rowsPerPage);
        
        let currentPage = 1;

        function showPage(page) {
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            
            rows.forEach((row, index) => {
                row.style.display = (index >= start && index < end) ? '' : 'none';
            });
            
            currentPage = page;
            updatePagination();
        }

        function updatePagination() {
            let pagination = table.parentElement.querySelector('.pagination');
            if (!pagination) {
                pagination = document.createElement('div');
                pagination.className = 'pagination mt-3';
                table.parentElement.appendChild(pagination);
            }
            
            let html = '';
            
            // Previous button
            html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>`;
            
            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>`;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }
            
            // Next button
            html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>`;
            
            pagination.innerHTML = html;
            
            // Add click events
            pagination.querySelectorAll('.page-link[data-page]').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = parseInt(this.dataset.page);
                    if (page >= 1 && page <= totalPages) {
                        showPage(page);
                    }
                });
            });
        }

        // Initial display
        showPage(1);
    };

    // ==========================================
    // Select All Checkboxes
    // ==========================================
    window.selectAllCheckbox = function(source, tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const checkboxes = table.querySelectorAll('tbody input[type="checkbox"]');
        checkboxes.forEach(cb => cb.checked = source.checked);
        
        updateBulkActions(tableId);
    };

    window.updateBulkActions = function(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;
        
        const checked = table.querySelectorAll('tbody input[type="checkbox"]:checked');
        const bulkActions = document.querySelector('.bulk-actions');
        
        if (bulkActions) {
            bulkActions.style.display = checked.length > 0 ? '' : 'none';
            const count = bulkActions.querySelector('.selected-count');
            if (count) count.textContent = checked.length;
        }
    };

    // ==========================================
    // Form Validation
    // ==========================================
    window.validateForm = function(formId) {
        const form = document.getElementById(formId);
        if (!form) return false;

        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            const value = field.value.trim();
            
            if (!value) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            showToast('error', 'Validation Error', 'Please fill in all required fields.');
        }

        return isValid;
    };

    // Clear invalid state on input
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
        }
    });

    // ==========================================
    // Auto-dismiss Alerts
    // ==========================================
    document.querySelectorAll('.alert:not(.alert-permanent)').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // ==========================================
    // Print Function
    // ==========================================
    window.printContent = function(elementId) {
        const content = document.getElementById(elementId);
        if (!content) return;

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Print - ${document.title}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
                <style>
                    body { padding: 20px; }
                    @media print { .no-print { display: none !important; } }
                </style>
            </head>
            <body>${content.innerHTML}</body>
            </html>
        `);
        printWindow.document.close();
        printWindow.onload = () => printWindow.print();
    };

    // ==========================================
    // Export to CSV
    // ==========================================
    window.exportTableToCSV = function(tableId, filename) {
        const table = document.getElementById(tableId);
        if (!table) return;

        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        rows.forEach(row => {
            const cols = row.querySelectorAll('td, th');
            const rowData = [];
            
            cols.forEach(col => {
                // Skip action columns and checkboxes
                if (!col.querySelector('input[type="checkbox"]') && 
                    !col.classList.contains('actions') &&
                    !col.classList.contains('no-export')) {
                    rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
                }
            });
            
            if (rowData.length > 0) {
                csv.push(rowData.join(','));
            }
        });

        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename + '.csv';
        link.click();
    };

    // ==========================================
    // Initialize on DOM Ready
    // ==========================================
    document.addEventListener('DOMContentLoaded', function() {
        // Add animation class to cards on scroll
        const observerOptions = {
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.card').forEach(card => {
            observer.observe(card);
        });
    });

})();

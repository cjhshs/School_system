// Common JavaScript utilities
document.addEventListener('DOMContentLoaded', function() {
    // Universal search for tables
    document.querySelectorAll('.search-input').forEach(function(input) {
        input.addEventListener('keyup', function() {
            const tableId = this.getAttribute('data-table');
            const filter = this.value.toLowerCase();
            const table = document.getElementById(tableId);
            if (!table) return;
            const clearBtn = this.parentElement.querySelector('.search-clear');
            if (clearBtn) clearBtn.style.display = filter ? 'block' : 'none';
            table.querySelectorAll('tbody tr').forEach(function(row) {
                row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
            });
        });
    });
});

function clearSearch(btn) {
    const input = btn.parentElement.querySelector('.search-input');
    input.value = '';
    btn.style.display = 'none';
    input.dispatchEvent(new Event('keyup'));
}

function generatePassword(length) {
    length = length || 8;
    const chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    let pwd = '';
    for (let i = 0; i < length; i++) pwd += chars.charAt(Math.floor(Math.random() * chars.length));
    return pwd;
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        return true;
    }).catch(function() {
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        return true;
    });
}

function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this? This action cannot be undone.');
}

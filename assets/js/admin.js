/**
 * Smart Cafe - Admin Panel JavaScript
 * 
 * Handles admin dashboard interactions: charts, CRUD modals,
 * status updates, image previews, and data management.
 */

(function() {
    'use strict';

    // ---- Sidebar Toggle (Mobile) ----
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.admin-sidebar');
    const sidebarOverlay = document.querySelector('.sidebar-overlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            if (sidebarOverlay) sidebarOverlay.classList.toggle('show');
        });

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('show');
            });
        }
    }

    // ---- Image Preview for File Uploads ----
    document.querySelectorAll('.image-upload-input').forEach(function(input) {
        input.addEventListener('change', function() {
            const preview = document.getElementById(this.dataset.preview);
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

})();

// ============================================================
// Simple Canvas Bar Chart (No external library needed)
// ============================================================

/**
 * Draw a bar chart on a canvas element
 * @param {string} canvasId - The canvas element ID
 * @param {Array} labels - X-axis labels
 * @param {Array} values - Y-axis values
 * @param {string} color - Bar color (hex or rgba)
 */
function drawBarChart(canvasId, labels, values, color) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;

    // Set canvas size
    const rect = canvas.parentElement.getBoundingClientRect();
    canvas.width = rect.width * dpr;
    canvas.height = rect.height * dpr;
    canvas.style.width = rect.width + 'px';
    canvas.style.height = rect.height + 'px';
    ctx.scale(dpr, dpr);

    const width = rect.width;
    const height = rect.height;
    const padding = { top: 20, right: 20, bottom: 40, left: 60 };
    const chartWidth = width - padding.left - padding.right;
    const chartHeight = height - padding.top - padding.bottom;

    // Get theme colors
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const textColor = isDark ? '#9ca3b4' : '#6b7280';
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';

    // Clear canvas
    ctx.clearRect(0, 0, width, height);

    if (values.length === 0) return;

    const maxValue = Math.max(...values) || 1;
    const barWidth = (chartWidth / labels.length) * 0.6;
    const barGap = (chartWidth / labels.length) * 0.4;

    // Draw grid lines
    ctx.strokeStyle = gridColor;
    ctx.lineWidth = 1;
    ctx.font = '11px Inter, sans-serif';
    ctx.fillStyle = textColor;
    ctx.textAlign = 'right';

    const gridLines = 5;
    for (let i = 0; i <= gridLines; i++) {
        const y = padding.top + (chartHeight / gridLines) * i;
        const value = Math.round(maxValue - (maxValue / gridLines) * i);
        
        ctx.beginPath();
        ctx.moveTo(padding.left, y);
        ctx.lineTo(width - padding.right, y);
        ctx.stroke();
        
        ctx.fillText(value.toLocaleString(), padding.left - 8, y + 4);
    }

    // Draw bars
    ctx.textAlign = 'center';
    labels.forEach(function(label, index) {
        const barHeight = (values[index] / maxValue) * chartHeight;
        const x = padding.left + (chartWidth / labels.length) * index + barGap / 2;
        const y = padding.top + chartHeight - barHeight;

        // Bar with rounded top corners
        const radius = 4;
        ctx.fillStyle = color || '#e67e22';
        ctx.beginPath();
        ctx.moveTo(x, y + radius);
        ctx.arcTo(x, y, x + radius, y, radius);
        ctx.arcTo(x + barWidth, y, x + barWidth, y + radius, radius);
        ctx.lineTo(x + barWidth, padding.top + chartHeight);
        ctx.lineTo(x, padding.top + chartHeight);
        ctx.closePath();
        ctx.fill();

        // Label
        ctx.fillStyle = textColor;
        ctx.fillText(label, x + barWidth / 2, height - padding.bottom + 20);
    });
}

/**
 * Draw a simple donut chart
 */
function drawDonutChart(canvasId, data, colors) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;

    const rect = canvas.parentElement.getBoundingClientRect();
    const size = Math.min(rect.width, rect.height);
    canvas.width = size * dpr;
    canvas.height = size * dpr;
    canvas.style.width = size + 'px';
    canvas.style.height = size + 'px';
    ctx.scale(dpr, dpr);

    const centerX = size / 2;
    const centerY = size / 2;
    const outerRadius = size / 2 - 10;
    const innerRadius = outerRadius * 0.6;

    const total = data.reduce(function(sum, d) { return sum + d.value; }, 0) || 1;
    let startAngle = -Math.PI / 2;

    data.forEach(function(item, index) {
        const sliceAngle = (item.value / total) * Math.PI * 2;
        const endAngle = startAngle + sliceAngle;

        ctx.beginPath();
        ctx.arc(centerX, centerY, outerRadius, startAngle, endAngle);
        ctx.arc(centerX, centerY, innerRadius, endAngle, startAngle, true);
        ctx.closePath();
        ctx.fillStyle = colors[index % colors.length];
        ctx.fill();

        startAngle = endAngle;
    });

    // Center text
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    ctx.fillStyle = isDark ? '#f1f3f7' : '#1a1d23';
    ctx.font = 'bold 24px Outfit, sans-serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(total, centerX, centerY - 8);
    ctx.font = '12px Inter, sans-serif';
    ctx.fillStyle = isDark ? '#9ca3b4' : '#6b7280';
    ctx.fillText('Total', centerX, centerY + 14);
}

// ============================================================
// Admin CRUD Helpers
// ============================================================

/**
 * Confirm deletion with a modal
 */
function confirmDelete(itemName, deleteUrl) {
    const modal = document.getElementById('deleteModal');
    if (!modal) return;

    document.getElementById('deleteItemName').textContent = itemName;
    document.getElementById('deleteForm').action = deleteUrl;

    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

/**
 * Update order status via AJAX
 */
async function updateOrderStatus(orderId, newStatus) {
    try {
        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('status', newStatus);

        const response = await fetch(getBaseUrl() + '/api/admin/orders.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        
        if (data.success) {
            showToast('Status Updated', 'Order status changed to ' + newStatus, 'success');
            // Update the badge in the table
            const badge = document.querySelector('[data-order-id="' + orderId + '"] .status-badge');
            if (badge) {
                badge.className = 'status-badge ' + newStatus;
                badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            }
        } else {
            showToast('Error', data.message || 'Failed to update status', 'error');
        }
    } catch (error) {
        showToast('Error', 'Network error. Please try again.', 'error');
    }
}

/**
 * Get admin base URL
 */
function getAdminBaseUrl() {
    const meta = document.querySelector('meta[name="base-url"]');
    return meta ? meta.getAttribute('content') : '';
}

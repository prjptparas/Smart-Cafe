<?php
/**
 * Smart Cafe - QR Code Generator
 * 
 * Generates QR codes for each table that link directly to the menu.
 * Admin can print these cards and place them on tables.
 * Uses qrcode.js (CDN) for client-side QR code generation.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

// Get settings
$settings = getAllSettings($pdo);
$cafeName = $settings['cafe_name'] ?? APP_NAME;
$totalTables = (int)($settings['total_tables'] ?? TOTAL_TABLES);
$menuUrl = BASE_URL . '/pages/menu.php';

$pageTitle = 'QR Code Generator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($pageTitle); ?> | <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/variables.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <script src="<?php echo BASE_URL; ?>/assets/js/theme.js"></script>

    <style>
        /* ---- QR Page Layout ---- */
        .qr-page-header {
            text-align: center;
            padding: 3rem 0 2rem;
        }

        .qr-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .qr-controls label {
            font-weight: 600;
            color: var(--text-primary);
            font-size: var(--font-size-sm);
        }

        .qr-range-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 0.5rem 1rem;
        }

        .qr-range-group input[type="number"] {
            width: 60px;
            text-align: center;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 4px 8px;
            color: var(--text-primary);
            font-weight: 600;
            font-size: var(--font-size-sm);
        }

        .qr-range-group span {
            color: var(--text-tertiary);
            font-size: var(--font-size-sm);
        }

        /* ---- QR Card Grid ---- */
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.5rem;
            padding-bottom: 3rem;
        }

        .qr-card {
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            text-align: center;
            transition: var(--transition-base);
            position: relative;
            overflow: hidden;
        }

        .qr-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-primary), var(--color-accent));
        }

        .qr-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(var(--color-primary-rgb), 0.3);
        }

        .qr-card-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: var(--font-heading);
            font-weight: 700;
            font-size: 1rem;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
        }

        .qr-card-brand i {
            color: var(--color-primary);
            font-size: 1.1rem;
        }

        .qr-canvas-wrap {
            background: #ffffff;
            border-radius: var(--radius-md);
            padding: 1rem;
            display: inline-block;
            margin: 0.5rem 0;
            box-shadow: var(--shadow-sm);
        }

        .qr-canvas-wrap canvas {
            display: block;
        }

        .qr-table-label {
            font-family: var(--font-heading);
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--color-primary);
            margin: 0.75rem 0 0.25rem;
        }

        .qr-scan-text {
            font-size: var(--font-size-xs);
            color: var(--text-tertiary);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
        }

        .qr-url-text {
            font-size: 0.7rem;
            color: var(--text-tertiary);
            word-break: break-all;
            margin-top: 0.5rem;
            opacity: 0.7;
        }

        .qr-download-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: var(--font-size-xs);
            color: var(--color-primary);
            background: rgba(var(--color-primary-rgb), 0.08);
            border: 1px solid rgba(var(--color-primary-rgb), 0.2);
            border-radius: var(--radius-full);
            padding: 4px 12px;
            cursor: pointer;
            transition: var(--transition-fast);
            text-decoration: none;
            margin-top: 0.5rem;
        }

        .qr-download-btn:hover {
            background: rgba(var(--color-primary-rgb), 0.15);
            color: var(--color-primary-dark);
        }

        /* ---- Print Styles ---- */
        @media print {
            body {
                background: #fff !important;
                color: #000 !important;
            }

            .no-print,
            .navbar-smart,
            .footer-smart,
            .qr-page-header .section-subtitle,
            .qr-controls,
            .qr-download-btn,
            .qr-url-text {
                display: none !important;
            }

            .qr-page-header {
                padding: 1rem 0 0.5rem;
            }

            .qr-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 0.75rem;
                padding: 0;
            }

            .qr-card {
                border: 2px solid #ddd;
                border-radius: 12px;
                padding: 1rem;
                page-break-inside: avoid;
                break-inside: avoid;
                box-shadow: none;
                transform: none;
            }

            .qr-card::before {
                background: #e67e22;
            }

            .qr-card:hover {
                transform: none;
                box-shadow: none;
            }

            .qr-canvas-wrap {
                box-shadow: none;
                padding: 0.5rem;
            }

            .qr-card-brand {
                color: #000;
            }

            .qr-table-label {
                color: #e67e22;
            }

            .qr-scan-text {
                color: #666;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar-smart no-print">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between w-100">
            <a href="<?php echo BASE_URL; ?>/index.php" class="navbar-brand-smart">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Smart Cafe Logo" class="brand-logo-img"><?php echo APP_NAME; ?>
            </a>
            <div class="d-flex align-items-center gap-2">
                <button class="theme-toggle" id="themeToggle" title="Toggle Theme">
                    <i class="bi bi-moon-fill"></i>
                </button>
                <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="btn-outline-smart btn-sm-smart">
                    <i class="bi bi-arrow-left"></i> Admin
                </a>
            </div>
        </div>
    </div>
</nav>

<main>
    <!-- Page Header -->
    <div class="qr-page-header">
        <h1 class="section-title"><i class="bi bi-qr-code-scan" style="color:var(--color-primary)"></i> QR Code Generator</h1>
        <p class="section-subtitle">Generate printable QR codes for each table. Customers scan to order.</p>
    </div>

    <div class="container">
        <!-- Controls -->
        <div class="qr-controls no-print">
            <div class="qr-range-group">
                <label for="tableFrom">Tables</label>
                <input type="number" id="tableFrom" value="1" min="1" max="<?php echo $totalTables; ?>">
                <span>to</span>
                <input type="number" id="tableTo" value="<?php echo $totalTables; ?>" min="1" max="<?php echo $totalTables; ?>">
            </div>

            <button class="btn-primary-smart" onclick="generateQRCodes()">
                <i class="bi bi-arrow-clockwise"></i> Generate
            </button>

            <button class="btn-outline-smart" onclick="window.print()">
                <i class="bi bi-printer"></i> Print All
            </button>

            <button class="btn-outline-smart" onclick="downloadAll()">
                <i class="bi bi-download"></i> Download All
            </button>
        </div>

        <!-- QR Code Grid -->
        <div class="qr-grid" id="qrGrid">
            <!-- QR cards generated by JavaScript -->
        </div>
    </div>
</main>

<!-- QR Code JS Library (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>

<script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
    const CAFE_NAME = '<?php echo addslashes($cafeName); ?>';
    const TOTAL_TABLES = <?php echo $totalTables; ?>;
    const MENU_URL = '<?php echo $menuUrl; ?>';

    /**
     * Generate QR codes for the selected table range
     */
    function generateQRCodes() {
        const from = Math.max(1, parseInt(document.getElementById('tableFrom').value) || 1);
        const to = Math.min(TOTAL_TABLES, parseInt(document.getElementById('tableTo').value) || TOTAL_TABLES);

        if (from > to) {
            alert('Start table must be less than or equal to end table.');
            return;
        }

        const grid = document.getElementById('qrGrid');
        grid.innerHTML = '';

        for (let table = from; table <= to; table++) {
            const url = MENU_URL + '?table=' + table;
            const card = createQRCard(table, url);
            grid.appendChild(card);
        }
    }

    /**
     * Create a single QR card element
     */
    function createQRCard(tableNumber, url) {
        // Generate QR code
        var qr = qrcode(0, 'M');
        qr.addData(url);
        qr.make();

        // Create canvas from QR
        var moduleCount = qr.getModuleCount();
        var cellSize = 6;
        var margin = 2;
        var size = moduleCount * cellSize + margin * 2 * cellSize;

        var canvas = document.createElement('canvas');
        canvas.width = size;
        canvas.height = size;
        canvas.id = 'qr-canvas-' + tableNumber;
        var ctx = canvas.getContext('2d');

        // White background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, size, size);

        // Draw QR modules
        ctx.fillStyle = '#1a1d23';
        for (var row = 0; row < moduleCount; row++) {
            for (var col = 0; col < moduleCount; col++) {
                if (qr.isDark(row, col)) {
                    ctx.fillRect(
                        (col + margin) * cellSize,
                        (row + margin) * cellSize,
                        cellSize,
                        cellSize
                    );
                }
            }
        }

        // Build card HTML
        var card = document.createElement('div');
        card.className = 'qr-card';
        card.setAttribute('data-table', tableNumber);

        var brandDiv = document.createElement('div');
        brandDiv.className = 'qr-card-brand';
        brandDiv.innerHTML = '<i class="bi bi-cup-hot-fill"></i> ' + escapeHtml(CAFE_NAME);

        var canvasWrap = document.createElement('div');
        canvasWrap.className = 'qr-canvas-wrap';
        canvasWrap.appendChild(canvas);

        var tableLabel = document.createElement('div');
        tableLabel.className = 'qr-table-label';
        tableLabel.textContent = 'Table ' + tableNumber;

        var scanText = document.createElement('div');
        scanText.className = 'qr-scan-text';
        scanText.textContent = 'Scan to Order';

        var urlText = document.createElement('div');
        urlText.className = 'qr-url-text';
        urlText.textContent = url;

        var downloadBtn = document.createElement('a');
        downloadBtn.className = 'qr-download-btn';
        downloadBtn.href = '#';
        downloadBtn.innerHTML = '<i class="bi bi-download"></i> Download';
        downloadBtn.onclick = function(e) {
            e.preventDefault();
            downloadSingleQR(tableNumber);
        };

        card.appendChild(brandDiv);
        card.appendChild(canvasWrap);
        card.appendChild(tableLabel);
        card.appendChild(scanText);
        card.appendChild(urlText);
        card.appendChild(downloadBtn);

        return card;
    }

    /**
     * Download a single QR code as PNG
     */
    function downloadSingleQR(tableNumber) {
        var canvas = document.getElementById('qr-canvas-' + tableNumber);
        if (!canvas) return;

        // Create a larger canvas with branding for download
        var dlCanvas = document.createElement('canvas');
        var dlSize = 500;
        var padding = 40;
        dlCanvas.width = dlSize;
        dlCanvas.height = dlSize + 100;
        var ctx = dlCanvas.getContext('2d');

        // White background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, dlCanvas.width, dlCanvas.height);

        // Orange header strip
        ctx.fillStyle = '#e67e22';
        ctx.fillRect(0, 0, dlCanvas.width, 6);

        // Cafe name
        ctx.fillStyle = '#1a1d23';
        ctx.font = 'bold 20px "Segoe UI", Arial, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(CAFE_NAME, dlSize / 2, 40);

        // QR code
        var qrSize = dlSize - padding * 2;
        ctx.drawImage(canvas, padding, 55, qrSize, qrSize);

        // Table label
        ctx.fillStyle = '#e67e22';
        ctx.font = 'bold 28px "Segoe UI", Arial, sans-serif';
        ctx.fillText('Table ' + tableNumber, dlSize / 2, dlSize + 40);

        // Scan text
        ctx.fillStyle = '#999999';
        ctx.font = '600 12px "Segoe UI", Arial, sans-serif';
        ctx.fillText('SCAN TO ORDER', dlSize / 2, dlSize + 65);

        // URL
        ctx.fillStyle = '#cccccc';
        ctx.font = '10px "Segoe UI", Arial, sans-serif';
        ctx.fillText(MENU_URL + '?table=' + tableNumber, dlSize / 2, dlSize + 85);

        // Download
        var link = document.createElement('a');
        link.download = 'smartcafe-table-' + tableNumber + '-qr.png';
        link.href = dlCanvas.toDataURL('image/png');
        link.click();
    }

    /**
     * Download all QR codes as individual PNGs (triggers multiple downloads)
     */
    function downloadAll() {
        var from = parseInt(document.getElementById('tableFrom').value) || 1;
        var to = parseInt(document.getElementById('tableTo').value) || TOTAL_TABLES;

        if (from > to) return;

        var delay = 0;
        for (var t = from; t <= to; t++) {
            (function(table) {
                setTimeout(function() {
                    downloadSingleQR(table);
                }, delay);
            })(t);
            delay += 300; // Stagger downloads to avoid browser blocking
        }
    }

    /**
     * Escape HTML for XSS prevention
     */
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    // Generate on page load
    document.addEventListener('DOMContentLoaded', function() {
        generateQRCodes();
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory & Stock Management</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f5f7fa;
            color: #1f2937;
            margin: 0;
            padding: 24px;
            line-height: 1.5;
        }
        .container {
            max-width: 960px;
            margin: 0 auto;
        }
        h1 {
            margin: 0 0 20px;
            font-size: 1.75rem;
            color: #111827;
        }
        h2 {
            margin: 0 0 16px;
            font-size: 1.15rem;
            color: #111827;
        }
        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            align-items: start;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        label {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: #374151;
        }
        input[type="text"],
        input[type="number"],
        select {
            padding: 8px 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.95rem;
            width: 100%;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
        }
        .field-error {
            color: #dc2626;
            font-size: 0.8rem;
            margin-top: 4px;
            min-height: 1em;
        }
        button {
            padding: 9px 16px;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        button:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }
        .btn-primary {
            background-color: #2563eb;
            color: #ffffff;
        }
        .btn-primary:hover:not(:disabled) {
            background-color: #1d4ed8;
        }
        .btn-sell {
            background-color: #059669;
            color: #ffffff;
            padding: 7px 14px;
        }
        .btn-sell:hover:not(:disabled) {
            background-color: #047857;
        }
        .btn-secondary {
            background-color: #e5e7eb;
            color: #1f2937;
            padding: 6px 12px;
        }
        .btn-secondary:hover:not(:disabled) {
            background-color: #d1d5db;
        }
        .alert {
            padding: 12px 14px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 0.92rem;
            display: none;
        }
        .alert.show {
            display: block;
        }
        .alert-success {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 14px;
        }
        .limit-control {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
        }
        .limit-control select {
            width: auto;
            padding: 5px 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 11px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.93rem;
        }
        th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        .sell-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .sell-qty {
            width: 80px !important;
            padding: 6px 8px !important;
        }
        .stock-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.85rem;
            background-color: #eff6ff;
            color: #1d4ed8;
        }
        .stock-badge.out-of-stock {
            background-color: #fef2f2;
            color: #b91c1c;
        }
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 16px;
            font-size: 0.875rem;
            color: #4b5563;
        }
        .pagination-buttons {
            display: flex;
            gap: 8px;
        }
        .empty-state {
            text-align: center;
            color: #6b7280;
            padding: 24px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Inventory &amp; Stock Management</h1>

    <div id="status-alert" class="alert" role="alert"></div>

    <!-- Add Product Form -->
    <section class="card">
        <h2>Add New Product</h2>
        <form id="add-product-form" novalidate>
            <div class="form-grid">
                <div class="form-group">
                    <label for="product-name">Product Name</label>
                    <input type="text" id="product-name" name="name" placeholder="e.g. Wireless Mouse" required>
                    <span class="field-error" data-error-for="name"></span>
                </div>
                <div class="form-group">
                    <label for="product-sku">SKU</label>
                    <input type="text" id="product-sku" name="sku" placeholder="e.g. WM-1001" required>
                    <span class="field-error" data-error-for="sku"></span>
                </div>
                <div class="form-group">
                    <label for="product-price">Price</label>
                    <input type="number" id="product-price" name="price" step="0.01" min="0" placeholder="0.00" required>
                    <span class="field-error" data-error-for="price"></span>
                </div>
                <div class="form-group">
                    <label for="product-stock">Initial Stock</label>
                    <input type="number" id="product-stock" name="stock" step="1" min="0" placeholder="0" required>
                    <span class="field-error" data-error-for="stock"></span>
                </div>
            </div>
            <div style="margin-top: 14px;">
                <button type="submit" id="add-product-btn" class="btn-primary">Add Product</button>
            </div>
        </form>
    </section>

    <!-- Inventory Table -->
    <section class="card">
        <div class="table-header">
            <h2 style="margin: 0;">Available Inventory</h2>
            <div class="limit-control">
                <label for="page-limit" style="margin: 0;">Rows per page:</label>
                <select id="page-limit">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="20">20</option>
                </select>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <table>
                <thead>
                <tr>
                    <th>Product Name</th>
                    <th>SKU</th>
                    <th>Price</th>
                    <th>Current Stock</th>
                    <th>Sell Stock</th>
                </tr>
                </thead>
                <tbody id="inventory-body">
                <tr>
                    <td colspan="5" class="empty-state">Loading products...</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <span id="pagination-info">Page 1 of 1</span>
            <div class="pagination-buttons">
                <button type="button" id="prev-page-btn" class="btn-secondary" disabled>Previous</button>
                <button type="button" id="next-page-btn" class="btn-secondary" disabled>Next</button>
            </div>
        </div>
    </section>
</div>

<script>
    const API_BASE = @json(url('/api/products'));

    let currentPage = 1;
    let currentLimit = 10;
    let lastPage = 1;

    const alertBox = document.getElementById('status-alert');
    const addForm = document.getElementById('add-product-form');
    const addBtn = document.getElementById('add-product-btn');
    const inventoryBody = document.getElementById('inventory-body');
    const limitSelect = document.getElementById('page-limit');
    const paginationInfo = document.getElementById('pagination-info');
    const prevPageBtn = document.getElementById('prev-page-btn');
    const nextPageBtn = document.getElementById('next-page-btn');

    function showAlert(message, type) {
        alertBox.textContent = message;
        alertBox.className = `alert show ${type === 'success' ? 'alert-success' : 'alert-error'}`;
    }

    function clearAlert() {
        alertBox.textContent = '';
        alertBox.className = 'alert';
    }

    function clearFieldErrors() {
        document.querySelectorAll('.field-error').forEach((el) => {
            el.textContent = '';
        });
    }

    function showFieldErrors(errors) {
        if (!errors || typeof errors !== 'object') {
            return;
        }
        Object.entries(errors).forEach(([field, messages]) => {
            const errorEl = document.querySelector(`[data-error-for="${field}"]`);
            if (errorEl && Array.isArray(messages) && messages.length > 0) {
                errorEl.textContent = messages[0];
            }
        });
    }

    function extractErrorMessage(payload, fallback) {
        if (payload && payload.errors && typeof payload.errors === 'object') {
            const firstFieldErrors = Object.values(payload.errors)[0];
            if (Array.isArray(firstFieldErrors) && firstFieldErrors.length > 0) {
                return firstFieldErrors[0];
            }
        }
        return (payload && payload.message) ? payload.message : fallback;
    }

    function updateStockBadge(badgeEl, stock) {
        badgeEl.textContent = stock;
        badgeEl.classList.toggle('out-of-stock', Number(stock) === 0);
    }

    function renderProducts(products) {
        inventoryBody.innerHTML = '';

        if (!products || products.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 5;
            cell.className = 'empty-state';
            cell.textContent = 'No products found. Add a product above to get started.';
            row.appendChild(cell);
            inventoryBody.appendChild(row);
            return;
        }

        products.forEach((product) => {
            const row = document.createElement('tr');
            row.dataset.productId = product.id;

            const nameCell = document.createElement('td');
            nameCell.textContent = product.name;

            const skuCell = document.createElement('td');
            skuCell.textContent = product.sku;

            const priceCell = document.createElement('td');
            priceCell.textContent = `$${Number(product.price).toFixed(2)}`;

            const stockCell = document.createElement('td');
            const stockBadge = document.createElement('span');
            stockBadge.className = 'stock-badge';
            updateStockBadge(stockBadge, product.stock);
            stockCell.appendChild(stockBadge);

            const actionCell = document.createElement('td');
            const sellControls = document.createElement('div');
            sellControls.className = 'sell-controls';

            const qtyInput = document.createElement('input');
            qtyInput.type = 'number';
            qtyInput.className = 'sell-qty';
            qtyInput.value = '1';
            qtyInput.min = '1';
            qtyInput.step = '1';
            qtyInput.setAttribute('aria-label', `Quantity to sell for ${product.name}`);

            const sellBtn = document.createElement('button');
            sellBtn.type = 'button';
            sellBtn.className = 'btn-sell';
            sellBtn.textContent = 'Sell';
            sellBtn.addEventListener('click', () => handleSell(product.id, qtyInput, sellBtn, stockBadge));

            sellControls.appendChild(qtyInput);
            sellControls.appendChild(sellBtn);
            actionCell.appendChild(sellControls);

            row.appendChild(nameCell);
            row.appendChild(skuCell);
            row.appendChild(priceCell);
            row.appendChild(stockCell);
            row.appendChild(actionCell);

            inventoryBody.appendChild(row);
        });
    }

    function updatePagination(meta) {
        currentPage = meta.current_page;
        lastPage = Math.max(1, meta.last_page);
        paginationInfo.textContent = `Page ${currentPage} of ${lastPage} (Total products: ${meta.total})`;
        prevPageBtn.disabled = currentPage <= 1;
        nextPageBtn.disabled = currentPage >= lastPage;
    }

    async function loadProducts(page = currentPage, limit = currentLimit) {
        try {
            const response = await fetch(`${API_BASE}?page=${page}&limit=${limit}`, {
                headers: { 'Accept': 'application/json' },
            });
            const payload = await response.json();

            if (!response.ok) {
                showAlert(extractErrorMessage(payload, 'Failed to load products.'), 'error');
                return;
            }

            renderProducts(payload.data);
            updatePagination(payload.meta);
        } catch (error) {
            showAlert('Network error while loading products.', 'error');
        }
    }

    async function handleAddProduct(event) {
        event.preventDefault();
        clearAlert();
        clearFieldErrors();

        const formData = new FormData(addForm);
        const body = {
            name: formData.get('name'),
            sku: formData.get('sku'),
            price: formData.get('price'),
            stock: formData.get('stock'),
        };

        addBtn.disabled = true;

        try {
            const response = await fetch(API_BASE, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(body),
            });

            const payload = await response.json();

            if (!response.ok) {
                showFieldErrors(payload.errors);
                showAlert(extractErrorMessage(payload, 'Failed to add product.'), 'error');
                return;
            }

            addForm.reset();
            showAlert(payload.message || 'Product created successfully.', 'success');
            await loadProducts(1, currentLimit);
        } catch (error) {
            showAlert('Network error while creating product.', 'error');
        } finally {
            addBtn.disabled = false;
        }
    }

    async function handleSell(productId, qtyInput, sellBtn, stockBadge) {
        clearAlert();
        const rawQty = qtyInput.value.trim();
        const quantity = rawQty === '' ? null : Number(rawQty);

        sellBtn.disabled = true;

        try {
            const response = await fetch(`${API_BASE}/${productId}/sell`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ quantity }),
            });

            const payload = await response.json();

            if (!response.ok) {
                if (typeof payload.available_stock === 'number') {
                    updateStockBadge(stockBadge, payload.available_stock);
                }
                showAlert(extractErrorMessage(payload, 'Failed to sell product.'), 'error');
                return;
            }

            updateStockBadge(stockBadge, payload.data.stock);
            qtyInput.value = '1';
            showAlert(payload.message || 'Sale completed successfully.', 'success');
        } catch (error) {
            showAlert('Network error while processing sale.', 'error');
        } finally {
            sellBtn.disabled = false;
        }
    }

    addForm.addEventListener('submit', handleAddProduct);

    limitSelect.addEventListener('change', () => {
        currentLimit = Number(limitSelect.value);
        loadProducts(1, currentLimit);
    });

    prevPageBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            loadProducts(currentPage - 1, currentLimit);
        }
    });

    nextPageBtn.addEventListener('click', () => {
        if (currentPage < lastPage) {
            loadProducts(currentPage + 1, currentLimit);
        }
    });

    loadProducts();
</script>
</body>
</html>

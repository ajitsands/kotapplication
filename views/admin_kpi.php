<div id="kpi" class="tab-content">
    <div style="background: var(--card-bg); border-radius: 16px; padding: 20px; margin-bottom: 20px; box-shadow: var(--shadow-sm); border: 1px solid var(--card-border); display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
            <h3 style="margin: 0; font-size: 16px; color: var(--text-color);">KPI Date Filter</h3>
            <p style="margin: 4px 0 0; font-size: 13px; color: var(--text-muted);">Filter all KPI tables by date range</p>
        </div>
        <div style="display: flex; gap: 10px; align-items: flex-end;">
            <div>
                <label style="font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 4px; font-weight: 600;">From Date</label>
                <input type="date" id="kpiStartDate" class="form-input" style="padding: 8px 12px; height: auto; background: white; border-color: var(--card-border);">
            </div>
            <div>
                <label style="font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 4px; font-weight: 600;">To Date</label>
                <input type="date" id="kpiEndDate" class="form-input" style="padding: 8px 12px; height: auto; background: white; border-color: var(--card-border);">
            </div>
            <button type="button" class="btn-primary" onclick="loadKPIs()" style="padding: 8px 20px; height: auto;">Filter Data</button>
        </div>
    </div>

    <div class="panel-card" style="margin-bottom: 20px;">
        <div class="panel-title">Product-wise Sales Report</div>
        <table class="dataTable" id="productSalesTable">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Items Sold (Qty)</th>
                    <th>Menu Price</th>
                    <th>Recipe Cost</th>
                    <th>Total Revenue</th>
                    <th>Total Expense</th>
                    <th>Total Profit</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="panel-card" style="margin-bottom: 20px;">
        <div class="panel-title">Item Profitability & Margins (Static Potential)</div>
        <table class="dataTable" id="profitabilityTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Selling Price</th>
                    <th>Recipe Cost</th>
                    <th>Profit</th>
                    <th>Margin (%)</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="grid-2">
        <div class="panel-card">
            <div class="panel-title">Chef Consumption KPI</div>
            <table class="dataTable" id="chefKpiTable">
                <thead>
                    <tr>
                        <th>Chef Name</th>
                        <th>KOT Items Prepped</th>
                        <th>Ingredients Consumed</th>
                        <th>Total Consumed Cost</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="panel-card">
            <div class="panel-title">Supplier Price Comparison</div>
            <table class="dataTable" id="supplierKpiTable">
                <thead>
                    <tr>
                        <th>Ingredient</th>
                        <th>Supplier</th>
                        <th>Avg Unit Price</th>
                        <th>Total Supplied</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Chef Details Modal -->
<div class="modal" id="chefDetailsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(11, 15, 25, 0.85); backdrop-filter: blur(10px); z-index: 2000; align-items: center; justify-content: center;">
    <div class="modal-content" style="max-width: 1000px; width: 95%; padding: 0; background: var(--card-bg); border: 1px solid var(--card-border); border-radius: 24px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.2);">
        <div style="background: var(--primary-grad); padding: 25px 30px; display: flex; align-items: center; justify-content: space-between;">
            <h3 id="chefDetailsTitle" style="margin: 0; color: white; display: flex; align-items: center; gap: 10px; font-size: 18px;">Chef Consumption Details</h3>
            <button type="button" onclick="$('#chefDetailsModal').fadeOut()" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div style="padding: 30px; max-height: 70vh; overflow-y: auto;">
            <table class="dataTable" id="chefDetailsTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Order/KOT</th>
                        <th>Item Prepared</th>
                        <th>Qty</th>
                        <th>Selling Price</th>
                        <th>Total Revenue</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
let productSalesTable, profitTable, chefTable, supplierTable, chefDetailsTable;

function loadKPIs() {
    const today = new Date().toISOString().split('T')[0];
    if (!$('#kpiStartDate').val()) $('#kpiStartDate').val(today);
    if (!$('#kpiEndDate').val()) $('#kpiEndDate').val(today);

    if ($.fn.DataTable.isDataTable('#profitabilityTable')) {
        $('#productSalesTable').DataTable().ajax.reload();
        $('#profitabilityTable').DataTable().ajax.reload();
        $('#chefKpiTable').DataTable().ajax.reload();
        $('#supplierKpiTable').DataTable().ajax.reload();
        return;
    }

    productSalesTable = $('#productSalesTable').DataTable({
        ajax: {
            url: '/admin/reports/kpi/product-sales',
            data: function(d) {
                d.startDate = $('#kpiStartDate').val();
                d.endDate = $('#kpiEndDate').val();
            }
        },
        columns: [
            { data: 'product_name' },
            { data: 'total_sold' },
            { data: 'menu_price', render: data => parseFloat(data).toFixed(window.PRICE_DECIMALS || 3) },
            { data: 'recipe_cost', render: data => parseFloat(data).toFixed(window.PRICE_DECIMALS || 3) },
            { data: 'total_revenue', render: data => parseFloat(data).toFixed(window.PRICE_DECIMALS || 3) },
            { data: 'total_expense', render: data => parseFloat(data).toFixed(window.PRICE_DECIMALS || 3) },
            { 
                data: 'total_profit', 
                render: function(data) {
                    let val = parseFloat(data);
                    return `<strong style="color:${val >= 0 ? 'var(--accent-green)' : 'var(--accent-red)'}">${val.toFixed(window.PRICE_DECIMALS || 3)}</strong>`;
                }
            }
        ]
    });

    profitTable = $('#profitabilityTable').DataTable({
        ajax: {
            url: '/admin/reports/profitability',
            data: function(d) {
                d.startDate = $('#kpiStartDate').val();
                d.endDate = $('#kpiEndDate').val();
            }
        },
        columns: [
            { data: 'product_name' },
            { data: 'selling_price', render: data => parseFloat(data).toFixed(window.PRICE_DECIMALS || 3) },
            { data: 'total_cost', render: data => parseFloat(data).toFixed(window.PRICE_DECIMALS || 3) },
            { 
                data: 'profit', 
                render: function(data) {
                    let val = parseFloat(data);
                    return `<strong style="color:${val >= 0 ? 'var(--accent-green)' : 'var(--accent-red)'}">${val.toFixed(window.PRICE_DECIMALS || 3)}</strong>`;
                }
            },
            { 
                data: 'margin_percent',
                render: function(data) {
                    let val = parseFloat(data);
                    return `<span style="background:${val > 50 ? 'rgba(16, 185, 129, 0.1)' : (val > 0 ? 'rgba(245, 158, 11, 0.1)' : 'rgba(239, 68, 68, 0.1)')}; padding: 4px 8px; border-radius: 8px;">${val.toFixed(2)}%</span>`;
                }
            }
        ]
    });

    chefTable = $('#chefKpiTable').DataTable({
        ajax: {
            url: '/admin/reports/kpi/chef',
            data: function(d) {
                d.startDate = $('#kpiStartDate').val();
                d.endDate = $('#kpiEndDate').val();
            }
        },
        columns: [
            { data: 'chef_name' },
            { data: 'total_transactions' },
            { data: 'total_items_consumed', render: data => parseFloat(data).toFixed(2) },
            { data: 'total_consumed_cost', render: data => parseFloat(data || 0).toFixed(window.PRICE_DECIMALS || 3) },
            { 
                data: null, 
                orderable: false,
                render: function(data, type, row) {
                    return `<button class="btn-action btn-print" style="margin:0 auto; display:flex; justify-content:center; align-items:center; padding: 6px;" title="View Details" onclick="viewChefDetails(${row.chef_id}, '${row.chef_name.replace(/'/g, "\\'")}')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>`;
                }
            }
        ]
    });

    supplierTable = $('#supplierKpiTable').DataTable({
        ajax: {
            url: '/admin/reports/kpi/supplier',
            data: function(d) {
                d.startDate = $('#kpiStartDate').val();
                d.endDate = $('#kpiEndDate').val();
            }
        },
        columns: [
            { data: 'item_name' },
            { data: 'supplier_name' },
            { data: 'avg_price', render: data => parseFloat(data).toFixed(window.PRICE_DECIMALS || 3) },
            { data: 'total_supplied', render: data => parseFloat(data).toFixed(2) }
        ]
    });
}

function viewChefDetails(chefId, chefName) {
    document.getElementById('chefDetailsTitle').innerText = chefName + ' - Consumption Details';
    
    if ($.fn.DataTable.isDataTable('#chefDetailsTable')) {
        $('#chefDetailsTable').DataTable().destroy();
    }
    
    chefDetailsTable = $('#chefDetailsTable').DataTable({
        ajax: {
            url: '/admin/reports/kpi/chef/details/' + chefId,
            data: function(d) {
                d.startDate = $('#kpiStartDate').val();
                d.endDate = $('#kpiEndDate').val();
            }
        },
        columns: [
            { 
                data: null,
                render: function(data, type, row) {
                    return `Order #${row.order_number} (KOT ${row.kot_number})`;
                }
            },
            { data: 'item_name' },
            { data: 'quantity' },
            { data: 'selling_price', render: data => parseFloat(data || 0).toFixed(window.PRICE_DECIMALS || 3) },
            { data: 'total_revenue', render: function(data) {
                return `<strong style="color:var(--accent-green)">${parseFloat(data || 0).toFixed(window.PRICE_DECIMALS || 3)}</strong>`;
            }},
            { data: 'time', render: data => new Date(data).toLocaleString() }
        ],
        order: [[5, 'desc']]
    });
    
    $('#chefDetailsModal').css('display', 'flex').hide().fadeIn();
}
</script>

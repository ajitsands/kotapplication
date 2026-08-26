<div id="kpi" class="tab-content">
    <div class="panel-card" style="margin-bottom: 20px;">
        <div class="panel-title">Item Profitability & Margins</div>
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
let profitTable, chefTable, supplierTable, chefDetailsTable;

function loadKPIs() {
    if ($.fn.DataTable.isDataTable('#profitabilityTable')) {
        $('#profitabilityTable').DataTable().ajax.reload();
        $('#chefKpiTable').DataTable().ajax.reload();
        $('#supplierKpiTable').DataTable().ajax.reload();
        return;
    }

    profitTable = $('#profitabilityTable').DataTable({
        ajax: '/admin/reports/profitability',
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
        ajax: '/admin/reports/kpi/chef',
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
        ajax: '/admin/reports/kpi/supplier',
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
        ajax: '/admin/reports/kpi/chef/details/' + chefId,
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

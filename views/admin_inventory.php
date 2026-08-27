<div id="inventory" class="tab-content">
    <div class="panel-card">
        <div class="panel-title">
            <span>Inventory Stock</span>
            <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
                <button class="btn-primary" onclick="$('#importItemsModal').css('display', 'flex').hide().fadeIn();" style="background: rgba(99, 102, 241, 0.1); color: #6366f1; border: 1px solid rgba(99, 102, 241, 0.2); box-shadow: none; font-size: 13px; padding: 6px 12px; height: auto;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px; vertical-align: middle;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Import Items
                </button>
                <button class="btn-primary" onclick="$('#importStockModal').css('display', 'flex').hide().fadeIn();" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); box-shadow: none; font-size: 13px; padding: 6px 12px; height: auto;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px; vertical-align: middle;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Import Stock
                </button>
                <button class="btn-primary" onclick="fetchSuppliersForSelect(); $('#inventoryForm')[0].reset(); $('#inv_id').val(''); $('#inventoryModalTitle').html('<svg width=\'22\' height=\'22\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'><path d=\'M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z\'></path><line x1=\'7\' y1=\'7\' x2=\'7.01\' y2=\'7\'></line></svg> Add Inventory Item'); $('#inventoryModal').css('display', 'flex').hide().fadeIn();" style="margin-left: 10px;">Add New Item</button>
                <button class="btn-primary" style="background:#10b981;" onclick="fetchSuppliersForSelect(); $('#stock_items_container').empty(); addStockItemRow(); $('#stockModal').css('display', 'flex').hide().fadeIn();">Add Stock (Purchase)</button>
            </div>
        </div>
        <table class="dataTable" id="inventoryTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Stock</th>
                    <th>Min Qty</th>
                    <th>Unit</th>
                    <th>Buying Price</th>
                    <th>Selling Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Item Modal -->
<div class="modal" id="inventoryModal">
    <div class="modal-content" style="max-width: 600px; padding: 0; overflow: hidden;">
        <div style="background: var(--primary-grad); padding: 25px 30px; display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin: 0; color: white; display: flex; align-items: center; gap: 10px; font-size: 18px;" id="inventoryModalTitle">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                </svg>
                Add Inventory Item
            </h3>
            <button type="button" onclick="$('#inventoryModal').fadeOut();" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <form id="inventoryForm" style="padding: 30px;">
            <input type="hidden" name="id" id="inv_id">
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="font-weight: 600; color: var(--text-color);">Item Name <span style="color: #ef4444;">*</span></label>
                <input type="text" name="name" id="inv_name" class="form-input" placeholder="e.g. Tomato, Burger Bun, Milk" required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; color: var(--text-color);">Unit of Measurement</label>
                    <select name="unit" id="inv_unit" class="form-input" style="cursor: pointer;">
                        <?php
                            $customUnits = explode(',', $settings['custom_units'] ?? 'Nos, Box, Packet, Gram, KG, Litre, ML');
                            foreach ($customUnits as $u) {
                                $u = trim($u);
                                if ($u !== '') {
                                    echo "<option value=\"" . htmlspecialchars($u) . "\">" . htmlspecialchars($u) . "</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; color: var(--text-color);">Minimum Qty Level <span style="color: var(--text-muted); font-weight: 400; font-size: 12px;">(Optional)</span></label>
                    <input type="number" step="0.001" name="min_stock_level" id="inv_min_stock" class="form-input" placeholder="e.g. 10.0">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; color: var(--text-color);">Buying Price (Per Unit)</label>
                    <div style="position: relative;">
                        <div style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: bold; color: var(--text-muted); font-size: 13px;"><?= htmlspecialchars($settings['currency_code'] ?? '$') ?></div>
                        <input type="number" step="0.001" name="buying_price_per_unit" id="inv_buy" class="form-input" style="padding-left: 48px;" placeholder="0.00" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; color: var(--text-color);">Selling Price</label>
                    <div style="position: relative;">
                        <div style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: bold; color: var(--text-muted); font-size: 13px;"><?= htmlspecialchars($settings['currency_code'] ?? '$') ?></div>
                        <input type="number" step="0.001" name="selling_price" id="inv_sell" class="form-input" style="padding-left: 48px;" placeholder="0.00" required>
                    </div>
                </div>
            </div>

            
            <div style="display: flex; gap: 15px; justify-content: flex-end; border-top: 1px solid var(--card-border); padding-top: 25px;">
                <button type="button" class="modal-close" onclick="$('#inventoryModal').fadeOut();" style="margin: 0; padding: 12px 25px; border-radius: 12px; font-weight: 600;">Cancel</button>
                <button type="submit" class="btn-primary" style="margin: 0; padding: 12px 30px; border-radius: 12px; font-weight: 600; font-size: 15px; box-shadow: 0 10px 25px rgba(99,102,241,0.3);">Save Item Details</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Stock Modal -->
<div class="modal" id="stockModal">
    <div class="modal-content" style="width: 90%; max-width: 800px; padding: 0; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 25px 30px; display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin: 0; color: white; display: flex; align-items: center; gap: 10px; font-size: 18px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Register Purchase (Add Stock)
            </h3>
            <button type="button" onclick="$('#stockModal').fadeOut();" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <form id="stockForm" style="padding: 25px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; color: var(--text-color);">Supplier (Optional)</label>
                    <select name="supplier_id" id="stock_supplier_id" class="form-input" style="cursor: pointer;">
                        <option value="">-- No Supplier --</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; color: var(--text-color);">Invoice Number / Notes</label>
                    <input type="text" name="notes" class="form-input" placeholder="e.g. INV-2023-001">
                </div>
            </div>
            
            <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                <label class="form-label" style="font-weight: 600; color: var(--text-color); margin: 0;">Items Received <span style="color: #ef4444;">*</span></label>
                <button type="button" class="btn-primary" style="padding: 8px 16px; font-size: 13px; height: auto;" onclick="addStockItemRow()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px; vertical-align: middle;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add Item
                </button>
            </div>
            
            <div style="max-height: 350px; overflow-y: auto; margin-bottom: 20px; border: 1px solid var(--card-border); border-radius: 8px; padding: 15px; background: #f9fafb;" id="stock_items_container">
                <!-- Dynamic rows here -->
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: flex-end; border-top: 1px solid var(--card-border); padding-top: 25px;">
                <button type="button" class="modal-close" onclick="$('#stockModal').fadeOut();" style="margin: 0; padding: 12px 25px; border-radius: 12px; font-weight: 600;">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #10b981; margin: 0; padding: 12px 30px; border-radius: 12px; font-weight: 600; font-size: 15px; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);">Confirm Stock Entry</button>
            </div>
        </form>
    </div>
</div>

<!-- Import Items Modal -->
<div class="modal" id="importItemsModal">
    <div class="modal-content" style="max-width: 500px; padding: 0; overflow: hidden;">
        <div style="background: var(--primary-grad); padding: 25px 30px; display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin: 0; color: white; display: flex; align-items: center; gap: 10px; font-size: 18px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                Import Inventory Items
            </h3>
            <button type="button" onclick="$('#importItemsModal').fadeOut();" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <form id="importItemsForm" style="padding: 30px;" enctype="multipart/form-data">
            <div style="margin-bottom: 20px; text-align: center;">
                <a href="/admin/inventory/items/template" target="_blank" class="btn-secondary" style="display: inline-block; text-decoration: none;">Download CSV Template</a>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">Download the template, fill it with your items, and upload it below. Existing items with the same name will be updated.</p>
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-label" style="font-weight: 600; color: var(--text-color);">Upload CSV File <span style="color: #ef4444;">*</span></label>
                <input type="file" name="csv_file" class="form-input" accept=".csv" required style="padding: 10px;">
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: flex-end; border-top: 1px solid var(--card-border); padding-top: 25px;">
                <button type="button" class="modal-close" onclick="$('#importItemsModal').fadeOut();" style="margin: 0; padding: 12px 25px; border-radius: 12px; font-weight: 600;">Cancel</button>
                <button type="submit" class="btn-primary" style="margin: 0; padding: 12px 30px; border-radius: 12px; font-weight: 600; font-size: 15px; box-shadow: 0 10px 25px rgba(59,130,246,0.3);">Upload & Import</button>
            </div>
        </form>
    </div>
</div>

<!-- Import Stock Modal -->
<div class="modal" id="importStockModal">
    <div class="modal-content" style="max-width: 500px; padding: 0; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 25px 30px; display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin: 0; color: white; display: flex; align-items: center; gap: 10px; font-size: 18px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                Import Stock Additions
            </h3>
            <button type="button" onclick="$('#importStockModal').fadeOut();" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <form id="importStockForm" style="padding: 30px;" enctype="multipart/form-data">
            <div style="margin-bottom: 20px; text-align: center;">
                <a href="/admin/inventory/stock/template" target="_blank" class="btn-secondary" style="display: inline-block; text-decoration: none;">Download CSV Template</a>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">Download the template, fill it with your purchase details, and upload it below. Stock will be added to the matching Item Names.</p>
            </div>
            
            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-label" style="font-weight: 600; color: var(--text-color);">Upload CSV File <span style="color: #ef4444;">*</span></label>
                <input type="file" name="csv_file" class="form-input" accept=".csv" required style="padding: 10px;">
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: flex-end; border-top: 1px solid var(--card-border); padding-top: 25px;">
                <button type="button" class="modal-close" onclick="$('#importStockModal').fadeOut();" style="margin: 0; padding: 12px 25px; border-radius: 12px; font-weight: 600;">Cancel</button>
                <button type="submit" class="btn-primary" style="background:#10b981; margin: 0; padding: 12px 30px; border-radius: 12px; font-weight: 600; font-size: 15px; box-shadow: 0 10px 25px rgba(16,185,129,0.3);">Upload & Import</button>
            </div>
        </form>
    </div>
</div>

<!-- History Modal -->
<div class="modal" id="historyModal">
    <div class="modal-content" style="width: 90%; max-width: 1000px; padding: 0; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); padding: 25px 30px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h3 style="margin: 0 0 5px 0; color: white; display: flex; align-items: center; gap: 10px; font-size: 18px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    Stock History
                </h3>
                <p style="margin: 0; color: rgba(255,255,255,0.8); font-size: 13px;">Item: <strong id="historyItemName" style="color: white; font-weight: 700;"></strong></p>
            </div>
            <button type="button" onclick="$('#historyModal').fadeOut();" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <div style="padding: 20px; background: rgba(139, 92, 246, 0.05); border-bottom: 1px solid var(--card-border);">
            <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                <div>
                    <label style="font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 4px; font-weight: 600;">From Date</label>
                    <input type="date" id="historyStartDate" class="form-input" style="padding: 6px 12px; height: auto; background: white; border-color: var(--card-border);">
                </div>
                <div>
                    <label style="font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 4px; font-weight: 600;">To Date</label>
                    <input type="date" id="historyEndDate" class="form-input" style="padding: 6px 12px; height: auto; background: white; border-color: var(--card-border);">
                </div>
                <div style="align-self: flex-end;">
                    <button type="button" class="btn-primary" onclick="reloadHistoryTable()" style="padding: 6px 16px; height: auto; background: #8b5cf6; box-shadow: 0 4px 10px rgba(139,92,246,0.2);">Filter</button>
                    <button type="button" class="btn-secondary" onclick="$('#historyStartDate').val(''); $('#historyEndDate').val(''); reloadHistoryTable();" style="padding: 6px 16px; height: auto; margin-left: 5px; background: rgba(0,0,0,0.05); color: var(--text-color); border: 1px solid rgba(0,0,0,0.1); border-radius: 8px; font-weight: 600;">Clear</button>
                </div>
                <div style="flex-grow: 1; display: flex; justify-content: flex-end; gap: 20px;">
                    <div style="background: white; padding: 8px 15px; border-radius: 8px; text-align: right; border: 1px solid var(--card-border); box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                        <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Opening Stock</div>
                        <div id="historyOpeningStock" style="font-size: 18px; font-weight: bold; color: var(--text-color);">-</div>
                    </div>
                    <div style="background: white; padding: 8px 15px; border-radius: 8px; text-align: right; border: 1px solid var(--card-border); box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                        <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Closing Stock</div>
                        <div id="historyClosingStock" style="font-size: 18px; font-weight: bold; color: var(--accent-green);">-</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="padding: 20px;">
            <table class="dataTable" id="historyTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Price/Unit</th>
                        <th>Expiry</th>
                        <th>Supplier/Chef</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Exp History Modal -->
<div class="modal" id="expHistoryModal">
    <div class="modal-content" style="width: 90%; max-width: 800px; padding: 0; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); padding: 25px 30px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h3 style="margin: 0 0 5px 0; color: white; display: flex; align-items: center; gap: 10px; font-size: 18px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    Expiry History
                </h3>
                <p style="margin: 0; color: rgba(255,255,255,0.8); font-size: 13px;">Item: <strong id="expHistoryItemName" style="color: white; font-weight: 700;"></strong></p>
            </div>
            <button type="button" onclick="$('#expHistoryModal').fadeOut();" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <div style="padding: 20px;">
            <table class="dataTable" id="expHistoryTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Purchase Date</th>
                        <th>Supplier</th>
                        <th>Qty</th>
                        <th>Expiry Date</th>
                        <th>Days to Expire</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
let inventoryTable;
function loadInventory() {
    if ($.fn.DataTable.isDataTable('#inventoryTable')) {
        $('#inventoryTable').DataTable().ajax.reload();
        populateItemSelect();
        return;
    }
    inventoryTable = $('#inventoryTable').DataTable({
        ajax: '/admin/inventory/items/list',
        columns: [
            { data: 'id' },
            { data: 'name' },
            { 
                data: 'current_stock',
                render: function(data, type, row) {
                    let stock = parseFloat(data);
                    let minStock = parseFloat(row.min_stock_level || 0);
                    
                    if (minStock > 0) {
                        let percent = stock / minStock;
                        if (percent <= 0.25) {
                            return `<span style="background: rgba(239, 68, 68, 0.1); color: var(--accent-red); padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 4px;" title="Critical Low Stock! (<= 25% of Minimum)">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                        ${stock}
                                    </span>`;
                        } else if (percent <= 0.5) {
                            return `<span style="background: rgba(249, 115, 22, 0.1); color: #f97316; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 4px;" title="Low Stock! (<= 50% of Minimum)">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                        ${stock}
                                    </span>`;
                        } else if (percent <= 1.0) {
                            return `<span style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 4px;" title="Below Minimum Level">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                        ${stock}
                                    </span>`;
                        } else {
                            return `<span style="color: var(--accent-green); font-weight: 600;">${stock}</span>`;
                        }
                    } else {
                        if (stock <= 0) {
                            return `<span style="background: rgba(239, 68, 68, 0.1); color: var(--accent-red); padding: 4px 8px; border-radius: 6px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 4px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                        ${stock}
                                    </span>`;
                        }
                        return `<span style="color: var(--accent-green); font-weight: 600;">${stock}</span>`;
                    }
                }
            },
            {
                data: 'min_stock_level',
                render: function(data) {
                    let minStock = parseFloat(data || 0);
                    return minStock > 0 ? `<span style="color: var(--text-muted); font-size: 13px;">${minStock}</span>` : '-';
                }
            },
            { data: 'unit' },
            { data: 'buying_price_per_unit' },
            { data: 'selling_price' },
            {
                data: null,
                render: function(data, type, row) {
                    return `<button class="btn-delete" style="background:#0ea5e9; color:white; border:none;" onclick="viewExpHistory(${row.id}, '${row.name.replace(/'/g, "\\'")}')">Exp History</button>
                            <button class="btn-delete" style="background:#8b5cf6; color:white; border:none;" onclick="viewHistory(${row.id}, '${row.name.replace(/'/g, "\\'")}')">History</button>
                            <button class="btn-delete" style="background:var(--primary-grad); color:white; border:none;" onclick="editInventoryItem(${row.id})">Edit</button>
                            <button class="btn-delete" onclick="deleteInventoryItem(${row.id})">Delete</button>`;
                }
            }
        ]
    });
    populateItemSelect();
}

function populateItemSelect() {
    $.get('/admin/inventory/items/list', function(res) {
        let r = typeof res === 'string' ? JSON.parse(res) : res;
        window.inventoryItemsList = r.data; // Store for dynamic rows
    });
}

function addStockItemRow() {
    if (!window.inventoryItemsList) return;
    
    let options = '<option value="">Select Item</option>';
    window.inventoryItemsList.forEach(function(item) {
        options += `<option value="${item.id}" data-unit="${item.unit}">${item.name}</option>`;
    });

    let rowHtml = `
        <div class="stock-item-row" style="display: grid; grid-template-columns: 2fr 1fr 1.5fr 1.5fr 40px; gap: 15px; margin-bottom: 15px; align-items: end; background: white; padding: 15px; border-radius: 8px; border: 1px solid var(--card-border); box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Item <span style="color: #ef4444;">*</span></label>
                <select name="inventory_item_id[]" class="form-input stock-item-select" style="cursor: pointer;" required>
                    ${options}
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Qty <span style="color: #ef4444;">*</span></label>
                <input type="number" step="0.001" name="quantity[]" class="form-input" placeholder="0" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Total Batch Cost <span style="color: #ef4444;">*</span></label>
                <input type="number" step="0.001" name="unit_price[]" class="form-input" placeholder="0.00" required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Expiry Date (Opt)</label>
                <input type="date" name="expiry_date[]" class="form-input" style="height: auto; padding: 8px 12px;">
            </div>
            <button type="button" class="btn-delete" style="height: 38px; width: 38px; padding: 0; display: flex; align-items: center; justify-content: center; margin: 0; border-radius: 8px;" onclick="$(this).closest('.stock-item-row').remove();">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    `;
    $('#stock_items_container').append(rowHtml);
}

function fetchSuppliersForSelect() {
    $.get('/admin/suppliers/list', function(res) {
        let r = typeof res === 'string' ? JSON.parse(res) : res;
        let options = '<option value="">-- No Supplier --</option>';
        r.data.forEach(function(s) {
            options += `<option value="${s.id}">${s.name}</option>`;
        });
        $('#stock_supplier_id').html(options);
    });
}

$('#inventoryForm').on('submit', function(e) {
    e.preventDefault();
    $.post('/admin/inventory/items', $(this).serialize(), function(res) {
        if(typeof Ladda !== 'undefined') Ladda.stopAll();
        let response = typeof res === 'string' ? JSON.parse(res) : res;
        if (response.status === 'success') {
            $('#inventoryModal').fadeOut();
            loadInventory();
            $('#inventoryForm')[0].reset();
            $('#inv_id').val('');
            
            Swal.fire({
                icon: 'success',
                title: 'Saved',
                text: 'Item saved successfully',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            alert(response.message);
        }
    });
});

$('#stockForm').on('submit', function(e) {
    e.preventDefault();
    if ($('#stock_items_container .stock-item-row').length === 0) {
        Swal.fire({ icon: 'warning', title: 'No items', text: 'Please add at least one item to stock.' });
        return;
    }
    
    $.post('/admin/inventory/stock/add', $(this).serialize(), function(res) {
        if(typeof Ladda !== 'undefined') Ladda.stopAll();
        let response = typeof res === 'string' ? JSON.parse(res) : res;
        if (response.status === 'success') {
            $('#stockModal').fadeOut();
            loadInventory();
            $('#stockForm')[0].reset();
            $('#stock_items_container').empty();
            
            Swal.fire({
                icon: 'success',
                title: 'Stock Added',
                text: 'Stock registered successfully',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            alert(response.message);
        }
    });
});



function editInventoryItem(id) {
    let row = inventoryTable.row(function(idx, data) { return data.id == id; }).data();
    if (row) {
        $('#inv_id').val(row.id);
        $('#inv_name').val(row.name);
        $('#inv_unit').val(row.unit);
        $('#inv_buy').val(row.buying_price_per_unit);
        $('#inv_sell').val(row.selling_price);
        $('#inv_min_stock').val(row.min_stock_level || 0);
        $('#inventoryModalTitle').text('Edit Inventory Item');
        $('#inventoryModal').css('display', 'flex').hide().fadeIn();
    }
}

function deleteInventoryItem(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this item?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/admin/inventory/items/delete/' + id, function(res) {
                loadInventory();
                Swal.fire('Deleted!', 'The item has been deleted.', 'success');
            });
        }
    });
}



$('#importItemsForm').on('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    let btn = $(this).find('button[type="submit"]');
    let originalText = btn.text();
    btn.text('Importing...').prop('disabled', true);
    
    $.ajax({
        url: '/admin/inventory/items/import',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            let response = typeof res === 'string' ? JSON.parse(res) : res;
            btn.text(originalText).prop('disabled', false);
            if (response.status === 'success') {
                $('#importItemsModal').fadeOut();
                $('#importItemsForm')[0].reset();
                loadInventory();
                Swal.fire({ icon: 'success', title: 'Import Complete', text: response.message });
            } else {
                Swal.fire({ icon: 'error', title: 'Import Failed', text: response.message });
            }
        },
        error: function() {
            btn.text(originalText).prop('disabled', false);
            Swal.fire({ icon: 'error', title: 'Import Failed', text: 'Server error occurred' });
        }
    });
});

$('#importStockForm').on('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    let btn = $(this).find('button[type="submit"]');
    let originalText = btn.text();
    btn.text('Importing...').prop('disabled', true);
    
    $.ajax({
        url: '/admin/inventory/stock/import',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            let response = typeof res === 'string' ? JSON.parse(res) : res;
            btn.text(originalText).prop('disabled', false);
            if (response.status === 'success') {
                $('#importStockModal').fadeOut();
                $('#importStockForm')[0].reset();
                loadInventory();
                Swal.fire({ icon: 'success', title: 'Import Complete', text: response.message });
            } else {
                Swal.fire({ icon: 'error', title: 'Import Failed', text: response.message });
            }
        },
        error: function() {
            btn.text(originalText).prop('disabled', false);
            Swal.fire({ icon: 'error', title: 'Import Failed', text: 'Server error occurred' });
        }
    });
});

let historyTable;
let currentHistoryItemId = null;

function viewHistory(id, name) {
    currentHistoryItemId = id;
    $('#historyItemName').text(name);
    $('#historyStartDate').val('');
    $('#historyEndDate').val('');
    $('#historyOpeningStock').text('-');
    $('#historyClosingStock').text('-');

    if ($.fn.DataTable.isDataTable('#historyTable')) {
        $('#historyTable').DataTable().destroy();
    }
    historyTable = $('#historyTable').DataTable({
        ajax: {
            url: '/admin/inventory/transactions/' + id,
            data: function(d) {
                d.start_date = $('#historyStartDate').val();
                d.end_date = $('#historyEndDate').val();
            },
            dataSrc: function(json) {
                $('#historyOpeningStock').text(json.opening_stock !== undefined ? json.opening_stock : '-');
                $('#historyClosingStock').text(json.closing_stock !== undefined ? json.closing_stock : '-');
                return json.data;
            }
        },
        order: [[0, 'desc']],
        pageLength: 5,
        lengthMenu: [5, 10, 25, 50],
        columns: [
            { 
                data: 'created_at',
                render: function(data) {
                    if(!data) return '-';
                    return new Date(data).toLocaleString();
                }
            },
            { 
                data: 'transaction_type',
                render: function(data) {
                    let map = {
                        'add_stock': '<span style="color:var(--accent-green); font-weight:600;">+ Purchase</span>',
                        'consume_kot': '<span style="color:var(--accent-red); font-weight:600;">- KOT</span>',
                        'adjustment': '<span style="color:#f59e0b; font-weight:600;">Adjustment</span>',
                        'damage': '<span style="color:#ef4444; font-weight:600;">Damage</span>'
                    };
                    return map[data] || data;
                }
            },
            { 
                data: 'quantity',
                render: function(data) {
                    return `<strong style="color:${data > 0 ? 'var(--accent-green)' : 'var(--accent-red)'}">${data > 0 ? '+'+data : data}</strong>`;
                }
            },
            { 
                data: 'unit_price',
                render: function(data) {
                    return data ? parseFloat(data).toFixed(window.PRICE_DECIMALS || 3) : '-';
                }
            },
            {
                data: 'expiry_date',
                render: function(data) {
                    if(!data) return '-';
                    // Format date as dd-mm-yyyy
                    let parts = data.split('-');
                    let formatted = parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : data;
                    return `<span style="font-size: 13px;">${formatted}</span>`;
                }
            },
            { 
                data: null,
                render: function(data, type, row) {
                    return row.supplier_name || row.chef_name || '-';
                }
            },
            { data: 'notes', defaultContent: '-' }
        ]
    });
    $('#historyModal').css('display', 'flex').hide().fadeIn();
}

function reloadHistoryTable() {
    if (historyTable) {
        historyTable.ajax.reload();
    }
}

let expHistoryTable;
function viewExpHistory(id, name) {
    $('#expHistoryItemName').text(name);
    
    if ($.fn.DataTable.isDataTable('#expHistoryTable')) {
        $('#expHistoryTable').DataTable().destroy();
    }
    
    expHistoryTable = $('#expHistoryTable').DataTable({
        ajax: {
            url: '/admin/inventory/exp-history/' + id,
            dataSrc: 'data'
        },
        order: [[3, 'asc']], // order by expiry date ascending
        pageLength: 5,
        lengthMenu: [5, 10, 25],
        columns: [
            { 
                data: 'created_at',
                render: function(data) {
                    if(!data) return '-';
                    return new Date(data).toLocaleString();
                }
            },
            { data: 'supplier_name', defaultContent: '-' },
            { 
                data: 'quantity',
                render: function(data) {
                    return `<strong style="color:var(--accent-green)">+${data}</strong>`;
                }
            },
            { 
                data: 'expiry_date',
                render: function(data) {
                    if(!data) return '-';
                    let parts = data.split('-');
                    let formatted = parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : data;
                    return `<span style="font-weight: 600;">${formatted}</span>`;
                }
            },
            {
                data: 'expiry_date',
                render: function(data) {
                    if (!data) return '-';
                    let expDate = new Date(data);
                    let today = new Date();
                    today.setHours(0,0,0,0);
                    let diffTime = expDate - today;
                    let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    
                    if (diffDays < 0) {
                        return `<span style="color: #ef4444; font-weight: bold;">Expired (${Math.abs(diffDays)} days ago)</span>`;
                    } else if (diffDays === 0) {
                        return `<span style="color: #ef4444; font-weight: bold;">Expires Today</span>`;
                    } else if (diffDays <= 7) {
                        return `<span style="color: #f97316; font-weight: bold;">${diffDays} Days</span>`;
                    } else {
                        return `<span style="color: var(--accent-green); font-weight: 600;">${diffDays} Days</span>`;
                    }
                }
            },
            { data: 'notes', defaultContent: '-' }
        ]
    });
    $('#expHistoryModal').css('display', 'flex').hide().fadeIn();
}
</script>

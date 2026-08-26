<!-- Recipe Modal -->
<div class="modal" id="recipeModal">
    <div class="modal-content" style="max-width: 650px; padding: 0; overflow: hidden; text-align: left;">
        <div style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); padding: 25px 30px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h3 style="margin: 0 0 5px 0; color: white; display: flex; align-items: center; gap: 10px; font-size: 18px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                    Recipe Configuration (BOM)
                </h3>
                <p style="margin: 0; color: rgba(255,255,255,0.8); font-size: 13px;">Product: <strong id="recipeProductName" style="color: white; font-weight: 700;"></strong></p>
            </div>
            <button type="button" onclick="$('#recipeModal').fadeOut();" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <form id="recipeForm" style="padding: 30px;">
            <input type="hidden" name="product_id" id="recipe_product_id">
            
            <div style="display: grid; grid-template-columns: 2fr 1fr 40px; gap: 10px; margin-bottom: 10px; padding: 0 10px;">
                <label style="font-weight: 600; color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Raw Material (Ingredient)</label>
                <label style="font-weight: 600; color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Qty Required</label>
            </div>
            
            <div id="recipeItemsContainer" style="max-height: 350px; overflow-y: auto; padding-right: 5px; padding-top: 12px; margin-bottom: 20px;">
                <!-- Recipe items injected here -->
            </div>
            
            <button type="button" class="btn-primary" style="background: rgba(99, 102, 241, 0.05); border: 1.5px dashed rgba(99, 102, 241, 0.3); color: #6366f1; width: 100%; margin-bottom: 20px; padding: 12px; font-weight: 600; box-shadow: none; border-radius: 12px; transition: all 0.2s;" onclick="addRecipeRow()" onmouseover="this.style.background='rgba(99, 102, 241, 0.1)'" onmouseout="this.style.background='rgba(99, 102, 241, 0.05)'">
                + Add Another Ingredient
            </button>

            <div style="background: rgba(99, 102, 241, 0.05); border: 1px solid rgba(99, 102, 241, 0.2); border-radius: 12px; padding: 15px 20px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Recipe Cost</div>
                    <div id="recipeTotalCost" style="font-size: 18px; font-weight: 700; color: var(--text-color);">0.000</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Est. Profitability</div>
                    <div id="recipeProfit" style="font-size: 18px; font-weight: 700; color: var(--accent-green);">0.000</div>
                </div>
            </div>

            <div style="display: flex; gap: 15px; justify-content: flex-end; border-top: 1px solid var(--card-border); padding-top: 25px;">
                <button type="button" class="modal-close" onclick="$('#recipeModal').fadeOut();" style="margin: 0; padding: 12px 25px; border-radius: 12px; font-weight: 600;">Cancel</button>
                <button type="submit" class="btn-primary" style="background: #6366f1; margin: 0; padding: 12px 30px; border-radius: 12px; font-weight: 600; font-size: 15px; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);">Save Recipe Configuration</button>
            </div>
        </form>
    </div>
</div>

<script>
let inventoryItemsList = [];

function fetchInventoryForRecipes(callback) {
    $.get('/admin/inventory/items/list', function(res) {
        let r = typeof res === 'string' ? JSON.parse(res) : res;
        inventoryItemsList = r.data;
        if(callback) callback();
    });
}

function manageRecipe(prod) {
    window.currentRecipeProductPrice = parseFloat(prod.price) || 0;
    $('#recipe_product_id').val(prod.id);
    let cur = '<?= htmlspecialchars($settings['currency_code'] ?? '$') ?>';
    $('#recipeProductName').html(`${prod.name} <span style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 10px;">Selling Price: ${window.currentRecipeProductPrice.toFixed(window.PRICE_DECIMALS || 3)} ${cur}</span>`);
    $('#recipeItemsContainer').empty();
    
    // Fetch inventory items then load recipe
    fetchInventoryForRecipes(function() {
        $.get('/admin/inventory/recipes/' + prod.id, function(res) {
            let response = typeof res === 'string' ? JSON.parse(res) : res;
            if (response.data && response.data.length > 0) {
                response.data.forEach(item => {
                    addRecipeRow(item.inventory_item_id, item.quantity_required);
                });
            } else {
                addRecipeRow(); // Add one empty row
            }
            calculateRecipeCost();
            $('#recipeModal').css('display', 'flex').hide().fadeIn();
        });
    });
}

function addRecipeRow(selectedItemId = '', quantity = '') {
    let options = '<option value="">-- Select Ingredient --</option>';
    inventoryItemsList.forEach(inv => {
        let selected = (inv.id == selectedItemId) ? 'selected' : '';
        options += `<option value="${inv.id}" ${selected}>${inv.name} (${inv.unit})</option>`;
    });

    let qtyFormatted = quantity;
    if (quantity !== '') {
        qtyFormatted = parseFloat(quantity).toFixed(2);
    }

    let rowHtml = `
        <div class="recipe-row" style="display: grid; grid-template-columns: 2fr 1fr 40px; gap: 10px; margin-bottom: 15px; align-items: center; padding: 0 10px;">
            <div>
                <select name="inventory_item_id[]" class="form-input" style="margin-bottom: 0; cursor: pointer;" required>
                    ${options}
                </select>
            </div>
            <div style="position: relative;">
                <span class="row-cost-badge" style="position: absolute; top: -10px; left: 10px; background: #ef4444; color: white; font-size: 11px; font-weight: bold; padding: 2px 6px; border-radius: 4px; z-index: 10; box-shadow: 0 2px 4px rgba(239,68,68,0.3); display: none;">0.000</span>
                <input type="number" step="0.01" name="quantity_required[]" class="form-input" style="margin-bottom: 0; text-align: center;" placeholder="Qty" value="${qtyFormatted}" required>
            </div>
            <div>
                <button type="button" class="btn-delete" style="width: 100%; height: 42px; padding: 0; display: flex; align-items: center; justify-content: center; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #ef4444;" onclick="$(this).closest('.recipe-row').remove(); calculateRecipeCost();" title="Remove Ingredient">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>
    `;
    $('#recipeItemsContainer').append(rowHtml);
}

$(document).on('change keyup', '#recipeItemsContainer select, #recipeItemsContainer input', calculateRecipeCost);

function calculateRecipeCost() {
    let totalCost = 0;
    $('#recipeItemsContainer .recipe-row').each(function() {
        let invId = $(this).find('select[name="inventory_item_id[]"]').val();
        let qty = parseFloat($(this).find('input[name="quantity_required[]"]').val()) || 0;
        let rowCost = 0;
        if (invId) {
            let invItem = inventoryItemsList.find(i => i.id == invId);
            if (invItem) {
                rowCost = parseFloat(invItem.selling_price || 0) * qty;
                totalCost += rowCost;
            }
        }
        
        let badge = $(this).find('.row-cost-badge');
        if (badge.length) {
            if (invId) {
                badge.text(rowCost.toFixed(window.PRICE_DECIMALS || 3));
                badge.fadeIn(200);
            } else {
                badge.fadeOut(200);
            }
        }
    });
    
    let price = window.currentRecipeProductPrice || 0;
    let profit = price - totalCost;
    let margin = price > 0 ? (profit / price) * 100 : 0;
    let cur = '<?= htmlspecialchars($settings['currency_code'] ?? '$') ?>';
    
    $('#recipeTotalCost').text(totalCost.toFixed(window.PRICE_DECIMALS || 3) + ' ' + cur);
    
    let profitColor = profit >= 0 ? 'var(--accent-green)' : 'var(--accent-red)';
    $('#recipeProfit').html(`<span style="color:${profitColor}">${profit.toFixed(window.PRICE_DECIMALS || 3)} ${cur} (${margin.toFixed(1)}%)</span>`);
}

$('#recipeForm').on('submit', function(e) {
    e.preventDefault();
    $.post('/admin/inventory/recipes', $(this).serialize(), function(res) {
        if(typeof Ladda !== 'undefined') Ladda.stopAll();
        let response = typeof res === 'string' ? JSON.parse(res) : res;
        if (response.status === 'success') {
            $('#recipeModal').fadeOut();
            Swal.fire({
                icon: 'success',
                title: 'Saved',
                text: 'Recipe saved successfully',
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
</script>

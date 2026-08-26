<div id="suppliers" class="tab-content">
    <div class="panel-card">
        <div class="panel-title">
            <span>Suppliers Management</span>
            <button class="btn-primary" onclick="$('#supplierModal').css('display', 'flex').hide().fadeIn();">Add Supplier</button>
        </div>
        <table class="dataTable" id="suppliersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact Person</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Supplier Modal -->
<div class="modal" id="supplierModal">
    <div class="modal-content" style="max-width: 600px; padding: 0; overflow: hidden;">
        <div style="background: var(--primary-grad); padding: 25px 30px; display: flex; align-items: center; justify-content: space-between;">
            <h3 style="margin: 0; color: white; display: flex; align-items: center; gap: 10px; font-size: 18px;" id="supplierModalTitle">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Add New Supplier
            </h3>
            <button type="button" onclick="$('#supplierModal').fadeOut();" style="background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        
        <form id="supplierForm" style="padding: 30px;">
            <input type="hidden" name="id" id="supplier_id">
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="font-weight: 600; color: var(--text-color);">Supplier Company Name <span style="color: #ef4444;">*</span></label>
                <div style="position: relative;">
                    <div style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                    </div>
                    <input type="text" name="name" id="supplier_name" class="form-input" style="padding-left: 45px;" placeholder="e.g. Fresh Foods Ltd" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; color: var(--text-color);">Contact Person</label>
                    <input type="text" name="contact_person" id="supplier_contact_person" class="form-input" placeholder="e.g. John Doe">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; color: var(--text-color);">Phone Number</label>
                    <input type="text" name="phone" id="supplier_phone" class="form-input" placeholder="e.g. +1 234 567 8900">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" style="font-weight: 600; color: var(--text-color);">Email Address</label>
                <input type="email" name="email" id="supplier_email" class="form-input" placeholder="e.g. orders@freshfoods.com">
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-label" style="font-weight: 600; color: var(--text-color);">Physical Address</label>
                <textarea name="address" id="supplier_address" class="form-input" rows="3" placeholder="Enter complete address..." style="resize: vertical;"></textarea>
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: flex-end; border-top: 1px solid var(--card-border); padding-top: 25px;">
                <button type="button" class="modal-close" onclick="$('#supplierModal').fadeOut();" style="margin: 0; padding: 12px 25px; border-radius: 12px; font-weight: 600;">Cancel</button>
                <button type="submit" class="btn-primary" style="margin: 0; padding: 12px 30px; border-radius: 12px; font-weight: 600; font-size: 15px; box-shadow: 0 10px 25px rgba(99,102,241,0.3);">Save Supplier Details</button>
            </div>
        </form>
    </div>
</div>

<script>
let suppliersTable;
function loadSuppliers() {
    if ($.fn.DataTable.isDataTable('#suppliersTable')) {
        $('#suppliersTable').DataTable().ajax.reload();
        return;
    }
    suppliersTable = $('#suppliersTable').DataTable({
        ajax: '/admin/suppliers/list',
        columns: [
            { data: 'id' },
            { data: 'name' },
            { data: 'contact_person' },
            { data: 'phone' },
            { data: 'email' },
            {
                data: null,
                render: function(data, type, row) {
                    return `<button class="btn-delete" style="background:var(--primary-grad); color:white; border:none;" onclick="editSupplier(${row.id})">Edit</button>
                            <button class="btn-delete" onclick="deleteSupplier(${row.id})">Delete</button>`;
                }
            }
        ]
    });
}

$('#supplierForm').on('submit', function(e) {
    e.preventDefault();
    $.post('/admin/suppliers', $(this).serialize(), function(res) {
        if(typeof Ladda !== 'undefined') Ladda.stopAll();
        let response = typeof res === 'string' ? JSON.parse(res) : res;
        if (response.status === 'success') {
            $('#supplierModal').fadeOut();
            loadSuppliers();
            $('#supplierForm')[0].reset();
            $('#supplier_id').val('');
            
            Swal.fire({
                icon: 'success',
                title: 'Saved',
                text: 'Supplier saved successfully',
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

function editSupplier(id) {
    let row = suppliersTable.row(function(idx, data) { return data.id == id; }).data();
    if (row) {
        $('#supplier_id').val(row.id);
        $('#supplier_name').val(row.name);
        $('#supplier_contact_person').val(row.contact_person);
        $('#supplier_phone').val(row.phone);
        $('#supplier_email').val(row.email);
        $('#supplier_address').val(row.address);
        $('#supplierModalTitle').text('Edit Supplier');
        $('#supplierModal').css('display', 'flex').hide().fadeIn();
    }
}

function deleteSupplier(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete this supplier?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/admin/suppliers/delete/' + id, function(res) {
                loadSuppliers();
                Swal.fire('Deleted!', 'The supplier has been deleted.', 'success');
            });
        }
    });
}
</script>

<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        
        <div class="modal-header">
          <h5 class="fw-bold mb-0">Add User</h5>
        </div>
        
        <div class="modal-body">
          <div class="mb-3">
            <label class="fw-bold">Email <span class="fw-medium" style="color: var(--error);">*</span></label>
            <input type="email" name="email" class="form-control" placeholder="juandelacruz@denr.gov.ph" required>
          </div>

          <div class="mb-3">
            <label class="fw-bold">Password <span class="fw-medium" style="color: var(--error);">*</span></label>
            <input type="password" name="password" class="form-control" placeholder="********" required>
            <small class="text-muted"><i>Password should contain at least 8 characters.</i></small>
          </div>   

          <div class="mb-3">
            <label class="fw-bold">Section <span class="fw-medium" style="color: var(--error);">*</span></label>
            <select name="department" id="department" class="form-select" required>
              <option value="">Select Section</option>
              <option value="Accounting">Accounting</option>
              <option value="Budget">Budget</option>
              <option value="System Administration">System Administration</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="fw-bold">Role <span class="fw-medium" style="color: var(--error);">*</span></label>
            <select name="role" id="role" class="form-select" required>
              <option value="">Select Role</option>
            </select>
          </div>

          <div class="mb-3 d-none" id="permission_level_container">
            <label class="fw-bold text-primary">System Access Permission Level</label>
            <select name="permission_level" id="permission_level" class="form-select">
              <option value="restricted">Restricted (Normal Operations Only)</option>
              <option value="special">Special (Can Manage Quarter Locking/Unlocking)</option>
            </select>
          </div>
        </div>
        
        <div class="modal-footer">
          <x-button type="button" variant="secondary" data-bs-dismiss="modal">Cancel</x-button>
          <x-button type="submit" variant="primary">Add Entry</x-button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const department = document.getElementById('department');
    const role = document.getElementById('role');
    const permContainer = document.getElementById('permission_level_container');
    const permInput = document.getElementById('permission_level');

    function loadRoles() {
      role.innerHTML = '';
      const existingHidden = document.getElementById('hidden-role');
      if (existingHidden) existingHidden.remove();

      if (department.value === 'System Administration') {
        role.innerHTML = '<option value="admin" selected>Admin</option>';
        role.disabled = true;
        createHiddenInput('admin');
        permContainer.classList.add('d-none');
        permInput.required = false;

      } else if (department.value === 'Budget') {
        role.innerHTML = '<option value="budget" selected>Budget</option>';
        role.disabled = true;
        createHiddenInput('budget');
        permContainer.classList.add('d-none');
        permInput.required = false;

      } else if (department.value === 'Accounting') {
        role.innerHTML = `
          <option value="">Select Role</option>
          <option value="accountant">Accountant</option>
          <option value="bookkeeper">Book Keeper</option>`;
        role.disabled = false;
        
        // Show permission levels choices instantly
        permContainer.classList.remove('d-none');
        permInput.required = true;
      } else {
        permContainer.classList.add('d-none');
        permInput.required = false;
      }
    }

    function createHiddenInput(value) {
      const hiddenInput = document.createElement('input');
      hiddenInput.type = 'hidden';
      hiddenInput.name = 'role';
      hiddenInput.id = 'hidden-role';
      hiddenInput.value = value;
      role.form.appendChild(hiddenInput);
    }

    loadRoles(); 
    department.addEventListener('change', loadRoles);
  });
</script>
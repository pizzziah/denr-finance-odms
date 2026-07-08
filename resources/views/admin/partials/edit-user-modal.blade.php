<div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" data-bs-backdrop="false" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content text-start border shadow-lg">
      <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="modal-header">
          <h5 class="fw-bold mb-0">Modify Account Settings</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
        <div class="modal-body">
          <div class="mb-3">
            <label class="fw-bold">Email</label>
            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
          </div>      
          
          <div class="mb-3">
            <label class="fw-bold">Section</label>
            <select name="department" id="department_{{ $user->id }}" class="form-select" required> 
              <option value="Accounting" {{ $user->department == 'Accounting' ? 'selected' : '' }}>Accounting</option> 
              <option value="Budget" {{ $user->department == 'Budget' ? 'selected' : '' }}>Budget</option> 
              <option value="System Administration" {{ in_array($user->department, ['System Administration', 'Admin']) ? 'selected' : '' }}>System Administration</option> 
            </select>    
          </div>  

          <div class="mb-3">
            <label class="fw-bold">Role</label>
            <select name="role" id="role_{{ $user->id }}" class="form-select" required>
              </select>
          </div>

          <div class="mb-3 {{ $user->department === 'Accounting' ? '' : 'd-none' }}" id="permission_level_container_{{ $user->id }}">
            <label class="fw-bold text-primary">Accounting Access Scope</label>
            <select name="permission_level" id="permission_level_{{ $user->id }}" class="form-select">
              <option value="restricted" {{ $user->permission_level === 'restricted' ? 'selected' : '' }}>Restricted (Normal Operations Only)</option>
              <option value="special" {{ $user->permission_level === 'special' ? 'selected' : '' }}>Special (Can Manage Quarter Locking/Unlocking)</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="fw-bold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Leave blank to keep existing password" autocomplete="new-password">
            <div class="invalid-feedback" id="password_length_error" style="color: var(--error);"><p><i>Password must be at least 8 characters long.</i></p></div>
          </div>
        </div>
        
        <div class="modal-footer">
          <x-button type="button" variant="secondary" data-bs-dismiss="modal">Cancel</x-button>
          <x-button type="submit" variant="primary">Save Changes</x-button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
(function() {
    const deptSelect = document.getElementById("department_{{ $user->id }}");
    const roleSelect = document.getElementById("role_{{ $user->id }}");
    const scopeContainer = document.getElementById("permission_level_container_{{ $user->id }}");
    const scopeSelect = document.getElementById("permission_level_{{ $user->id }}");

    const currentRole = "{{ $user->role }}";

    function updateRoleOptions(department, selectedRole) {
        if (!roleSelect) return;
        roleSelect.innerHTML = "";

        let options = [];

        if (department === "Accounting") {
            options = [
                { value: "Accountant", text: "Accountant" },
                { value: "Book Keeper", text: "Book Keeper" }
            ];
        } else if (department === "Budget") {
            options = [
                { value: "Budget", text: "Budget" }
            ];
        } else if (department === "System Administration" || department === "Admin") {
            options = [
                { value: "Admin", text: "Admin" }
            ];
        }

        options.forEach(opt => {
            let el = document.createElement("option");
            el.value = opt.value;
            el.text = opt.text;
            if (opt.value.toLowerCase().replace(/\s+/g, '') === selectedRole.toLowerCase().replace(/\s+/g, '')) {
                el.selected = true;
            }
            roleSelect.appendChild(el);
        });
    }

    if (deptSelect) {
        updateRoleOptions(deptSelect.value, currentRole);

        deptSelect.addEventListener("change", function() {
            updateRoleOptions(this.value, "");

            if (this.value === "Accounting") {
                scopeContainer.classList.remove("d-none");
                if (scopeSelect) scopeSelect.required = true;
            } else {
                scopeContainer.classList.add("d-none");
                if (scopeSelect) {
                    scopeSelect.required = false;
                    scopeSelect.value = ""; 
                }
            }
        });
    }
})();
</script>
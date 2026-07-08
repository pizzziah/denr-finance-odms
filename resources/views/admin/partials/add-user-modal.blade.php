<!-- Wrapper Modal Container -->
<div class="modal fade" id="addUserModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
  <div class="modal-dialog">
    
    <!-- Main Modal Content Container Wrapper Frame -->
    <div class="modal-content border shadow-lg text-start position-relative">
      
      <!-- Issue #2 Fix: Placed overlay container inside the modal-content stack to receive real pointer clicks -->
      <div id="confirmDiscardOverlay" class="position-absolute top-0 start-0 w-100 h-100 rounded d-none flex-column align-items-center justify-content-center p-4 text-center" style="background: rgba(255,255,255,0.96); z-index: 1060; backdrop-filter: blur(4px);">
        <div class="p-3 rounded shadow border bg-white" style="max-width: 320px; z-index: 1061;">
          <div class="text-warning mb-2">
            <i class="bi bi-exclamation-triangle-fill fs-1"></i>
          </div>
          <h6 class="fw-bold text-dark mb-1">Discard Changes?</h6>
          <p class="text-muted small mb-3">You have unsaved information. Are you sure you want to close this form?</p>
          <div class="d-flex gap-2 justify-content-center">
            <button type="button" id="btnAbortClose" class="btn btn-sm fw-medium px-3" style="background-color: var(--secondary-variant); color: var(--primary); border: 1px solid var(--primary);">Keep Editing</button>
            <button type="button" id="btnConfirmClose" class="btn btn-sm fw-bold px-3 text-white" style="background-color: var(--error);">Discard</button>
          </div>
        </div>
      </div>

      <form action="{{ route('admin.users.store') }}" method="POST" id="addUserForm">
        @csrf
        
        <div class="modal-header">
          <h5 class="fw-bold mb-0">Add User</h5>
          <button type="button" class="btn-close custom-close-trigger" aria-label="Close"></button>
        </div>
        
        <div class="modal-body">
          <!-- Email Input Field -->
          <div class="mb-3">
            <label class="fw-bold">Email <span class="fw-medium" style="color: var(--error);">*</span></label>
            <input type="email" name="email" id="add_email" class="form-control @error('email') is-invalid @enderror" placeholder="juandelacruz@denr.gov.ph" required autocomplete="off">
            <div class="invalid-feedback d-block" id="email_taken_error" style="color: var(--error); display: none;"></div>
            @error('email')
              <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
          </div>

          <!-- Password Input Field -->
          <div class="mb-3">
            <label class="fw-bold">Password <span class="fw-medium" style="color: var(--error);">*</span></label>
            <input type="password" name="password" id="add_password" class="form-control" placeholder="********" required>
            <small class="text-muted d-block mt-1"><i>Password should contain at least 8 characters.</i></small>
            <div class="invalid-feedback" id="password_length_error" style="color: var(--error);">Password must be at least 8 characters long.</div>
          </div>   

          <!-- Section / Department Select Box -->
          <div class="mb-3">
            <label class="fw-bold">Section <span class="fw-medium" style="color: var(--error);">*</span></label>
            <select name="department" id="department" class="form-select" required>
              <option value="">Select Section</option>
              <option value="Accounting">Accounting</option>
              <option value="Budget">Budget</option>
              <option value="System Administration">System Administration</option>
            </select>
          </div>

          <!-- Role Select Box -->
          <div class="mb-3">
            <label class="fw-bold">Role <span class="fw-medium" style="color: var(--error);">*</span></label>
            <select name="role" id="role" class="form-select" required>
              <option value="">Select Role</option>
            </select>
          </div>

          <!-- Accounting Access Scope Container -->
          <div class="mb-3 d-none" id="permission_level_container">
            <label class="fw-bold text-primary">System Access Permission Level</label>
            <select name="permission_level" id="permission_level" class="form-select">
              <option value="restricted">Restricted (Normal Operations Only)</option>
              <option value="special">Special (Can Manage Quarter Locking/Unlocking)</option>
            </select>
          </div>
        </div>
        
        <div class="modal-footer">
          <x-button type="button" variant="secondary" class="custom-close-trigger">Cancel</x-button>
          <x-button type="submit" variant="primary" id="btnSubmitAdd">Add Entry</x-button>
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
    
    const emailInput = document.getElementById('add_email');
    const emailError = document.getElementById('email_taken_error');
    const submitBtn = document.getElementById('btnSubmitAdd');
    const form = document.getElementById('addUserForm');
    
    const overlay = document.getElementById('confirmDiscardOverlay');
    const btnAbortClose = document.getElementById('btnAbortClose');
    const btnConfirmClose = document.getElementById('btnConfirmClose');

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

    department.addEventListener('change', loadRoles);

    // Issue #1 Fix: Precise matching boundaries using regex patterns instead of global html tracking
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const emailValue = this.value.trim();
            
            emailError.style.display = 'none';
            emailError.innerText = '';
            emailInput.classList.remove('is-invalid');
            submitBtn.disabled = false;

            if (emailValue === '' || emailValue.length < 5) return;

            fetch(`{{ route('admin.users') }}?search=${encodeURIComponent(emailValue)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                // Creates a clean regex boundary check targeting standalone text segments or cell nodes
                const escapeRegex = emailValue.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                const emailRegex = new RegExp('(>|\\b)"?' + escapeRegex + '"?(<|\\b)', 'i');

                if (emailRegex.test(html)) {
                    emailError.innerText = 'This email has already been taken.';
                    emailError.style.display = 'block';
                    emailInput.classList.add('is-invalid');
                    submitBtn.disabled = true;
                }
            })
            .catch(err => console.error('Verification tracking failed:', err));
        });
    }

    function checkIfFormIsDirty() {
        let isDirty = false;
        const inputs = form.querySelectorAll('input, select');
        
        inputs.forEach(input => {
            if (input.type === 'hidden' || input.name === '_token') return;
            
            if (input.tagName === 'SELECT') {
                if (input.selectedIndex > 0) isDirty = true;
            } else if (input.type === 'email' || input.type === 'password') {
                if (input.value.trim() !== '') isDirty = true;
            }
        });
        return isDirty;
    }

    // Intercept Modal Closing triggers
    document.getElementById('addUserModal').querySelectorAll('.custom-close-trigger').forEach(trigger => {
        trigger.removeAttribute('data-bs-dismiss'); 
        
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            if (checkIfFormIsDirty()) {
                overlay.classList.remove('d-none');
                overlay.classList.add('d-flex');
            } else {
                var modalEl = document.getElementById('addUserModal');
                var modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modalInstance.hide();
            }
        });
    });

    // Issue #2 Fix: Click bindings now work perfectly since overlay is an internal node child stack item
    btnAbortClose.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        overlay.classList.add('d-none');
        overlay.classList.remove('d-flex');
    });

    btnConfirmClose.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        overlay.classList.add('d-none');
        overlay.classList.remove('d-flex');
        form.reset();
        loadRoles();
        
        var modalEl = document.getElementById('addUserModal');
        var modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) {
            modalInstance.hide();
        }
    });
});
</script>
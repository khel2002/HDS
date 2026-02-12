<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="icon-base ri ri-add-line me-2"></i>
          Add New User
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="role_id" class="form-label">Role</label>
              <select name="role_id" id="role_id" class="form-select" required>
                <option value="">Select Role</option>
                <option value="1">Admin</option>
                <option value="2">Staff</option>
                <option value="3">Manager</option>
                <option value="4">Guest</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="email" class="form-label">Email</label>
              <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="col-md-6">
              <label for="password" class="form-label">Password</label>
              <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label for="first_name" class="form-label">First Name</label>
              <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name') }}" required>
            </div>
            <div class="col-md-6">
              <label for="middle_name" class="form-label">Middle Name</label>
              <input type="text" name="middle_name" id="middle_name" class="form-control" value="{{ old('middle_name') }}">
            </div>
            <div class="col-md-6">
              <label for="last_name" class="form-label">Last Name</label>
              <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name') }}" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ri ri-add-line me-1"></i>
            Add User
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

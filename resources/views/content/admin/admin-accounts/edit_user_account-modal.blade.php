<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="icon-base ri ri-edit-line me-2"></i>
          Edit User
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editUserForm" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="user_id" id="editUserId">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="editRoleId" class="form-label">Role</label>
              <select name="role_id" id="editRoleId" class="form-select" required>
                <option value=""></option>
                <option value="1">Guest</option>
                <option value="2">Staff</option>
                <option value="3">Admin</option>
                <option value="4">Super Admin</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="editEmail" class="form-label">Email</label>
              <input type="email" name="email" id="editEmail" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label for="editPassword" class="form-label">Password <small class="text-muted">(Leave blank to keep
                  current)</small></label>
              <input type="password" name="password" id="editPassword" class="form-control">
            </div>
            <div class="col-md-6">
              <label for="editFirstName" class="form-label">First Name</label>
              <input type="text" name="first_name" id="editFirstName" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label for="editMiddleName" class="form-label">Middle Name</label>
              <input type="text" name="middle_name" id="editMiddleName" class="form-control">
            </div>
            <div class="col-md-6">
              <label for="editLastName" class="form-label">Last Name</label>
              <input type="text" name="last_name" id="editLastName" class="form-control" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ri ri-save-line me-1"></i>
            Update User
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

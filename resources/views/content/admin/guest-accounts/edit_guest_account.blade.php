{{-- Edit Guest Account Modal --}}
<div class="modal fade" id="editGuestModal" tabindex="-1" aria-labelledby="editGuestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editGuestModalLabel">
          <i class="icon-base ri ri-edit-line me-2"></i>
          Edit Guest Account
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editGuestForm" method="POST">
        @csrf
        <input type="hidden" id="editGuestId" name="guest_id">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="editGuestFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="editGuestFirstName" name="first_name" required>
            </div>
            <div class="col-md-6">
              <label for="editGuestMiddleName" class="form-label">Middle Name</label>
              <input type="text" class="form-control" id="editGuestMiddleName" name="middle_name">
            </div>
            <div class="col-12">
              <label for="editGuestLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="editGuestLastName" name="last_name" required>
            </div>
            <div class="col-12">
              <label for="editGuestEmail" class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="editGuestEmail" name="email" required>
            </div>
            <div class="col-md-6">
              <label for="editGuestContactNumber" class="form-label">Contact Number <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="editGuestContactNumber" name="contact_number" required>
            </div>
            <div class="col-md-6">
              <label for="editGuestDob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="editGuestDob" name="dob" required>
            </div>
            <div class="col-12">
              <label for="editGuestPassword" class="form-label">New Password</label>
              <input type="password" class="form-control" id="editGuestPassword" name="password" minlength="8">
              <div class="form-text">Leave blank to keep current password. Minimum 8 characters if changing.</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ri ri-save-line me-1"></i>
            Update Guest
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

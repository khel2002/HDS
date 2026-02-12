{{-- View Guest Account Modal --}}
<div class="modal fade" id="viewGuestModal" tabindex="-1" aria-labelledby="viewGuestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewGuestModalLabel">
          <i class="icon-base ri ri-user-line me-2"></i>
          Guest Account Details
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-4">
          <div class="avatar avatar-xl mb-3">
            <span class="avatar-initial rounded-circle bg-label-primary fs-2" id="viewGuestAvatar">AB</span>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold text-muted small">First Name</label>
            <p class="mb-0" id="viewGuestFirstName">-</p>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold text-muted small">Middle Name</label>
            <p class="mb-0" id="viewGuestMiddleName">-</p>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold text-muted small">Last Name</label>
            <p class="mb-0" id="viewGuestLastName">-</p>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold text-muted small">Email</label>
            <p class="mb-0" id="viewGuestEmail">-</p>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold text-muted small">Contact Number</label>
            <p class="mb-0" id="viewGuestContactNumber">-</p>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold text-muted small">Date of Birth</label>
            <p class="mb-0" id="viewGuestDob">-</p>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold text-muted small">Status</label>
            <p class="mb-0" id="viewGuestStatus">-</p>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold text-muted small">Account Created</label>
            <p class="mb-0" id="viewGuestJoinedDate">-</p>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold text-muted small">Last Updated</label>
            <p class="mb-0" id="viewGuestUpdatedDate">-</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

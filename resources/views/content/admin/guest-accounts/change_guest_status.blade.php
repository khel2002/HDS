{{-- Change Guest Status Modal --}}
<div class="modal fade" id="changeGuestStatusModal" tabindex="-1" aria-labelledby="changeGuestStatusModalLabel"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="changeGuestStatusModalLabel">
          <i class="icon-base ri ri-refresh-line me-2"></i>
          Change Guest Status
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="changeGuestStatusForm" method="POST">
        @csrf
        <input type="hidden" id="statusGuestId" name="user_id">
        <div class="modal-body">
          <p class="mb-3">Change status for: <strong id="statusGuestName"></strong></p>
          <div class="mb-3">
            <label for="newGuestStatus" class="form-label">New Status <span class="text-danger">*</span></label>
            <select class="form-select" id="newGuestStatus" name="status" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="icon-base ri ri-check-line me-1"></i>
            Update Status
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

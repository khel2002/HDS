{{-- Delete Guest Modal --}}
<div class="modal fade" id="deleteGuestModal" tabindex="-1" aria-labelledby="deleteGuestModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteGuestModalLabel">
          <i class="icon-base ri ri-delete-bin-line me-2"></i>
          Delete Guest Account
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="deleteGuestForm" method="POST">
        @csrf
        <input type="hidden" id="deleteGuestId" name="user_id">
        <div class="modal-body">
          <div class="text-center mb-3">
            <i class="icon-base ri ri-error-warning-line text-danger" style="font-size: 3rem;"></i>
          </div>
          <p class="text-center mb-3">
            Are you sure you want to delete <strong id="deleteGuestName"></strong>?
          </p>
          <div class="alert alert-warning mb-0">
            <i class="icon-base ri ri-alert-line me-2"></i>
            <strong>Warning:</strong> This action cannot be undone!
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="icon-base ri ri-delete-bin-line me-1"></i>
            Delete Guest User
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

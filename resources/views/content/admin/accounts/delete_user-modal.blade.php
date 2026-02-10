<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h5 class="modal-title text-white">
          <i class="icon-base ri ri-delete-bin-line me-2"></i>
          Delete User Account
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="deleteUserForm" method="POST">
        @csrf
        <input type="hidden" name="user_id" id="deleteUserId">
        <div class="modal-body">
          <div class="text-center mb-3">
            <i class="icon-base ri ri-alert-line text-danger" style="font-size: 3rem;"></i>
          </div>
          <h6 class="text-center mb-3">Are you sure you want to delete this user?</h6>
          <p class="text-center mb-0">User: <strong id="deleteUserName"></strong></p>
          <p class="text-center text-danger"><small>This action cannot be undone!</small></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="icon-base ri ri-delete-bin-line me-1"></i>
            Delete User
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

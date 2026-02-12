<div class="col-12 mb-4">
  <div class="row g-4">
    <div class="col-xl-3 col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-primary rounded shadow-xs">
                <i class="icon-base ri ri-user-fill icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0 text-muted small">Total Guest Accounts</p>
              <h4 class="mb-0 fw-bold" id="stat-total">{{ $totalUsers }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-success rounded shadow-xs">
                <i class="icon-base ri ri-user-follow-fill icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0 text-muted small">Active</p>
              <h4 class="mb-0 fw-bold" id="stat-active">{{ $activeUsers }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-warning rounded shadow-xs">
                <i class="icon-base ri ri-user-unfollow-fill icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0 text-muted small">Inactive</p>
              <h4 class="mb-0 fw-bold" id="stat-inactive">{{ $inactiveUsers }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-danger rounded shadow-xs">
                <i class="icon-base ri ri-user-forbid-fill icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0 text-muted small">Suspended</p>
              <h4 class="mb-0 fw-bold" id="stat-suspended">{{ $suspendedUsers }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@extends('layouts/contentNavbarLayout')
@section('title', 'Users - Management')

@section('content')
<div class="row gy-6">

  {{-- Page Header --}}
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-1">User Management</h4>
            <p class="mb-0">Manage all system users and their roles</p>
          </div>
          <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="icon-base ri ri-user-add-line me-1"></i>Add New User
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Flash Messages --}}
  @if(session('success'))
    <div class="col-12">
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ri-checkbox-circle-line me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    </div>
  @endif
  @if(session('error'))
    <div class="col-12">
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ri-error-warning-line me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    </div>
  @endif

  {{-- Stats Cards --}}
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar"><div class="avatar-initial bg-primary rounded shadow-xs"><i class="icon-base ri ri-group-line icon-24px"></i></div></div>
          <div class="ms-3"><p class="mb-0">Total Users</p><h5 class="mb-0">{{ $stats['total'] }}</h5></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar"><div class="avatar-initial bg-success rounded shadow-xs"><i class="icon-base ri ri-shield-check-line icon-24px"></i></div></div>
          <div class="ms-3"><p class="mb-0">Active Users</p><h5 class="mb-0">{{ $stats['active'] }}</h5></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar"><div class="avatar-initial bg-warning rounded shadow-xs"><i class="icon-base ri ri-user-settings-line icon-24px"></i></div></div>
          <div class="ms-3"><p class="mb-0">Staff & Admins</p><h5 class="mb-0">{{ $stats['admin'] + $stats['staff'] + $stats['super_admin'] }}</h5></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar"><div class="avatar-initial bg-info rounded shadow-xs"><i class="icon-base ri ri-user-line icon-24px"></i></div></div>
          <div class="ms-3"><p class="mb-0">Guests</p><h5 class="mb-0">{{ $stats['guest'] }}</h5></div>
        </div>
      </div>
    </div>
  </div>

  {{-- Filters --}}
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="row g-4">
          <div class="col-md-4">
            <label class="form-label">Search User</label>
            <input type="text" class="form-control" id="searchTable" placeholder="Search by name or email…">
          </div>
          <div class="col-md-3">
            <label class="form-label">Role</label>
            <select class="form-select" id="filterRole">
              <option value="">All Roles</option>
              <option value="super_admin">Super Admin</option>
              <option value="admin">Admin</option>
              <option value="staff">Staff</option>
              <option value="guest">Guest</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select class="form-select" id="filterStatus">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">&nbsp;</label>
            <button class="btn btn-outline-secondary d-block w-100" type="button" onclick="resetFilters()">
              <i class="icon-base ri ri-refresh-line me-1"></i>Reset
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Users Table --}}
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="card-title m-0">All Users</h5>
          <div class="dropdown">
            <button class="btn text-body-secondary p-0" type="button" data-bs-toggle="dropdown">
              <i class="icon-base ri ri-more-2-line icon-24px"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
              <a class="dropdown-item" href="javascript:void(0);" onclick="window.location.reload()">Refresh</a>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="usersTableBody">
              @forelse($users as $index => $user)
                <tr
                  data-name="{{ strtolower($user->name) }}"
                  data-email="{{ strtolower($user->email) }}"
                  data-role="{{ $user->role }}"
                  data-status="{{ strtolower($user->status ?? 'active') }}"
                >
                  <td>{{ $index + 1 }}</td>

                  {{-- Avatar + Name --}}
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <div class="avatar avatar-sm">
                        <div class="avatar-initial rounded-circle
                          {{ $user->role === 'super_admin' ? 'bg-label-danger'
                            : ($user->role === 'admin'     ? 'bg-label-warning'
                            : ($user->role === 'staff'     ? 'bg-label-info'
                            : 'bg-label-secondary')) }}">
                          {{ strtoupper(substr($user->first_name ?? '?', 0, 1)) }}
                        </div>
                      </div>
                      <div>
                        <span class="fw-medium d-block">{{ $user->name }}</span>
                        <small class="text-body-secondary">ID #{{ $user->user_id }}</small>
                      </div>
                    </div>
                  </td>

                  <td>{{ $user->email }}</td>

                  {{-- Role Badge --}}
                  <td>
                    @if($user->role === 'super_admin')
                      <span class="badge bg-label-danger">Super Admin</span>
                    @elseif($user->role === 'admin')
                      <span class="badge bg-label-warning">Admin</span>
                    @elseif($user->role === 'staff')
                      <span class="badge bg-label-info">Staff</span>
                    @else
                      <span class="badge bg-label-secondary">Guest</span>
                    @endif
                  </td>

                  {{-- Status Badge --}}
                  <td>
                    @if(strtolower($user->status ?? 'active') === 'active')
                      <span class="badge bg-label-success">Active</span>
                    @else
                      <span class="badge bg-label-danger">Inactive</span>
                    @endif
                  </td>

                  <td>{{ $user->created_at->format('M d, Y') }}</td>

                  {{-- Actions --}}
                  <td>
                    <div class="dropdown">
                      <button type="button" class="btn btn-sm btn-icon" data-bs-toggle="dropdown">
                        <i class="icon-base ri ri-more-2-line"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="javascript:void(0);"
                           data-bs-toggle="modal" data-bs-target="#viewUserModal{{ $user->user_id }}">
                          <i class="icon-base ri ri-eye-line me-2"></i>View Details
                        </a>
                        <a class="dropdown-item" href="javascript:void(0);"
                           data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->user_id }}">
                          <i class="icon-base ri ri-edit-line me-2"></i>Edit
                        </a>
                        <a class="dropdown-item" href="javascript:void(0);"
                           data-bs-toggle="modal" data-bs-target="#changeStatusModal{{ $user->user_id }}">
                          <i class="icon-base ri ri-toggle-line me-2"></i>Change Status
                        </a>
                        <div class="dropdown-divider"></div>
                        @if($user->user_id !== auth()->user()->user_id)
                          <a class="dropdown-item text-danger" href="javascript:void(0);"
                             onclick="confirmDeleteUser({{ $user->user_id }}, '{{ addslashes($user->name) }}')">
                            <i class="icon-base ri ri-delete-bin-line me-2"></i>Delete
                          </a>
                        @else
                          <span class="dropdown-item text-muted disabled">
                            <i class="icon-base ri ri-delete-bin-line me-2"></i>Delete
                          </span>
                        @endif
                      </div>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-5">
                    <i class="icon-base ri ri-group-line icon-48px text-body-secondary mb-3 d-block"></i>
                    <p class="mb-0 text-body-secondary">No users found.</p>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- Role Summary --}}
  <div class="col-12">
    <div class="card">
      <div class="card-header"><h5 class="mb-0">Users by Role</h5></div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Role</th>
                <th class="text-center">Total</th>
                <th class="text-center">Active</th>
                <th class="text-center">Inactive</th>
              </tr>
            </thead>
            <tbody>
              @foreach([
                ['role' => 'super_admin', 'label' => 'Super Admin', 'color' => 'danger',    'icon' => 'ri-shield-star-line'],
                ['role' => 'admin',       'label' => 'Admin',       'color' => 'warning',   'icon' => 'ri-admin-line'],
                ['role' => 'staff',       'label' => 'Staff',       'color' => 'info',      'icon' => 'ri-user-settings-line'],
                ['role' => 'guest',       'label' => 'Guest',       'color' => 'secondary', 'icon' => 'ri-user-line'],
              ] as $r)
                @php
                  $roleUsers    = $users->filter(fn($u) => $u->role === $r['role']);
                  $roleActive   = $roleUsers->filter(fn($u) => strtolower($u->status ?? 'active') === 'active')->count();
                  $roleInactive = $roleUsers->filter(fn($u) => strtolower($u->status ?? '') === 'inactive')->count();
                @endphp
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <i class="icon-base ri {{ $r['icon'] }} text-{{ $r['color'] }}"></i>
                      <span class="fw-medium">{{ $r['label'] }}</span>
                    </div>
                  </td>
                  <td class="text-center"><span class="badge bg-label-{{ $r['color'] }} rounded-pill">{{ $roleUsers->count() }}</span></td>
                  <td class="text-center"><span class="badge bg-label-success rounded-pill">{{ $roleActive }}</span></td>
                  <td class="text-center"><span class="badge bg-label-danger rounded-pill">{{ $roleInactive }}</span></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>{{-- end .row --}}


{{-- ════════════════════════════════════════════════════════
     ADD USER MODAL
════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="icon-base ri ri-user-add-line me-2"></i>Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="{{ route('super_admin.users.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="row g-4">
            <div class="col-md-6">
              <label class="form-label">First Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="first_name" required placeholder="First name">
            </div>
            <div class="col-md-6">
              <label class="form-label">Last Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="last_name" required placeholder="Last name">
            </div>
            <div class="col-md-12">
              <label class="form-label">Email Address <span class="text-danger">*</span></label>
              <input type="email" class="form-control" name="email" required placeholder="user@example.com">
            </div>
            <div class="col-md-6">
              <label class="form-label">Role <span class="text-danger">*</span></label>
              <select class="form-select" name="role_id" required>
                <option value="">Select Role</option>
                @foreach($roles as $role)
                  <option value="{{ $role->role_id }}">{{ ucfirst(str_replace('_', ' ', $role->role_name)) }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Status <span class="text-danger">*</span></label>
              <select class="form-select" name="status" required>
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Password <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" class="form-control" name="password" id="addPassword" required minlength="8" placeholder="Min. 8 characters">
                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('addPassword', this)"><i class="ri-eye-line"></i></button>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="password" class="form-control" name="password_confirmation" id="addPasswordConfirm" required minlength="8" placeholder="Repeat password">
                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('addPasswordConfirm', this)"><i class="ri-eye-line"></i></button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="icon-base ri ri-user-add-line me-1"></i>Create User</button>
        </div>
      </form>
    </div>
  </div>
</div>


{{-- ════════════════════════════════════════════════════════
     PER-USER MODALS
════════════════════════════════════════════════════════ --}}
@foreach($users as $user)

  {{-- VIEW MODAL --}}
  <div class="modal fade" id="viewUserModal{{ $user->user_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="icon-base ri ri-eye-line me-2"></i>User Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row align-items-center g-4">
            <div class="col-md-3 text-center">
              <div class="avatar avatar-xl mx-auto mb-3">
                <div class="avatar-initial rounded-circle fs-2
                  {{ $user->role === 'super_admin' ? 'bg-label-danger'
                    : ($user->role === 'admin'     ? 'bg-label-warning'
                    : ($user->role === 'staff'     ? 'bg-label-info'
                    : 'bg-label-secondary')) }}"
                  style="width:80px;height:80px;">
                  {{ strtoupper(substr($user->first_name ?? '?', 0, 1)) }}
                </div>
              </div>
              @if($user->role === 'super_admin')
                <span class="badge bg-label-danger">Super Admin</span>
              @elseif($user->role === 'admin')
                <span class="badge bg-label-warning">Admin</span>
              @elseif($user->role === 'staff')
                <span class="badge bg-label-info">Staff</span>
              @else
                <span class="badge bg-label-secondary">Guest</span>
              @endif
            </div>
            <div class="col-md-9">
              <table class="table table-borderless table-sm mb-0">
                <tr>
                  <td class="text-body-secondary fw-medium" style="width:140px;">Full Name</td>
                  <td>{{ $user->name }}</td>
                </tr>
                <tr>
                  <td class="text-body-secondary fw-medium">Email</td>
                  <td>{{ $user->email }}</td>
                </tr>
                <tr>
                  <td class="text-body-secondary fw-medium">Role</td>
                  <td>{{ ucfirst(str_replace('_', ' ', $user->role ?? '—')) }}</td>
                </tr>
                <tr>
                  <td class="text-body-secondary fw-medium">Status</td>
                  <td>
                    @if(strtolower($user->status ?? 'active') === 'active')
                      <span class="badge bg-label-success">Active</span>
                    @else
                      <span class="badge bg-label-danger">Inactive</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <td class="text-body-secondary fw-medium">Joined</td>
                  <td>{{ $user->created_at->format('F d, Y \a\t h:i A') }}</td>
                </tr>
              </table>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->user_id }}">
            <i class="icon-base ri ri-edit-line me-1"></i>Edit User
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- EDIT MODAL --}}
  <div class="modal fade" id="editUserModal{{ $user->user_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="icon-base ri ri-edit-line me-2"></i>Edit User — {{ $user->name }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('super_admin.users.update', $user->user_id) }}" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <div class="row g-4">
              <div class="col-md-6">
                <label class="form-label">First Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="first_name" value="{{ $user->first_name }}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="last_name" value="{{ $user->last_name }}" required>
              </div>
              <div class="col-md-12">
                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Role <span class="text-danger">*</span></label>
                <select class="form-select" name="role_id" required>
                  @foreach($roles as $role)
                    <option value="{{ $role->role_id }}" {{ $user->role_id == $role->role_id ? 'selected' : '' }}>
                      {{ ucfirst(str_replace('_', ' ', $role->role_name)) }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Status <span class="text-danger">*</span></label>
                <select class="form-select" name="status" required>
                  <option value="active"   {{ strtolower($user->status ?? 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                  <option value="inactive" {{ strtolower($user->status ?? '')       === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
              </div>
              <div class="col-12">
                <hr class="my-0">
                <small class="text-body-secondary d-block mt-2 mb-0">Leave password fields empty to keep the current password.</small>
              </div>
              <div class="col-md-6">
                <label class="form-label">New Password</label>
                <div class="input-group">
                  <input type="password" class="form-control" name="password" id="editPassword{{ $user->user_id }}" minlength="8" placeholder="Min. 8 characters">
                  <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('editPassword{{ $user->user_id }}', this)"><i class="ri-eye-line"></i></button>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Confirm New Password</label>
                <div class="input-group">
                  <input type="password" class="form-control" name="password_confirmation" id="editPasswordConfirm{{ $user->user_id }}" minlength="8" placeholder="Repeat password">
                  <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('editPasswordConfirm{{ $user->user_id }}', this)"><i class="ri-eye-line"></i></button>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="icon-base ri ri-save-line me-1"></i>Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- CHANGE STATUS MODAL --}}
  <div class="modal fade" id="changeStatusModal{{ $user->user_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="icon-base ri ri-toggle-line me-2"></i>Change Status</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('super_admin.users.status', $user->user_id) }}" method="POST">
          @csrf
          <div class="modal-body">
            <p class="mb-3">Update status for <strong>{{ $user->name }}</strong></p>
            <div class="mb-0">
              <label class="form-label">New Status <span class="text-danger">*</span></label>
              <select class="form-select" name="status" required>
                <option value="active"   {{ strtolower($user->status ?? 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ strtolower($user->status ?? '')       === 'inactive' ? 'selected' : '' }}>Inactive</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary"><i class="icon-base ri ri-check-line me-1"></i>Update Status</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- DELETE FORM --}}
  <form id="deleteUserForm{{ $user->user_id }}" action="{{ route('super_admin.users.destroy', $user->user_id) }}" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
  </form>

@endforeach


{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  function applyFilters() {
    const search = document.getElementById('searchTable').value.toLowerCase();
    const role   = document.getElementById('filterRole').value;
    const status = document.getElementById('filterStatus').value;

    document.querySelectorAll('#usersTableBody tr[data-name]').forEach(row => {
      const matchSearch = !search || row.dataset.name.includes(search) || row.dataset.email.includes(search);
      const matchRole   = !role   || row.dataset.role   === role;
      const matchStatus = !status || row.dataset.status === status;
      row.style.display = matchSearch && matchRole && matchStatus ? '' : 'none';
    });
  }

  document.getElementById('searchTable').addEventListener('input',  applyFilters);
  document.getElementById('filterRole').addEventListener('change',  applyFilters);
  document.getElementById('filterStatus').addEventListener('change', applyFilters);

  function resetFilters() {
    document.getElementById('searchTable').value  = '';
    document.getElementById('filterRole').value   = '';
    document.getElementById('filterStatus').value = '';
    applyFilters();
  }

  function togglePassword(fieldId, btn) {
    const field = document.getElementById(fieldId);
    const icon  = btn.querySelector('i');
    if (field.type === 'password') {
      field.type = 'text';
      icon.classList.replace('ri-eye-line', 'ri-eye-off-line');
    } else {
      field.type = 'password';
      icon.classList.replace('ri-eye-off-line', 'ri-eye-line');
    }
  }

  function confirmDeleteUser(id, name) {
    Swal.fire({
      title: 'Delete User?',
      html: `Are you sure you want to delete <strong>${name}</strong>? This cannot be undone.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, delete',
      cancelButtonText: 'Cancel',
    }).then(result => {
      if (result.isConfirmed) {
        document.getElementById('deleteUserForm' + id).submit();
      }
    });
  }
</script>
@endsection
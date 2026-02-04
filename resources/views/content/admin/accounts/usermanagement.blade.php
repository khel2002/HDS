@extends('layouts/contentNavbarLayout')
@section('title', 'Account - Management')
@section('content')
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-1">User Management</h4>
            <p class="mb-0">Manage and view all user accounts</p>
          </div>
          <button class="btn btn-primary" type="button">
            <i class="icon-base ri ri-add-line me-1"></i>
            Add New User
          </button>
        </div>
      </div>
    </div>
  </div>
  <BR></BR>
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h5 class="mb-0">All Users</h5>
        <div class="d-flex gap-2 flex-wrap">
          <input type="text" id="searchTable" class="form-control form-control-sm" placeholder="Search users..."
            style="width: 200px;">
          <select id="filterStatus" class="form-select form-select-sm" style="width: 150px;">
            <option value="">All Accounts</option>
            <option value="">Staff</option>
            <option value="">Guest</option>
            <option value="">Admin</option>
          </select>
          <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.location.reload()">
            <i class="icon-base ri ri-refresh-line me-1"></i>
            Refresh
          </button>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Avatar</th>
              <th>Name</th>
              <th>Email</th>
              <th>Status</th>
              <th>Joined</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($users as $user)
              <tr>
                <td></td>
                <td>{{ $user->first_name }} {{ $user->last_name }} </td>
                <td>{{ $user->email }}</td>
                <td>
                  <span
                    class="badge
                    {{ strtolower($user->STATUS) === 'active' ? 'bg-success' : 'bg-danger' }}">
                    {{ ucfirst(strtolower($user->STATUS)) }}
                  </span>
                </td>
                <td>{{ $user->created_at }}</td>
                <td class="text-center align-middle">
                  <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button"
                      title="View Details" data-bs-toggle="modal">
                      <i class="icon-base ri ri-eye-line"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button" title="Edit"
                      data-bs-toggle="modal">
                      <i class="icon-base ri ri-edit-line"></i>
                    </button>
                    <div class="dropdown">
                      <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                        <i class="icon-base ri ri-more-2-line"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal">
                          <i class="icon-base ri ri-refresh-line me-2"></i>
                          Change Status
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="javascript:void(0);">
                          <i class="icon-base ri ri-delete-bin-line me-2"></i>
                          Delete Room
                        </a>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            @endforeach
            @empty($users)
              <td colspan="6" class="text-center">No data found</td>
            @endempty
          </tbody>
        </table>
      </div>
    </div>
  </div>

@endsection

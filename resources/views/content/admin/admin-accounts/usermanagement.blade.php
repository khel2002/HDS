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
          <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="icon-base ri ri-add-line me-1"></i>
            Add New User
          </button>
        </div>
      </div>
    </div>
  </div>
  <br>
  @include('content.admin.admin-accounts.account_statistics')
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h5 class="mb-0">All Users</h5>
        <div class="d-flex gap-2 flex-wrap">
          <input type="text" id="searchTable" class="form-control form-control-sm" placeholder="Search users..."
            style="width: 200px;">
          <select id="filterStatus" class="form-select form-select-sm" style="width: 150px;">
            <option value="">All Accounts</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="suspended">Suspended</option>
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
              <th class="text-center">Status</th>
              <th class="text-center">Joined</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($users as $user)
              {{-- IMPORTANT: data-user-id lets JS find and update this row without a page reload --}}
              <tr data-user-id="{{ $user->user_id }}">
                <td>
                  <div class="avatar avatar-sm">
                    <span class="avatar-initial rounded-circle bg-label-primary">
                      {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                    </span>
                  </div>
                </td>
                <td class="user-name">{{ $user->first_name }} {{ $user->last_name }}</td>
                <td class="user-email">{{ $user->email }}</td>
                <td class="text-center align-middle">
                  <span
                    class="badge
                    {{ strtolower($user->STATUS) === 'active' ? 'bg-success' : 'bg-danger' }}">
                    {{ ucfirst(strtolower($user->STATUS)) }}
                  </span>
                </td>
                <td class="text-center align-middle">{{ $user->created_at }}</td>
                <td class="text-center align-middle">
                  <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button"
                      title="View Details"
                      onclick="viewUser(
                        '{{ $user->user_id }}',
                        '{{ $user->first_name }}',
                        '{{ addslashes($user->middle_name ?? '') }}',
                        '{{ $user->last_name }}',
                        '{{ $user->email }}',
                        '{{ $user->role->name ?? 'N/A' }}',
                        '{{ $user->STATUS }}',
                        '{{ $user->created_at }}',
                        '{{ $user->updated_at }}'
                      )">
                      <i class="icon-base ri ri-eye-line"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button" title="Edit"
                      onclick="editUser(
                        '{{ $user->user_id }}',
                        '{{ $user->role_id }}',
                        '{{ $user->email }}',
                        '{{ addslashes($user->first_name) }}',
                        '{{ addslashes($user->middle_name ?? '') }}',
                        '{{ addslashes($user->last_name) }}'
                      )">
                      <i class="icon-base ri ri-edit-line"></i>
                    </button>
                    <div class="dropdown">
                      <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button"
                        data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                        <i class="icon-base ri ri-more-2-line"></i>
                      </button>
                      <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="javascript:void(0);"
                          onclick="changeStatus(
                            '{{ $user->user_id }}',
                            '{{ addslashes($user->first_name) }} {{ addslashes($user->last_name) }}',
                            '{{ $user->STATUS }}'
                          )">
                          <i class="icon-base ri ri-refresh-line me-2"></i>
                          Change Status
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                          onclick="deleteUser(
                            '{{ $user->user_id }}',
                            '{{ addslashes($user->first_name) }} {{ addslashes($user->last_name) }}'
                          )">
                          <i class="icon-base ri ri-delete-bin-line me-2"></i>
                          Delete Account
                        </a>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            @endforeach
            @if ($users->isEmpty())
              <tr id="no-data-row">
                <td colspan="6" class="text-center">No data found</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Include all modals --}}
  @include('content.admin.admin-accounts.add-new-user-modal')
  @include('content.admin.admin-accounts.view_user_account-modal')
  @include('content.admin.admin-accounts.edit_user_account-modal')
  @include('content.admin.admin-accounts.change_status-modal')
  @include('content.admin.admin-accounts.delete_user-modal')
@endsection

@section('page-script')
  <script src="{{ asset('assets/js/user-management.js') }}"></script>
@endsection

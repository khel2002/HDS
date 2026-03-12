@extends('layouts/contentNavbarLayout')
@section('title', 'Guest - Management')
@section('content')
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-1">Guest Management</h4>
            <p class="mb-0">Manage and view all guest accounts</p>
          </div>
          <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addGuestModal">
            <i class="icon-base ri ri-add-line me-1"></i>
            Add New Guest User
          </button>
        </div>
      </div>
    </div>
  </div>
  <br>
  @include('content.admin.guest-accounts.guest_statistics')
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <h5 class="mb-0">All Guest Users</h5>
        <div class="d-flex gap-2 flex-wrap">
          <input type="text" id="searchTable" class="form-control form-control-sm" placeholder="Search guests..."
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
              <th class="text-center">Contact Number</th>
              <th class="text-center">Status</th>
              <th class="text-center">Created at</th>
              <th class="text-center">Date of Departure</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($guests as $guest)
              <tr data-guest-id="{{ $guest->guest_details_id }}">
                <td>
                  <div class="avatar avatar-sm">
                    <span class="avatar-initial rounded-circle bg-label-primary">
                      {{ strtoupper(substr($guest->first_name, 0, 1) . substr($guest->last_name, 0, 1)) }}
                    </span>
                  </div>
                </td>
                <td class="user-name">{{ $guest->first_name }} {{ $guest->last_name }}</td>
                <td class="user-email">{{ $guest->user->email }}</td>
                <td class="user-contact text-center">{{ $guest->contact_number }}</td>
                <td class="text-center align-middle">
                  <span
                    class="badge {{ strtolower($guest->user->STATUS) === 'asd' ? 'bg-success' : (strtolower($guest->user->STATUS) === 'inactive' ? 'bg-warning' : 'bg-danger')  }}">
                    {{ ucfirst(strtolower($guest->user->STATUS)) }}
                  </span>
                </td>
                <td class="text-center align-middle">{{ $guest->user->created_at }}</td>
                <td class="text-center align-middle">{{ $guest->departure_date->toDateString() }}</td>

                <td class="text-center align-middle">
                  <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button"
                      title="View Details"
                      onclick="viewGuest(
                        '{{ $guest->guest_details_id }}',
                        '{{ $guest->first_name }}',
                        '{{ addslashes($guest->middle_name ?? '') }}',
                        '{{ $guest->last_name }}',
                        '{{ $guest->user->email }}',
                        '{{ $guest->contact_number }}',
                        '{{ $guest->dob }}',
                        '{{ $guest->user->STATUS }}',
                        '{{ $guest->user->created_at }}',
                        '{{ $guest->user->updated_at }}'
                      )">
                      <i class="icon-base ri ri-eye-line"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill" type="button" title="Edit"
                      onclick="editGuest(
                        '{{ $guest->guest_details_id }}',
                        '{{ $guest->user->email }}',
                        '{{ addslashes($guest->first_name) }}',
                        '{{ addslashes($guest->middle_name ?? '') }}',
                        '{{ addslashes($guest->last_name) }}',
                        '{{ $guest->contact_number }}',
                        '{{ $guest->dob }}'
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
                          onclick="changeGuestStatus(
                            '{{ $guest->guest_details_id }}',
                            '{{ addslashes($guest->first_name) }} {{ addslashes($guest->last_name) }}',
                            '{{ $guest->user->STATUS }}'
                          )">
                          <i class="icon-base ri ri-refresh-line me-2"></i>
                          Change Status
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="javascript:void(0);"
                          onclick="deleteGuest(
                            '{{ $guest->guest_details_id }}',
                            '{{ addslashes($guest->first_name) }} {{ addslashes($guest->last_name) }}'
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
            @if ($guests->isEmpty())
              <tr id="no-data-row">
                <td colspan="7" class="text-center">No data found</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Include all modals --}}
  {{-- @include('content.admin.guest-accounts.add-new-guest-modal') --}}
  @include('content.admin.guest-accounts.view_guest_account')
  @include('content.admin.guest-accounts.edit_guest_account')
  @include('content.admin.guest-accounts.change_guest_status')
  @include('content.admin.guest-accounts.delete_guest')
@endsection

@section('page-script')
  <script src="{{ asset('assets/js/guest-management.js') }}"></script>
@endsection

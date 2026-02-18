@extends('layouts/contentNavbarLayout')
@section('title', 'Reservations Calendar - Management')

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css">
@endsection

@section('content')
  <div class="row gy-6">
    <!-- Page Header -->
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
              <h4 class="mb-1">Reservations Calendar</h4>
              <p class="mb-0">Visual overview of all hotel reservations</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <a href="{{ route('super_admin.reservations.index') }}" class="btn btn-outline-secondary">
                <i class="icon-base ri ri-list-check me-1"></i>
                List View
              </a>
              <button class="btn btn-outline-primary" type="button" id="todayBtn">
                <i class="icon-base ri ri-calendar-todo-line me-1"></i>
                Today
              </button>
              <button class="btn btn-primary" type="button" onclick="window.location.reload()">
                <i class="icon-base ri ri-refresh-line me-1"></i>
                Refresh
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-xl-3 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-warning rounded shadow-xs">
                <i class="icon-base ri ri-time-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Pending</p>
              <h5 class="mb-0" id="pendingCount">0</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-success rounded shadow-xs">
                <i class="icon-base ri ri-checkbox-circle-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Approved</p>
              <h5 class="mb-0" id="approvedCount">0</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-info rounded shadow-xs">
                <i class="icon-base ri ri-hotel-bed-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Check-ins Today</p>
              <h5 class="mb-0" id="checkinCount">0</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-3 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="avatar">
              <div class="avatar-initial bg-danger rounded shadow-xs">
                <i class="icon-base ri ri-logout-box-line icon-24px"></i>
              </div>
            </div>
            <div class="ms-3">
              <p class="mb-0">Check-outs Today</p>
              <h5 class="mb-0" id="checkoutCount">0</h5>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Calendar Filters -->
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Filter by Status</label>
              <select class="form-select" id="statusFilter">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
                <option value="completed">Completed</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">View</label>
              <select class="form-select" id="calendarView">
                <option value="dayGridMonth">Month</option>
                <option value="timeGridWeek">Week</option>
                <option value="timeGridDay">Day</option>
                <option value="listWeek">List</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">&nbsp;</label>
              <button class="btn btn-outline-secondary d-block w-100" type="button" onclick="resetFilters()">
                <i class="icon-base ri ri-refresh-line me-1"></i>
                Reset Filters
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Calendar Legend -->
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h6 class="mb-3">Calendar Legend</h6>
          <div class="d-flex flex-wrap gap-3">
            <div class="d-flex align-items-center">
              <span class="badge bg-warning me-2" style="width: 20px; height: 20px;"></span>
              <span>Pending</span>
            </div>
            <div class="d-flex align-items-center">
              <span class="badge bg-success me-2" style="width: 20px; height: 20px;"></span>
              <span>Approved</span>
            </div>
            <div class="d-flex align-items-center">
              <span class="badge bg-info me-2" style="width: 20px; height: 20px;"></span>
              <span>Completed</span>
            </div>
            <div class="d-flex align-items-center">
              <span class="badge bg-danger me-2" style="width: 20px; height: 20px;"></span>
              <span>Rejected</span>
            </div>
            <div class="d-flex align-items-center">
              <span class="badge bg-secondary me-2" style="width: 20px; height: 20px;"></span>
              <span>Cancelled</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Calendar -->
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div id="calendar"></div>
        </div>
      </div>
    </div>

    <!-- Upcoming Reservations -->
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Upcoming Check-ins (Next 7 Days)</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Guest Name</th>
                  <th>Room</th>
                  <th>Contact</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="upcomingReservations">
                <tr>
                  <td colspan="6" class="text-center text-body-secondary">Loading...</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Event Details Modal -->
  <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="icon-base ri ri-calendar-event-line me-2"></i>
            Reservation Details
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="eventModalBody">
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <a href="#" id="viewFullDetailsBtn" class="btn btn-primary">
            <i class="icon-base ri ri-eye-line me-1"></i>
            View Full Details
          </a>
        </div>
      </div>
    </div>
  </div>

  <style>
    #calendar {
      max-width: 100%;
      margin: 0 auto;
    }
    
    .fc-event {
      cursor: pointer;
      border: none !important;
      padding: 2px 4px;
    }
    
    .fc-event:hover {
      opacity: 0.8;
    }
    
    .fc-daygrid-event {
      white-space: normal !important;
    }
    
    .fc .fc-button-primary {
      background-color: #7367f0 !important;
      border-color: #7367f0 !important;
    }
    
    .fc .fc-button-primary:hover {
      background-color: #5e50ee !important;
      border-color: #5e50ee !important;
    }
    
    .fc .fc-button-primary:not(:disabled).fc-button-active {
      background-color: #5e50ee !important;
      border-color: #5e50ee !important;
    }
    
    .fc-toolbar-title {
      font-size: 1.5rem !important;
      font-weight: 600 !important;
    }
  </style>

  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    let calendar;
    let allEvents = [];
    
    document.addEventListener('DOMContentLoaded', function() {
      const calendarEl = document.getElementById('calendar');
      
      calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        height: 'auto',
        navLinks: true,
        selectable: true,
        selectMirror: true,
        editable: false,
        dayMaxEvents: true,
        events: function(info, successCallback, failureCallback) {
          fetch(`{{ route('super_admin.reservations.calendar-events') }}?start=${info.startStr}&end=${info.endStr}`)
            .then(response => response.json())
            .then(data => {
              allEvents = data;
              updateStats(data);
              successCallback(data);
            })
            .catch(error => {
              console.error('Error fetching events:', error);
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load calendar events'
              });
              failureCallback(error);
            });
        },
        eventClick: function(info) {
          info.jsEvent.preventDefault();
          const reservationId = info.event.id || info.event.extendedProps?.reservation_id;
          if (reservationId) {
            window.location.href = `/super-admin/reservations/${reservationId}`;
          }
        },
        eventDidMount: function(info) {
          info.el.title = info.event.title;
        }
      });
      
      calendar.render();
      
      // Today button
      document.getElementById('todayBtn').addEventListener('click', function() {
        calendar.today();
      });
      
      // Filter listeners
      document.getElementById('statusFilter').addEventListener('change', applyFilters);
      document.getElementById('calendarView').addEventListener('change', function() {
        calendar.changeView(this.value);
      });
    });
    
    function updateStats(events) {
      const today = new Date().toISOString().split('T')[0];
      
      let pending = 0;
      let approved = 0;
      let checkinToday = 0;
      let checkoutToday = 0;
      
      events.forEach(event => {
        const status = event.extendedProps?.reservation_status || event.status || '';
        const startDate = event.start ? new Date(event.start).toISOString().split('T')[0] : '';
        const endDate = event.end ? new Date(event.end).toISOString().split('T')[0] : '';
        
        if (status === 'pending') pending++;
        if (status === 'approved') approved++;
        if (startDate === today) checkinToday++;
        if (endDate === today) checkoutToday++;
      });
      
      document.getElementById('pendingCount').textContent = pending;
      document.getElementById('approvedCount').textContent = approved;
      document.getElementById('checkinCount').textContent = checkinToday;
      document.getElementById('checkoutCount').textContent = checkoutToday;
    }
    
    function applyFilters() {
      const statusFilter = document.getElementById('statusFilter').value;
      
      const filteredEvents = allEvents.filter(event => {
        const eventStatus = event.extendedProps?.reservation_status || event.status || '';
        return !statusFilter || eventStatus === statusFilter;
      });
      
      calendar.removeAllEvents();
      calendar.addEventSource(filteredEvents);
      updateStats(filteredEvents);
    }
    
    function resetFilters() {
      document.getElementById('statusFilter').value = '';
      calendar.removeAllEvents();
      calendar.addEventSource(allEvents);
      updateStats(allEvents);
    }
    
    function getStatusBadgeClass(status) {
      const statusMap = {
        'pending': 'bg-label-warning',
        'approved': 'bg-label-success',
        'rejected': 'bg-label-danger',
        'cancelled': 'bg-label-secondary',
        'completed': 'bg-label-info'
      };
      return statusMap[status] || 'bg-label-secondary';
    }
    
    function formatDate(dateString) {
      const date = new Date(dateString);
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
    }
    
    @if(session('success'))
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        showConfirmButton: false,
        timer: 3000
      });
    @endif

    @if(session('error'))
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '{{ session('error') }}',
        showConfirmButton: true
      });
    @endif
  </script>
@endsection
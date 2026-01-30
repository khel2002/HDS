<div class="card bg-transparent shadow-none ">
  <div class="card-body text-center">
    <img src="{{ asset('assets/img/frontpages/img/test.jpg') }}" class="card-img rounded-4  object-fit-cover"
      style="height: 220px;" alt="{{ $title ?? 'Room' }}">
    <h5 class="card-title" style="position: absolute; bottom:20%; left:41%; color:aliceblue;">{{ $title }}</h5>
    <p class="card-text text-muted mt-6">
      {{ $description ?? 'Spacious room with modern comfort and amenities.' }}
    </p>
  </div>
</div>


{{-- <div class="card bg-transparent shadow-none ">
  <div class="card-body text-center">
    <img src="{{ asset('assets/' . $image) }}" class="card-img rounded-4 object-fit-cover" style="height: 220px;"
      alt="{{ $title ?? 'Room' }}">

    <h5 class="card-title" style="position: absolute; bottom:20%; left:41%; color:aliceblue;">{{ $title }}</h5>
    <p class="card-text text-muted mt-6">
      {{ $description ?? 'Spacious room with modern comfort and amenities.' }}
    </p>
  </div>
</div> --}}

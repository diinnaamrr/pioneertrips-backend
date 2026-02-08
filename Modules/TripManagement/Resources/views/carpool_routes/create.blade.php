@extends('adminmodule::layouts.master')

@section('content')
<div class="container">
    <h2 class="mb-4">Create Carpool Route</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('carpool.store') }}">
        @csrf
        {{-- ① map picker --}}
        <div id="leaflet" style="height: 300px; border:1px solid #ced4da;"></div>

        <div class="row mt-3">
            <div class="col-md-6">
                <label class="form-label">Start Address</label>
                <input type="text" id="start_address" name="start_address" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">End Address</label>
                <input type="text" id="end_address" name="end_address" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Start Latitude</label>
                <input type="text" name="start_lat" id="start_lat" class="form-control" readonly required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Start Longitude</label>
                <input type="text" name="start_lng" id="start_lng" class="form-control" readonly required>
            </div>
            <div class="col-md-3">
                <label class="form-label">End Latitude</label>
                <input type="text" name="end_lat" id="end_lat" class="form-control" readonly required>
            </div>
            <div class="col-md-3">
                <label class="form-label">End Longitude</label>
                <input type="text" name="end_lng" id="end_lng" class="form-control" readonly required>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-3">
                <label class="form-label">Driver</label>
                <select name="user_id" class="form-select" required>
                    <option value="">Select Driver</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}">{{ $driver->full_name ?? ($driver->first_name . ' ' . $driver->last_name) }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Vehicle</label>
                <select name="vehicle_id" id="vehicle_id" class="form-select" required>
                    <option value="">Select a driver first</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Start Time</label>
                <input type="datetime-local" name="start_time" class="form-control" required>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Ride Type</label>
                <select name="ride_type" class="form-select">
                    <option value="work">Work</option>
                    <option value="personal">Personal</option>
                    <option value="airport">Airport</option>
                    <option value="shopping">Shopping</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-3">
                <label class="form-label">Seats Available</label>
                <input type="number" name="seats_available" class="form-control" min="1" max="8" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Price (EGP)</label>
                <input type="number" name="price" step="0.01" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Estimated Distance (km)</label>
                <input type="number" name="estimated_distance" step="0.1" class="form-control" placeholder="Optional">
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select">
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="wallet">Wallet</option>
                </select>
            </div>
        </div>

        {{-- ③ toggles --}}
        <div class="row mt-3">
            @php
                $toggles = [
                    'is_ac' => 'A/C',
                    'is_smoking_allowed' => 'Smoking',
                    'has_music' => 'Music',
                    'has_screen_entertainment' => 'Screens',
                    'allow_luggage' => 'Luggage',
                ];
            @endphp
            @foreach($toggles as $field => $label)
                <div class="col-md-2 form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1" id="{{ $field }}">
                    <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                </div>
            @endforeach
        </div>

        {{-- ④ demographics --}}
        <div class="row mt-3">
            <div class="col-md-4">
                <label class="form-label">Allowed Gender</label>
                <select name="allowed_gender" class="form-select" required>
                    <option value="both">Both</option>
                    <option value="male">Male only</option>
                    <option value="female">Female only</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Age Min</label>
                <input type="number" name="allowed_age_min" class="form-control" placeholder="Default: 18">
            </div>
            <div class="col-md-4">
                <label class="form-label">Age Max</label>
                <input type="number" name="allowed_age_max" class="form-control" placeholder="Default: 50">
            </div>
        </div>

        {{-- ⑤ JSON blobs --}}
        <div class="mt-3">
            <label class="form-label">Route Points (JSON)</label>
            <textarea name="route_points" class="form-control" rows="2" required>[{"lat":30.05,"lng":31.24}]</textarea>
        </div>
        <div class="mt-3">
            <label class="form-label">Rest Stops (JSON)</label>
            <textarea name="rest_stops" class="form-control" rows="2">[]</textarea>
        </div>

        <button class="btn btn-primary mt-4">Create Carpool Route</button>
    </form>
</div>

<!-- Keep your existing JavaScript -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCeF4BHLDezqD1pH7mlzxEchtX962QU9Os&libraries=places"></script>

<script>
// Your existing JavaScript code remains the same
document.addEventListener('DOMContentLoaded', () => {
  const map = L.map('leaflet').setView([30.0444, 31.2357], 11);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OSM'
  }).addTo(map);

  let startMarker = null, endMarker = null;

function setMarker(lat, lng, type) {
  const icon = L.icon({
    iconUrl: type === 'start'
      ? 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png'
      : 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    shadowSize: [41, 41]
  });

  const marker = L.marker([lat, lng], { draggable: true, icon }).addTo(map)
    .bindPopup(type === 'start' ? 'Start' : 'End').openPopup();

  // Helper: update inputs
  function updateInputs(lat, lng) {
    document.getElementById(type + '_lat').value = lat.toFixed(6);
    document.getElementById(type + '_lng').value = lng.toFixed(6);

    // Reverse geocode → address
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ location: { lat: lat, lng: lng } }, (results, status) => {
      if (status === "OK" && results[0]) {
        document.getElementById(type + '_address').value = results[0].formatted_address;
      }
    });
  }

  // Initial set
  updateInputs(lat, lng);

  // On drag
  marker.on('dragend', e => {
    const p = e.target.getLatLng();
    updateInputs(p.lat, p.lng);
  });

  if (type === 'start') {
    if (startMarker) map.removeLayer(startMarker);
    startMarker = marker;
  } else {
    if (endMarker) map.removeLayer(endMarker);
    endMarker = marker;
  }

  
  }

  map.on('click', e => {
    if (!startMarker) {
      setMarker(e.latlng.lat, e.latlng.lng, 'start');
    } else if (!endMarker) {
      setMarker(e.latlng.lat, e.latlng.lng, 'end');
    } else {
      map.removeLayer(startMarker);
      map.removeLayer(endMarker);
      startMarker = endMarker = null;
      setMarker(e.latlng.lat, e.latlng.lng, 'start');
    }
  });

  // Google Autocomplete
  const startInput = document.getElementById('start_address');
  const endInput = document.getElementById('end_address');
  const startAutocomplete = new google.maps.places.Autocomplete(startInput);
  const endAutocomplete = new google.maps.places.Autocomplete(endInput);

  [
    { auto: startAutocomplete, type: 'start' },
    { auto: endAutocomplete, type: 'end' }
  ].forEach(pair =>
    pair.auto.addListener('place_changed', () => {
      const place = pair.auto.getPlace();
      if (!place.geometry) return alert("No details available for: " + place.name);
      const lat = place.geometry.location.lat();
      const lng = place.geometry.location.lng();
      setMarker(lat, lng, pair.type);
      map.setView({ lat, lng }, 14);
    })
  );

  document.querySelector('form').addEventListener('submit', function (e) {
    if (!startMarker || !endMarker) {
      e.preventDefault();
      return alert('Please pick both Start AND End locations (via search or map).');
    }
  });

  // VEHICLE DROPDOWN AJAX
  const driverSelect = document.querySelector('select[name="user_id"]');
  const vehicleSelect = document.querySelector('#vehicle_id');

  driverSelect.addEventListener('change', function () {
    const driverId = this.value;
    vehicleSelect.innerHTML = '<option value="">Loading...</option>';

    if (!driverId) {
      vehicleSelect.innerHTML = '<option value="">Select a driver first</option>';
      return;
    }

    fetch(`/dashboard/carpool/vehicles-by-driver/${driverId}`)
      .then(res => res.json())
      .then(data => {
        if (data.length > 0) {
          vehicleSelect.innerHTML = '';
          data.forEach(vehicle => {
            const name = vehicle.licence_plate_number ?? vehicle.ref_id;
            vehicleSelect.innerHTML += `<option value="${vehicle.id}">${name}</option>`;
          });
          vehicleSelect.selectedIndex = 0;
        } else {
          vehicleSelect.innerHTML = '<option value="">No vehicles found</option>';
        }
      })
      .catch(err => {
        console.error(err);
        vehicleSelect.innerHTML = '<option value="">Error loading</option>';
      });
  });
});
</script>

@endsection
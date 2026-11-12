@if(session('success'))
    <div class="custom-alert" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i>{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="custom-alert custom-alert-error" role="alert"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="validation-summary" role="alert">
        <strong>Revisa estos datos</strong>
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

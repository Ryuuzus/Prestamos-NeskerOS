@extends('layouts.app')
@section('content')
    @include('reservation.list', ['listMode' => 'active'])
@endsection

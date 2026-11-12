@extends('layouts.app')
@section('content')
    @include('reservation.list', ['listMode' => 'history'])
@endsection

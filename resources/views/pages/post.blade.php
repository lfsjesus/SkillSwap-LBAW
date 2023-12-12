@extends('layouts.appLogged')

@section('title', 'User')

@section('content')

@include('partials.post', ['post' => $post])

@endsection

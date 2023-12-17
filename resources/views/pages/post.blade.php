@extends(Auth::guard('webadmin')->check() ? 'layouts.appLoggedAdmin' : 'layouts.appLogged')

@section('title', 'User')

@section('content')

@include('partials.post', ['post' => $post])

@endsection

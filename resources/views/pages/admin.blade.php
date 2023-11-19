@extends('layouts.app')

@section('title', 'User')

@section('content')
 
<section id="admin">

    <div>
        <h1>Admin</h1>
        <p>Here you login as an admin:</p>
        <h1>{{$admin->username}}</h1>
    </div>
    

</section>


@endsection


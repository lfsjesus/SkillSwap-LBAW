@extends('layouts.appLoggedAdmin')

@section('title', 'Create Profile Admin')

@section('content')

<!-- Edit Profile Section -->
<section id="create-profile" class="create-profile-section">
    <div class="container">
        <h1>Create Profile</h1>
        <form action="{{route('create_user_admin')}}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}
            @method('POST')
            <!-- Profile Picture -->
            <div id="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" name="profile_picture" id="profile_picture" class="form-control">
            </div>

            <!-- Name -->
            <div id="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control">
            </div>

            <!-- Email -->
            <div id="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control">
            </div>

            <!-- Username -->
            <div id="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control">
            </div>

            <!-- Password -->
            <div id="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control">
            </div>

            <!-- Password Confirmation -->
            <div id="form-group">
                <label for="password_confirmation">Password Confirmation</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
            </div>

            <!-- Phone Number -->
            <div id="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" name="phone_number" id="phone_number" class="form-control">
            </div>

            <!-- Birthdate -->
            <div id="form-group">
                <label for="birthdate">Birthdate</label>
                <input type="text" name="birth_date" id="birthdate" class="form-control">
            </div>

            <!-- Description -->
            <div id="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control"></textarea>
            </div>

            <!-- Submit Button -->
            <div id="form-group">
                <button type="submit" class="btn btn-primary">Create Profile</button>
            </div>
        </form>
    </div>
</section>

@endsection

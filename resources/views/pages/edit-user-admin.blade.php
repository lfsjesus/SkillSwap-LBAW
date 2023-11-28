@extends('layouts.appLoggedAdmin')

@section('title', 'Edit Profile Admin')

@section('content')

<!-- Edit Profile Section -->
<section id="edit-profile" class="edit-profile-section">
    <div class="container">
        <h1>Edit Profile</h1>
        <form action="{{route('edit_profile_admin')}}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}
            @method('PUT')
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <!-- Profile Picture -->
            <div id="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" name="profile_picture" id="profile_picture" class="form-control">
            </div>


            <!-- Name -->
            <div id="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}">
                @if ($errors->has('name'))
                <span class="error">
                    {{ $errors->first('name') }}
                </span>
                @endif
            </div>

            <!-- Email -->
            <div id="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}">
                @if ($errors->has('email'))
                <span class="error">
                    {{ $errors->first('email') }}
                </span>
                @endif
            </div>

            <!-- Username -->
            <div id="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" value="{{ $user->username }}">
                @if ($errors->has('username'))
                <span class="error">
                    {{ $errors->first('username') }}
                </span>
                @endif
            </div>

            <!-- Phone Number -->
            <div id="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ $user->phone_number }}">
                @if ($errors->has('phone_number'))
                <span class="error">
                    {{ $errors->first('phone_number') }}
                </span>
                @endif
            </div>

            <!-- Birthdate -->
            <div id="form-group">
                <label for="birthdate">Birthdate</label>
                <input type="text" name="birth_date" id="birthdate" class="form-control" value="{{ $user->birth_date->format('d/m/Y') }}">
                @if ($errors->has('birth_date'))
                <span class="error">
                    {{ $errors->first('birth_date') }}
                </span>
                @endif
            </div>

            <!-- Description -->
            <div id="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control">{{ $user->description }}</textarea>
                @if ($errors->has('description'))
                <span class="error">
                    {{ $errors->first('description') }}
                </span>
                @endif
            </div>

            <!-- Submit Button -->
            <div id="form-group">
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>
</section>

@endsection

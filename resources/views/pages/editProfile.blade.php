@extends('layouts.appLogged')

@section('title', 'Edit Profile')

@section('content')

<!-- Edit Profile Section -->
@if (session('error'))
<p class="error">
    {{ session('error') }}
</p>
@endif
<section id="edit-profile" class="edit-profile-section">
    <div class="container">
        <h1>Edit Profile</h1>
        <form action="{{ route('edit_user') }}" method="POST" enctype="multipart/form-data" id="edit-profile-form">
            {{ csrf_field() }}
            @method('PUT')
            
            <!-- Profile Picture -->
            <div id="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" name="profile_picture" id="profile_picture" class="form-control">
                @if ($errors->has('profile_picture'))
                <span class="error">
                    {{ $errors->first('profile_picture') }}
                </span>
                @endif
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
                <input type="date" name="birth_date" id="birthdate" class="form-control" value="{{ $user->birth_date->format('Y-m-d') }}">
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

            <!-- Public Profile -->
            <div id="form-group">
                <label for="visibility">Visibility</label>
                <select name="visibility" id="visibility" class="form-control">
                    <option value="1" {{ $user->public_profile ? 'selected' : '' }}>Public</option>
                    <option value="0" {{ $user->public_profile ? '' : 'selected' }}>Private</option>
                </select>
                @if ($errors->has('visibility'))
                <span class="error">
                    {{ $errors->first('visibility') }}
                </span>
                @endif
            </div>          

        </form>
        <form action="{{ route('delete_user') }}" method="POST" id="delete-profile-form">
            <input type="hidden" name="id" value="{{ $user->id }}">
            {{ csrf_field() }}
            @method('DELETE')
        </form>
        <button type="submit" form="edit-profile-form" class="btn btn-primary">Save Changes</button>
        <button type="submit" form="delete-profile-form" class="btn btn-danger">Delete Profile</button>
    </div>
</section>

@endsection

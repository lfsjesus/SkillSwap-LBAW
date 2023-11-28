@extends('layouts.appLoggedAdmin')

@section('title', 'Create Profile Admin')

@section('content')

<!-- Edit Profile Section -->
<section id="create-profile" class="create-profile-section">
    <div class="container">
        <h1>Create Profile</h1>
        <p class="error">
            {{ $errors->first('error') }}
        </p>
        <form action="{{route('create_user_admin')}}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}
            @method('POST')
            <!-- Profile Picture -->
            <div id="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" name="profile_picture" id="profile_picture" class="form-control" value="{{ old('profile_picture') }}">
                @if ($errors->has('profile_picture'))
                <span class="error">
                    {{ $errors->first('profile_picture') }}
                </span>
                @endif
            </div>

            <!-- Name -->
            <div id="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}">
                @if ($errors->has('name'))
                <span class="error">
                    {{ $errors->first('name') }}
                </span>
                @endif
            </div>

            <!-- Email -->
            <div id="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
                @if ($errors->has('email'))
                <span class="error">
                    {{ $errors->first('email') }}
                </span>
                @endif
            </div>

            <!-- Username -->
            <div id="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" value="{{ old('username') }}">
                @if ($errors->has('username'))
                <span class="error">
                    {{ $errors->first('username') }}
                </span>
                @endif
            </div>

            <!-- Password -->
            <div id="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" value="{{ old('password') }}">
            </div>

            <!-- Password Confirmation -->
            <div id="form-group">
                <label for="password_confirmation">Password Confirmation</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" value="{{ old('password_confirmation') }}">
                @if ($errors->has('password'))
                <span class="error">
                    {{ $errors->first('password') }}
                </span>
                @endif
            </div>

            <!-- Phone Number -->
            <div id="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ old('phone_number') }}">
                @if ($errors->has('phone_number'))
                <span class="error">
                    {{ $errors->first('phone_number') }}
                </span>
                @endif
            </div>

            <!-- Birthdate -->
            <div id="form-group">
                <label for="birthdate">Birthdate</label>
                <input type="date" name="birth_date" id="birthdate" class="form-control" value="{{ old('birthdate') }}">
                @if ($errors->has('birth_date'))
                <span class="error">
                    {{ $errors->first('birth_date') }}
                </span>
                @endif
            </div>

            <!-- Description -->
            <div id="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
                @if ($errors->has('description'))
                <span class="error">
                    {{ $errors->first('description') }}
                </span>
                @endif
            </div>

            <!-- Submit Button -->
            <div id="form-group">
                <button type="submit" class="btn btn-primary">Create Profile</button>
            </div>
        </form>
    </div>
</section>

@endsection

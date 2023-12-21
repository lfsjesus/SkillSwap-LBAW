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
                <div class="field-title">
                    <label for="profile_picture">Profile Picture</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Accepted formats: jpg, jpeg, png. Max size: 5MB.
                    </div>
                </div>
                <input type="file" name="profile_picture" id="profile_picture" class="form-control" value="{{ old('profile_picture') }}">
                @if ($errors->has('profile_picture'))
                <span class="error">
                    {{ $errors->first('profile_picture') }}
                </span>
                @endif
            </div>

            <!-- Name -->
            <div id="form-group">
                <div class="field-title">
                    <label for="name">Name *</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Write your full name here. Max 50 characters.
                    </div>
                </div>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" placeholder="Name" required>
                @if ($errors->has('name'))
                <span class="error">
                    {{ $errors->first('name') }}
                </span>
                @endif
            </div>

            <!-- Email -->
            <div id="form-group">
                <div class="field-title">
                    <label for="email">Email *</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Provide a valid email address.
                    </div>
                </div>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="Email" required>
                @if ($errors->has('email'))
                <span class="error">
                    {{ $errors->first('email') }}
                </span>
                @endif
            </div>

            <!-- Username -->
            <div id="form-group">
                <div class="field-title">
                    <label for="username">Username *</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Choose a unique username for your profile. Max 50 characters.
                    </div>
                </div>
                <input type="text" name="username" id="username" class="form-control" value="{{ old('username') }}" placeholder="Username" required>
                @if ($errors->has('username'))
                <span class="error">
                    {{ $errors->first('username') }}
                </span>
                @endif
            </div>

            <!-- Password -->
            <div id="form-group">
                <label for="password">Password *</label>
                <input type="password" name="password" id="password" class="form-control" value="{{ old('password') }}" placeholder="Password" required>
            </div>

            <!-- Password Confirmation -->
            <div id="form-group">
                <label for="password_confirmation">Password Confirmation *</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" value="{{ old('password_confirmation') }}" placeholder="Password Confirmation" required>
                @if ($errors->has('password'))
                <span class="error">
                    {{ $errors->first('password') }}
                </span>
                @endif
            </div>

            <!-- Phone Number -->
            <div id="form-group">
                <div class="field-title">
                    <label for="phone_number">Phone Number</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Format: +[Country Code (optional)][Number (8-15 characters)]. You can use '-'.
                    </div>
                </div>
                <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ old('phone_number') }}" placeholder="Phone Number">
                @if ($errors->has('phone_number'))
                <span class="error">
                    {{ $errors->first('phone_number') }}
                </span>
                @endif
            </div>

            <!-- Birthdate -->
            <div id="form-group">
                <div class="field-title">
                    <label for="birthdate">Birthdate *</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        "Enter your birthdate in the format DD-MM-YYYY.
                    </div>
                </div>
                <input type="date" name="birth_date" id="birthdate" class="form-control" value="{{ old('birthdate') }}" placeholder="Birthdate" required>
                @if ($errors->has('birth_date'))
                <span class="error">
                    {{ $errors->first('birth_date') }}
                </span>
                @endif
            </div>

            <!-- Description -->
            <div id="form-group">
                <div class="field-title">
                    <label for="description">Description</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Write a brief description about yourself. Max 500 characters.
                    </div>
                </div>
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

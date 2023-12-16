@extends(Auth::guard('webadmin')->check() ? 'layouts.appLoggedAdmin' : 'layouts.appLogged')

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
        <form action="{{ Auth::guard('webadmin')->check() ? route('edit_user_admin') : route('edit_user') }}" method="POST" id="edit-profile-form" enctype="multipart/form-data">
            {{ csrf_field() }}
            @method('PUT')
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <!-- Profile Picture -->
            <div id="form-group">
                <div class="field-title">
                    <label for="profile_picture">Profile Picture</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Accepted formats: jpg, png. Max size: 2MB.
                    </div>
                </div>

                <input type="file" name="profile_picture" id="profile_picture" class="form-control">
                @if ($errors->has('profile_picture'))
                    <span class="error">
                        {{ $errors->first('profile_picture') }}
                    </span>
                @endif
            </div>

            <!-- Name -->
            <div id="form-group">
                <div class="field-title">
                    <label for="name">Name</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Upload your profile picture here. Formats accepted: jpg, png. Max size: 2MB.
                    </div>
                </div>
                <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}">
                @if ($errors->has('name'))
                    <span class="error">
                        {{ $errors->first('name') }}
                    </span>
                @endif
            </div>
                        

            <!-- Email -->
            <div id="form-group">
                <div class="field-title">
                    <label for="email">Email</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Provide a valid email address.
                    </div>
                </div>
                <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}">
                @if ($errors->has('email'))
                    <span class="error">
                        {{ $errors->first('email') }}
                    </span>
                @endif
            </div>

            <!-- Username -->
            <div id="form-group">
                <div class="field-title">
                    <label for="username">Username</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Choose a unique username for your profile.
                    </div>
                </div>
                
                <input type="text" name="username" id="username" class="form-control" value="{{ $user->username }}">
                @if ($errors->has('username'))
                    <span class="error">
                        {{ $errors->first('username') }}
                    </span>
                @endif
            </div>

            <!-- Phone Number -->
            <div id="form-group">
                <div class="field-title">
                    <label for="phone_number">Phone Number</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Format: +[Country Code (optional)][Number (8-15 characters)].
                    </div>
                </div>
                
                <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ $user->phone_number }}">
                @if ($errors->has('phone_number'))
                    <span class="error">
                        {{ $errors->first('phone_number') }}
                    </span>
                @endif
            </div>

            <!-- Birthdate -->
            <div id="form-group">
                <div class="field-title">
                    <label for="birthdate">Birthdate</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        "Enter your birthdate in the format DD-MM-YYYY.
                    </div>
                </div>

                <input type="date" name="birth_date" id="birthdate" class="form-control" value="{{ $user->birth_date->format('Y-m-d') }}">
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
                        Write a brief description about yourself.
                    </div>
                </div>
    
                <textarea name="description" id="description" class="form-control">{{ $user->description }}</textarea>
                @if ($errors->has('description'))
                    <span class="error">
                        {{ $errors->first('description') }}
                    </span>
                @endif
            </div>

            <!-- Public Profile -->
            <div id="form-group">
                <div class="field-title">
                    <label for="visibility">Visibility</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Select 'Public' for everyone to view your details, or 'Private' for limited access.
                    </div>
                </div>
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
        <form action="{{ Auth::guard('webadmin')->check() ? route('delete_user_admin') : route('delete_user') }}" method="POST" id="delete-profile-form">
            <input type="hidden" name="id" value="{{ $user->id }}">
            {{ csrf_field() }}
            @method('DELETE')
        </form>
        <button type="submit" form="edit-profile-form" class="btn btn-primary">Save Changes</button>
        <button type="submit" form="delete-profile-form" class="btn btn-danger">Delete Profile</button>
    </div>
</section>

@endsection

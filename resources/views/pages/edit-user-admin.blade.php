@extends('layouts.appLoggedAdmin')

@section('title', 'Edit Profile Admin')

@section('content')

<!-- Edit Profile Section -->
<section id="edit-profile" class="edit-profile-section">
    <div class="container">
        <h1>Edit Profile</h1>
            @csrf
            @method('PUT')
            
            <!-- Profile Picture -->
            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" name="profile_picture" id="profile_picture" class="form-control" style="display: none">
            </div>
            <div class="post-files" id="attach-button">
                <span class="material-symbols-outlined">
                    attach_file
                </span>
            </div>

            <!-- Name -->
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}">
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}">
            </div>

            <!-- Username -->
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" value="{{ $user->username }}">
            </div>

            <!-- Phone Number -->
            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" name="phone_number" id="phone_number" class="form-control" value="{{ $user->phone_number }}">
            </div>

            <!-- Birthdate -->
            <div class="form-group">
                <label for="birthdate">Birthdate</label>
                <input type="text" name="birthdate" id="birthdate" class="form-control" value="{{ $user->birth_date->format('d/m/Y') }}">
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control">{{ $user->description }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>
</section>

@endsection

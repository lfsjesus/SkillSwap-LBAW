@extends('layouts.appLogged')

@section('title', 'Create Group')

@section('content')

<!-- Edit Profile Section -->
<section id="create-group" class="create-group-section">
    <div class="container">
        <h1>Create Group</h1>
        <form action="{{ route('create_group') }}" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}            
            <!-- Banner -->
            <div id="form-group">
                <div class="field-title">
                    <label for="banner">Banner</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Accepted formats: jpg, jpeg, png. Max size: 5MB.
                    </div>
                </div>
                <input type="file" name="banner" id="banner" class="form-control">
                @if ($errors->has('banner'))
                <span class="error">
                    {{ $errors->first('banner') }}
                </span>
                @endif
            </div>

            <!-- Name -->
            <div id="form-group">
                <div class="field-title">
                    <label for="name">Name *</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Write the group's name here. Max 50 characters.
                    </div>
                </div>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" placeholder="Group Name" required>
                @if ($errors->has('name'))
                <span class="error">
                    {{ $errors->first('name') }}
                </span>
                @endif
            </div>

            <!-- Description -->
            <div id="form-group">
                <div class="field-title">
                    <label for="description">Description</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Write a brief description about the group. Max 255 characters.
                    </div>
                </div>
                <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
                @if ($errors->has('description'))
                <span class="error">
                    {{ $errors->first('description') }}
                </span>
                @endif
            </div>

            <!-- Visibility -->
            <div id="form-group">
                <div class="field-title">
                    <label for="visibility">Visibility *</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Select 'Public' for everyone to view your details, or 'Private' for limited access.
                    </div>
                </div>
                <select name="visibility" id="visibility" class="form-control" required>
                    <option value="1">Public</option>
                    <option value="0">Private</option>
                </select>
                @if ($errors->has('visibility'))
                <span class="error">
                    {{ $errors->first('visibility') }}
                </span>
                @endif
            </div>

            <!-- Submit Button -->
            <div id="form-group">
                <button type="submit" class="btn btn-primary">Create Group</button>
            </div>
        </form>
    </div>
</section>

@endsection

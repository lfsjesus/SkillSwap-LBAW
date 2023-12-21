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
                <label for="banner">Banner</label>
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
                    <label for="name">Name</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Write the group's name here. Max 50 characters.
                    </div>
                </div>
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}">
                @if ($errors->has('name'))
                <span class="error">
                    {{ $errors->first('name') }}
                </span>
                @endif
            </div>

            <!-- Description -->
            <div id="form-group">
                <div class="field-title">
                    <label for="name">Description</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Write a brief description about the group. Max 255 characters.
                    </div>
                </div>
                <label for="description">Description</label>
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
                    <label for="visibility">Visibility</label>
                    <span class="help-icon material-symbols-outlined"> info </span>
                    <div class="help-tooltip">
                        Select 'Public' for everyone to view your details, or 'Private' for limited access.
                    </div>
                </div>
                <label for="visibility">Visibility</label>
                <select name="visibility" id="visibility" class="form-control">
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

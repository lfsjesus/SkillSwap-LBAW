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

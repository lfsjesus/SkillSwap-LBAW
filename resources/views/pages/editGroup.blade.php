@extends('layouts.appLogged')

@section('title', 'Create Group')

@section('content')

<!-- Edit Profile Section -->
<section id="edit-group" class="edit-group-section">
    <div class="container">
        <h1>Edit Group</h1>
        <form action="{{ route('edit_group') }}" method="POST" enctype="multipart/form-data" id="edit-group-form">
            @method('PUT')
            {{ csrf_field() }}            
            <input type="hidden" name="id" value="{{ $group->id }}">
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
                <input type="text" name="name" id="name" class="form-control" value="{{ $group->name }}" required>
                @if ($errors->has('name'))
                <span class="error">
                    {{ $errors->first('name') }}
                </span>
                @endif
            </div>

            <!-- Description -->
            <div id="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control">{{ $group->description }}</textarea>
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
                    <option value="1" {{ $group->public_group ? 'selected' : '' }}>Public</option>
                    <option value="0" {{ $group->public_group ? '' : 'selected' }}>Private</option>
                </select>
                @if ($errors->has('visibility'))
                <span class="error">
                    {{ $errors->first('visibility') }}
                </span>
                @endif
            </div>
        </form>
        <form action="{{ route('delete_group') }}" method="POST" id="delete-group-form">
            <input type="hidden" name="id" value="{{ $group->id }}">
            {{ csrf_field() }}
            @method('DELETE')
        </form>
        <button type="submit" form="edit-group-form" class="btn btn-primary">Save Changes</button>
        <button type="submit" form="delete-group-form" class="btn btn-danger">Delete Profile</button>
    </div>
</section>

@endsection

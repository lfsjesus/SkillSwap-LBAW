@extends(Auth::guard('webadmin')->check() ? 'layouts.appLoggedAdmin' : 'layouts.appLogged')

@section('title', 'Create Group')

@section('content')

<!-- Edit Profile Section -->
<section id="edit-group" class="edit-group-section">
    <div class="container">
        <h1>Edit Group</h1>
        <form action="{{ route('edit_group') }}" method="POST" id="edit-group-form" enctype="multipart/form-data">
            @method('PUT')
            {{ csrf_field() }}            
            <input type="hidden" name="id" value="{{ $group->id }}">
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
                <input type="text" name="name" id="name" class="form-control" value="{{ $group->name }}" placeholder="Group Name" required>
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
                <textarea name="description" id="description" class="form-control">{{ $group->description }}</textarea>
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
        <button type="submit" form="delete-group-form" class="btn btn-danger">Delete Group</button>
    </div>
</section>

@endsection

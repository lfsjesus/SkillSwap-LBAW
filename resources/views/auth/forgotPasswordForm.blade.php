@extends('layouts.app')

@section('content')
<div class="reset-password-page">
    <div class="auth-form">
        <h1>Reset Your Password</h1>

        <!-- Display Validation Errors -->
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Password Reset Form -->
        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <!-- Password Reset Token (Hidden) -->
            <input type="hidden" name="token" value="{{ $token }}">

            <!-- Email (Hidden) -->
            <input type="hidden" name="email" value="{{ $email }}">

            <!-- Password -->
            <div>
                <label for="password">New Password</label>
                <input id="password" type="password" name="password" required>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation">Confirm New Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit">Reset Password</button>
            </div>
        </form>
    </div>
</div>
@endsection


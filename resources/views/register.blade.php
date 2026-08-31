<x-layout title="Register">
    <p>Enter your username and phone number to get your unique link.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <label for="username">Username</label>
            <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus>
            @error('username')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone_number">Phone number</label>
            <input id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number') }}" required>
            @error('phone_number')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <button type="submit">Register</button>
        </div>
    </form>
</x-layout>

<!DOCTYPE html>
<html>
<head>
    <title>Lead Form</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; background: #1e1e2e; color: #cdd6f4; }
        h1 { color: #cdd6f4; }
        input { width: 100%; padding: 8px; margin: 5px 0 15px; border: 1px solid #45475a; border-radius: 4px; box-sizing: border-box; background: #313244; color: #cdd6f4; }
        button { background: #89b4fa; color: #1e1e2e; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        button:hover { background: #74c7ec; }
        .success { color: #a6e3a1; }
        .error { color: #f38ba8; }
    </style>
</head>
<body>

    <h1>Send Lead</h1>

    @if(isset($success))
        <p class="success">Lead sent successfully! Lead ID: {{ $leadId }}</p>
    @endif

    @if(isset($authError))
        <p class="error">Authorization failed. API key missing/invalid.</p>
    @endif

    @if(isset($serverError))
        <p class="error">Server error: {{ $serverError }}</p>
    @endif

    @if($errors->any())
        <ul class="error">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="/lead-form">
        @csrf
        <p>First Name: <input type="text" name="first_name" value="{{ old('first_name') }}" required></p>
        <p>Last Name: <input type="text" name="last_name" value="{{ old('last_name') }}" required></p>
        <p>Email: <input type="text" name="email" value="{{ old('email') }}" required></p>
        <p>Phone: <input type="text" name="phone_number" value="{{ old('phone_number') }}" required></p>
        <button type="submit">Send</button>
    </form>

</body>
</html>

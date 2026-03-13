<div>
    <h1>Send Lead</h1>

    @if($success)
        <p class="success">Lead sent successfully! Lead ID: {{ $leadId }}</p>
    @endif

    @if($authError)
        <p class="error">Authorization failed. API key missing/invalid.</p>
    @endif

    @if($serverError)
        <p class="error">Server error: {{ $serverError }}</p>
    @endif

    @if(!$success)
        <form wire:submit="submit">
            <p>
                First Name:
                <input type="text" wire:model="first_name" required>
            </p>
            @error('first_name')
                <p class="error">{{ $message }}</p>
            @enderror

            <p>
                Last Name:
                <input type="text" wire:model="last_name" required>
            </p>
            @error('last_name')
                <p class="error">{{ $message }}</p>
            @enderror

            <p>
                Email:
                <input type="text" wire:model="email" required>
            </p>
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror

            <p>
                Phone:
                <input type="text" wire:model="phone_number" required>
            </p>
            @error('phone_number')
                <p class="error">{{ $message }}</p>
            @enderror

            <button type="submit" wire:loading.attr="disabled">Send</button>
        </form>
    @endif

</div>

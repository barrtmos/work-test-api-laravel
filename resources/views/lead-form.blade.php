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
        button:disabled { background: #45475a; cursor: not-allowed; }
        .success { color: #a6e3a1; }
        .error { color: #f38ba8; }
    </style>

    <!-- Facebook Pixel Base Code -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ config('services.facebook.pixel_id') }}');
        fbq('track', 'PageView');
    </script>
    <noscript>
        <img height="1" width="1" style="display:none"
             src="https://www.facebook.com/tr?id={{ config('services.facebook.pixel_id') }}&ev=PageView&noscript=1"/>
    </noscript>
</head>
<body>
    <h1>Send Lead</h1>

    <div id="message"></div>

    <form id="leadForm">
        <input type="hidden" id="event_id"   value="{{ $eventId }}">
        <input type="hidden" id="ip_address" value="{{ $ipAddress }}">
        <input type="hidden" id="user_agent" value="{{ $userAgent }}">
        <p>First Name: <input type="text" id="first_name" required></p>
        <p>Last Name:  <input type="text" id="last_name"  required></p>
        <p>Email:      <input type="text" id="email"      required></p>
        <p>Phone:      <input type="text" id="phone_number" required></p>
        <button type="submit" id="submitBtn">Send</button>
    </form>

    <script>
        document.getElementById('leadForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const eventId = document.getElementById('event_id').value;
            const btn = document.getElementById('submitBtn');
            const msg = document.getElementById('message');

            btn.disabled = true;

            fetch('/api/lead', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    first_name:   document.getElementById('first_name').value,
                    last_name:    document.getElementById('last_name').value,
                    email:        document.getElementById('email').value,
                    phone_number: document.getElementById('phone_number').value,
                    ip_address:   document.getElementById('ip_address').value,
                    user_agent:   document.getElementById('user_agent').value,
                    event_id:     eventId,
                })
            })
            .then(r => r.json().then(data => ({ status: r.status, data })))
            .then(({ status, data }) => {
                if (status === 201) {
                    msg.innerHTML = '<p class="success">Lead sent! ID: ' + data.lead_id + '</p>';
                    document.getElementById('leadForm').style.display = 'none';
                    fbq('track', 'Lead', {}, { eventID: eventId });
                } else if (status === 422) {
                    const errs = Object.values(data.errors).flat().join('<br>');
                    msg.innerHTML = '<p class="error">' + errs + '</p>';
                    btn.disabled = false;
                } else {
                    msg.innerHTML = '<p class="error">Something went wrong. Please try again.</p>';
                    btn.disabled = false;
                }
            })
            .catch(() => {
                msg.innerHTML = '<p class="error">Network error. Please try again.</p>';
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>
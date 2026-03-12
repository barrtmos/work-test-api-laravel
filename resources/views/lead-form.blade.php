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
    @livewireStyles

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
    <livewire:lead-form />
    @livewireScripts
</body>
</html>

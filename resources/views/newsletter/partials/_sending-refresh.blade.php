{{-- While a newsletter is going out, its row on the list and its statistics say how far it has
     got ("Sending: 312 of 1,234"). The page asks again every 15 seconds until nothing is sending,
     and never while it sits in a background tab. Expects $sending. --}}
@if ($sending ?? false)
<script {!! nonce_attr() !!}>
    setInterval(function () {
        if (document.visibilityState === 'visible') {
            window.location.reload();
        }
    }, 15000);
</script>
@endif

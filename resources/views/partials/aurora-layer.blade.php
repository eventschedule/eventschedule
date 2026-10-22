{{-- One Aurora: the lights, mirrored or not, and the grain. Every value is a number from
     App\Utils\Aurora. $still: the page's own reason to hold it still (an embed sits in somebody
     else's site, a graphic is a picture); the owner's switch is added by attributes(). --}}
<div class="gp-aurora" @foreach ($aurora->attributes($still ?? false) as $name => $value) {{ $name }}="{{ $value }}" @endforeach>
    <div class="gp-aurora-m">@foreach ($aurora->lightStyles() as $lightStyle)<i style="{{ $lightStyle }}"></i>@endforeach</div>
    <div class="gp-aurora-grain"></div>
</div>

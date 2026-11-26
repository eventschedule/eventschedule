{{-- The guest list while its events load: the list's own shape (.gk-day, .gk-dayhead, .gk-row and
     its picture column, partials/guest-kit-styles and guest-stage-styles) with bars where the
     words will be, so nothing moves when the events arrive. The placeholder this replaced was a
     generic card with the picture on the wrong side and no date column, and the list jumped
     sideways and changed height the moment it filled.

     It is what a visitor sees before the page's script has started as well (role/partials/
     calendar puts it outside v-cloak), so it is plain markup: no binding in it reads anything. --}}
<div class="gk-days gk-wait animate-pulse" aria-hidden="true">
    @foreach ([3, 2] as $rows)
    <section class="gk-day">
        <div class="gk-panel gk-dayhead">
            <span class="gk-dayhead-word"><i class="gk-wait-bar" style="width: 3.5rem"></i></span>
            <span class="gk-dayhead-title"><i class="gk-wait-bar" style="width: 9rem"></i></span>
            <span class="gk-dayhead-big"><span class="gk-dh-week"><i class="gk-wait-bar" style="width: 3.5rem"></i></span><span class="gk-dh-num"><i class="gk-wait-bar" style="width: 2.25rem"></i></span><span class="gk-dh-month"><i class="gk-wait-bar" style="width: 4rem"></i></span></span>
            <span class="gk-dh-count"><i class="gk-wait-bar" style="width: 3rem"></i></span>
        </div>
        <ul class="gk-rows">
            @for ($i = 0; $i < $rows; $i++)
            <li class="gk-row-item">
                <div class="gk-row">
                    <div class="gk-row-time"><i class="gk-wait-bar" style="width: 4rem"></i></div>
                    <div class="gk-row-body">
                        <div class="gk-row-title"><i class="gk-wait-bar" style="width: {{ [72, 58, 66][$i % 3] }}%"></i></div>
                        <p class="gk-row-who"><i class="gk-wait-bar" style="width: {{ [44, 52, 38][$i % 3] }}%"></i></p>
                    </div>
                    <div class="gk-row-media gk-wait-media"></div>
                </div>
            </li>
            @endfor
        </ul>
    </section>
    @endforeach
</div>

{{-- Script, included inside a script tag by the campaign pages. A chart's ink and grid are read from
     the live palette, never written as hex: the portal has six palettes and the picker switches
     between them without a reload. The old test (the `dark` class, or the system's preference
     unless a `light` class was set) drew a dark chart on a light page for anyone whose computer
     is dark and whose portal is light, because nothing ever sets a `light` class. --}}
@php $boostChartLocale = str_replace('_', '-', app()->getLocale()); @endphp
            function boostProbe(className, property) {
                const el = document.createElement('span');
                el.className = className;
                el.style.display = 'none';
                document.body.appendChild(el);
                const value = getComputedStyle(el)[property];
                el.remove();
                return value;
            }
            function boostChartPalette() {
                const root = getComputedStyle(document.documentElement);
                return {
                    blue: root.getPropertyValue('--brand-blue').trim(),
                    blueSoft: root.getPropertyValue('--brand-blue-a10').trim() || 'transparent',
                    green: '#10B981',
                    grid: boostProbe('border-gray-200 dark:border-gray-700', 'borderTopColor'),
                    ink: boostProbe('text-gray-500 dark:text-gray-400', 'color'),
                };
            }
            // "2026-10-03" as the reader writes a day ("Oct 3"), so the axis is not a row of ISO dates.
            function boostDayLabel(value) {
                const parts = /^(\d{4})-(\d{2})-(\d{2})/.exec(String(value));
                if (! parts) {
                    return value;
                }
                try {
                    return new Date(parts[1], parts[2] - 1, parts[3]).toLocaleDateString(@json($boostChartLocale), { month: 'short', day: 'numeric' });
                } catch (e) {
                    return value;
                }
            }

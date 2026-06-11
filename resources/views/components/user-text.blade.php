{{-- Renders user-controlled text inside a Vue-mounted element safely. The app
     mounts Vue with the runtime template compiler, so the mount element's HTML is
     compiled as a Vue template; v-pre tells Vue to skip this subtree so Vue mustache
     expressions in user data are left as literal text instead of executed as JS. --}}
<span {{ $attributes }} v-pre>{{ $slot }}</span>

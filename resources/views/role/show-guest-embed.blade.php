@extends('master')

@section('title', $role->name)

@section('content')
<body class="antialiased dark:bg-black dark:text-white/50">
    <div class="px-4 pb-4 max-w-5xl mx-auto">

        @include('role/partials/calendar', ['route' => 'guest', 'tab' => ''])
    
    </div>
</body>
@endsection

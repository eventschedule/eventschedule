@extends('errors::minimal')

@section('title', __('Server Error'))
@section('code', '500')

@if (config('app.hosted'))
    @section('message', __('Server Error'))
@elseif (config('app.url'))
    @section('message', 'Check /storage/logs for more information')
@else
    @section('message', 'Copy .env.example to .env and then refresh the page')
@endif

<!DOCTYPE html>
<html class="h-100" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>iCARRY</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <link href="{{ env('APP_URL') === 'http://localhost:8000' ?  asset('css/bootstrap.min.css') : secure_asset('css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ env('APP_URL') === 'http://localhost:8000' ? asset('css/styles.css') : secure_asset('css/styles.css') }}" rel="stylesheet" />
</head>

<body class="antialiased h-100">

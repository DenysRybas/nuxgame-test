@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <style>
        :root { color-scheme: light dark; }
        body { font: 1rem/1.5 system-ui, sans-serif; margin: 0; padding: 2rem 1rem; }
        main { max-width: 34rem; margin: 0 auto; display: grid; gap: 1.5rem; }
        h1 { font-size: 1.5rem; margin: 0; }
        h2 { font-size: 1rem; margin: 0; }
        form { display: grid; gap: 1rem; }
        label { display: block; margin-bottom: .25rem; font-weight: 600; }
        input { width: 100%; padding: .5rem; font: inherit; box-sizing: border-box; }
        button { padding: .5rem 1rem; font: inherit; cursor: pointer; }
        ul { list-style: none; margin: 0; padding: 0; display: grid; gap: .5rem; }
        li { border: 1px solid; padding: .5rem; display: flex; gap: 1rem; justify-content: space-between; }
        .error { color: #b00020; margin: .25rem 0 0; }
        .actions { display: flex; gap: .5rem; flex-wrap: wrap; }
        .result { text-align: center; }
        .number { font-size: 2.5rem; font-weight: 700; }
        hr { width: 100%; border: 0; border-top: 1px solid; }
    </style>
</head>
<body>
    <main>
        <h1>{{ $title }}</h1>
        {{ $slot }}
    </main>
</body>
</html>

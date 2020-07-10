<link rel="stylesheet" href="{{ mix('app.css', 'adminarchitect_assetpath') }}">
<link rel="stylesheet" href="{{ mix('vendor.css', 'adminarchitect_assetpath') }}">

@if(file_exists(public_path($icons = 'glyphicons.css')))
    <link rel="stylesheet" href="{{ mix($icons) }}">
@endif

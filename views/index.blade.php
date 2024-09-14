<!doctype html>
<html lang="hu">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ substr(strip_tags(trim($title)),0, 100) }}</title>
    @include('datatable::header')
    {!! config('simplecrud.wysiwyg_in_head')  !!}
    @if(isset($customHeader) && is_array($customHeader))
        @foreach($customHeader as $blade)
            @include($blade)
        @endforeach
    @endif
</head>
<body>
@if(isset($customBefore) && is_array($customBefore))
    @foreach($customBefore as $blade)
        @include($blade)
    @endforeach
@endif
<div id="crud-container" class="simplecrud-container {{ config('simplecrud.html_class.simplecrud-container') }}">
    @if (session()->has('error'))
        <div id="simplecrud-error" class="{{ config('simplecrud.html_class.error-msg') }}">{{ session('error') }}</div>
    @elseif(session()->has('success'))
        <div id="simplecrud-success" class="{{ config('simplecrud.html_class.success-msg') }}">{{ session('success') }}</div>
    @endif

    @if(isset($error) && !empty($error))
        <div id="simplecrud-error" class="{{ config('simplecrud.html_class.error-msg') }}">{{ $error }}</div>
    @elseif(isset($success) && !empty($success))
        <div id="simplecrud-success" class="{{ config('simplecrud.html_class.success-msg') }}">{{ $esuccess }}</div>
    @endif
        <h2 style="" class="simplecrud-h2 {{ config('simplecrud.html_class.h2_tag') }}">{{ substr(strip_tags(trim(\DelocalZrt\SimpleCrud\Services\SimpleCrudHelper::getTextValueByLang($title))),0, 100) }}</h2>

    @isset($description)
        <div id="simplecrud-description-container" class="simplecrud-description-container {{ config('simplecrud.html_class.simplecrud-description-container') }}">
            {!! $description !!}
        </div>
    @endisset

    @isset($content)
        <div id="simplecrud-content-container" class="simplecrud-content-container {{ config('simplecrud.html_class.simplecrud-content-container') }}">
            {!! $content !!}
        </div>
    @endisset
    @isset($datatable)
        <div id="simplecrud-datatable-container" class="simplecrud-datatable-container  {{ config('simplecrud.html_class.simplecrud-datatable-container') }}">
            {!! $datatable !!}
        </div>
    @endisset
</div>
@if(!empty($csvfiles))
    <div>
        <h4>Nagyobb CSV export fájlok</h4>
        <ul>
            @foreach($csvfiles as $url => $csv)
                <li><a target="_blank" href="{{ $url }}">{{ $csv }}</a></li>
            @endforeach
        </ul>
    </div>
@endif
@if(isset($customAfter) && is_array($customAfter))
    @foreach($customAfter as $blade)
        @include($blade)
    @endforeach
@endif

@include('datatable::footer')
</body>
</html>

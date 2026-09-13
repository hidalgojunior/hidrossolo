@extends('layouts.main')

@section('content')
<section class="hero">
    <div class="container">
        <h1>{{ $page['title'] }}</h1>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                @if ($page['featured_image'])
                    <img src="{{ $page['featured_image'] }}" alt="{{ $page['title'] }}" class="img-fluid rounded-4 shadow mb-4 w-100" style="max-height:400px;object-fit:cover">
                @endif

                <div class="content">
                    {!! $page['content'] !!}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

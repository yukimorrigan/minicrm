@props(['prev' => '/', 'prevHeader' => ''])
<div class="col-sm-6">
    <ol class="breadcrumb float-sm-right">
        @if (strcasecmp($prevHeader, $slot) != 0)
            <li class="breadcrumb-item"><a href="{{$prev}}">{{$prevHeader}}</a></li>
            <li class="breadcrumb-item active">{{$slot}}</li>
        @endif
    </ol>
</div>

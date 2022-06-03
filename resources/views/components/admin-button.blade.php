<a {{ $attributes->merge(['href' => $href, 'class' => 'btn btn-app', 'data-toggle' => $toggle, 'data-target' => $target]) }}>
    <i class="fas fa-{{$icon}}"></i>{{$slot}}
</a>

@if ($modal)
    <div class="modal fade" id="{{ltrim($target, '#')}}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{$modalHeader}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{__('Close')}}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>{{$modalText}}</p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{__('No')}}</button>
                    <form action="{{route("$table.$action", $params)}}" method="post">
                        {{ method_field(mb_strtoupper($method)) }}
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-primary">{{$modalButton}}</button>
                    </form>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
@endif

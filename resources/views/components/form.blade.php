<div class="row">
    <div class="col-lg-6">
        <form method="post" action="{{$action}}" enctype="multipart/form-data" class="pl-2 mb-3">
            {{ method_field(mb_strtoupper($method)) }}
            {{ csrf_field() }}
            @isset($controls)
                @foreach($controls as $i => $control)
                    @switch($control['type'])
                        @case('text')
                        @case('email')
                        <div class="form-group">
                            <input type="{{$control['type']}}"
                                   name="{{$control['name']}}"
                                   placeholder="{{$control['placeholder']}}"
                                   value="{{old($control['name']) ?? $control['value'] ?? ''}}"
                                   class="form-control {{$errors->has($control['name']) ? 'is-invalid' : ''}}"
                                   required>
                            @if($errors->has($control['name']))
                                <span class="error invalid-feedback">{{implode('<br>', $errors->get($control['name']))}}</span>
                            @endif
                        </div>
                        @break

                        @case('select')
                        <div class="form-group">
                            <select name="{{$control['name']}}"
                                    class="form-control {{$errors->has($control['name']) ? 'is-invalid' : ''}}"
                                    required>
                                <option value="">{{$control['placeholder']}}</option>
                                @foreach($control['values'] as $value => $text)
                                    <option value="{{$value}}" {{old($control['name']) == $value || $control['value'] == $value
                                        ? 'selected' : ''}}>{{$text}}</option>
                                @endforeach
                            </select>
                            @if($errors->has($control['name']))
                                <span class="error invalid-feedback">{{implode('<br>', $errors->get($control['name']))}}</span>
                            @endif
                        </div>
                        @break

                        @case('image')
                        <div class="form-group">
                            <img id="file_{{$i}}_preview"
                                 width="200px" height="200px"
                                 src="{{$control['value']}}"
                                 alt="{{$control['placeholder']}}">
                            <div class="custom-file mt-3">
                                <input accept="image/*" type="file"
                                       name="{{$control['name']}}"
                                       lang="{{config('app.locale')}}"
                                       class="custom-file-input {{$errors->has($control['name']) ? 'is-invalid' : ''}}"
                                       id="file_{{$i}}">
                                <label class="custom-file-label" for="file_{{$i}}">{{$control['placeholder']}}</label>
                                @if($errors->has($control['name']))
                                    <span class="error invalid-feedback">{{implode('<br>', $errors->get($control['name']))}}</span>
                                @endif
                            </div>
                        </div>
                        <script>
                            let fileInput = document.getElementById('file_{{$i}}');
                            let filePreview = document.getElementById('file_{{$i}}_preview');
                            fileInput.onchange = evt => {
                                const [file] = fileInput.files;
                                filePreview.src = file ? URL.createObjectURL(file) : '{{$control['value']}}';
                            }
                        </script>
                        @break
                    @endswitch
                @endforeach
            @endisset

            <button type="submit" class="btn btn-primary">{{$btnText}}</button>

        </form>
    </div>
</div>

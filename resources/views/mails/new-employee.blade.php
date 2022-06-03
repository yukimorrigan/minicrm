@component('mail::message')
    <p>
        {!!__('emails.new_employee_title', [
            'id' => $employee['id'],
            'link' => route('employees.edit', ['employee' => $employee['id']])
        ])!!}
    </p>
    @foreach($employee as $field => $value)
        @continue($field == 'id')
        <p>{{$field}}: {{$value}}</p>
    @endforeach
@endcomponent

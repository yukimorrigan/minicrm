<x-admin-layout>

    <x-slot name="prev">{{route('employees.index')}}</x-slot>
    <x-slot name="prevHeader">{{__('Employees')}}</x-slot>
    <x-slot name="header">
        @lang('admin.'.$action) @lang('admin.employee_genitive')
    </x-slot>

    <x-form :action="$route"
            :method="$method"
            :controls="$controls"
            :btnText="__('admin.'.$action)"/>

    @if (session('modal'))
        {{session('modal')->render()}}
    @endif

</x-admin-layout>

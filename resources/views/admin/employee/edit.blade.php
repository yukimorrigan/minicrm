<x-admin-layout>

    <x-slot name="prev">{{route('employees.index')}}</x-slot>
    <x-slot name="prevHeader">{{__('Employees')}}</x-slot>
    <x-slot name="header">
        @lang('admin.edit') @lang('admin.employee_genitive')
    </x-slot>

    <x-form :action="route('employees.update', ['employee' => $id])"
            :method="'put'"
            :controls="$controls"
            :btnText="__('admin.edit')"/>

    @if (session('modal'))
        {{session('modal')->render()}}
    @endif

</x-admin-layout>

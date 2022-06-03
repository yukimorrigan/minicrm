<x-admin-layout>

    <x-slot name="prev">{{route('employees.index')}}</x-slot>
    <x-slot name="prevHeader">{{__('Employees')}}</x-slot>
    <x-slot name="header">
        {{ __('Employees') }}
    </x-slot>

    <x-table
        :table="'employees'"
        :entity="'employee'"
        :columns="['id', 'company_name', 'first_name', 'last_name', 'email', 'phone', 'edit', 'delete']"
    />

    @if (session('modal'))
        {{session('modal')->render()}}
    @endif

</x-admin-layout>

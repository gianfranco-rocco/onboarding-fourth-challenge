@extends('layout')

@section ('title')
    Cities
@endsection

@section('body')
    <x-table :records="$cities">
        <x-slot:heading>
            <th>ID</th>
            <th>Name</th>
            <th>Incoming Flights</th>
            <th>Outgoing Flights</th>
            <th></th>
        </x-slot:heading>
        
        <x-slot:rows>
            @foreach($cities as $city)
                <tr>
                    <td>{{ $city->id }}</td>
                    <td>{{ $city->name }}</td>
                    <td>{{ $city->count_incoming_fligths ?? 0 }}</td>
                    <td>{{ $city->count_outgoing_flights ?? 0 }}</td>
                    <td>
                        <x-button>Edit</x-button>
                        <x-button>Delete</x-button>
                    </td>
                </tr>
            @endforeach
        </x-slot:rows>
    </x-table>
@endsection
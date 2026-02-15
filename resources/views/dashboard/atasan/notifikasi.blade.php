@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header 
            title="Notifikasi" 
            subtitle="Pantau status pengajuan Anda" 
            :showNotification="true" 
            :showProfile="true" 
        />

        <div class="dashboard-content">
            <livewire:notification-list />
        </div>
    </div>
</div>

@endsection

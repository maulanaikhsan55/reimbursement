@extends('layouts.app')

@section('title', 'Notifikasi - Reimbursement System')

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Notifikasi" subtitle="Pantau semua notifikasi sistem reimbursement Anda" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content">
            <livewire:notification-list />
        </div>
    </div>
</div>


@endsection

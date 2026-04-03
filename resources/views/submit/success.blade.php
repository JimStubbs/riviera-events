@extends('layouts.calendar')

@section('title', 'Submission Received — ' . config('app.name'))

@section('content')
<div class="max-w-lg mx-auto text-center py-16">
    <div class="text-5xl mb-4">✅</div>
    @if(session('verified'))
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Email Verified!</h1>
    <p class="text-gray-600 mb-6">Your event has been verified and is now pending review by our team. We'll have it live within 24 hours.</p>
    @elseif(request()->has('featured'))
    <h1 class="text-2xl font-bold text-gray-900 mb-2">★ Featured Listing Confirmed!</h1>
    <p class="text-gray-600 mb-6">Payment received — your event will appear in the Featured carousel within minutes. Check your email for the verification link to complete your submission.</p>
    @else
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Submission Received!</h1>
    <p class="text-gray-600 mb-6">We've sent a verification email to the address you provided. Please click the link in that email to complete your submission.</p>
    @endif
    <a href="{{ route('calendar.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
        ← Back to Events
    </a>
</div>
@endsection

@extends('landing-page.layouts.master')

@section('title', 'Wayak – Smart Delivery Platform')

@section('content')

    {{-- ===========================
        HERO SECTION
    ============================ --}}
    @include('landing-page.sections.hero')

    {{-- ===========================
        About SECTION
    ============================ --}}
    @include('landing-page.sections.about')

    {{-- ===========================
        PLATFORM FEATURES SECTION
    ============================ --}}
    @include('landing-page.sections.features')

    {{-- ===========================
        EARN MONEY (DRIVER) SECTION
    ============================ --}}
    @include('landing-page.sections.earn-money')

    {{-- ===========================
         achievements SECTION
    ============================ --}}
    @include('landing-page.sections.achievements')

    {{-- ===========================
        download-app SECTION
    ============================ --}}
    @include('landing-page.sections.download-app')

    {{-- ===========================
        CONTACT SECTION
    ============================ --}}
    @include('landing-page.sections.contact')


    {{-- ===========================
        testimonials SECTION
    ============================ --}}
    @include('landing-page.sections.testimonials')

@endsection

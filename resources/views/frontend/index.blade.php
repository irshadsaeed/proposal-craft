@extends('frontend.layouts.frontend')

@section('title', 'ProposalCraft — Beautiful Proposals That Close Deals Faster')
@section('description', 'ProposalCraft helps freelancers, agencies, and businesses create stunning, interactive proposals in minutes. Drag-and-drop editor, e-signatures, real-time tracking, and more.')
@section('og_title', 'ProposalCraft — Win More Deals With Beautiful Proposals')
@section('og_description', 'Create stunning, interactive proposals in minutes. Used by 25,000+ freelancers, agencies, and businesses worldwide.')

@section('content')
  @include('frontend.partials.hero')
  @include('frontend.partials.problem-solution')
  @include('frontend.partials.features')
  @include('frontend.partials.how-it-works')
  @include('frontend.partials.pricing')
  @include('frontend.partials.testimonials')
  @include('frontend.partials.faq')
  @include('frontend.partials.cta')
  @include('frontend.partials.contact')
@endsection
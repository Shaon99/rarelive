@extends('backend.auth.master')
@section('content')
    <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-sm p-8 space-y-6">
            <div class="flex flex-col items-center space-y-2">
                <x-heroicon-o-key class="w-12 h-12" />
                <h2 class="text-2xl font-bold">{{ __('Reset Your Password') }}</h2>
                <p class="text-gray-600 text-sm text-center">
                    {{ __('Enter your email address and we wll send you a code to reset your password') }}
                </p>
            </div>
            @if (session('error'))
                <div class="flex items-center justify-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-zinc-800 dark:text-red-400"
                    role="alert">
                    <x-heroicon-o-shield-exclamation class="w-6 h-6 text-red-500 mr-2" />
                    <span class="font-medium text-center w-full capitalize">{{ session('error') }}</span>
                </div>
            @endif
            <form x-data="{ loading: false }" @submit.prevent="loading = true; $el.submit();" class="space-y-4"
                action="{{ route('admin.password.reset') }}" method="POST">
                @csrf
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        {{ __('Email Address') }}
                    </label>
                    <input type="email" name="email"
                        class="w-full px-3 py-2 border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500
                 @error('email') border-red-500 @enderror"
                        placeholder="{{ __('Enter your email address') }}" required />
                    @error('email')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>
                <button type="submit" :disabled="loading"
                    class="w-full bg-gray-900 text-white py-2 mb-5 rounded-lg flex justify-center items-center hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <template x-if="!loading">
                        <span>{{ __('Send Reset Code') }}</span>
                    </template>
                    <template x-if="loading">
                        <svg class="w-6 h-6 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 2v4m0 12v4m4-12h4m-12 0H4"></path>
                        </svg>
                    </template>
                </button>
            </form>

            <div class="flex items-center justify-center mt-4">
                <a href="{{ route('admin.login') }}" class="text-sm text-blue-600 hover:underline inline-flex items-center">
                    <x-heroicon-o-arrow-left class="h-4 w-4 mr-1" /> <span> {{ __('Back to Sign in') }}</span>
                </a>
            </div>
        </div>
    </div>
@endsection
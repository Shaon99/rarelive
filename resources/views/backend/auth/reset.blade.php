@extends('backend.auth.master')

@section('content')
    <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-sm p-6">
            <div class="flex flex-col items-center space-y-2">
                <x-heroicon-o-lock-closed class="w-12 h-12" />
                <h2 class="text-2xl font-bold">{{ __('Create New Password') }}</h2>
                <p class="text-gray-600 text-sm text-center">
                    {{ __('Please enter your new password below.') }}
                </p>
            </div>

            <form x-data="{ loading: false }" @submit.prevent="loading = true; $el.submit();" class="space-y-4"
                action="{{ route('admin.password.change') }}" method="POST">
                @csrf

                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-gray-700">{{ __('New Password') }}</label>
                    <div x-data="{ showPassword: false }" class="relative">
                        <input id="txtPassword" :type="showPassword ? 'text' : 'password'" name="password"
                            class="w-full px-3 py-2 border rounded-lg @error('email') border-red-500 @enderror text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
                            placeholder="Enter your password" autocomplete="off" required />
                        <button @click="showPassword = !showPassword" type="button"
                            class="absolute inset-y-0 right-2 flex items-center text-gray-600 hover:text-gray-700">
                            <template x-if="showPassword">
                                <x-heroicon-o-eye-slash class="w-4 h-4" />
                            </template>
                            <template x-if="!showPassword">
                                <x-heroicon-o-eye class="w-4 h-4" />
                            </template>
                        </button>
                    </div>
                    @error('password')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-gray-700">{{ __('Confirm New Password') }}</label>
                    <div x-data="{ showPassword: false }" class="relative">
                        <input id="txtPassword" :type="showPassword ? 'text' : 'password'" name="password_confirmation"
                            class="w-full px-3 py-2 border rounded-lg @error('email') border-red-500 @enderror text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
                            placeholder="Confirm your new password" autocomplete="off" required />
                        <button @click="showPassword = !showPassword" type="button"
                            class="absolute inset-y-0 right-2 flex items-center text-gray-600 hover:text-gray-700">
                            <template x-if="showPassword">
                                <x-heroicon-o-eye-slash class="w-4 h-4" />
                            </template>
                            <template x-if="!showPassword">
                                <x-heroicon-o-eye class="w-4 h-4" />
                            </template>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" :disabled="loading"
                    class="w-full bg-gray-900 text-white py-2 mb-5 rounded-lg flex justify-center items-center hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <template x-if="!loading" x-cloak>
                        <span>{{ __('Reset Password') }}</span>
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
            <div class="flex flex-col items-center justify-center mt-2">
                <a href="{{ route('admin.login') }}"
                    class="mt-4 text-sm text-blue-600 hover:underline inline-flex items-center">
                    <x-heroicon-o-arrow-left class="h-4 w-4 mr-1" /> <span> {{ __('Back to Sign in') }}</span>
                </a>
            </div>
        </div>
    </div>
@endsection
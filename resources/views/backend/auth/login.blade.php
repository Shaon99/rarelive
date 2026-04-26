@extends('backend.auth.master')
@push('styles')
    <style>
        .custom-checkbox {
            position: relative;
            width: 20px;
            height: 20px;
        }

        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            height: 0;
            width: 0;
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: white;
            border: 2px solid #E5E7EB;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .custom-checkbox input:checked~.checkmark {
            background-color: black;
            border-color: black;
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .custom-checkbox input:checked~.checkmark:after {
            display: block;
        }

        /* Add these styles after your existing styles */
        .crypto-bg {
            background: radial-gradient(circle at center,
                    rgba(18, 19, 23, 0.9) 0%,
                    rgba(12, 13, 16, 1) 50%,
                    rgba(8, 9, 11, 1) 100%);
            /* background: radial-gradient(circle at center, rgb(181 161 255) 0%,
                                                    rgb(132 101 255) 50%,
                                                    rgb(169 143 255) 100%); */
            position: relative;
        }

        .card-dark {
            background: linear-gradient(145deg, rgba(18, 19, 23, 0.6), rgba(12, 13, 16, 0.8));
            border: 1px solid rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
        }

        /* Update chart styles */
        .chart-line {
            filter: drop-shadow(0 0 8px rgba(204, 255, 0, 0.3));
        }

        .chart-gradient {
            background: linear-gradient(180deg,
                    rgba(204, 255, 0, 0.15) 0%,
                    rgba(204, 255, 0, 0.05) 50%,
                    rgba(204, 255, 0, 0) 100%);
        }
    </style>
@endpush
@section('content')
    <div class="flex min-h-screen">
        <div class="w-full md:w-1/2 py-6 px-4 sm:px-8 md:px-16 flex flex-col justify-center">
            <div class="absolute top-2 left-1/2 transform -translate-x-1/2 text-center w-full z-50 md:hidden">
                <a href="https://optimosell.com" target="_blank"
                    class="flex items-center justify-center gap-2 text-xs font-semibold text-gray-400 transition-colors duration-200 hover:text-orange-400 hover:underline group">
                    Powered by 
                    <x-heroicon-o-bolt class="w-3 h-3 text-orange-400" /> 
                    Optimosell.com
                </a>
            </div>
            <div class="mb-1">
                <img src="{{ getFile('logo', $general->logo) }}"
                    class="h-16 w-16 sm:h-20 sm:w-20 md:h-24 md:w-24 rounded-full object-cover">
            </div>
            <div class="mb-4">
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2">
                    Welcome back,<br>{{ $general->sitename ?? '' }}
                </h1>
                 <p class="text-gray-500">We are glad to see you again! Please, Enter your credential</p>
            </div>
            @if (session('error'))
                <div class="flex items-center justify-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-zinc-800 dark:text-red-400"
                    role="alert">
                    <x-heroicon-o-shield-exclamation class="w-6 h-6 text-red-500 mr-2" />
                    <span class="font-medium text-center w-full capitalize">{{ session('error') }}</span>
                </div>
            @endif
            <form x-data="{ loading: false }" @submit.prevent="loading = true; $el.submit();"
                action="{{ route('admin.login') }}" method="post">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label for="username"
                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email Or Username') }}
                        </label>
                        <input type="text" value="{{ old('email', request()->cookie('remember_email')) }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-gray-400 focus:ring-0 transition-colors
                        @error('email') border-red-500 @enderror"
                            name="email" placeholder="Enter email or username" autocomplete="email" required />
                        @error('email')
                            <small class="text-red-500">{{ $message }}</small>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">{{ __('Password') }}
                        </label>
                        <div x-data="{ showPassword: false }" class="relative">
                            <input id="txtPassword" value="{{ request()->cookie('remember_password') }}"
                                :type="showPassword ? 'text' : 'password'" name="password"
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-gray-400 focus:ring-0 transition-colors @error('password') border-red-500 @enderror"
                                placeholder="Enter password" autocomplete="off" required />
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
                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-3 cursor-pointer">

                            <div class="custom-checkbox">

                                <input type="checkbox" name="remember"
                                    {{ request()->cookie('remember_me') ? 'checked' : '' }}>

                                <span class="checkmark"></span>

                            </div>

                            <span class="text-sm text-gray-600">Remember me</span>

                        </label>
                        <a href="{{ route('admin.password.reset') }}"
                            class="text-sm text-gray-600 hover:text-gray-800">Forgot Password?</a>
                    </div>

                    <button type="submit" :disabled="loading"
                        class="w-full py-3 bg-black text-white rounded-xl hover:bg-gray-800 transition-colors flex justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="!loading">
                            <span>{{ __('Sign In') }}</span>
                        </template>
                        <template x-if="loading">
                            <svg class="w-6 h-6 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 2v4m0 12v4m4-12h4m-12 0H4"></path>
                            </svg>
                        </template>
                    </button>
                </div>
            </form>
        </div>
        <div class="hidden md:flex flex-1 crypto-bg p-6 items-center relative overflow-hidden">
            <div class="absolute top-4 left-1/2 transform -translate-x-1/2 z-50">
                <a href="https://optimosell.com" target="_blank"
                    class="flex items-center gap-2 text-white font-normal text-sm tracking-wide transition-colors duration-200 hover:text-accent hover:underline">
                  Powered by  <x-heroicon-o-bolt class="w-5 h-5 text-accent" />  Optimosell.com
                </a>
            </div>
            <!-- Stats Section -->
            <div class="grid grid-cols-2 gap-6">
                <!-- Chart Card -->
                <!-- Chart Card -->
                <div class="card-dark rounded-2xl p-6 relative">
                    <!-- Chart Background Grid -->
                    <div class="absolute inset-0 grid grid-cols-4 gap-4 p-6 opacity-5">
                        <div class="border-r border-t border-gray-400"></div>
                        <div class="border-r border-t border-gray-400"></div>
                        <div class="border-r border-t border-gray-400"></div>
                        <div class="border-t border-gray-400"></div>
                    </div>

                    <!-- Chart Content -->
                    <div class="relative z-10">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <p class="text-gray-400 text-sm">Sales Growth</p>
                                <p class="text-white text-2xl font-bold">Boost Revenue Significantly</p>
                                <span class="text-accent">Scale Your Business Daily</span>
                            </div>
                        </div>

                        <!-- Chart Area -->
                        <div class="relative h-40 mt-4">
                            <div class="absolute inset-x-0 bottom-0 h-32 chart-gradient rounded-lg"></div>
                            <svg class="absolute inset-0 w-full h-32 chart-line" preserveAspectRatio="none">
                                <path d="M0,100 C50,90 100,60 150,80 S250,50 300,40 T400,70" fill="none"
                                    stroke="rgb(204, 255, 0)" stroke-width="2" />
                            </svg>
                            <!-- Active Point -->
                            <div class="absolute bottom-[45%] left-[75%] flex items-center justify-center">
                                <div class="w-3 h-3 bg-accent rounded-full"></div>
                                <div class="absolute w-5 h-5 bg-accent/30 rounded-full animate-ping"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-accent rounded-2xl p-6 space-y-4">
                    <h3 class="text-primary text-xl font-semibold">Boost Your Sales & Revenue</h3>
                    <div class="space-y-3">
                        <p class="text-gray-700 text-sm">
                            Transform your business with Real-Time Insights, AI-Powered Analytics, Smart Automation, and
                            Seamless Integration for
                            E-commerce and inventory management solutions.
                        </p>
                        <ul class="text-gray-700 text-sm list-disc list-inside space-y-1">
                            <li>Generate detailed analytics for data-driven decisions</li>
                            <li>Enterprise-grade security with scalable infrastructure that grows with your business.</li>
                        </ul>
                    </div>
                    <a href="https://optimosell.com/" target="_blank"
                        class="inline-flex items-center text-primary font-medium hover:underline">
                        Start Growing Today
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
                <div class="card-dark rounded-2xl p-6">
                    <div class="flex items-center gap-2 text-accent mb-2">
                        <span class="text-2xl">|</span>
                        <span class="text-xl font-semibold">Secure, Scalable & Affordable</span>
                    </div>
                    <p class="text-gray-400 text-sm">
                        Secure, scalable, and affordable e-commerce revenue solutions with optimized sales and inventory
                        management.
                    </p>
                </div>
                <!-- Platform Info -->
                <div class="space-y-4">
                    <h3 class="text-2xl font-bold text-white">
                        Trusted <span class="text-accent">platform</span><br>
                        anytime & <span class="text-gray-400">anywhere.</span>
                    </h3>
                    <div class="flex gap-2">
                        <svg class="w-6 h-6 text-accent" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <svg class="w-6 h-6 text-accent" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <svg class="w-6 h-6 text-accent" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <svg class="w-6 h-6 text-accent" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <svg class="w-6 h-6 text-accent" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                    <div class="flex items-center gap-8">

                        <a href="https://optimosell.com/" target="_blank"
                            class="bg-accent text-primary px-6 py-3 rounded-lg font-medium inline-block"> Get Started </a>
                        <a href="https://optimosell.com/" target="_blank" class="text-gray-400 hover:text-white">
                            Contact Support ?
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

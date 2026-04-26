@extends('backend.auth.master')
@section('content')
    <div class="min-h-screen bg-gray-100 flex flex-col justify-center items-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-sm p-6">
            <div class="flex flex-col items-center space-y-2">
                <x-heroicon-o-shield-check class="w-12 h-12" />
                <h2 class="text-2xl font-bold">{{ __('Enter Verification Code') }}</h2>
                <p class="text-gray-600 text-sm text-center">
                    {{ __('We\'ve sent a 6-digit code to your email. Enter it below to verify your identity.') }}
                </p>
            </div>

            <form x-cloak x-data="{ loading: false }" @submit.prevent="loading = true; $el.submit();" class="space-y-4"
                action="{{ route('admin.password.verify.code') }}" method="POST">
                @csrf
                <div class="space-y-2">
                    <div x-data="otpInput()" class="flex justify-center space-x-2">
                        <template x-for="(digit, index) in otp" :key="index">
                            <input :ref="'otp-input-' + index" type="text" x-model="otp[index]" maxlength="1"
                                name="code[]"
                                class="w-12 h-12 text-center border rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
                                @paste="handlePaste($event, index)" required />
                        </template>
                    </div>
                    @error('code')
                        <small class="text-red-500">{{ $message }}</small>
                    @enderror
                </div>
                <button type="submit" :disabled="loading"
                    class="w-full bg-gray-900 text-white py-2 mb-5 rounded-lg flex justify-center items-center hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <template x-if="!loading">
                        <span>{{ __('Verify') }}</span>
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
            <div class="flex flex-col items-center justify-center mt-5">
                <p className="text-sm text-center text-gray-600">
                    {{ __('Didn\'t receive the code?') }}
                    <a href="{{ route('admin.password.reset') }}"
                        class="text-sm text-blue-600 hover:underline inline-flex items-center">
                        {{ __('Resend Code') }}
                    </a>
                </p>
                <a href="{{ route('admin.login') }}"
                    class="mt-4 text-sm text-blue-600 hover:underline inline-flex items-center">
                    <x-heroicon-o-arrow-left class="h-4 w-4 mr-1" /> <span> {{ __('Back to Sign in') }}</span>
                </a>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function otpInput() {
            return {
                otp: Array(6).fill(''),
                handlePaste(event, index) {
                    let pastedValue = event.clipboardData.getData('text').slice(0, this.otp.length - index);
                    this.otp.splice(index, pastedValue.length, ...pastedValue.split(''));
                    this.$nextTick(() => {
                        if (this.otp[index + pastedValue.length] !== undefined) {
                            this.$refs[`otp-input-${index + pastedValue.length}`].focus();
                        }
                    });
                    event.preventDefault();
                }
            }
        }
    </script>
@endpush
@extends('layouts.guest')

@section('content')
<div class="container mx-auto my-8">
    <h1 class="text-3xl font-bold mb-4">{{ __('Cart') }}</h1>

    @if (count($cart) > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($cart as $item)
        <div class="bg-white rounded-lg shadow-md p-4">
            <h2 class="text-lg font-bold mb-2">{{ $item['title'] }}</h2>
            <p class="text-gray-600 mb-4">{{ $item['description'] }}</p>
            @if ($item['type'] === 'lesson')
            <div class="flex justify-between items-center mb-4">
                <p class="text-gray-800 font-bold">{{ $item['monthly_price'] }} ر.س / شهريا</p>
                <p class="text-gray-800 font-bold">{{ $item['yearly_price'] }} ر.س / سنويا</p>
            </div>
            @else
            <p class="text-gray-800 font-bold mb-4">{{ $item['price'] }} ر.س</p>
            @endif
            <div class="flex justify-between items-center">
                @if ($item['type'] === 'lesson')
                <select class="border rounded-md px-2 py-1 mr-2 plan-selector" data-item-id="{{ $item['id'] }}" data-type="{{ $item['type'] }}">
                    <option value="monthly">{{ __('Monthly') }}</option>
                    <option value="yearly">{{ __('Yearly') }}</option>
                </select>
                @endif
                <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded remove-from-cart" data-item-id="{{ $item['id'] }}" data-type="{{ $item['type'] }}">
                    {{ __('Remove') }}
                </button>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <p>{{ __('Your cart is empty.') }}</p>
    @endif

    <div class="mt-8 bg-white rounded-lg shadow-md p-4">
        @if (Auth::check())
        <p class="text-gray-800 font-bold mb-2">{{ __('Welcome, :name!', ['name' => Auth::user()->name]) }}</p>
        @else
        <form class="space-y-4" id="login-form">
            <div>
                <label for="email" class="block text-gray-800 font-bold mb-2">{{ __('Email') }}</label>
                <input type="email" id="email" name="email" class="border rounded-md px-2 py-1 w-full" required>
            </div>
            <div>
                <label for="password" class="block text-gray-800 font-bold mb-2">{{ __('Password') }}</label>
                <input type="password" id="password" name="password" class="border rounded-md px-2 py-1 w-full" required>
            </div>
            <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Login') }}
            </button>
        </form>
        @endif

        <div class="mt-4">
            <label for="coupon" class="block text-gray-800 font-bold mb-2">{{ __('Coupon Code') }}</label>
            <input type="text" id="coupon" name="coupon" class="border rounded-md px-2 py-1 w-full">
        </div>

        <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mt-4 w-full">
            {{ __('Place Order') }}
        </button>
    </div>
</div>
@endsection
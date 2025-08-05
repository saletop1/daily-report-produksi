@extends('layouts.app') {{-- atau sesuaikan layout kamu --}}

@section('content')
<div class="flex min-h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md p-4">
        <div class="text-xl font-semibold mb-6">
            Dashboard
        </div>
        <ul class="space-y-2">
            <li><a href="#" class="block p-2 hover:bg-blue-100 rounded">Overview</a></li>
            <li><a href="#" class="block p-2 hover:bg-blue-100 rounded">Transfer WHFG</a></li>
            <li><a href="#" class="block p-2 hover:bg-blue-100 rounded">Total QTY</a></li>
            <li><a href="#" class="block p-2 hover:bg-blue-100 rounded">Total GR</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-6">
        <div class="bg-white rounded shadow p-6">
            <div class="text-lg font-bold mb-4 text-center">{{ $date }}</div>

            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-sm font-semibold text-gray-600">GR</div>
                    <div class="text-2xl font-bold text-blue-600">{{ $gr }}</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-600">TRANSFER WHFG</div>
                    <div class="text-2xl font-bold text-gray-800">{{ $transfer_whfg }}</div>
                </div>
                <div>
                    <div class="text-sm font-semibold text-gray-600">TOTAL GR QTY PRO</div>
                    <div class="text-2xl font-bold text-green-600">{{ $total_gr_qty_pro }}</div>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 text-center mt-6">
                <div></div>
                <div>
                    <div class="text-sm font-semibold text-gray-600">TOTAL QTY PRO</div>
                    <div class="text-2xl font-bold text-purple-600">{{ $total_qty_pro }}</div>
                </div>
                <div></div>
            </div>
        </div>
    </main>
</div>
@endsection

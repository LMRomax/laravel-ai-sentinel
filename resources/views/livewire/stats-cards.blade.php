<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

    <!-- Today's Cost -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Today's Cost</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">${{ $todayCost }}</p>
            </div>
            <div class="p-3 bg-blue-50 rounded-full">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <p class="text-sm text-gray-500 mt-4">{{ $todayRequests }} requests</p>
    </div>

    <!-- This Month -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">This Month</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">${{ $monthCost }}</p>
            </div>
            <div class="p-3 bg-green-50 rounded-full">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>
        <p class="text-sm text-gray-500 mt-4">{{ $monthRequests }} requests</p>
    </div>

    <!-- Average Cost -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Avg per Request</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">
                    ${{ $monthRequests > 0 ? number_format($monthCost / $monthRequests, 4) : '0.0000' }}
                </p>
            </div>
            <div class="p-3 bg-purple-50 rounded-full">
                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
        </div>
        <p class="text-sm text-gray-500 mt-4">Based on month data</p>
    </div>

    <!-- Alert Status -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Monthly Limit</p>
                @php
                    $limit = config('ai-guard.alerts.monthly_limit', 1000);
                    $percentage = ($monthCost / $limit) * 100;
                @endphp
                <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($percentage, 0) }}%</p>
            </div>
            <div class="p-3 {{ $percentage > 80 ? 'bg-red-50' : 'bg-yellow-50' }} rounded-full">
                <svg class="w-8 h-8 {{ $percentage > 80 ? 'text-red-600' : 'text-yellow-600' }}" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>
        <p class="text-sm text-gray-500 mt-4">${{ $monthCost }} / ${{ $limit }}</p>
    </div>

</div>

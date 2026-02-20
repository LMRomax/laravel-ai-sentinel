<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

    <!-- Today's Cost -->
    <div class="stat-card relative overflow-hidden bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
        <!-- Gradient accent -->
        <div
            class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-400/20 to-transparent rounded-full -mr-16 -mt-16">
        </div>

        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/50">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-3 py-1 rounded-full">Today</span>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Today's Cost</p>
                <p class="text-3xl font-bold text-gray-900">${{ $todayCost }}</p>
                <p class="text-sm text-gray-500 mt-2">
                    <span class="font-medium text-gray-700">{{ $todayRequests }}</span> requests
                </p>
            </div>
        </div>
    </div>

    <!-- This Month -->
    <div class="stat-card relative overflow-hidden bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
        <div
            class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-green-400/20 to-transparent rounded-full -mr-16 -mt-16">
        </div>

        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="p-3 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg shadow-green-500/50">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <span class="text-xs font-semibold text-green-600 bg-green-50 px-3 py-1 rounded-full">Month</span>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">This Month</p>
                <p class="text-3xl font-bold text-gray-900">${{ $monthCost }}</p>
                <p class="text-sm text-gray-500 mt-2">
                    <span class="font-medium text-gray-700">{{ $monthRequests }}</span> requests
                </p>
            </div>
        </div>
    </div>

    <!-- Average Cost -->
    <div class="stat-card relative overflow-hidden bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
        <div
            class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-400/20 to-transparent rounded-full -mr-16 -mt-16">
        </div>

        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="p-3 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg shadow-purple-500/50">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <span class="text-xs font-semibold text-purple-600 bg-purple-50 px-3 py-1 rounded-full">Average</span>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Avg per Request</p>
                <p class="text-3xl font-bold text-gray-900">
                    ${{ $monthRequests > 0 ? number_format($monthCostRaw / $monthRequests, 4) : '0.0000' }}
                </p>
                <p class="text-sm text-gray-500 mt-2">Based on month data</p>
            </div>
        </div>
    </div>

    <!-- Monthly Limit -->
    <div class="stat-card relative overflow-hidden bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
        @php
            $limit = config('ai-guard.alerts.monthly_limit', 1000);
            $percentage = $limit > 0 ? ($monthCostRaw / $limit) * 100 : 0;
            $isWarning = $percentage > 80;
        @endphp

        <div
            class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-{{ $isWarning ? 'red' : 'amber' }}-400/20 to-transparent rounded-full -mr-16 -mt-16">
        </div>

        <div class="relative">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="p-3 bg-gradient-to-br from-{{ $isWarning ? 'red' : 'amber' }}-500 to-{{ $isWarning ? 'red' : 'orange' }}-600 rounded-xl shadow-lg shadow-{{ $isWarning ? 'red' : 'amber' }}-500/50">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <span
                    class="text-xs font-semibold text-{{ $isWarning ? 'red' : 'amber' }}-600 bg-{{ $isWarning ? 'red' : 'amber' }}-50 px-3 py-1 rounded-full">
                    {{ $isWarning ? 'Alert' : 'Safe' }}
                </span>
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500 mb-1">Monthly Limit</p>
                <p class="text-3xl font-bold text-gray-900">{{ number_format($percentage, 0) }}%</p>
                <p class="text-sm text-gray-500 mt-2">${{ $monthCost }} / ${{ number_format($limit) }}</p>
            </div>

            <!-- Progress bar -->
            <div class="mt-4 bg-gray-100 rounded-full h-2 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-{{ $isWarning ? 'red' : 'amber' }}-500 to-{{ $isWarning ? 'red' : 'orange' }}-600 rounded-full transition-all duration-500"
                    style="width: {{ min($percentage, 100) }}%"></div>
            </div>
        </div>
    </div>

</div>

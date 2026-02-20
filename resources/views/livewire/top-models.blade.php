<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Top Models</h3>
                <p class="text-sm text-gray-500 mt-1">Most used this month</p>
            </div>
            <div class="p-2 bg-white rounded-lg shadow-sm border border-gray-100">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Provider
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Model
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Requests
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Total Cost
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Avg Cost
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Tokens
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-50">
                @forelse($topModels as $index => $model)
                    <tr class="hover:bg-gradient-to-r hover:from-blue-50/30 hover:to-purple-50/30 transition-all duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="provider-badge inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm
                                @if ($model->provider === 'openai') bg-gradient-to-r from-green-500 to-emerald-600 text-white
                                @elseif($model->provider === 'anthropic') bg-gradient-to-r from-purple-500 to-purple-600 text-white
                                @elseif($model->provider === 'groq') bg-gradient-to-r from-orange-500 to-orange-600 text-white
                                @elseif($model->provider === 'google') bg-gradient-to-r from-blue-500 to-blue-600 text-white
                                @else bg-gradient-to-r from-gray-500 to-gray-600 text-white @endif">
                                {{ ucfirst($model->provider) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 max-w-xs truncate" title="{{ $model->model }}">
                            <div class="flex items-center space-x-2">
                                @if($index === 0)
                                    <span class="text-yellow-500">🥇</span>
                                @elseif($index === 1)
                                    <span class="text-gray-400">🥈</span>
                                @elseif($index === 2)
                                    <span class="text-amber-600">🥉</span>
                                @endif
                                <span>{{ $model->model }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                            {{ number_format($model->request_count) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600 text-right">
                            ${{ number_format($model->total_cost, 4) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right font-mono">
                            ${{ number_format($model->avg_cost, 6) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">
                            {{ number_format($model->total_tokens_input + $model->total_tokens_output) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="text-sm font-medium text-gray-500">No data available yet</p>
                                <p class="text-xs text-gray-400 mt-1">Start tracking your AI requests!</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
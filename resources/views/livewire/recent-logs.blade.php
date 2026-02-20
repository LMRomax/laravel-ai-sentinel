<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Recent Logs</h3>
                <p class="text-sm text-gray-500 mt-1">Latest AI requests</p>
            </div>
            <div class="p-2 bg-white rounded-lg shadow-sm border border-gray-100">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Provider
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Model
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Prompt
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Tokens
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Cost
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                        Duration
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-50">
                @forelse($logs as $log)
                    <tr class="hover:bg-gradient-to-r hover:from-blue-50/30 hover:to-purple-50/30 transition-all duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                            {{ $log->created_at->format('M d, H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="provider-badge inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm
                                @if ($log->provider === 'openai') bg-gradient-to-r from-green-500 to-emerald-600 text-white
                                @elseif($log->provider === 'anthropic') bg-gradient-to-r from-purple-500 to-purple-600 text-white
                                @elseif($log->provider === 'groq') bg-gradient-to-r from-orange-500 to-orange-600 text-white
                                @elseif($log->provider === 'google') bg-gradient-to-r from-blue-500 to-blue-600 text-white
                                @else bg-gradient-to-r from-gray-500 to-gray-600 text-white @endif">
                                {{ ucfirst($log->provider) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 max-w-xs truncate" title="{{ $log->model }}">
                            {{ Str::limit($log->model, 30) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-md truncate" title="{{ $log->prompt }}">
                            {{ Str::limit($log->prompt, 60) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            <span class="text-gray-400 font-mono">{{ number_format($log->tokens_input) }}</span>
                            <span class="text-gray-400 mx-1">/</span>
                            <span class="text-gray-900 font-mono font-semibold">{{ number_format($log->tokens_output) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600 text-right font-mono">
                            ${{ number_format($log->cost, 6) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right font-mono">
                            @if ($log->duration_ms)
                                {{ number_format($log->duration_ms) }}ms
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm font-medium text-gray-500">No logs yet</p>
                                <p class="text-xs text-gray-400 mt-1">Start tracking your AI requests!</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Premium -->
    @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing <span class="font-semibold text-gray-900">{{ $logs->firstItem() }}</span> 
                    to <span class="font-semibold text-gray-900">{{ $logs->lastItem() }}</span> 
                    of <span class="font-semibold text-gray-900">{{ $logs->total() }}</span> results
                </div>
                
                <div class="flex items-center space-x-2">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
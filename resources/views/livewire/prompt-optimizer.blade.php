<div class="max-w-5xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-3 mb-2">
            <div class="p-2 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 via-blue-900 to-purple-900 bg-clip-text text-transparent">
                    Prompt Optimizer
                </h1>
                <p class="text-gray-600 mt-1">Reduce token usage and save money on AI API calls</p>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        
        <!-- Input Section -->
        <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <label class="block text-sm font-bold text-gray-700 mb-3">
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Original Prompt
                </span>
            </label>
            <textarea 
                wire:model="prompt"
                rows="6"
                placeholder="Enter your prompt here... (e.g., 'Please can you help me understand how Laravel works?')"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none font-mono text-sm"
            ></textarea>
            @error('prompt')
                <p class="mt-2 text-sm text-red-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
            @enderror

            <div class="flex items-center justify-between mt-4">
                <p class="text-xs text-gray-500">
                    <span class="font-semibold">Tip:</span> The optimizer removes filler words and compresses your prompt
                </p>
                <div class="flex space-x-3">
                    <button 
                        wire:click="clear"
                        type="button"
                        class="px-4 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                    >
                        Clear
                    </button>
                    <button 
                        wire:click="optimize"
                        wire:loading.attr="disabled"
                        type="button"
                        class="px-6 py-2 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 rounded-lg shadow-lg shadow-blue-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2"
                    >
                        <span wire:loading.remove wire:target="optimize">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </span>
                        <span wire:loading wire:target="optimize">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span>Optimize</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        @if($result)
            <div class="p-6 space-y-6">
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Tokens Saved -->
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-green-600 uppercase">Tokens Saved</p>
                                <p class="text-2xl font-bold text-green-900 mt-1">{{ $result['tokens_saved'] }}</p>
                            </div>
                            <div class="p-3 bg-green-500 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-green-700 mt-2">
                            {{ $result['tokens_original'] }} → {{ $result['tokens_optimized'] }} tokens
                        </p>
                    </div>

                    <!-- Compression Ratio -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-blue-600 uppercase">Compression</p>
                                <p class="text-2xl font-bold text-blue-900 mt-1">{{ $result['compression_ratio'] }}%</p>
                            </div>
                            <div class="p-3 bg-blue-500 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-blue-700 mt-2">Less tokens = lower cost</p>
                    </div>

                    <!-- Cost Estimate -->
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-4 border border-purple-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-purple-600 uppercase">Est. Savings</p>
                                <p class="text-2xl font-bold text-purple-900 mt-1">
                                    ${{ number_format(($result['tokens_saved'] / 1000) * 0.0025, 6) }}
                                </p>
                            </div>
                            <div class="p-3 bg-purple-500 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-purple-700 mt-2">Per request (GPT-4o)</p>
                    </div>
                </div>

                <!-- Optimized Output -->
                <div class="bg-gradient-to-br from-gray-50 to-blue-50 rounded-xl p-5 border border-gray-200">
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-bold text-gray-700 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Optimized Prompt
                        </label>
                        <button
                            onclick="navigator.clipboard.writeText('{{ addslashes($result['optimized']) }}'); 
                                     this.innerHTML = '<svg class=\'w-4 h-4\' fill=\'currentColor\' viewBox=\'0 0 20 20\'><path d=\'M9 2a1 1 0 000 2h2a1 1 0 100-2H9z\'/><path fill-rule=\'evenodd\' d=\'M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z\'/></svg> Copied!';
                                     setTimeout(() => this.innerHTML = '<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z\'/></svg> Copy', 2000);"
                            class="flex items-center space-x-1 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-100 hover:bg-blue-200 rounded-lg transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span>Copy</span>
                        </button>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200 font-mono text-sm text-gray-900">
                        {{ $result['optimized'] }}
                    </div>
                </div>

                <!-- Comparison -->
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                    <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Before vs After
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Original</p>
                            <div class="bg-white rounded-lg p-3 border border-gray-200 text-xs text-gray-600 font-mono">
                                {{ $result['original'] }}
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-green-600 uppercase mb-2">Optimized</p>
                            <div class="bg-green-50 rounded-lg p-3 border border-green-200 text-xs text-gray-900 font-mono">
                                {{ $result['optimized'] }}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        @endif

    </div>
</div>
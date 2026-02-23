<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prompt Optimizer - AI Guard</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>

    @livewireStyles
</head>

<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen">

    <!-- Header -->
    <header class="bg-white/80 backdrop-blur-lg border-b border-gray-200/50 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <a href="/ai-guard" class="flex items-center space-x-3 hover:opacity-80 transition-opacity">
                        <div class="relative">
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl blur opacity-75">
                            </div>
                            <div class="relative bg-gradient-to-r from-blue-600 to-purple-600 p-2.5 rounded-xl">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h1
                                class="text-xl font-bold bg-gradient-to-r from-gray-900 via-blue-900 to-purple-900 bg-clip-text text-transparent">
                                AI Guard
                            </h1>
                        </div>
                    </a>

                    <div class="hidden md:flex items-center space-x-1 ml-6">
                        <a href="/ai-guard"
                            class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                            Dashboard
                        </a>
                        <a href="/ai-guard/optimizer"
                            class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg">
                            Optimizer
                        </a>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    @auth
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <livewire:ai-guard.prompt-optimizer />
    </main>

    @livewireScripts
</body>

</html>
